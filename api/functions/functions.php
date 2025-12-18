<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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
function guardarBase64($base64, $extension, $rutaDestino)
{
    // Evitar warnings visibles
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    // Asegurarse de que la ruta termine con una barra
    if (!str_ends_with($rutaDestino, '/') && !str_ends_with($rutaDestino, '\\')) {
        $rutaDestino .= '/';
    }

    // Validar o crear el directorio de destino
    if (!file_exists($rutaDestino)) {
        mkdir($rutaDestino, 0777, true);
    }

    // Tipos MIME admitidos
    $mime = match (strtolower($extension)) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'mp4' => 'video/mp4',
        'pdf' => 'application/pdf',
        default => null
    };

    if (!$mime) {
        return "Extensión no soportada";
    }

    // Armar el string base64 completo con encabezado
    $dataUri = "data:$mime;base64,$base64";

    // Extraer el contenido real en base64
    if (!preg_match('/^data:(.*?);base64,(.*)$/', $dataUri, $matches)) {
        return "Formato Base64 inválido";
    }

    $data = base64_decode($matches[2]);
    if ($data === false) {
        return "Error al decodificar Base64";
    }

    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);

    if ($isImage) {
        // Crear imagen desde datos binarios
        $image = @imagecreatefromstring($data);
        if (!$image) {
            return "Error al crear imagen desde datos";
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Crear un lienzo true color con soporte de transparencia
        $trueColor = imagecreatetruecolor($width, $height);

        // Habilitar transparencia (muy importante)
        imagealphablending($trueColor, false);
        imagesavealpha($trueColor, true);

        // Rellenar con transparente
        $transparent = imagecolorallocatealpha($trueColor, 0, 0, 0, 127);
        imagefill($trueColor, 0, 0, $transparent);

        // Copiar la imagen original al nuevo lienzo
        imagecopy($trueColor, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);
        $image = $trueColor;

        // Nombre del archivo destino
        $nombreArchivo = 'archivo_' . uniqid() . '.webp';
        $rutaCompleta = $rutaDestino . $nombreArchivo;

        // Guardar como WebP con calidad 80 y transparencia
        if (!@imagewebp($image, $rutaCompleta, 80)) {
            imagedestroy($image);
            return "Error al guardar imagen WebP";
        }

        imagedestroy($image);
    } else {
        // Guardar archivo sin modificar
        $nombreArchivo = 'archivo_' . uniqid() . '.' . $extension;
        $rutaCompleta = $rutaDestino . $nombreArchivo;

        if (file_put_contents($rutaCompleta, $data) === false) {
            return "Error al guardar archivo";
        }
    }

    return $nombreArchivo; // ✅ devuelve la ruta final sin romper headers
}


function sendMail($to, $subject, $body)
{

    require __DIR__ . '/../../vendor/autoload.php';

    # Enviar correo por SMTP
    $host = 'c2730301.ferozo.com';
    $port = 465;
    $username = 'info@welderar.com';
    $password = '@gkBr*3F';
    $secure = 'ssl'; // o 'ssl'
    $charset = 'UTF-8';
    $headers = [
        'MIME-Version' => '1.0',
        'Content-Type' => "text/html; charset=$charset",
        'From' => $username,
        'Reply-To' => $username,
        'X-Mailer' => 'PHP/' . phpversion()
    ];

    // Usar PHPMailer para enviar el correo
    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPSecure = $secure;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;

        // Remitente y destinatario
        $mail->setFrom($username, 'Welderar');
        $mail->addAddress($to);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->CharSet = $charset;

        return $mail->send();
    } catch (Exception $e) {
        //echo "Error al enviar correo: {$mail->ErrorInfo}";
        return false;
    }
}

function log_variable($data, $filename = '/app.log')
{
    // 1. Obtiene la marca de tiempo actual para el registro
    $timestamp = date('Y-m-d H:i:s');

    // 2. Prepara el contenido a escribir
    $content = '';

    // Si es un string o un número, lo loguea directamente.
    if (is_scalar($data) || is_null($data)) {
        $content = (string) $data;
        if (is_null($data)) {
            $content = 'NULL';
        } elseif (is_bool($data)) {
            $content = $data ? 'TRUE' : 'FALSE';
        }
    } else {
        // Para objetos o arrays, usa print_r para obtener una representación legible
        // Pasa el segundo parámetro como true para que devuelva el contenido en lugar de imprimirlo
        $content = print_r($data, true);
    }

    // 3. Formatea la línea de log
    $log_line = "[$timestamp] " . $content . PHP_EOL;

    // 4. Escribe en el archivo de log (usa FILE_APPEND para añadir al final)
    // El flag LOCK_EX evita que otros procesos escriban al mismo tiempo (bloqueo exclusivo)
    $result = file_put_contents($filename, $log_line, FILE_APPEND | LOCK_EX);

    return $result !== false;
}