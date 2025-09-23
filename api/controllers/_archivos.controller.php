<?php
class Archivos{
    public function obtener(){
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'IdProducto' => $data['idProducto'] ?? 0
            ];
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Base64`, `Extension`, `Orden`
                    FROM `tbl_productos_archivos`
                    WHERE `IdProducto` = :IdProducto OR :IdProducto = 0", $params);
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
}