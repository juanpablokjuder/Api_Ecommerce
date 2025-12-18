<?php

class Productos
{
    public function obtenerInicio()
    {
        try {
            $database = new Database();
            $categorias = $database->executeQuery(
                "SELECT C.`Id`, C.`Titulo` as 'Nombre', C.`Titulo`
                    FROM `tbl_categorias` C
                    WHERE C.`Estado` = 1 AND C.`Inicio` = 1"
            );
            $aux = 0;
            foreach ($categorias as $categoria) {
                $param = [
                    'idCategoria' => $categoria['Id']
                ];
                $categorias[$aux]['Productos'] = $database->executeQuery(
                    "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`, PA.`Extension`,
                    CASE WHEN PA.`Archivo` IS NOT NULL AND PA.`Archivo` != '' AND PA.`Nombre` != '' AND PA.`Nombre` IS NOT NULL 
                    THEN CONCAT('https://welderar.com/Api/productos/imagenes/', PA.`Nombre`) ELSE '' END AS 'Archivo'
                    FROM `tbl_productos_categorias` PC
                    INNER JOIN `tbl_productos` P ON P.`Id` = PC.`IdProducto`
                    LEFT JOIN `tbl_productos_archivos` PA ON PA.`Orden` = 1 AND P.`Id` = PA.`IdProducto` 
                    WHERE P.`Estado` = 1 AND PC.`IdCategoria` = :idCategoria ",
                    $param
                );
                $aux++;
            }
            return $categorias;
        } catch (\Throwable $th) {
            return "asd";
        }
    }
    public function obtener()
    {
        try {
            $database = new Database();
            $param = [];
            $productos = $database->executeQuery(
                "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`, PA.`Base64`, PA.`Extension`
                    FROM `tbl_productos` P 
                    LEFT JOIN `tbl_productos_archivos` PA ON PA.`Orden` = 1 AND P.`Id` = PA.`IdProducto` 
                    WHERE P.`Estado` = 1",
                $param
            );
            $aux = 0;
            foreach ($productos as $producto) {
                $param = [
                    'idProducto' => $producto['Id']
                ];
                $productos[$aux]['Categorias'] = $database->executeQuery(
                    "SELECT PC.`IdCategoria`
                    FROM `tbl_productos_categorias` PC
                    WHERE PC.`IdProducto` = :idProducto ",
                    $param
                );
                $productos[$aux]['Colores'] = $database->executeQuery(
                    "SELECT PC.`IdColor`
                    FROM `tbl_productos_colores` PC
                    WHERE PC.`IdProducto` = :idProducto ",
                    $param
                );
                $aux++;
            }
            return $productos;
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function listado()
    {
        try {
            $database = new Database();
            $param = [];
            $productos = $database->executeQuery(
                //"SELECT P.`Id`, P.`Nombre`, PA.`Base64`, PA.`Extension`
                "SELECT P.`Id`, P.`Nombre`,PA.`Archivo`, PA.`Extension`
                    FROM `tbl_productos` P 
                    LEFT JOIN `tbl_productos_archivos` PA ON PA.`Orden` = 1 AND P.`Id` = PA.`IdProducto` 
                    ",
                $param
            );
            $aux = 0;
            foreach ($productos as $producto) {
                if ($producto['Archivo'] != null && $producto['Archivo'] != "") {
                    $productos[$aux]['Base64'] = fileToBase64($producto['Archivo']) ?? "";
                } else {
                    $productos[$aux]['Base64'] = "";
                }
                $aux++;
            }
            return $productos;
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function obtenerId()
    {
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $database = new Database();
            $param = [
                "id" => $data['id'],
            ];
            $productos = $database->executeQuery(
                "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`
                    FROM `tbl_productos` P 
                    WHERE P.`Estado` = 1 AND P.`Id` = :id",
                $param
            );
            $aux = 0;
            foreach ($productos as $producto) {
                $param = [
                    'idProducto' => $producto['Id']
                ];
                $productos[$aux]['Categorias'] = $database->executeQuery(
                    "SELECT PC.`IdCategoria`
                    FROM `tbl_productos_categorias` PC
                    WHERE PC.`IdProducto` = :idProducto ",
                    $param
                );
                $productos[$aux]['Colores'] = $database->executeQuery(
                    "SELECT PC.`IdColor`, C.`Nombre`, C.`Imagen`
                    FROM `tbl_productos_colores` PC
                    INNER JOIN `tbl_colores` C ON C.`Id` = PC.`IdColor`
                    WHERE PC.`IdProducto` = :idProducto ",
                    $param
                );
                $archivos = $database->executeQuery(
                    //"SELECT PA.`Id`, PA.`Base64`, PA.`Extension`, PA.`Orden`
                    "SELECT PA.`Id`,PA.`archivo`, PA.`Extension`, PA.`Orden`
                    FROM `tbl_productos_archivos` PA
                    WHERE PA.`IdProducto` = :idProducto
                    ORDER BY PA.`Orden` ASC",
                    $param
                );
                $archivosAux = [];
                foreach ($archivos as $archivo) {
                    $archivosAux[] = [
                        "Extension" => $archivo['Extension'],
                        "Base64" => fileToBase64($archivo['archivo']) ?? "",
                        "Orden" => $archivo['Orden'],
                    ];
                }

                $productos[$aux]['Archivos'] = $archivosAux;
                // $productos[$aux]['Archivos'] = $database->executeQuery(
                $aux++;
            }
            return $productos;
        } catch (\Throwable $th) {
            return "asd";
        }
    }
    public function insertar()
    {
        // Lee el cuerpo de la solicitud
        $rawBody = file_get_contents("php://input");

        // Decodifica el JSON recibido
        $data = json_decode($rawBody, true);

        $database = new Database();
        $params = [
            'Nombre' => $data['nombre'] ?? "",
            'Descripcion' => $data['descripcion'] ?? "",
            'IdMoneda' => 1,
            'Precio' => $data['precio'] ?? "",
            'Descuento' => $data['descuento'] ?? "",
            'Estado' => 1,
        ];
        $idProducto = $database->executeQuery(
            "INSERT INTO `tbl_productos`
                    (`Nombre`, `Descripcion`, `IdMoneda`, `Precio`, `Descuento`, `Estado`)
                    VALUES
                    (:Nombre, :Descripcion, :IdMoneda, :Precio, :Descuento, :Estado ) 
                    ",
            $params
        );


        // // CATEGORIAS
        if (isset($data['categorias'])) {
            $categoriasArray = $data['categorias'];

            foreach ($categoriasArray as $idCategoria) {
                $params = [
                    'IdProducto' => $idProducto,
                    'IdCategoria' => $idCategoria,
                ];
                $database->executeQuery(
                    "INSERT INTO `tbl_productos_categorias`
                            (`IdProducto`, `IdCategoria`)
                            VALUES
                            (:IdProducto, :IdCategoria) 
                            ",
                    $params
                );
            }
        }


        if (isset($data['colores'])) {
            $coloresArray = $data['colores'];

            foreach ($coloresArray as $idColor) {
                $params = [
                    'IdProducto' => $idProducto,
                    'IdColor' => $idColor,
                ];
                $database->executeQuery(
                    "INSERT INTO `tbl_productos_colores`
                            (`IdProducto`, `IdColor`)
                            VALUES
                            (:IdProducto, :IdColor) 
                            ",
                    $params
                );
            }
        }
        if (isset($data['archivos'])) {
            $archivosArray = $data['archivos'];
            if (count($archivosArray) > 0) {
                foreach ($archivosArray as $archivo) {
                    $nombreArchivo = guardarBase64($archivo['File'], $archivo['Extension'], 'uploads/productos/');
                    if ($nombreArchivo) {
                        $params = [
                            'IdProducto' => $idProducto,
                            'Archivo' => 'uploads/productos/' . $nombreArchivo,
                            'Nombre' => $nombreArchivo,
                            'Alt' => $nombreArchivo,
                            'Extension' => $archivo['Extension'],
                            'Orden' => $archivo['Orden'],
                        ];
                        $database->executeQuery(
                            "INSERT INTO `tbl_productos_archivos`
                                (`IdProducto`, `Archivo`, `Nombre`, `Alt`, `Orden`,`Extension`)
                                VALUES
                                (:IdProducto, :Archivo, :Nombre, :Alt, :Orden, :Extension)
                                ",
                            $params
                        );
                    }
                }
            }
        }
    }
    public function imagenes($imagen)
    {
        $database = new Database();
        $params = [
            'Nombre' => $imagen,
        ];
        $imagen = $database->executeQuery(
            "SELECT `Archivo` FROM `tbl_productos_archivos`
                    WHERE `Nombre` = :Nombre",
            $params
        );
        return $imagen[0]['Archivo'] ?? "";
    }
    public function editar()
    {
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            // $producto = new Productos();
            $database = new Database();
            $params = [
                'Id' => $data['id'] ?? "",
                'Nombre' => $data['nombre'] ?? "",
                'Descripcion' => $data['descripcion'] ?? "",
                'IdMoneda' => 1,
                'Precio' => $data['precio'] ?? "",
                'Descuento' => $data['descuento'] ?? "",
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
                        WHERE `Id` = :Id ",
                $params
            );


            // // CATEGORIAS
            if (isset($data['categorias'])) {

                $params = [
                    'IdProducto' => $data['id'],
                ];
                $database->executeQuery(
                    "DELETE FROM `tbl_productos_categorias`
                            WHERE `IdProducto` = :IdProducto",
                    $params
                );

                $categoriasArray = $data['categorias'];
                foreach ($categoriasArray as $idCategoria) {
                    if ($idCategoria != 0) {
                        $params = [
                            'IdProducto' => $data['id'],
                            'IdCategoria' => $idCategoria,
                        ];
                        $database->executeQuery(
                            "INSERT INTO `tbl_productos_categorias`
                                (`IdProducto`, `IdCategoria`)
                                VALUES
                                (:IdProducto, :IdCategoria) 
                                ",
                            $params
                        );
                    }
                }
            }


            if (isset($data['colores'])) {
                $params = [
                    'IdProducto' => $data['id'],
                ];
                $database->executeQuery(
                    "DELETE FROM `tbl_productos_colores`
                            WHERE `IdProducto` = :IdProducto",
                    $params
                );
                $coloresArray = $data['colores'];

                foreach ($coloresArray as $idColor) {
                    if ($idColor != 0) {
                        $params = [
                            'IdProducto' => $data['id'],
                            'IdColor' => $idColor,
                        ];
                        $database->executeQuery(
                            "INSERT INTO `tbl_productos_colores`
                                (`IdProducto`, `IdColor`)
                                VALUES
                                (:IdProducto, :IdColor) 
                                ",
                            $params
                        );
                    }
                }
            }
            if (isset($data['archivos'])) {
                $archivosArray = $data['archivos'];
                if (count($archivosArray) > 0) {
                    $params = [
                        'IdProducto' => $data['id'],
                    ];
                    $archivosPrevios = $database->executeQuery(
                        "SELECT * FROM `tbl_productos_archivos`
                            WHERE `IdProducto` = :IdProducto ",
                        $params
                    );

                    if (count($archivosPrevios) > 0) {
                        foreach ($archivosPrevios as $archivo) {
                            if (file_exists($archivo['Archivo'])) {
                                unlink($archivo['Archivo']);
                            }
                        }
                        $params = [
                            'IdProducto' => $data['id'],
                        ];
                        $database->executeQuery(
                            "DELETE FROM`tbl_productos_archivos`
                            WHERE `IdProducto` = :IdProducto ",
                            $params
                        );
                    }

                    foreach ($archivosArray as $archivo) {
                        $nombreArchivo = guardarBase64($archivo['File'], $archivo['Extension'], 'uploads/productos/');
                        if ($nombreArchivo == true) {
                            $params = [
                                'IdProducto' => $data['id'],
                                'Archivo' => 'uploads/productos/' . $nombreArchivo,
                                'Nombre' => $nombreArchivo,
                                'Alt' => $nombreArchivo,
                                'Orden' => $archivo['Orden'],
                                'Extension' => $archivo['Extension'],
                            ];
                            $database->executeQuery(
                                "INSERT INTO `tbl_productos_archivos`
                                (`IdProducto`, `Archivo`, `Nombre`, `Alt`, `Orden`, `Extension`)
                                VALUES
                                (:IdProducto, :Archivo, :Nombre, :Alt, :Orden, :Extension)
                                ",
                                $params
                            );
                        } else {
                            return $nombreArchivo;
                        }
                    }
                }
            }
            return "OK";
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }


    }
}
