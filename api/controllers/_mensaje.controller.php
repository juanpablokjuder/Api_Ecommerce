<?php
class Mensaje{
    public function enviar(){
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);

            $database = new Database();
            $params = [
                'Nombre' =>  $data['Nombre'],
                'Email' => $data['Email'],
                'Mensaje' => $data['Mensaje']
            ];
            $database->executeQuery(
            "INSERT INTO `tbl_web_mensajes`
            ( `Fecha`, `Nombre`, `Email`, `Mensaje`)
            VALUES
            (NOW(),:Nombre, :Email, :Mensaje)", $params);

            return $data['Nombre'];
        } catch (\Throwable $th) {
            return "asd";
        } 
        

        
    }
}