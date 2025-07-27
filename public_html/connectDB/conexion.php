<?php
class Database {

    private $host = 'localhost';
    private $username = 'root';
    private $password = 'admin';
    private $database = 'bd_pwtt';
    private $conn;


    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function getConnection() {
        return $this->conn;
    }

    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("Error en prepare: " . $this->conn->error);
        }
        return $stmt;
    }

    public function getLastInsertId() {
        return $this->conn->insert_id;
    }

    public function rptArray($query) {
        $result = $this->conn->query($query);
        if (!$result) {
            die("Error en la consulta: " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function __destruct() {
        if ($this->conn) { 
            $this->conn->close();
        }
    }

    public function getLastError() {
        return $this->conn ? $this->conn->error : "No hay conexión a la base de datos.";
    }

}
?>
