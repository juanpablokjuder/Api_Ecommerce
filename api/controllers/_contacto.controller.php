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
}