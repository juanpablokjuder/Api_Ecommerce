<?php
class Login{
    public function Admin(){
        try {
            $rawBody = file_get_contents("php://input");
            // Decodifica el JSON recibido
            $data = json_decode($rawBody, true);
            if($data == null)
                throw new Exception("Hubo un error al iniciar sesion");
            $email = $data['email'];
            $pass = $data['pass'];
            if($email == null || $email == "")
                throw new Exception("Hubo un error al iniciar sesion - Email");

            if($pass == null || $pass == "")
                throw new Exception("Hubo un error al iniciar sesion - Pass");

            // hacer login
            $database = new Database();
            $params = [':Email' => $email, ':Pass' => $pass];
            $resultado = $database->executeQuery(
                "SELECT 
                        U.`Id`,
                        U.`Nombre`,
                        U.`Apellido`,
                        U.`Email`,
                        U.`Contraseña`,
                        U.`Imagen`,
                        U.`Categoria` AS IdCategoria,
                        UC.`Nombre` AS Categoria,
                        U.`Estado`
                        FROM `tbl_usuarios` U
                        INNER JOIN `tbl_usuarios_categorias` UC ON UC.`Id` = U.`Categoria`
                        WHERE U.`Email` = :Email AND U.`Contraseña` = :Pass AND U.`Estado` = 1", $params);
            if(count($resultado)>0){
                $usuario = [
                    "id"=>$resultado[0]['Id'],
                    "nombre"=>$resultado[0]['Nombre'],
                    "apellido"=>$resultado[0]['Apellido'],
                    "email"=>$resultado[0]['Email'],
                    "imagen"=>  $resultado[0]['Imagen'],
                    "estado"=>  $resultado[0]['Estado'],
                    "idCategoria"=>  $resultado[0]['IdCategoria'],
                    "categoria"=>  $resultado[0]['Categoria'],
                ];
                session_start();
                $_SESSION['usuario'] = $usuario;
                return $usuario;
                // header("Location: ../../Inicio");
            }else{
                throw new Exception("Hubo un error al iniciar sesion");
            }

            } catch (\Throwable $th) {
                respond($th->getMessage(), 404);
        }
    }
}