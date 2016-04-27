<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 26-Apr-16
 * Time: 19:03
 */
require("functions.php");

function create_user($people_id, $username, $password, $userrights_id){
    global $mysqli;
    $error = array();

    if(empty($people_id) || empty($username) || empty($password) || empty($userrights_id)){
         array_push($error, "All fields must be filled");
    }

    if(strlen($password) < 8){
        array_push($error, "Password has to be at least 8 characters");
    }

    $stmt = $mysqli->prepare("SELECT id FROM people WHERE id = ?");
    $stmt->bind_param("i", $people_id);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows() == 0){
        array_push($error, "No person with that ID");
        return $error;
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE people_id = ?");
    $stmt->bind_param("i", $people_id);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows() > 0){
        array_push($error, "There is already a user with that ID");
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows() > 0){
        array_push($error, "There is already a user with that username");
    }
    $stmt->close();

    if(count($error) == 0){
        $password = generate_hash($password);
        $query = "INSERT INTO users (username, password, user_rights_id, people_id) VALUES (?,?,?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssii', $username, $password, $userrights_id, $people_id);
        if(!$stmt->execute()){
            $stmt->close();
            array_push($error, "Something went wrong when creating user. Please contact a system administrator");
            return $error;
        }
    }else{
        return $error;
    }
    $stmt->close();
    return true;


}

if(isset($_SESSION["id"])){
    $can_create_user = get_user_rights($_SESSION["id"])["create_user"];
    if($can_create_user){
        ?>
        <html>
        <head>
            <title>Admin - Create user</title>
        </head>
        <body>
        <?php
        if(empty($_POST)) {
            ?>
            <form action="" method="post">
                <input type="text" name="username" value="Username"><br>
                <input type="password" name="password" value="11111111"><br>
                <input type="number" name="people_id" value="Person_ID"><br>
                <input type="number" name="userrights_id" value="userrights_id"><br>
                <input type="submit" value="Create user">
            </form>
            <?php
        }else{
            $create_user_response = create_user($_POST["people_id"],
                $_POST["username"],
                $_POST["password"],
                $_POST["userrights_id"]);
            if($create_user_response === true){
                echo "<h1>SUCCESS</h1>";
            }elseif(is_array($create_user_response)){
                echo "<ul>";
                foreach($create_user_response as $error){
                    echo "<li>".$error."</li>";
                }
                echo "</ul>";
            }
        }
        $mysqli->close();

        ?>
        </body>
    </html>
    <?php
    }
}else{
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    header("Location: not_found.php");
}
?>