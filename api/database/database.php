<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'ecommerce';
    private $username = 'root';
    private $password = '';
    private $pdo;

    // Constructor: inicializa las propiedades y establece la conexión
    public function __construct() {
       
        try {
            $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    // Método para ejecutar consultas SQL
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            // Devuelve los resultados si es una consulta SELECT
            if (strpos(strtoupper($query), "SELECT") === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // Si es una consulta INSERT, devuelve el último ID
            if (strpos(strtoupper($query), "INSERT") === 0) {
                return $this->pdo->lastInsertId();
            }
            // Devuelve la cantidad de filas afectadas para otras consultas
            return $stmt->rowCount();
        } catch (PDOException $e) {
           respond([
            "Resultado" => "",
            "Data" => $e->getMessage()
           ]);
        }
    }

    // Método para cerrar la conexión (opcional)
    public function closeConnection() {
        $this->pdo = null;
    }
}

?>