<?php


function respond($datos, $estado = 200)
{
    // Configura el encabezado de la respuesta
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code($estado); // Código de estado HTTP

    // Convierte el array u objeto a JSON
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit; // Finaliza el script
}
function respondFile($ruta)
{
    if (file_exists($ruta)) {
        // Determinar el tipo MIME (opcional pero recomendable)
        $mime = mime_content_type($ruta);
        header("Content-Type: $mime");
        header("Content-Length: " . filesize($ruta));

        // Leer el archivo y enviarlo al navegador
        readfile($ruta);
        exit;
    } else {
        http_response_code(404);
        echo "Imagen no encontrada";
        exit;
    }
}