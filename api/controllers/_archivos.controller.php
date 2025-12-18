<?php
class Archivos
{
    public function obtener()
    {
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $params = [
                'IdProducto' => $data['idProducto'] ?? 0
            ];
            $database = new Database();
            $archivos = $database->executeQuery(
                "SELECT `Id`, `Archivo`, `Extension`, `Orden`
                    FROM `tbl_productos_archivos`
                    WHERE `IdProducto` = :IdProducto OR :IdProducto = 0",
                $params
            );
            foreach ($archivos as $archivo) {
                $archivosAux[] = [
                    'Base64' => fileToBase64($archivo['Archivo']),
                    'Extension' => $archivo['Extension'],
                    'Orden' => $archivo['Orden']
                ];
            }
            return $archivosAux;
        } catch (\Throwable $th) {
            return "asd";
        }
    }
}