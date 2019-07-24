# PDO OOP Database Connection #  

## *Attention* ##
The Database Class allows you to make database changes using PDO. <br>
By design, the Database Class does not do any validation or sanitation.

For debugging purposes, Errors are set to display. <br>
For production, please remove attributes: 'PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION'.

#### Credentials ####
The credentials are passed when when the Database class is instantiated. The credentials must comply with the following format AND INDEXES:

` $_CREDENTIALS = array(
        'driver' => 'driver_type',
        'host'   => 'host_name',
       'database' => 'db_name',
       'username' => 'username',
       'password' => 'password'
    );`
    
#### Insert Queries ####
Insert queries may have unlimited number of columns and their respected values as long as $dataset complies with the following format:

 `$array_name = [
    'column_name0' => 'column_value',
    'column_name1' => 'column_value',
 ];`
 
#### Select Queries ####
Select queries may have unlimited number of where clauses as long as the $conditionals complies with the following format:
 
 `$array_name = [
    'Clause' => [ 'column_name', '=', 'haystack_value'],
    'And'    => [ 'column_name', '>', 'haystack_value']
 ];`
   
If you need multiple 'And' or 'Or' use different letter cases. <br>
I.E. *AND, and, And, aND, anD, etc.*

**Additional Notes:**
- - - -
Following TTD and Agile Methodologies, I will be updating this class with more functionality as needed. This class follows the Single Responsibility Principle and only depending on the database credentials.