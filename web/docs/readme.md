# Contacts
***
Storing information about the user's contacts.

##*0. Main input array*

==== Response body ====

```
{
    'full_name': '',
    'name_param': {
        {0}: {
            'value': '',
            'type': '',
        },
    },
    'nickname': '',
    'email': {
        {0}: {
            'value': '',
            'type': '',
        },
    },
    'phone': {
        {0}: {
            'value': '',
            'type': '',
        },
    },
    'address': {
        {0}: {
            'type': '',
            'country': '',
            'postcode': '',
            'provinces': '',
            'city': '',
            'address_string1': '',
            'address_string2': '',
            'post_office_box_number': '',
        },
    },
    'company_info': {
        'company': '',
        'department': '',
        'post': '',
    },
    'birthday': '',
    'sites': {
        {0}: {
            'value': '',
            'type': '',
        },
    },
    'relation': {
        {0}: {
            'value': '',
            'type': ''
        },
    },
    'chats': {
        'aim': '',
        'skype': '',
    },
    'note': '',
    'photo': '',
    'categories': {
        {0}: '',
        {1}: ''
    }
}
```

## *01. Contacts*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | Contact ID  | char (36) | 93ca1f66-28dc-4319-9ab3-8dc564bfc663 | Y
first_name | First name user | varchar (50) | Vasya | Y
last_name | Last name user | varchar (50) | Pupkin | Y
surname | Surname user | varchar (50) | Sydorovich | N
avatar | Image uploaded by the user | text | Example? | N
birthday | User's date of birth | date | 2011-11-22 | N
nickname | Username | varchar(50) | Ded Hasan | N
user_prefix | User prefix | varchar(20) | dr. | N
user_suffix | User suffix | varchar(20) | -san | N
is_favorite | User favorite | tinyint(1) | 1 | N
user_id | The ID of the user who uploaded the contact | bigint | 10 | Y
note | The user writes that he will climb into his head | text | Matyuki some | N

=== Request body ===

```
{
    'name_param': {
        {0}: {
            'value': 'Vasya',
            'type': 'first_name'
        },
        {1}: {
            'value': 'Pupkin',
            'type': 'last_name'
        },
        {2}: {
            'value': 'Sydorovich',
            'type': 'surname'
        },
        {3}: {
            'value': 'dr.',
            'type': 'user_prefix',
        },
        {4}: {
            'value': '-san',
            'type': 'user_suffix'
        }
    },
    'photo': 'http://...',
    'birthday': '2000-12-12',
    'nickname': 'Ded Hasan',
    'note': "Matyuki some"
}
```

