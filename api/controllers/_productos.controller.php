<?php

class Productos{
    public function obtenerInicio(){
        try {
            $database = new Database();
            $categorias = $database->executeQuery(
            "SELECT C.`Id`, C.`Nombre`
                    FROM `tbl_categorias` C
                    WHERE C.`Estado` = 1 AND C.`Inicio` = 1");
            $aux = 0;
            foreach($categorias as $categoria){
                $param = [
                    'idCategoria' => $categoria['Id']
                ];
                $categorias[$aux]['Productos']  = $database->executeQuery(
            "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`, PA.`Base64`, PA.`Extension`
                    FROM `tbl_productos_categorias` PC
                    INNER JOIN `tbl_productos` P ON P.`Id` = PC.`IdProducto`
                    LEFT JOIN `tbl_productos_archivos` PA ON PA.`Orden` = 1 AND P.`Id` = PA.`IdProducto` 
                    WHERE P.`Estado` = 1 AND PC.`IdCategoria` = :idCategoria ", $param);
                $aux++;
            }
            return $categorias;
        } catch (\Throwable $th) {
            return "asd";
        }
    }
    public function obtener(){
        try {
            $database = new Database();
            $param = [];
            $productos =  $database->executeQuery(
            "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`, PA.`Base64`, PA.`Extension`
                    FROM `tbl_productos` P 
                    LEFT JOIN `tbl_productos_archivos` PA ON PA.`Orden` = 1 AND P.`Id` = PA.`IdProducto` 
                    WHERE P.`Estado` = 1", $param);
            $aux = 0;
            foreach($productos as $producto){
                $param = [
                    'idProducto' => $producto['Id']
                ];
                $productos[$aux]['Categorias']  = $database->executeQuery(
            "SELECT PC.`IdCategoria`
                    FROM `tbl_productos_categorias` PC
                    WHERE PC.`IdProducto` = :idProducto ", $param);
                $productos[$aux]['Colores']  = $database->executeQuery(
            "SELECT PC.`IdColor`
                    FROM `tbl_productos_colores` PC
                    WHERE PC.`IdProducto` = :idProducto ", $param);
                $aux++;
            }
            return $productos;
        } catch (\Throwable $th) {
            return "asd";
        }
    }
    public function obtenerId(){
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $database = new Database();
            $param = [
                "id" => $data['id'],
            ];
            $productos =  $database->executeQuery(
            "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`
                    FROM `tbl_productos` P 
                    WHERE P.`Estado` = 1 AND P.`Id` = :id", $param);
            $aux = 0;
            foreach($productos as $producto){
                $param = [
                    'idProducto' => $producto['Id']
                ];
                $productos[$aux]['Categorias']  = $database->executeQuery(
            "SELECT PC.`IdCategoria`
                    FROM `tbl_productos_categorias` PC
                    WHERE PC.`IdProducto` = :idProducto ", $param);
                $productos[$aux]['Colores']  = $database->executeQuery(
            "SELECT PC.`IdColor`, C.`Nombre`, C.`Imagen`
                    FROM `tbl_productos_colores` PC
                    INNER JOIN `tbl_colores` C ON C.`Id` = PC.`IdColor`
                    WHERE PC.`IdProducto` = :idProducto ", $param);
                $productos[$aux]['Archivos']  = $database->executeQuery(
            "SELECT PA.`Id`, PA.`Base64`, PA.`Extension`, PA.`Orden`
                    FROM `tbl_productos_archivos` PA
                    WHERE PA.`IdProducto` = :idProducto
                    ORDER BY PA.`Orden` ASC", $param);
                $aux++;
            }
            return $productos;
        } catch (\Throwable $th) {
            return "asd";
        }
    }
    public function insertar(){
        // Lee el cuerpo de la solicitud
        $rawBody = file_get_contents("php://input");

        // Decodifica el JSON recibido
        $data = json_decode($rawBody, true);
        
        $database = new Database();
        $params = [
            'Nombre' => $data['nombre'] ??  "",
            'Descripcion' => $data['descripcion'] ??  "",
            'IdMoneda' => 1,
            'Precio' => $data['precio'] ??  "",
            'Descuento' => $data['descuento'] ??  "",
            'Estado' => 1,
        ];
         $idProducto = $database->executeQuery(
            "INSERT INTO `tbl_productos`
                    (`Nombre`, `Descripcion`, `IdMoneda`, `Precio`, `Descuento`, `Estado`)
                    VALUES
                    (:Nombre, :Descripcion, :IdMoneda, :Precio, :Descuento, :Estado ) 
                    ", $params);


        // // CATEGORIAS
        if ( isset($data['categorias']) ){
            $categoriasArray = $data['categorias'];

            foreach($categoriasArray as $idCategoria){
                $params = [
                    'IdProducto' => $idProducto,
                    'IdCategoria' => $idCategoria,
                ];
                $database->executeQuery(
                    "INSERT INTO `tbl_productos_categorias`
                            (`IdProducto`, `IdCategoria`)
                            VALUES
                            (:IdProducto, :IdCategoria) 
                            ", $params);
            }
        }


        if ( isset($data['colores']) ){
            $coloresArray = $data['colores'];

            foreach($coloresArray as $idColor){
                $params = [
                    'IdProducto' => $idProducto,
                    'IdColor' => $idColor,
                ];
                $database->executeQuery(
                    "INSERT INTO `tbl_productos_colores`
                            (`IdProducto`, `IdColor`)
                            VALUES
                            (:IdProducto, :IdColor) 
                            ", $params);
            }
        }
        if ( isset($data['archivos']) ){
            $archivosArray = $data['archivos'];
            if(count($archivosArray)>0){
                foreach($archivosArray as $archivo){
                    $params = [
                        'IdProducto' => $idProducto,
                        'Base64' => str_replace([" ", "\n", "\r"], "+", $archivo['File']),
                        'Orden' => $archivo['Orden'],
                        'Extension' => $archivo['Extension'],
                    ];
                    $database->executeQuery(
                    "INSERT INTO `tbl_productos_archivos`
                            (`IdProducto`, `Base64`, `Extension`, `Orden`)
                            VALUES
                            (:IdProducto, :Base64, :Extension, :Orden) 
                            ", $params); 
                }
            }
        }
    }

    public function editar(){
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            // $producto = new Productos();
            $database = new Database();
            $params = [
                'Id' => $data['id'] ?? "",
                'Nombre' => $data['nombre'] ??  "",
                'Descripcion' => $data['descripcion'] ??  "",
                'IdMoneda' => 1,
                'Precio' => $data['precio'] ??  "",
                'Descuento' => $data['descuento'] ??  "",
                'Estado' => 1,
            ];
            $idProducto = $database->executeQuery(
                "UPDATE `tbl_productos` SET
                        `Nombre` = :Nombre,
                        `Descripcion` = :Descripcion,
                        `IdMoneda` = :IdMoneda,
                        `Precio` = :Precio,
                        `Descuento` = :Descuento,
                        `Estado` = :Estado
                        WHERE `Id` = :Id ", $params);


            // // CATEGORIAS
            if ( isset($data['categorias']) ){
            
                $params = [
                    'IdProducto' => $data['id'],
                ];
                $database->executeQuery(
                    "DELETE FROM `tbl_productos_categorias`
                            WHERE `IdProducto` = :IdProducto", $params);
                            
                $categoriasArray = $data['categorias'];
                foreach($categoriasArray as $idCategoria){
                    if($idCategoria != 0){
                    $params = [
                        'IdProducto' => $data['id'],
                        'IdCategoria' => $idCategoria,
                    ];
                    $database->executeQuery(
                        "INSERT INTO `tbl_productos_categorias`
                                (`IdProducto`, `IdCategoria`)
                                VALUES
                                (:IdProducto, :IdCategoria) 
                                ", $params);
                    }
                }
            }


            if ( isset($data['colores'])){
                $params = [
                    'IdProducto' => $data['id'],
                ];
                $database->executeQuery(
                    "DELETE FROM `tbl_productos_colores`
                            WHERE `IdProducto` = :IdProducto", $params);
                $coloresArray = $data['colores'];

                foreach($coloresArray as $idColor){
                    if($idColor != 0){
                    $params = [
                        'IdProducto' => $data['id'],
                        'IdColor' => $idColor,
                    ];
                    $database->executeQuery(
                        "INSERT INTO `tbl_productos_colores`
                                (`IdProducto`, `IdColor`)
                                VALUES
                                (:IdProducto, :IdColor) 
                                ", $params);
                    }
                }
            }
            if (isset($data['archivos'])) {
                $archivosArray = $data['archivos'];
                if(count($archivosArray)>0){
                    $params = [
                        'IdProducto' => $data['id'],
                    ];
                    $database->executeQuery(
                    "DELETE FROM`tbl_productos_archivos`
                            WHERE `IdProducto` = :IdProducto ", $params); 
                
                    foreach($archivosArray as $archivo){
                        $params = [
                            'IdProducto' => $data['id'],
                            'Base64' => str_replace([" ", "\n", "\r"], "+", $archivo['File']),
                            'Orden' => $archivo['Orden'],
                            'Extension' => $archivo['Extension'],
                        ];
                        $database->executeQuery(
                        "INSERT INTO `tbl_productos_archivos`
                                (`IdProducto`, `Base64`, `Extension`, `Orden`)
                                VALUES
                                (:IdProducto, :Base64, :Extension, :Orden) 
                                ", $params); 
                    }
                }
            }
            return "OK";
        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }
        
        
    }
}
