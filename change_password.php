<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 30-Apr-16
 * Time: 18:27
 */

require("functions.php");
?>
<html>
<head>
    <title>Change password</title>
</head>
<body>
<?php
if(isset($_SESSION["id"])){
    if(isset($_GET["first_login"])){
        echo "<h1>Welcome ".$_SESSION["username"]."!</h1><br>
                Since this is your first time logging in, you need to create your own password.";
    }
    if(empty($_POST)) {

        ?>
        <form action="" method="POST">
            <label>
                Old password:<input type="password" name="password_old" value="********">
            </label><br>
            <label>
                New password:<input type="password" name="password_new" value="********">
            </label><br>
            <label>
                Repeat password:<input type="password" name="password_repeat" value="********">
            </label><br>
            <input type="submit" value="Change password">
        </form>
        <?php
    }else{
        $errors = array();
        $password_old = generate_hash($_POST["password_old"]);
        $password_new = $_POST["password_new"];
        $password_repeat = $_POST["password_repeat"];
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ? AND password = ?");
        $stmt->bind_param("is", $_SESSION["id"], $password_old);
        $stmt->execute();
        $stmt->store_result();
        $stmt->fetch();
        if($stmt->num_rows == 0){
            array_push($errors, "Old password is incorrect");
        }
        if($password_new != $password_repeat){
            array_push($errors, "Passwords don't match");
        }
        if(generate_hash($password_new) == $password_old){
            array_push($errors, "You need to pick a new password");
        }
        if(strlen($password_new) < 8){
            array_push($errors, "Your new password needs to be at least 8 characters");
        }
        $stmt->close();
        if(empty($errors)){
            $stmt = $mysqli->prepare("UPDATE users SET password = ?, first_time_login = 0 WHERE id = ?");
            $stmt->bind_param("si", generate_hash($password_new), $_SESSION["id"]);
            $stmt->execute();
            $stmt->close();
            echo "<h2>Password changed!</h2>";
            log_event("PASSWORD_CHANGE", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
        }else{
            log_event("PASSWORD_CHANGE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
            echo "<ul>";
            foreach($errors as $error){
                echo "<li>".$error."</li>";
            }
            echo "</ul>";
        }

    }

    $mysqli->close();
}else{
    echo "<h2>You need to be logged in, to change your password</h2>";
}

?>
</body>
</html>
