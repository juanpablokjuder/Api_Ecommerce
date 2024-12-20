<?php
require __DIR__ .'/../../vendor/autoload.php';

// SDK de Mercado Pago
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoController{
    public function webhook() {
        try {
            MercadoPagoConfig::setAccessToken("APP_USR-2524351933508703-121908-bc6011d50cf34f6f621528585614ac91-2169380250");


            // Capturar el cuerpo de la solicitud
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
            $headers = getallheaders();

            // Registrar notificación en un archivo (para pruebas)
            file_put_contents("notificaciones.log", print_r($body).PHP_EOL, FILE_APPEND);
            file_put_contents("notificaciones.log", print_r($headers, true) . PHP_EOL, FILE_APPEND);
            
           if($this->validarFirma($headers, $data, "ace4d8627c2c3c159034a57209bb2d12fee12117b23102d6b3ec5c5575893a9d")){

                file_put_contents("notificaciones.log", print_r("VALIDO", true) . PHP_EOL, FILE_APPEND);

                $this->procesarNotificacion($data);
           }else{
                file_put_contents("notificaciones.log", print_r("NO VALIDO", true) . PHP_EOL, FILE_APPEND);

           }

            // // Respuesta de éxito
            http_response_code(200); 
            // echo json_encode(["status" => "success"]);
            return $_SERVER;
        } catch (\Throwable $th) {
            // Manejo de errores
            http_response_code(500); 
            file_put_contents("errores.log", $th->getMessage() . PHP_EOL, FILE_APPEND);
            echo json_encode(["status" => "error", "message" => $th->getMessage()]);
        }
    }

    private function validarFirma($headers, $body, $clavePrivada) {
        // Obtain the x-signature value from the header
        $xSignature = $headers['HTTP_X_SIGNATURE'];
        $xRequestId = $headers['HTTP_X_REQUEST_ID'];

        // Obtain Query params related to the request URL
        $queryParams = $body;

        // Extract the "data.id" from the query params
        $dataID = isset($queryParams['data']['id']) ? $queryParams['data']['id'] : '';

        // Separating the x-signature into parts
        $parts = explode(',', $xSignature);

        // Initializing variables to store ts and hash
        $ts = null;
        $hash = null;

        // Iterate over the values to obtain ts and v1
        foreach ($parts as $part) {
            // Split each part into key and value
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) == 2) {
                $key = trim($keyValue[0]);
                $value = trim($keyValue[1]);
                if ($key === "ts") {
                    $ts = $value;
                } elseif ($key === "v1") {
                    $hash = $value;
                }
            }
        }

        // Obtain the secret key for the user/application from Mercadopago developers site
        $secret = "ace4d8627c2c3c159034a57209bb2d12fee12117b23102d6b3ec5c5575893a9d";

        // Generate the manifest string
        $manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";

        // Create an HMAC signature defining the hash type and the key as a byte array
        $sha = hash_hmac('sha256', $manifest, $secret);
        if ($sha === $hash) {
            // HMAC verification passed
            file_put_contents("notificaciones.log", print_r("ENTRO", true) . PHP_EOL, FILE_APPEND);

            return true;
        } else {
            // HMAC verification failed
            file_put_contents("notificaciones.log", print_r("NO ENTRO", true) . PHP_EOL, FILE_APPEND);

            return false;

        }
    }
    private function procesarNotificacion($data) {
        if ($data['type'] === 'payment' && $data['action'] === 'payment.created') {
            $idPago = $data['data']['id'];

            // Recupera detalles del pago desde la API de Mercado Pago
            require_once 'vendor/autoload.php';

            MercadoPagoConfig::setAccessToken("TU_ACCESS_TOKEN");

            $payment = \MercadoPago\Client\Payment::find_by_id($idPago);

            if ($payment) {
                switch ($payment->status) {
                    case 'approved':
                        file_put_contents("pagos_aprobados.log", json_encode($payment).PHP_EOL, FILE_APPEND);
                        break;

                    case 'pending':
                        file_put_contents("pagos_pendientes.log", json_encode($payment).PHP_EOL, FILE_APPEND);
                        break;

                    case 'rejected':
                        file_put_contents("pagos_rechazados.log", json_encode($payment).PHP_EOL, FILE_APPEND);
                        break;
                }
            }
        }
    }

    public function preferenceId(){
        MercadoPagoConfig::setAccessToken("APP_USR-2524351933508703-121908-bc6011d50cf34f6f621528585614ac91-2169380250");
        $client = new PreferenceClient();
        $preference = $client->create([
        "items"=> array(
            array(
            "title" => "Mi producto",
            "quantity" => 1,
            "unit_price" => 2000
            )
        )
        ]);
        return $preference->id;
    }
}