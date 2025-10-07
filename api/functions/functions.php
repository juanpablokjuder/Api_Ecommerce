<?php
function fileToBase64($rutaArchivo)
{
    $archivoBase64 = "";
    if (file_exists($rutaArchivo)) {
        // Lee el contenido del archivo
        $contenidoArchivo = file_get_contents($rutaArchivo);
        // Convierte el contenido del archivo a base64
        $archivoBase64 = base64_encode($contenidoArchivo);
    }
    return $archivoBase64;
}
function guardarBase64($base64, $rutaDestino = "uploads/")
{
    if (!is_dir($rutaDestino)) {
        mkdir($rutaDestino, 0777, true);
    }

    // Detectar encabezado
    if (preg_match("/^data:(.*?);base64,(.*)$/", $base64, $matches)) {
        $mime = $matches[1];   // ej: image/png, video/mp4
        $data = base64_decode($matches[2]);
    } else {
        return false; // formato inválido
    }

    if (!$data) {
        return false; // error en base64
    }

    $ext = "";
    $nombreArchivo = uniqid("archivo_", true);

    // --- Si es imagen ---
    if (str_starts_with($mime, "image/")) {
        $ext = "webp";
        $imagenTmp = tempnam(sys_get_temp_dir(), "img_");
        file_put_contents($imagenTmp, $data);

        // Detectar formato original
        $info = getimagesize($imagenTmp);
        if (!$info)
            return false;

        switch ($info['mime']) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($imagenTmp);
                break;
            case 'image/png':
                $img = imagecreatefrompng($imagenTmp);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($imagenTmp);
                break;
            default:
                return false; // formato no soportado
        }

        $rutaCompleta = rtrim($rutaDestino, "/") . "/" . $nombreArchivo . ".webp";
        imagewebp($img, $rutaCompleta, 80); // calidad 80
        imagedestroy($img);
        unlink($imagenTmp);

        // --- Si es video ---
    } elseif (str_starts_with($mime, "video/")) {
        $ext = "mp4"; // mantener en mp4
        $rutaCompleta = rtrim($rutaDestino, "/") . "/" . $nombreArchivo . "." . $ext;
        file_put_contents($rutaCompleta, $data);

        // Aquí podrías optimizar con FFmpeg si lo tenés instalado
        // exec("ffmpeg -i $rutaCompleta -vcodec libx264 -crf 28 optimizado.mp4");
    } else {
        return false; // no es imagen ni video
    }

    return $rutaCompleta;
}