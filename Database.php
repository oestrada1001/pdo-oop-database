<?php

/**
 * Name: PDO OOP Database Connection
 * Description: Database class used OOP and PDO for secure transactions. Although this class does use PDO, please validate and sanitize your data before attempting to query the database. For more information read README.md.
 * Version: 1.0.0
 * Author: Oscar Estrada
 * Author URI: https://www.oescar-estrada.com/
 * Github URI: https://github.com/oestrada1001/pdo-oop-database
 *
 * Class Database
 *
 * @array   $keys           | Dependence Injection - Keys are the credentials to connect to the Database using PDO.
 * @array   $credentials    | Contains the Database Credentials
 * @obj     $db_connection  | Contains the current connection.
 *
 * **ATTENTION**
 * 1) Credentials must comply with the following format AND INDEXES:
 *    $_CREDENTIALS = array(
 *       'driver' => 'driver_type',
 *       'host'   => 'host_name',
 *       'database' => 'db_name',
 *       'username' => 'username',
 *       'password' => 'password'
 *    );
 *
 * 2) Insert queries may have unlimited number of columns and their respected values
 *    as long as $dataset complies with the following format:
 *    $array_name = [
 *       'column_name0' => 'column_value',
 *       'column_name1' => 'column_value',
 *    ];
 *
 * 3) Select queries may have unlimited number of where clauses as long as the $conditionals
 *    complies with the following format:
 *    $array_name = [
 *       'Clause' => [ 'column_name', '=', 'haystack_value'],
 *       'And'    => [ 'column_name', '>', 'haystack_value']
 *    ];
 *
 *    Additional Notes: If you need multiple 'And' or 'Or' use different letter cases.
 *    I.E. AND, and, And, aND, anD, etc.
 *
 * The Database Class allows you to make database changes using PDO. By design,
 * the Database Class does not do any validation or sanitation.
 *
 * For debugging purposes, Errors are set to display.
 * For production, please remove attributes: 'PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION'.
 *
 */
class Database
{
    private $db_connection;

    public function __construct($_CREDENTIALS)
    {
        $this->database_connection($_CREDENTIALS);
    }

    /**
     * Returns DNS String needed to connect to database.
     *
     * @param String $driver
     * @param String $host
     * @param String $database
     * @return string
     */
    private function dns_string(String $driver, String $host, String $database)
    {
        $dns_string = $driver.':host='.$host.';dbname='.$database;

        return $dns_string;
    }

    /**
     * If the connection does not exist, it will create a new connection and set it to $db_connection.
     *
     * NOTICE:
     * For debugging purposes, Errors are set to display.
     * For production, please remove attributes: PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION.
     *
     * @param array $keys | Database Credentials
     */
    private function database_connection(Array $keys)
    {
        if(is_null($this->db_connection)){
            $dns_string = $this->dns_string($keys['driver'], $keys['host'], $keys['database']);

            try{

                $this->db_connection = new PDO($dns_string, $keys['username'], $keys['password']);
                $this->db_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(Exception $e){
                echo $e->getMessage();
            }

        }

    }

    /**
     * With the $dataset formatted properly, the strings for the columns and values needed for the
     * prepared statements will be created, bind, executed and return the last id created.
     *
     * @param String $table_name    | Table that will be modified.
     * @param array $dataset        | Dataset that will be insert to the table.
     * @return string               | Returns for the last insert created.
     */
    public function insert_into_table(String $table_name, Array $dataset)
    {
        $columns = $this->stringify_columns_for_pdo($dataset);
        $values = $this->stringify_values_for_pdo($dataset);

        $query = 'INSERT INTO '.$table_name.'('.$columns.')values(' .$values . ')';

        return $this->prepare_statement_and_execute($query, $dataset);

    }


    /**
     * Prepares Statement, Executes, and returns data depending on the type of query.
     *
     * @param String $query
     * @param array $dataset
     * @return mixed
     */
    private function prepare_statement_and_execute(String $query, Array $dataset)
    {
        $prepared_statement = $this->db_connection->prepare($query);

        $query_type = $this->strip_first_word_from_query($query);

        try{

            $prepared_statement->execute($dataset);

            switch($query_type){
                case 'SELECT':
                    return $prepared_statement->fetchAll();
                    break;
                case 'INSERT':
                    return $this->db_connection->lastInsertId();
                    break;
            }

        }catch(Exception $e){

            echo $e->getMessage();

        }
    }

    /**
     * Creates column string needed for the prepared statement.
     * String is trimmed and last comma removed before being returned.
     *
     * @param array $dataset
     * @return string
     */
    private function stringify_columns_for_pdo(Array $dataset)
    {
        $stringify_columns = null;
        foreach($dataset as $column => $value){
            $stringify_columns .= $column .', ';
        }

        return rtrim($stringify_columns, ', ');
    }

    /**
     * Creates column string needed for the prepared statement.
     * String is trimmed and last comma removed before being returned.
     *
     * @param array $dataset
     * @return string
     */
    private function stringify_values_for_pdo(Array $dataset)
    {
        $stringify_values = null;
        foreach($dataset as $column => $value){

            $stringify_values .= ':'.$column.', ';

        }

        return rtrim($stringify_values, ', ');
    }

    /**
     * Executes the Select query.
     *
     * @param String $table
     * @param String $columns
     * @param array|null $conditionals
     * @return mixed
     */
    public function select_from_table(String $table, String $columns, Array $conditionals=null)
    {
        $query_string = 'SELECT '.trim($columns).' FROM '.trim($table);

        if(!empty($conditionals)){

            $conditions_array = $this->stringify_conditionals($conditionals);

            $query_string .= ' '.$conditions_array['string'];

            $results = $this->prepare_statement_and_execute($query_string, $conditions_array['dataset']);

            return $results;

        }

        return $this->db_connection->query($query_string)->fetchAll();

    }

    /**
     * Returns an array with the stringified conditionals and an array with the columns and values.
     * String is trimmed for excess spaces at both ends.
     *
     * @param array $conditionals
     * @return array
     */
    private function stringify_conditionals(Array $conditionals)
    {
        $stringify_conditionals = null;
        $dataset = [];
        foreach($conditionals as $conditional => $inner_array){

            $stringify_conditionals .= ' '.$conditional.' ';

            foreach($inner_array as $column => $value){

                if($column == 2){

                    $stringify_conditionals .= ':'.$inner_array[0];

                    $dataset[$inner_array[0]] = trim($value);

                }else{

                    $dataset[$inner_array[0]] = trim($value);
                    $stringify_conditionals .=  trim($value);
                }
            }

        }

        $final_conditional['string'] = $stringify_conditionals;
        $final_conditional['dataset'] = $dataset;

        return $final_conditional;
    }

    /**
     * Strips and trims first word of the query.
     *
     * @param String $query
     * @return string
     */
    private function strip_first_word_from_query(String $query)
    {
        $query = explode(' ',trim($query));
        return trim($query[0]);
    }

    /**
     * Destroys Database Connection
     */
    public function __destruct()
    {
        $this->db_connection = null;
    }
}