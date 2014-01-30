# Silverstripe DataObject (de-)serializer

## Example configuration (_config.yml)

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
    public function memberToJson() {
        $data = array();
        if ($member = Member::currentUser()) {
            $data = $member->serialize('long','medium');
        }
        $response = new SS_HTTPResponse();
        $response->setBody(Convert::array2json($data));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }
        
With above configuration, this code will produce output similar to:

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

* object (reconstruction) deserializer
* property and group override precedence
* group access control
* fine grained property access control
* url properties and groups query override (partial query)

