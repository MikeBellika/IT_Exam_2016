<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 26-Apr-16
 * Time: 19:06
 */
$mysqli = new mysqli("localhost", "root", "", "eksamen");

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

function generate_hash($password){
    $salt = '$2y$rNQH1uwlNOqRbzYhWeUa$' . substr(md5(uniqid(rand(), true)), 0, 22);
    return crypt($password, $salt);
}
?>