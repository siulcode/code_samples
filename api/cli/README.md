# Inshur test

## CLI

The CLI  provides an easy way to query the API. We can also delete entries based on their id.

Command line help can be accessed with: `python main.py --help`

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

For a particular command help can be available with:
`python main.py COMMAND --help`

IE.:
`python main.py output_in_yaml --help`

Will output:
```
NAME
    main.py output_in_yaml - Returns all entries in yaml format

SYNOPSIS
    main.py output_in_yaml -

DESCRIPTION
    Returns all entries in yaml format
```
