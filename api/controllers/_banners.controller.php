<?php
class Banner{
    public function obtener(){
        try {
            $database = new Database();
            return $database->executeQuery(
            "SELECT `Id`, `Imagen`, `Orden`
                    FROM `tbl_web_banners`
                    ORDER BY `Orden` ASC");
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
    public function editar(){
        try {
            
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
             if (isset($data['archivos'])) {
                $archivosArray = $data['archivos'];
                if(count($archivosArray)>0){
                    $database = new Database();
                    $database->executeQuery(
                    "DELETE
                            FROM `tbl_web_banners`");

                    foreach($archivosArray as $archivo){
                        $params = [
                            'Imagen' => str_replace([" ", "\n", "\r"], "+", $archivo['File']),
                            'Orden' => $archivo['Orden'],
                        ];
                        $database->executeQuery(
                        "INSERT INTO `tbl_web_banners`
                                (`Imagen`, `Orden`)
                                VALUES
                                (:Imagen, :Orden) 
                                ", $params); 
                    }
                }
            }
        } catch (\Throwable $th) {
            return "asd";
        } 
    }
}