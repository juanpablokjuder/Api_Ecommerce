<?php
class Categorias{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Nombre`
                    FROM `tbl_categorias`
                    WHERE `Estado` = 1");
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
    public function obtenerTodas(){
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'Id' => $data['id'] ?? 0,
            ];
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Nombre`, `Inicio`, `Estado`
                    FROM `tbl_categorias`
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
                'Inicio' => $data['inicio'] ?? '',
                'Estado' => $data['estado'] ?? ''
            ];
            $database = new Database();
            return $database->executeQuery(
            "INSERT INTO `tbl_categorias`
                    (`Nombre`, `Inicio`, `Estado`)
                    VALUES
                    (:Nombre, :Inicio, :Estado)", $params);
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
                'Inicio' => $data['inicio'] ?? '',
                'Estado' => $data['estado'] ?? '',
                'Id' => $data['id'] ?? ''
            ];
            $database = new Database();
            return $database->executeQuery(
            "UPDATE`tbl_categorias` SET
                    `Nombre` = :Nombre, 
                    `Inicio` = :Inicio, 
                    `Estado` = :Estado
                    WHERE `Id` = :Id", $params);
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
}