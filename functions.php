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

function log_event($action, $ip, $user_id, $people_id){
    global $mysqli;
    $query = "INSERT INTO logs (action, ip, users_id, people_id) VALUES (?,?,?,?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssii', $action, $ip, $user_id, $people_id);
    if(!$stmt->execute()){
        $stmt->close();
        array_push($error, "Error code: #1");
        return $error;
    }
}
?>