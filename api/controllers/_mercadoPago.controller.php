<?php
require __DIR__ .'/../../vendor/autoload.php';

// SDK de Mercado Pago
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
class MercadoPagoController{

        public function __construct() {
            MercadoPagoConfig::setAccessToken("APP_USR-2524351933508703-121908-bc6011d50cf34f6f621528585614ac91-2169380250");
        }
        public function webhook() {
        try {

            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
            $headers = getallheaders();

            file_put_contents("notificaciones.log", print_r($body, true) . PHP_EOL, FILE_APPEND); // Imprime el cuerpo JSON
            file_put_contents("notificaciones.log", print_r($headers, true) . PHP_EOL, FILE_APPEND);

            if ($this->validarFirma($headers, $data, "ace4d8627c2c3c159034a57209bb2d12fee12117b23102d6b3ec5c5575893a9d")) {
                file_put_contents("notificaciones.log", "VALIDO" . PHP_EOL, FILE_APPEND);
                $this->procesarNotificacion($data);
            } else {
                file_put_contents("notificaciones.log", "NO VALIDO" . PHP_EOL, FILE_APPEND);
            }

            http_response_code(200);
            return $_SERVER; // o un simple echo "OK";
        } catch (\Throwable $th) {
            http_response_code(500);
            file_put_contents("errores.log", $th->getMessage() . PHP_EOL, FILE_APPEND);
            echo json_encode(["status" => "error", "message" => $th->getMessage()]);
        }
    }

    private function validarFirma($headers, $data, $clavePrivada) {
        $xSignature = isset($headers['HTTP_X_SIGNATURE']) ? $headers['HTTP_X_SIGNATURE'] : (isset($headers['X-Signature']) ? $headers['X-Signature'] : null);
        $xRequestId = isset($headers['HTTP_X_REQUEST_ID']) ? $headers['HTTP_X_REQUEST_ID'] : (isset($headers['X-Request-Id']) ? $headers['X-Request-Id'] : null);

        // ***CORRECCIÓN IMPORTANTE: Extraer data.id del CUERPO ($data)***
        $dataID = isset($data['data']['id']) ? $data['data']['id'] : '';  // <--- AQUI ESTABA EL ERROR

        $parts = explode(',', $xSignature);
        $ts = null;
        $hash = null;
        file_put_contents("notificaciones.log", "dataID: " . $dataID . PHP_EOL, FILE_APPEND);
        file_put_contents("notificaciones.log", "xSignature: " . $xSignature . PHP_EOL, FILE_APPEND);
        file_put_contents("notificaciones.log", "xRequestId: " . $xRequestId . PHP_EOL, FILE_APPEND);
        foreach ($parts as $part) {
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

        $secret = $clavePrivada; // Usa el parámetro $clavePrivada

        $manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";

        $sha = hash_hmac('sha256', $manifest, $secret);

        file_put_contents("notificaciones.log", "Manifest calculado: " . $manifest . PHP_EOL, FILE_APPEND);
    file_put_contents("notificaciones.log", "Hash calculado: " . $sha . PHP_EOL, FILE_APPEND);
    file_put_contents("notificaciones.log", "Hash recibido: " . $hash . PHP_EOL, FILE_APPEND);

        if ($sha === $hash) {
            file_put_contents("notificaciones.log", "ENTRO" . PHP_EOL, FILE_APPEND);
            return true;
        } else {
            file_put_contents("notificaciones.log", "NO ENTRO" . PHP_EOL, FILE_APPEND);
            return false;
        }
    }
    private function procesarNotificacion($data) {
        try {
            if ($data['type'] === 'payment' && $data['action'] === 'payment.created') {
                $idPago = $data['data']['id'];
                file_put_contents("notificaciones.log", print_r($data, true) . PHP_EOL, FILE_APPEND);   
                file_put_contents("notificaciones.log", "ID_PAGO: ".$idPago . PHP_EOL, FILE_APPEND);

                $paymentClient = new PaymentClient(); // Instancia PaymentClient
                $payment = $paymentClient->get($idPago); // Usa PaymentClient->get()
                file_put_contents("notificaciones.log", 'PAYMENT: ' . PHP_EOL, FILE_APPEND);   
                file_put_contents("notificaciones.log", print_r($payment, true) . PHP_EOL, FILE_APPEND);   

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
        } catch (\Throwable $th) {
            //throw $th;
            file_put_contents("notificaciones.log", print_r($th, true) . PHP_EOL, FILE_APPEND);   

        }
        
    }

    public function preferenceId(){
        
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $database = new Database();
            $param = [
                "id" => $data['id'],
            ];
            $productos =  $database->executeQuery(
            "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`IdMoneda`, P.`Precio`, P.`Descuento`
                    FROM `tbl_productos` P 
                    WHERE P.`Estado` = 1 AND P.`Id` = :id", $param);
            $aux = 0;
            
            MercadoPagoConfig::setAccessToken("APP_USR-2524351933508703-121908-bc6011d50cf34f6f621528585614ac91-2169380250");
            $client = new PreferenceClient();
            $preference = $client->create([
            "items"=> array(
                array(
                "title" => "Mi producto",
                "quantity" => 1,
                "unit_price" => 1000
                )
            )
            ]);
            return $preference->id;
        } catch (\Throwable $th) {
            return "asd";
        }

    }
}