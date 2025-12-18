<?php
class Ventas{
    public function obtener(){
        try {
            $database = new Database();
            $param = [];
            $ventas = $database->executeQuery(
                "SELECT 
                V.id AS 'Id',
                V.IdCliente AS 'IdCliente',
                V.Estado AS 'Estado',
                V.FechaAlta AS 'FechaAlta',
                VC.Nombre AS 'Nombre Cliente',
                VC.Email AS 'Email Cliente',
                VMP.descripcion AS 'Medio de Pago'
                FROM tbl_ventas V
                LEFT JOIN tbl_ventas_clientes VC ON V.IdCliente = VC.Id
                LEFT JOIN tbl_ventas_medio_pago VMP ON V.idMedioPago = VMP.id
                WHERE V.estado = 1",
                $param
            );
            $aux = 0;
            foreach ($ventas as $venta) {
                $param = [
                    'idVenta' => $venta['Id']
                ];
                $ventas[$aux]['Detalle'] = $database->executeQuery(
                    "SELECT *
                    FROM `tbl_ventas_detalle` VD
                    WHERE VD.`IdVenta` = :idVenta ",
                    $param
                );
                
                $aux++;
            }
            return $ventas;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}