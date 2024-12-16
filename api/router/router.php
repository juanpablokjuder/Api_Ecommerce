<?php

class Router{
    private $data;

    function __construct(){
        try {
            // .Obtener los parametros de GET
            $clase = isset($_GET['p1']) ? $_GET['p1'] : null;
            $metodo = isset($_GET['p2']) ? $_GET['p2'] : null;

            $data = [];
            $controller = new $clase();
            $data = $controller->$metodo();


            // $data = [];
            // $controller = new PreguntasFrecuentes();
            // $data = $controller->obtener();
            respond([
                'Respuesta' => 'OK',
                'Data' => $data
            ], 200);
        } catch (\Throwable $th) {
           respond([
            'Respuesta' => 'ERROR',
            'Data' => $th->getMessage()
           ], 400);

           respond($th, 400);
        }
    }
}