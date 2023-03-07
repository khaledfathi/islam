<?php
namespace Khaled\App\Core; 

class DB {
    private $host; 
    private $port ; 
    private $user ; 
    private $password ; 
    private $databaseName ;
    
    private $conn;
    public $table; 
    public $sql = ""; 
    public $connectionStatus  ;
    public static function table ($tableName){
        $obj= new self ; 
        $obj->table = $tableName;
        return $obj ; 
    }
    private function config (){
        $this->host = getConfig('DATABASE')->HOST; 
        $this->port = getConfig('DATABASE')->PORT; 
        $this->user = getConfig('DATABASE')->USER; 
        $this->password = getConfig('DATABASE')->PASSWORD; 
        $this->databaseName = getConfig('DATABASE')->DATABASE; 
    }
    private function  connect($customConfig=false){
        if(!$customConfig)$this->config();         
        try {
            $this->conn = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->databaseName", $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connectionStatus= "Connected successfully";
        } catch(PDOException $e) {
            $this->connectionStatus= "Connection failed: " . $e->getMessage();
        }
    }
    private function customConnect(array $config){
        $this->config(); 
        $allowed = ['host', 'port' , 'user' , 'password' , 'databaseName']; 
        foreach($config as $key=>$value){
            if (in_array($key , $allowed)) {
                switch ($key){
                    case 'host':
                        $this->host = $value; 
                    break ; 
                    case 'port':
                        $this->port = $value; 
                    break ;
                    case 'user':
                        $this->user= $value; 
                    break ;
                    case 'password':
                        $this->password = $value; 
                    break ;
                    case 'databaseName':
                        $this->databaseName = $value; 
                    break ;
                }
            }            
        }
        $this->connect(true); 
    }
    private function close(){
        $this->conn= NULL ; 
    }
    public static function createDatabase(string $databaseName , array $tables){    
        $obj= new self ;
        $obj->customConnect(['databaseName'=>'']);
        $obj->conn->query("DROP DATABASE if EXISTS $databaseName"); 
        $obj->conn->query("CREATE DATABASE  $databaseName"); 
        $obj->conn->query("USE  $databaseName"); 
        if ($tables){
            foreach ($tables as $table){
                $obj->conn->query($table);
            }
        }
        $obj->close();         
    }
    public function raw (string $statment){
        $this->connect(); 
        $res = $this->conn->query($statment);
        $this->conn = NULL ; 
        return $res; 
    }
    public function insert (array $data){
        $this->connect();
        $keys = '';
        $values = '';
        foreach ($data as $key=>$value){
            $keys .= $key.',';
            $values .= '\''.$value.'\''.',';
        }
        $this->sql = "INSERT INTO $this->table ( ".rtrim($keys,',')." ) VALUES ( ".trim($values , ',')." )";
        return $this;  
    }
    public function select(string $column = '*'){
        $this->connect();
        $this->sql.="SELECT $column FROM $this->table";
        return $this;
    }
    public function delete(){
        $this->connect();
        $this->sql = "DELETE FROM $this->table ";
        return $this;
    }
    public function update(array $data){ 
        $this->connect();
        $keysValues = '';
        foreach ($data as $key=>$value){
            $keysValues .= $key.'=' .'\''.$value.'\''.',';
        }
        $this->sql = "UPDATE $this->table SET ".rtrim($keysValues , ',');
        return $this;
    }
    public function where(string $condition){
        $this->sql .= " WHERE $condition "; 
        return $this; 
    }
    public function leftJoin(string $tableToJoin , string $relation){
        $this->connect(); 
        $this->sql .= " LEFT JOIN $tableToJoin ON $relation";
        return $this;
    }
    public function rightJoin(string $tableToJoin , string $relation){
        $this->connect(); 
        $this->sql .= " RIGHT JOIN $tableToJoin ON $relation";
        return $this;
    }
    public function innerJoin(string $tableToJoin , string $relation){
        $this->connect(); 
        $this->sql .= " INNER JOIN $tableToJoin ON $relation";
        return $this;
    }
    public function first(){
        $records = $this->conn->query($this->sql.' limit 1');
        return $records->fetchObject();
    }
    public function get(){
        $records = $this->conn->query($this->sql);
        return $records->fetchAll(PDO::FETCH_CLASS);
    }
    public function  exec(){
        $this->conn->query($this->sql);
        $this->close(); 
    }

}

