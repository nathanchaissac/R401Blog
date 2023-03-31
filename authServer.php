<?php

require "jwt_utils.php";

header("Content-Type:application/json");

$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method) {
    case "POST":
        $postedData = file_get_contents('php://input');
        $postedData = json_decode($postedData, TRUE);

        if(isset($postedData['username']) && isset($postedData['password'])){
            $response = user_is_valid($postedData['username'], $postedData['password']);
            if($response != NULL){
                $header = array('alg'=>'HS256', 'typ'=>'JWT');
                $playload = array('id_user'=>$response['Id_user'], 'type'=>$response['role'], 'exp'=>time()+3600*2);

                $jwt = generate_jwt($header, $playload);
                deliver_response(200, "Token généré", $jwt);
            }else{
                deliver_response(401, "Login ou mot de passe incorrecte", $response);
            }
        }else{
            deliver_response(400, "Login ou mot de passe non renseigné" , $postedData);
        }
        break;

    default:
        deliver_response(405, "Methode non gérée", NULL);
        break;
}

function user_is_valid($login, $password) {
    include "connectDb.php";
    $request = $db->prepare('SELECT * FROM users WHERE username = ?');
    $request->execute(array($login));

    if($request->rowCount() > 0){
        $user = $request->fetch();
        if(password_verify($password, $user['password'])) {
            return $user;
        } else {
            return NULL;
        }
    }else{
        return NULL;
    }
}

