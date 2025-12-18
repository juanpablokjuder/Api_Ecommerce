<?php
require __DIR__ . '/../../vendor/autoload.php';


class MercadoPago
{

    public function __construct()
    {
    }
    public function webhook()
    {
        try {
            // Obtener datos del webhook
            $body = file_get_contents('php://input');
            $webhook = json_decode($body);
            log_variable("WEBHOOK");
            log_variable($webhook);
            if ($webhook->type == "payment") {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments/' . $webhook->data->id . "?access_token=APP_USR-1952144071949939-092316-7d65711e937e556504ce49cc7990aea4-2681184631");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $payment = json_decode(curl_exec($ch));
                curl_close($ch);
            }

            // Verificar que sea un webhook de merchant_order
            //  payment es la orden de pago y no la necesitamos
            if (!isset($webhook->topic) || $webhook->topic !== "merchant_order") {
                exit;
            }



            // Obtener datos de la orden
            $resource = substr($webhook->resource, 45);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/merchant_orders/' . $resource . "?access_token=APP_USR-1952144071949939-092316-7d65711e937e556504ce49cc7990aea4-2681184631");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = json_decode(curl_exec($ch));
            curl_close($ch);

            // Obtener metadata desde la preferencia (si existe preference_id)
            $preferenceMetadata = null;
            if (isset($result->preference_id)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences/' . $result->preference_id . "?access_token=APP_USR-1952144071949939-092316-7d65711e937e556504ce49cc7990aea4-2681184631");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $preferenceResult = json_decode(curl_exec($ch));
                curl_close($ch);

                if (isset($preferenceResult->metadata)) {
                    $preferenceMetadata = $preferenceResult->metadata;
                }
            }
            log_variable("PREFERENCE METADATA");
            log_variable($preferenceMetadata);
            // Datos generales de la orden
            $idVenta = $preferenceMetadata->idVenta ?? null;

            // Verificar el estado de los pagos en la orden
            // puede haber varios pagos en la orden (pago combinado)
            $approvedPayments = 0;
            $totalPayments = count($result->payments);

            foreach ($result->payments as $payment) {
                if (in_array($payment->status, ['approved', 'paid'])) {
                    $approvedPayments++;
                }
            }

            // Determinar estado de la orden
            if ($approvedPayments > 0 && $approvedPayments == $totalPayments) {
                $estado = 1; // Aprobado - todos los pagos están aprobados
            } else {
                $estado = 2; // Rechazado/Pendiente - ningún pago aprobado
            }

            //No me interesa el estado opened, así que no hago nada
            if ($result->status == "opened") {
                return;
            }
            $database = new Database();

            $param = [
                "idVenta" => $idVenta,
                "estado" => $estado
            ];
            $venta = $database->executeQuery(
                "UPDATE `tbl_ventas` 
                SET `Estado` = :estado
                WHERE `Id` = :idVenta",
                $param
            );
            if ($estado == 1) {
                $param = [
                    "idVenta" => $idVenta
                ];
                $venta = $database->executeQuery(
                    "SELECT C.Nombre, C.Email, C.Telefono FROM tbl_ventas V 
                    INNER JOIN tbl_ventas_clientes C ON C.id = V.IdCliente
                    WHERE V.`Id` = :idVenta",
                    $param
                );
                $body = '<html lang="es">
                    <body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial, sans-serif; color:#333;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" style="padding:20px 0; background-color:#111;">
                            <h1 style="color:#ffffff; margin:0; font-size:24px;">WELDERAR</h1>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:40px 20px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                                <tr>
                                <td align="center" style="background-color:#0078d7; padding:30px;">
                                    <h2 style="color:#ffffff; font-size:22px; margin:0;">¡Gracias por tu compra!</h2>
                                </td>
                                </tr>
                                <tr>
                                <td style="padding:30px; text-align:left;">
                                    <p style="font-size:16px; line-height:1.6; margin-bottom:20px;">
                                    Hola <strong>' . $venta['Nombre'] . '</strong>,
                                    </p>
                                    <p style="font-size:16px; line-height:1.6; margin-bottom:20px;">
                                    Hemos recibido tu pedido correctamente. A continuación te dejamos un resumen:
                                    </p>' .
                    //     <table border="0" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse; margin-bottom:20px;">
                    //    ' . $filas . '
                    //     <tr>
                    //         <td colspan="2" align="right" style="font-size:14px; font-weight:bold; padding-top:10px;">Total:</td>
                    //         <td align="right" style="font-size:14px; font-weight:bold; padding-top:10px;">$' . number_format($total, 0, ',', '.') . '</td>
                    //     </tr>
                    //     </table>
                    '<p style="font-size:14px; line-height:1.6;">
                                    Nos pondremos en contacto para coordinar la entrega de tu pedido 🚚.
                                    </p>
                                </td>
                                </tr>
                                <tr>
                                <td align="center" style="background-color:#f4f4f4; padding:20px;">
                                    <p style="font-size:12px; color:#777; margin:0;">
                                    © 2025 WELDERAR. Todos los derechos reservados.<br>
                                    <a href="#" style="color:#0078d7; text-decoration:none;">Visita nuestra web</a>
                                    </p>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                    </body>
                </html>
            ';
                $mail = sendMail($venta['Email'], "Su compra fue existosa - WELDERAR", $body);
            }

        } catch (Exception $e) {
            log_variable("ERROR");
            log_variable($e->getMessage());

            error_log("Error en WebHook: " . $e->getMessage());
        }
    }

