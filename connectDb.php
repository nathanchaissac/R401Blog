<?php

try{
    $db = new PDO("mysql:host=localhost;dbname=CHANGE_ME;charset=utf8", "CHANGE_ME",'CHANGE_ME');
}catch (Exception $e){
    deliver_response(500, "Internal server error (Database connection)", NULL);
    die();
}
