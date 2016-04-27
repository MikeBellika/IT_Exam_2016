<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 27-Apr-16
 * Time: 17:32
 */
require("functions.php");

function login($username, $password){
    global $mysqli;
    $error = array();

    $password = generate_hash($password);
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->store_result();
    $stmt->fetch();
    if($stmt->num_rows == 0){
        array_push($error, "Invalid username or password");
        return $error;
    }
    $stmt->close();
    return $id;
}


?>
<html>
<head>
    <title>Login</title>
</head>
<body>
<?php
if(empty($_POST) && !isset($_SESSION["username"])) {
    ?>
    <form action="" method="post">
        <input type="text" name="username" value="Username"><br>
        <input type="password" name="password" value="11111111"><br>
        <input type="submit" value="Login">
    </form>
    <?php
}elseif(!empty($_POST)){
    $login_response = login($_POST["username"], $_POST["password"]);
    //The login function returns a user_ID if login is successful.
    //If not it returns an array of error.
    if(is_int($login_response)){
        log_event("LOGIN", 1, $_SERVER["REMOTE_ADDR"], $login_response, NULL);
        $_SESSION["username"] = $_POST["username"];
        $_SESSION["id"] = $login_response;
        echo "<h1>SUCCESS</h1>";
    }else{
        log_event("LOGIN", 0, $_SERVER["REMOTE_ADDR"], NULL, NULL);
        echo "<ul>";
        foreach($login_response as $error){
            echo "<li>".$error."</li>";
        }
        echo "</ul>";
    }
}else{
    echo "You are already logged in";
}
$mysqli->close();
?>
</body>
</html>
