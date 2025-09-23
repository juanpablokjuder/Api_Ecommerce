<?php
class Colores{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Nombre`, `Imagen`
                    FROM `tbl_colores`
                    WHERE `Estado` = 1");
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
    public function obtenerTodos(){
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'Id' => $data['id'] ?? 0
            ];
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Nombre`, `Imagen`, `Estado`
                    FROM `tbl_colores`
                    WHERE `Id` = :Id OR :Id = 0", $params);
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
    public function insertar(){
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'Nombre' => $data['nombre'] ?? '',
                'Imagen' => $data['archivos'][0]['File'] ??  "",
                'Estado' => $data['estado'] ?? ''
            ];
            $database = new Database();
            return $database->executeQuery(
            "INSERT INTO `tbl_colores`
                    (`Nombre`, `Imagen`, `Estado`)
                    VALUES
                    (:Nombre, :Imagen, :Estado)", $params);
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
    public function editar(){
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'Nombre' => $data['nombre'] ?? '',
                'Imagen' => $data['archivos'][0]['File'] ??  "",
                'Estado' => $data['estado'] ?? '',
                'Id' => $data['id'] ?? ''
            ];
            $database = new Database();
            return $database->executeQuery(
            "UPDATE`tbl_colores` SET
                    `Nombre` = :Nombre, 
                    `Imagen` = :Imagen, 
                    `Estado` = :Estado
                    WHERE `Id` = :Id", $params);
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
}