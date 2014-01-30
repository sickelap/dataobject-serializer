# Silverstripe DataObject (de-)serializer


## Example configuration (mysite/_config/config.yml)

    DataObjectSerializer: # module configuration namespace
        show_model_class: false # shorten json output
        override_precedence: merge # possible values: property, group or merge. Only merge is implemented at the moment
        Member: # DataObject
            groups: # configuration identifier
                short: # group name
                    - FirstName # property
                    - Surname
                medium:
                    - FirstName
                    - Surname
                    - Email
                    - Groups(properties:Title,Code) # relation component with serialization override to serialize speciffic fields only
                long:
                    - FirstName
                    - Surname
                    - Email
                    - Homepage
                    - Avatar(properties:Url)
                    - Groups(group:short) # relation component with serialization override to serialize speciffic group
                member_email:
                    - Email
        Group:
            groups:
                short:
                    - Title
                long:
                    - Title
                    - Code
                with_members_short:
                    - Title
                    - Code
                    - Members(group:short)
        Image:
            groups:
                long:
                    - Url
                    
                    

## Example usage:


Suppose we have following code:

  `mysite/code/MemberExtension.php`:

    class MemberExtension extends DataExtension {
        private static $db = array(
            'Homepage' => 'Varchar'
        );
        private static $has_one = array(
            'Avatar' => 'Image'
        );
    }

  `mysite/code/_config.php`:

    ...

    Member::add_extension('MemberExtension');

    ...

  `mysite/code/Page.php`:

    class Page_Controller extends ContentController {

        ...

        public function member() {
            $data = array();
            if ($member = Member::currentUser()) {
                $data = $member->serialize(array('short','long'));
            }
            $response = new SS_HTTP_Response();
            $response->setBody($data);
            $response->addHeader('Content-Type', 'application/json');
            return $response;
        }
    }


With above configuration and code, when accessing /member it will produce output similar to:

    {
        "ID": 1,
        "FirstName": "Default",
        "Surname": "Admin",
        "Email": "admin@localhost.dev",
        "Groups": [
            {
                "ID": 1,
                "Title": "Content Authors",
                "Code": "content-authors"
            },
            {
                "ID": 2,
                "Title": "Administrators",
                "Code": "administrators"
            }
        ],
        "Homepage": "http://admin.homepage.com",
        "Avatar": {
            "ID": 4,
            "Url": "/assets/Uploads/300x180-white.png"
        }
    }


# TODO

* deserialization
* nested loop protection
* property and group override precedence
* group access control
* fine grained property access control
* url properties and groups query override (partial query)

