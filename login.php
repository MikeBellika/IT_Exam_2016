<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 27-Apr-16
 * Time: 17:32
 */
require("User.php");
$user = new User();

top("Login");

if(empty($_POST) && !isset($_SESSION["username"])) {
    ?>
    <div id="loginform">
        <form action="" method="post">
            <table class="login">
                <tr>
                    <td><label for="username">Username</label></td>
                    <td><input type="text" name="username" id="username"></td>
                </tr>
                <tr>
                    <td><label for="password">Password</label></td>
                    <td><input type="password" name="password" id="password"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><label>
                            <input style="float:right;" type="submit">
                        </label>
                    </td>
                </tr>
            </table>
    
        </form>
    </div>
    <?php
}elseif(!empty($_POST)){
    $user->login($_POST["username"], $_POST["password"]);
}else{
    echo "Welcome " . $_SESSION["username"];
}
$mysqli->close();
?>
</body>
</html>
