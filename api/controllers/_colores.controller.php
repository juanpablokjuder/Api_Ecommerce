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
}