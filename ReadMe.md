# PHP DAL


This class can simplify the access databases using PDO.

It can connect to a given database supported by the PDO extension taking an array of parameters that define the connection details.

The class can also perform several types of common actions to access the connected database like:

- Execute queries
- Call stored procedures
- Get the list of tables in a database
- Check if a table exists
- Get the table fields
- Insert records in to a table
- Update records of a table
- Delete records of a table
- List and edit records of a table in a HTML page



# Latest Updates

## criteriaUpdate
perform update with multiple criteria for where clause
## criteriaDelete
perform delete with multiple criteria for where clause

# Class Methods

## getDefaultConnection
contains the default configuration for connection
connection parameters are: host, database, user, and password
## getConnection
**@param** $DBOCFG [database configuration]
will perform a PDO connection using the configuration provided
usefull when want to use custom connection other than the default database.
## call_sp
**@param** $q string value of the query
**@param** $p [default empty array] parameters for the prepared statement
$p follow the following rule
each entry is of the form ["k"=>"value","v"=>"value"] 
where k is for the key in the query and v is for the value to attach
**@param** $c for connection (null to use the default connection)
## execute_query
**@param** $q string value of the query
**@param** $p [default empty array] parameters for the prepared statement
$p follow the following rule
each entry is of the form ["k"=>"value","v"=>"value"] 
where k is for the key in the query and v is for the value to attach
**@param** $c for connection (null to use the default connection)

> call_sp is for when calling a stored procedure or using select query (expecting array result)
> execute_query for when performing update/insert/delete or DDL queries (create, drop, ...)

## getTables
>documentation todo
## table_exist
>documentation todo
## TableFields
>documentation todo
## insert
>documentation todo
## update
>documentation todo
## criteriaUpdate
>documentation todo
## delete
>documentation todo
## criteriaDelete
>documentation todo
## genViewTable
>documentation todo
## getEditTable
>documentation todo
## getFormForTable
>documentation todo
## getViewQuery
>documentation todo
## getDALT
>documentation todo
## class DALT
>documentation todo
## function p
print on screen in user-readable format for objects and arrays

## README
AUTHOR MhmdJawadZD
README Last edit By MHMD JAWAD ZD 2020/05/03