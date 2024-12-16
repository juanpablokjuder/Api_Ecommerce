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
}