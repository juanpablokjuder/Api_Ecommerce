<?php

class PreguntasFrecuentes{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Pregunta`, `Respuesta`, `Orden`
                    FROM `tbl_web_preguntas_frecuentes`
                    WHERE `Estado` = 1");
        } catch (\Throwable $th) {
            return "asd";
        }
        

        
    }
}