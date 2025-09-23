<?php
function fileToBase64($rutaArchivo){
    $archivoBase64 = "";
    if (file_exists($rutaArchivo)) {
        // Lee el contenido del archivo
        $contenidoArchivo = file_get_contents($rutaArchivo);
        // Convierte el contenido del archivo a base64
        $archivoBase64 = base64_encode($contenidoArchivo);
    }
    return $archivoBase64;
}