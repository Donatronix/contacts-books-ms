# Contacts
***
Storing information about the user's contacts.

##*0. Main input array*

==== Response body ====

```
{
    'full_name': '',
    'name_param': {
        0: {
            'value': '',
            'type': '',
        },
    },
    'nickname': '',
    'email': {
        0: {
            'value': '',
            'type': '',
        },
    },
    'phone': {
        0: {
            'value': '',
            'type': '',
        },
    },
    'address': {
        0: {
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
        0: {
            'value': '',
            'type': '',
        },
    },
    'relation': {
        0: {
            'value': '',
            'type': ''
        },
    },
    'chats': {
        'aim': '',
        'skype': '',
    },
    'note': '',
    'photo': 1,
    'categories': {
        0: '',
        1: ''
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
middle_name | middle_name user | varchar (50) | Sydorovich | N
avatar | Image uploaded by the user | tinyint(1) | 1 | N
birthday | User's date of birth | date | 2011-11-22 | N
nickname | Username | varchar(50) | Ded Hasan | N
prefix_name | User prefix | varchar(20) | dr. | N
suffix_name | User suffix | varchar(20) | -san | N
is_favorite | User favorite | tinyint(1) | 1 | N
user_id | The ID of the user who uploaded the contact | bigint | 10 | Y
note | The user writes that he will climb into his head | text | Matyuki some | N

=== Request body ===

```
{
    'name_param': {
        0: {
            'value': 'Vasya',
            'type': 'first_name'
        },
        {1}: {
            'value': 'Pupkin',
            'type': 'last_name'
        },
        {2}: {
            'value': 'Sydorovich',
            'type': 'middle_name'
        },
        {3}: {
            'value': 'dr.',
            'type': 'prefix_name',
        },
        {4}: {
            'value': '-san',
            'type': 'suffix_name'
        }
    },
    'photo': 1,
    'birthday': '2000-12-12',
    'nickname': 'Ded Hasan',
    'note': "Matyuki some"
}
```

## *02. Addresses*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | Contact ID  | char (36) | 93ca1f66-28dc-4319-9ab3-8dc564bfc663 | Y
country | Country of contact | varchar(100) | UA | N
provinces | Region, state, province where the contact resides | varchar(100) | Ivano-Frankivs'ka oblast | N
city | City of residence | varchar(50) | Київ| N
address | Residence address. **Consists of two parameters**: *address string1* - street and *address_string2* - house number | text | Apollonia St, 44 | N
address_type | The type of address to be grouped, such as home. if not specified, another is specified by default | varchar(30) | another | N
postcode | The zip code of the contact | varchar(10) | 02000 | N
post_office_box_number | User PO Box | varchar(10) | 123456 | N
is_default | Default contact | tinyint(1) | 1 | N
contact_id | The uuid of the user who uploaded the contact | char(36) | 93ca1f66-28dc-4319-9ab3-8dc564bfc663 | Y

=== Request body ===

```
{
    'address': {
        0: {
            'country': 'UA',
            'postcode': '02000',
            'provinces': 'Ivano-Frankivs'ka oblast',
            'city': 'Kiev',
            'address_string1': 'Drum Street',
            'address_string2': 1,
            'post_office_box_number': '123456'
        },
    },
}
```

## *03. Сategories*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | Contact ID  | bigint | 100 | Y
parent_id | Parent ID | bigint | 200 | Y
name | Name | varchar(255) | Vasya | Y

## *04. Chats*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | Contact ID  | char(36) | 100 | Y
chat | Chat name | text | chat_name | N
is_default | Default chat name | tinyint(1) | 1 | N
chat_name | The name of the chat provided by Google contacts | varchar(30) | skype | N
contact_id | The uuid of the user's chat who uploaded the contact | char(36) | 93ca1f66-28dc-4319-9ab3-8dc564bfc663 | Y

=== Request body ===

```
{
    'chats': {
        'skype': 'chat_name',
        'type': 'value'
    },
}
```

## *05. Contact emails*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | User ID  | char(36) | 100 | Y
email | User e-mail | varchar(255) | buk@suk.com | Y
type | The type of email field, for example: home, work, etc. | varchar(30) | Home | N
is_default | Default e-mail | tinyint(1) | 1 | N
contact_id | The uuid of the contact's | char(36) | 93ca3bee-46c8-4950-b0e1-7c54b51a80d8 | Y

=== Request body ===

```
{
    'email': {
        0: {
            'type': 'home',
            'value': 'buk@suk.com'
        },
    },
}
```

## *06. Contact group*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
contact_id | Contact UUID  | char(36) | 93ca3bee-46c8-4950-b0e1-7c54b51a80d8 | Y
group_id | Group UUID | char(36) | 93ca3bee-46c8-4950-b0e1-7c54b51a80d8 | Y

## *07. Contact phones*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | User UUID  | char(36) | 93ca1f66-503b-41ff-b785-e8335d365e6e | Y
phone      | Contact phone | varchar(255) | 0681221212 | Y
type       | A field type for a phone that denotes a group, such as home | varchar(30) | home | N
is_default | Default phone | tinyint(1) | 1 | N
contact_id | Contact UUID | char(36) | 93ca3bee-5fac-4e21-96d6-8ca627618918 | Y

=== Request body ===
```
{
    'phone': {
        0: {
            'value': '+430665782389',
            'type': 'work'
        },
    },
}
```

## *08. Groups*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | User UUID  | char(36) | 93ca1f66-4753-4438-906b-f412c815f4f1 | Y
name | Name group | varchar(255) | Home | N
user_id | Contact UUID | char(36) | 93ca3bee-7612-42fe-9fee-58620860f026 | N

=== Request body ===

```
{
    'categories': {
        0: 'Work',
        1: 'myContacts'
    },
}
```
## *09. Relations*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | User UUID  | char(36) | 93ca1f66-4753-4438-906b-f412c815f4f1 | Y
is_default | Default relation | tinyint(1) | 1 | N
relation | A description of the relationship by the user | varchar(255) | gog | N
relation_name | Types of relationships from the google contacts service | varchar(30) | father | N
contact_id | Contact UUID | char(36) | 93ca1f66-28dc-4319-9ab3-8dc564bfc663 | Y

=== Request body ===
```
{
    'relation': {
        0: {
            'type': 'spouse',
            'value': 'gog',
            'contact_id': [user_id]
        }, 
    },
}
```

## *10. Sites*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | User UUID  | char(36) | 93ca1f66-4753-4438-906b-f412c815f4f1 | Y
site | Site name | varchar(255) | gen-ka.gs | N
is_default | Default site | tinyint(1) | 1 | N
site_type | Site type for grouping | varchar(30) | homepage | N
contact_id | Contact UUID | char(36) | 93ca1f66-28dc-4319-9ab3-8dc564bfc663 | Y

=== Request body ===
```
{
    'sites': {
        0: {
            'type': 'homepage',
            'value': 'gen-ka.gs',
            'contact_id': [user_id]
        },
    },
}
```

## *11. Works*

==== Parameters ====

Parameter  | Description | Type | Example | Required
---------  | ----------- | ---- | ------- | --------
id         | User UUID  | char(36) | 93ca1f66-4753-4438-906b-f412c815f4f1 | Y
'company' | Company contact | varchar(100) | kettle | N
'department' | Department of the company where the contact works | varchar(100) | best | N
'is_default' | Default site | tinyint(1) | 1 | N
'post' | User position at work | varchar(50) | nothing too | N
'contact_id' | Contact UUID | char(36) | 993ca3bee-71b3-492c-a440-7aca1e8321a8 | Y

=== Request body ===

```
{
    'company_info':{
        'company': 'kettle',
        'department': 'best',
        'post': 'nothing too',
        'contact_id': [user_id]
    },
}
```
