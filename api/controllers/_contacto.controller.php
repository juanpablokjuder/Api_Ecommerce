<?php
class Contacto{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Telefono`, `Whatsapp`, `Instagram`, `Email`
                    FROM `tbl_web_contacto`");
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
    public function editar(){
        try {
           $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            // $producto = new Productos();
            $database = new Database();
            $params = [
                'Id' => 1,
                'Telefono' => $data['telefono'] ??  "",
                'Whatsapp' => $data['whatsapp'] ??  "",
                'Instagram' => $data['instagram'] ??  "",
                'Email' => $data['email'] ??  "",
            ];
            $database->executeQuery(
                "UPDATE `tbl_web_contacto` SET
                        `Telefono` = :Telefono,
                        `Whatsapp` = :Whatsapp,
                        `Instagram` = :Instagram,
                        `Email` = :Email
                        WHERE `Id` = :Id ", $params);
            return $database;
        } catch (\Throwable $th) {
            return "Error al actualizar";
        }
    }
}