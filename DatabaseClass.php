<?php

class DatabaseClass {

    private ?mysqli $connection = null;

    /**
     * This function is called everytime this class is instantiated
     * @param string $dbhost
     * @param string $username
     * @param string $dbname
     * @param string $password
     * @throws Exception
     */
    public function __construct(
        string $dbhost = 'localhost',
        string $dbname = 'dbName',
        string $username = 'userName',
        string $password = 'password') {
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
     * @param string $query
     * @param array $params
     * @return int|string
     * @throws Exception
     */
    public function insert(string $query = '', array $params = []): int|string
    {
	    try {
		    $stmt = $this->executeStatement($query, $params);
            $stmt->close();
		
            return $this->connection->insert_id;
		}
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }
    }

    /**
     * Select a row/s in a database table
     * @param string $query
     * @param array $params
     * @return bool|array
     * @throws Exception
     */
    public function select(string $query = '', array $params = []): bool|array
    {
        try {
            $stmt = $this->executeStatement($query, $params);
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);				
            $stmt->close();
		
            return $result;
        }
        catch(Exception $e) {
            throw New Exception($e->getMessage());
        }
    }

    /**
     * Update a row/s in a database table
     * @param string $query
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function update(string $query = '', array $params = []): bool
    {
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
     * @param string $query
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function remove(string $query = '', array $params = []): bool
    {
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
     * @param string $query
     * @param array $params
     * @return mysqli_stmt
     * @throws Exception
     */
    private function executeStatement(string $query = '', array $params = []): mysqli_stmt
    {
        try {
            $stmt = $this->connection->prepare($query);
            if($stmt === false) {
                throw New Exception('Unable to do prepared statement: '. $query);
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