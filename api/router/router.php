<?php

class Router
{
    private $data;

    function __construct()
    {
        try {
            // .Obtener los parametros de GET
            $clase = isset($_GET['p1']) ? $_GET['p1'] : null;
            $metodo = isset($_GET['p2']) ? $_GET['p2'] : null;
            $parametro = isset($_GET['p3']) ? $_GET['p3'] : null;

            $data = [];
            $controller = new $clase();
            if ($parametro != null && $parametro !== '') {
                $data = $controller->$metodo($parametro);
                respondFile($data);
            } else {
                $data = $controller->$metodo();
                respond([
                    'Respuesta' => 'OK',
                    'Data' => $data
                ], 200);
            }

            // $data = [];
            // $controller = new PreguntasFrecuentes();
            // $data = $controller->obtener();

        } catch (\Throwable $th) {
            respond([
                'Respuesta' => 'ERROR',
                'Data' => $th->getMessage()
            ], 400);

            respond($th, 400);
        }
    }
}