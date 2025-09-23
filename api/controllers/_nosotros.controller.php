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
    public function editar(){
        try {
           $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            // $producto = new Productos();
            $database = new Database();
            $params = [
                'Id' => 1,
                'Titulo' => $data['titulo'] ??  "",
                'Descripcion' => $data['descripcion'] ??  "",
                'Imagen' => $data['archivos'][0]['File'] ??  "",
            ];
            $database->executeQuery(
                "UPDATE `tbl_web_nosotros` SET
                        `Titulo` = :Titulo,
                        `Descripcion` = :Descripcion,
                        `Imagen` = :Imagen
                        WHERE `Id` = :Id ", $params);
            return "Ok";
        } catch (\Throwable $th) {
            return "Error al actualizar";
        }
    }
}