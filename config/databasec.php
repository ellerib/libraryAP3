<?php
class Database {
    private $host = "localhost";
    private $db = "librarysystem";
    private $username = "root";
    private $password = "07092003";
    public $conn;

    public function getconnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db);

        if($this->conn->connect_error){
            die ("Connection failed". $this->conn->connect_error);
        }
        return $this->conn;

    }
}
?>
