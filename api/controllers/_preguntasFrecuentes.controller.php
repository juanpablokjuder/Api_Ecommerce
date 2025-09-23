<?php

class PreguntasFrecuentes{
    public function obtener(){
        try {
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'Id' => isset($data['IdPregunta']) ? $data['IdPregunta'] : 0,
            ];
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Pregunta`, `Respuesta`, `Orden`
                    FROM `tbl_web_preguntas_frecuentes`
                    WHERE `Estado` = 1 AND (`Id` = :Id OR :Id = 0)", $params);
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
                'Id' => $data['id'] ?? 0,
                'Pregunta' => $data['pregunta'] ?? '',
                'Respuesta' => $data['respuesta'] ?? '',
                'Orden' => $data['orden'] ?? '',
            ];
            $database = new Database();
            return $database->executeQuery(
            "UPDATE `tbl_web_preguntas_frecuentes` SET
                    `Pregunta` = :Pregunta,
                    `Respuesta`= :Respuesta, 
                    `Orden`= :Orden
                    WHERE `Id` = :Id", $params);
        } catch (\Throwable $th) {
            return  $th;
        }
    }
    public function insertar(){
        try {
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'Pregunta' => $data['pregunta'] ?? '',
                'Respuesta' => $data['respuesta'] ?? '',
                'Orden' => $data['orden'] ?? '',
            ];
            $database = new Database();
            return $database->executeQuery(
            "INSERT INTO `tbl_web_preguntas_frecuentes`
                    (`Pregunta`, `Respuesta`, `Orden`)
                    VALUES
                    (:Pregunta, :Respuesta, :Orden)", $params);
        } catch (\Throwable $th) {
            return $th;
        }
    }
}