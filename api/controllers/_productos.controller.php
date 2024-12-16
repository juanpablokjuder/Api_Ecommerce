<?php

class Productos{
    public function obtenerInicio(){
        try {
            $database = new Database();
            $categorias = $database->executeQuery(
            "SELECT C.`Id`, C.`Nombre`
                    FROM `tbl_categorias` C
                    INNER JOIN `tbl_productos_categorias` PC ON PC.`IdCategoria` = C.`Id`
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
    public function insertar($data){
        var_dump($data);
        // $producto = new Productos();
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
            $categoriasArray = json_decode($data['categorias'], true);

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
            $coloresArray = json_decode($data['categorias'], true);

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
            $archivosArray = json_decode(urldecode($data['archivos']), true);
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
}