<?php

class Inicio{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Titulo`, `Logo`, `Icono`
                    FROM `tbl_web`");
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
                'Titulo' => $data['titulo'] ??  "",
            ];
            $database->executeQuery(
                "UPDATE `tbl_web` SET
                        `Titulo` = :Titulo
                        WHERE `Id` = :Id ", $params);
            return "Ok";
        } catch (\Throwable $th) {
            return "Error al actualizar";
        }
    }
}