# (De-)Serializer for Silverstripe DataObject (and DataList)

## Example configuration (_config.yml)

    DataObjectSerializer: # module configuration namespace
        show_model_class: false # shorten json output
        override_precedence: merge # property, group or merge, default merge
        Member: # DataObject
            groups: # configuration identifier
                short: # group name
                    - FirstName # property
                    - Surname
                medium:
                    - FirstName
                    - Surname
                    - Email
                    - Groups(properties:Title) # property with serialization override to export speciffic fields
                long:
                    - FirstName
                    - Surname
                    - Homepage
                    - Avatar(properties:Url)
                    - Groups(group:long) # property with serialization override to export for speciffic group
                member_email:
                    - Email
        Group:
            groups:
                short:
                    - Title
                long:
                    - Title
                    - Code
                group_with_members:
                    - Title
                    - Code
                    - Members(group:short)
        Image:
            groups:
                long:
                    - Url

