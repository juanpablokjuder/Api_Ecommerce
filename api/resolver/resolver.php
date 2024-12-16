<?php


function respond($datos, $estado = 200) {
    // Configura el encabezado de la respuesta
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code($estado); // Código de estado HTTP

    // Convierte el array u objeto a JSON
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit; // Finaliza el script
}