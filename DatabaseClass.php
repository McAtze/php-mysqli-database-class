<?php

class DatabaseClass {

    private $connection = null;

    /**
     * This function is called everytime this class is instantiated
     */
    public function __construct($dbhost = 'localhost', $dbname = 'databaseName', $username = 'userName', $password = '') {
        try {
            $this->connection = new mysqli($dbhost, $username, $password, $dbname);
            if(mysqli_connect_errno()) {
                throw new Exception('Could not connect to database.');
            }
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());   
        }
    }

    /**
     * Insert a row/s in a database table
     */
    public function insert($query = '', $params = []) {
	    try {
		    $stmt = $this->executeStatement($query, $params);
            $stmt->close();
		
            return $this->connection->insert_id;
		}
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }
	
        return false;	
    }

    /**
     * Select a row/s in a database table
     */
    public function select($query = '', $params = []) {
        try {
            $stmt = $this->executeStatement($query, $params);
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);				
            $stmt->close();
		
            return $result;
        }
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }
	
        return false;
    }

    /**
     * Update a row/s in a database table
     */
    public function update($query = '', $params = []) {
        try {
            $this->executeStatement($query, $params)->close();		
        }
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }
	
        return false;
    }		

    /**
     * Remove a row/s in a database table
     */
    public function remove($query = '', $params = []) {
        try {
            $this->executeStatement($query, $params)->close();
        }
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }
	
        return false;
    }		

    /**
     * Execute statement
     */
    private function executeStatement($query = '', $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            if($stmt === false) {
                throw New Exception('Unable to do prepared statement: .' $query);
            }		
            if($params) {
                call_user_func_array(array($stmt, 'bind_param'), $params);				
            }		
            $stmt->execute();
		
            return $stmt;		
        }
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }	
    }		
}