    public function generateLink()
    {
        // devolver link de pago
        // se le pasa [id, cantidad, idColor, idCliente]
        try {
            // Lee el cuerpo de la solicitud
            $rawBody = file_get_contents("php://input");

            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            $database = new Database();
            $productos = [];
            $total = 0;
            $filas = '';
            // Obtener detalles del producto desde la base de datos
            foreach ($data['productos'] as $producto) {
                $param = [
                    "id" => $producto['id_producto'],
                    "id_color" => $producto['id_color']
                ];
                $producto_ = $database->executeQuery(
                    "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`Precio`, P.`Descuento`, C.`Imagen` as ColorImagen
                    FROM `tbl_productos` P 
                    LEFT JOIN `tbl_colores` C ON C.`Id` = :id_color
                    WHERE P.`Estado` = 1 AND P.`Id` = :id",
                    $param
                );
                $filas .= '<tr>
                            <td style="font-size:14px; border-bottom:1px solid #eee;">' . $producto_[0]['Nombre'] . '</td>
                            <td align="center" style="font-size:14px; border-bottom:1px solid #eee;">' . $producto['cantidad'] . '</td>
                            <td align="right" style="font-size:14px; border-bottom:1px solid #eee;">$' . number_format($producto_[0]['Precio'] - ((int) $producto_[0]['Precio'] * (int) ($producto_[0]['Descuento'] ?? 0 / 100)), 0, ',', '.') . '</td>
                        </tr>';
                $producto_[0]['cantidad'] = $producto['cantidad'];
                $productos[] = $producto_;
                $total += ($producto_[0]['Precio'] - ((int) $producto_[0]['Precio'] * (int) ($producto_[0]['Descuento'] ?? 0 / 100))) * $producto['cantidad'];
            }



            // return $mail;

            //obtener cliente
            $client_id = null;
            $param = [
                "idCliente" => $client_id
            ];
            $cliente = $database->executeQuery(
                "SELECT * FROM  `tbl_ventas_clientes` WHERE `Id` = :idCliente",
                $param
            );
            if (count($cliente) == 0) {
                $param = [
                    "Nombre" => $data['cliente']['nombre'] ?? "",
                    "Email" => $data['cliente']['email'] ?? "",
                    "Telefono" => $data['cliente']['telefono'] ?? "",
                    "Direccion" => $data['cliente']['direccion'] ?? "",
                ];
                $client_id = $database->executeQuery(
                    "INSERT INTO `tbl_ventas_clientes` 
                    (`Nombre`, `Email`, `Telefono`, `Direccion`, `FechaAlta`) 
                    VALUES 
                    (:Nombre, :Email, :Telefono, :Direccion, NOW())",
                    $param
                );
            } else {
                $client_id = $cliente[0]['Id'];
            }

            // generar venta
            $param = [
                "idCliente" => $client_id
            ];
            $venta = $database->executeQuery(
                "INSERT INTO `tbl_ventas` 
                (`IdCliente`, `Estado`, `FechaAlta`)
                VALUES
                (:idCliente, 0, NOW())",
                $param
            );
            $idVenta = $venta; // ID único para la venta
            $aux = 0;
            $moneda = "ARS";
            $items = [];
            foreach ($data['productos'] as $producto) {
                $param = [
                    "id" => $producto['id_producto'],
                    "id_color" => $producto['id_color']
                ];
                $producto_ = $database->executeQuery(
                    "SELECT P.`Id`, P.`Nombre`, P.`Descripcion`, P.`Precio`, P.`Descuento`, C.`Nombre` as ColorNombre
                    FROM `tbl_productos` P 
                    LEFT JOIN `tbl_colores` C ON C.`Id` = :id_color
                    WHERE P.`Estado` = 1 AND P.`Id` = :id",
                    $param
                );

                $producto_[0]['cantidad'] = $producto['cantidad'];
                $items[] = [
                    'id' => $producto_[0]['Id'],
                    'title' => $producto_[0]['Nombre'],
                    'quantity' => $producto_[0]['cantidad'],
                    'unit_price' => round((int) $producto_[0]['Precio'] - ((int) $producto_[0]['Precio'] * (int) ($producto_[0]['Descuento'] ?? 0 / 100))),
                    'currency_id' => $moneda
                ];
            }
            $payload = [
                'items' => $items,
                'external_reference' => $idVenta,
                'notification_url' => "https://welderar.com/Api/MercadoPago/webhook",
                'binary_mode' => true, // Cambiar a false para permitir pagos parciales
                'auto_return' => 'approved',
                'back_urls' => [
                    'success' => 'https://welderar.com/',
                    'failure' => 'https://welderar.com/',
                    'pending' => 'https://welderar.com/'
                ],
                //cuotas y métodos de pago
                'payment_methods' => [
                    'installments' => ($moneda === 'USD') ? 1 : 12, // USD solo 1 cuota, ARS hasta 12
                    'excluded_payment_types' => ($moneda === 'USD') ? [
                        ['id' => 'ticket'], // Excluir Rapipago/Pago Fácil para USD
                        ['id' => 'atm']     // Excluir cajeros automáticos para USD
                    ] : []
                ],
                "metadata" => array_filter([
                    "idVenta" => $idVenta,

                ])
            ];
            define('ACCESS_TOKEN', 'APP_USR-1952144071949939-092316-7d65711e937e556504ce49cc7990aea4-2681184631');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences?access_token=' . ACCESS_TOKEN);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $res = json_decode($response);

            if ($httpCode !== 201 || !isset($res->id) || !isset($res->init_point)) {
                return [
                    'success' => false,
                    'error' => $response
                ];
            }

            return [
                'success' => true,
                'preference' => $res->id,
                'init_point' => $res->init_point,
                'items' => $items
            ];
        } catch (\Throwable $th) {
            return [
                "error" => $th->getMessage(),
                "line" => $th->getLine()
            ];
        }

    }
}