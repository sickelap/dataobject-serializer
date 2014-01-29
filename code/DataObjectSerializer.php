<?php

class DataObjectSerializer extends DataExtension
{

    /**
     * @return string
     */
    public function serialize()
    {
        $groups = func_get_args();
        /** @var DataObject $do */
        $do = $this->owner;
        return $this->serializeDataObject($do, $groups);
    }

    private function serializeDataObject(DataObject $do, $groups = array())
    {
        $result = array();

        $properties = $this->getProperties($do, $groups);

        foreach ($properties as $property => $propertyConfig) {
            if ($propertyConfig !== null) {
                $groups = $propertyConfig;
            }
            if ($do->hasField($property)) {
                $result[$property] = $do->$property;
            } else if ($do->$property() instanceof DataObject) {
                $result[$property] = $this->serializeDataObject($do->$property(), $groups);
            } else if ($do->$property() instanceof DataList) {
                $result[$property] = $this->serializeDataList($do->$property(), $groups);
            }
        }

        if (Config::inst()->get('DataObjectSerializer', 'show_model_class')) {
            return array($do->class => $result);
        }
        return $result;
    }

    /**
     * @param $json
     * @return Object
     */
    public function deserilize($json)
    {
        // TODO
        return DataObject::create();
    }

    /**
     * Walk DataList and serialize containing DataObjects
     *
     * @param DataList $list
     * @param array $groups
     * @return array
     */
    private function serializeDataList(DataList $list, $groups)
    {
        $result = array();
        foreach ($list as $item) {
            $result[] = $this->serializeDataObject($item, $groups);
        }
        return $result;
    }

    /**
     * Utility method to filter/merge serialization group configuration.
     * As a result it produces flat array of properties to be serialized for given DataObject.
     *
     * @param DataObject $do
     * @param mixed $groups
     * @return array
     */
    private function getProperties(DataObject $do, $groups)
    {
        $config = Config::inst()->get('DataObjectSerializer', $do->class);
        if (!$config) return null;

        $items = array('ID' => 0);

        if (is_array($groups)) {

            if (empty($groups)) {
                $fields = DataObject::custom_database_fields($do->class);
                $items = array_merge($items, $fields);

            } else {
                /**
                 * Here we have simple array of groups in form:
                 * ('group1','group2',...)
                 */
                $exposeGroups = array_intersect_key($config['groups'], array_flip($groups));
                foreach ($exposeGroups as $exposeGroup) {
                    $items = array_merge($items, array_flip($exposeGroup));
                }
            }

        } else if (is_string($groups)) {
            /**
             * TODO: implement override precedence for property and group. Now only merge option is implemented.
             */

            /**
             * example $groups:
             * group:some_group|properties:property1,property2|group:...
             */
            $segments = explode("|", $groups);
            foreach ($segments as $segment) {
                if (preg_match('/^group:([\w]+)$/', $segment, $matches)) {
                    if (array_key_exists($matches[1], $config['groups'])) {
                        $items = array_merge($items, array_flip($config['groups'][$matches[1]]));
                    }
                } elseif (preg_match('/^properties:([\w,]+)$/', $segment, $matches)) {
                    $properties = explode(',', $matches[1]);
                    $items = array_merge($items, array_flip($properties));
                }
            }
        }

        $result = array();
        foreach (array_keys($items) as $item) {
            if (preg_match('/(.+)\((.+)\)/', $item, $matches)) {
                if (array_key_exists($matches[1], $result)) {
                    $result[$matches[1]] .= '|' . $matches[2];
                } else {
                    $result[$matches[1]] = $matches[2];
                }

            } else {
                $result[$item] = '';
            }
        }
        return $result;
    }

}
