<?php

function connection(){
    $host = "localhost";
    $user = "root";
    $pass = "rober";

    $bd = "syscotel";

    $connect=mysqli_connect($host, $user, $pass);

    mysqli_select_db($connect, $bd);

    return $connect;

}


?>