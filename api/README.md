# Inshur test

## API

This API is serving the filtered data from the Cat warlords bunker. We are only presenting the facts from Cats that are not older than 10 years old and with their first name that begins with the odd letters from the alphabet, these are A, C, E etc.

The private API can be accessed via this URL:
https://05d2-173-63-132-127.ngrok.io/

The code for the API is located in <ROOT>/api.

##### NOTE: 
There is no physical infrastructure available or deployed in the public for this implementation. We are serving the API endpoint from our secret Cat WarLord bunker.

## CLI

The CLI  provides an easy way to query the API. We can also delete entries based on their id.

Command line help can be accessed with: `python main.py --help`

```
NAME
    main.py

SYNOPSIS
    main.py COMMAND

COMMANDS
    COMMAND is one of the following:

     output_in_json
       Returns all entries in json format

     output_in_yaml
       Returns all entries in yaml format

     output_in_table
       Returns all entries in table format

     delete_entry
       Deletes single entry based on id
```

For a particular command help can be available with:
`python main.py COMMAND --help`

## Installation

We are using a requirement file. The requirements can be installed with:
`pip install -r requirements.txt`
