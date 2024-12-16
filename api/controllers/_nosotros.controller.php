<?php
class Nosotros{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Titulo`, `Descripcion`, `Imagen`
                    FROM `tbl_web_nosotros`");
        } catch (\Throwable $th) {
            return "asd";
        } 
        

        
    }
}