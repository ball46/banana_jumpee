<?php
class DB{
    private string $host = "localhost";
    private string $username = "root";
    private string $password = "";
    private string $dbname = "banana_jumpee";
    private $conn;

    //will use PDO connection
    public function connect(): PDO{
        $conn_str = "mysql:host=$this->host;dbname=$this->dbname";
        if($this->conn == null) {
            $this->conn = new PDO($conn_str, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->conn;
    }
}