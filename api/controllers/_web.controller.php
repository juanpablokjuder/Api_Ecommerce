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
}