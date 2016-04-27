<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 26-Apr-16
 * Time: 19:06
 */
session_start();
$mysqli = new mysqli("localhost", "root", "", "eksamen");

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

function generate_hash($password){
    $salt = '$2y$rNQH1uwlNOqRbzYhWeUa$' . substr(md5(uniqid(rand(), true)), 0, 22);
    return crypt($password, $salt);
}

function log_event($action, $response, $ip, $user_id, $people_id){
    global $mysqli;
    $error = array();
    $query = "INSERT INTO logs (action, response, ip, users_id, people_id) VALUES (?,?,?,?,?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sisii', $action, $response, $ip, $user_id, $people_id);
    if(!$stmt->execute()){
        $stmt->close();
        array_push($error, "Error code: #1");
        return $error;
    }
}

function get_user_rights($user_id){
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT user_rights.* FROM user_rights
                              LEFT JOIN users
                              ON user_rights.id=users.user_rights_id
                              WHERE users.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $stmt->close();
    return $row;
}
?>