# Creating a MySqli Database Class in PHP
You will learn how to create a basic OOP MySQLi class using PHP. You'll also learn how to bind the MySQLi parameters dynamically.

## Quick Explanation
What is MySQLi? It's derived from the abbreviation "My" (Co-founder Michael Widenius' daughter), "SQL" (Structured Query Language), and "i" (Improved version from MySQL)

Here's a brief explanation of some MySQLi codes that we will be using.

### Creating a MySQLi instance
```php
$instance = new mysqli(host, username, password, databasename)
```
* host [Required] - The Data Source Name that contains information required to connect to the database
* username [Required] - MySQL Username
* password [Required] - MySQL Password
* databasename [Required] - MySQL Database name

Returns a MySLQi object or false

### Preparing and Executing a MySQLi statement
`MySQLi::prepare(statement);`
* statement [Required] - An SQL statement to execute, e.g.
    ```php
    $instance->prepare('Select * from tableName');
    ```
    Returns a mysqli_stmt (MySQLi Statement)

`mysqli_stmt::execute();`
```php
$stmt = $instance->prepare('Select * from tableName');
$stmt->execute();
```
Executes the prepared statement.
* Returns Boolean

### Binding Parameters
```php
mysqli_stmt::bind_param(types, columnValue1, columnValue2, .......);
```
* types [Required] - String types of corresponding columnValues,

    Values:
    * i - integer
    * b - blob
    * d - double
    * s - string

* columnValue^ [Required] - Values to bind to our prepared statements.

Example:
```php
$stmt = $instance->prepare('Insert into TableName (IntegerColumn, StringColumn1, StringColumn2, DoubleColumn) values( ?, ?, ?, ? )');
$stmt->bind_param("issd" ,1 ,"Hello" , "World", 12.34)
```
Notice we have some ? in our Query String. Those will be replaced later on automatically by MySQLi and you don't need to worry about escaping the strings.

So we have 4 `?`, this will then be replaced by the following in our `$stmt->bind_param`
- `i` which is our ``1``
- `s` which is our ``Hello``
- `s` which is our ``World``
- `d` which is our ``12.34``

### Fetching Data from the Database
``mysqli_stmt::get_result();``
Gets all data from a successful "Select" Query.
* Returns a mysqli_result

``mysqli_result::fetch_all();``
Converts a mysqli_result to a readable object/array. so we can loop it.

### call_user_func_array('fn',mixed_array);
``fn`` - Function to call

``mixed_array`` - Arguments to pass dynamically to fn

Example:
```php
call_user_func_array('str_replace',["World","User","Hello World"]);
```
is also the same as
```php
str_replace("World","User","Hello World");
```

## Creating the Class
### Preparing our Class name, variables and functions.
```php
class DatabaseClass {
    private $connection = null;

    // this function is called everytime this class is instantiated		
    public function __construct() {}
		
    // Insert a row/s in a database table
    public function insert() {}

    // Select a row/s in a database table
    public function select() {}
    
    // Update a row/s in a database table
    public function update() {}		
    
    // Remove a row/s in a database table
    public function remove() {}		
    
    // execute statement
    private function executeStatement() {}
}
```
Now that we have a simple design for our Database class. Lets fill the functions with some codes.

### Establish the MySQL connection in the costructor
```php
// this function is called everytime this class is instantiated		
public function __construct($dbhost = 'localhost', $dbname = 'dbName', $username = 'userName', $password = '') {
    try {
        $this->connection = new mysqli($dbhost, $username, $password, $dbname);
        if(mysqli_connect_errno()) {
            throw new Exception("Could not connect to database.");   
        }
    } catch(Exception $e) {
        throw new Exception($e->getMessage());   
    }
}
```

The constructor will have 4 parameters:
* `$dbhost` - The database host.
* `$dbname` - The database name.
* `$username` - The database User.
* `$password` - The database password for the User.

### A Function that will execute all statements
```php
// execute statement
private function executeStatement($query = '', $params = []) {
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
    } catch(Exception $e) {
        throw New Exception($e->getMessage());
    }
}
```
We will be passing our SQL Statements to this function (Insert, Select, Update and Remove).

Returns `mysqli_stmt` or throws an exception if it get's an error.

The challenge here is that we have to make bind_param in our class dynamically accept any number of parameters.

We can do this by using `call_user_func_array();` of course we have to also make our parameters to look like the bind_param itself.

Example:
```php
$stmt->bind_param("issd", 1, "Hello", "World", 12.34)
```
Then we will pass to this function
```php
DatabaseClass::executeStatement("Insert ........ values ( ?, ?, ?, ? )", ["issd", 1, "Hello", "World", 12.34])
```

### Insert Function
```php
// Insert a row/s in a database table
public function insert $query = '', $params = []) {
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
```
Insert will add a row and will return an integer of the last ID inserted or throws an exception if it get's an error.

### Select Function
```php
// Select a row/s in a database table
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
```
Select will return all row/s or throws an exception if it get's an error.

### Update Function
```php
// Update a row/s in a database table
public function update($query = '', $params = []) {
    try {
        $this->executeStatement($query, $params)->close();
    } 
    catch(Exception $e) {
        throw New Exception($e->getMessage());
    }
    return false;
}
```
Update will update a row/s or throws an exception if it get's an error.

### Remove Function
```php
// Remove a row/s in a database table
public function remove($query = '', $params = []) {
    try {  
        $this->executeStatement($query, $params)->close();
    } 
    catch(Exception $e)
    {
        throw New Exception($e->getMessage());
    }
    return false;
}
```
Remove will remove a row/s or throws an exception if it get's an error.

## Using the Database Class:
### Create/Instantiate the Database Class.
```php
$db = new Database(
    "MySQLHost",
    "myDatabaseName",
    "myUserName",
    "myUserPassword"
);
```
### Insert Example
```php
$id = $db->Insert("Insert into `TableName`( `column1` , `column2`) values ( ? , ? )", ['ss', 'column1 Value', 'column2 Value']);
```

### Select Example
```php
$db->Select("Select * from TableName");
```
### Update Example
```php
$db->Update("Update TableName set `column1` = ? where id = ?", ['si', 'a new column1 value', 1]);
```
### Remove Example
```php
$db->Remove("Delete from TableName where id = ?", ['i', 1]);
```