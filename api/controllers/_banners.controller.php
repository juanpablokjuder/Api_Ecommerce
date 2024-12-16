<?php
class Banner{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Imagen`
                    FROM `tbl_web_banners`
                    ORDER BY `Orden` ASC");
        } catch (\Throwable $th) {
            return "asd";
        } 
        

        
    }
}