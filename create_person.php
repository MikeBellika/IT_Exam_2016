<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 26-Apr-16
 * Time: 23:35
 */
require("functions.php");

function create_person($name, $cpr){
    global $mysqli;
    $error = array();

    $cpr = preg_replace('/\D/', '', $cpr);

    if(empty($cpr) || empty($name)){
        array_push($error, "All fields must be filled");
    }
    if(strlen($cpr) != 10){
        array_push($error, "CPR needs to be 10 characters long and may only consist of numbers");
    }


    $stmt = $mysqli->prepare("SELECT id FROM people WHERE cpr = ?");
    $stmt->bind_param("i", $cpr);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        array_push($error, "There is already a user with that CPR");
        return $error;
    }
    $stmt->close();

    if(count($error) == 0){
        //TODO: ENCRYPT THIS SHIT
        $query = "INSERT INTO people (name, cpr) VALUES (?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $name, $cpr);
        if(!$stmt->execute()){
            $stmt->close();
            array_push($error, "Something went wrong when creating person. Please contact a system administrator");
            return $error;
        }
    }else{
        return $error;
    }
    $stmt->close();

    return true;


}

if(isset($_SESSION["id"])){
    $can_create_person = get_user_rights($_SESSION["id"])["create_person"];
    if($can_create_person){
        ?>
        <html>
        <head>
            <title>Admin - Create person</title>
        </head>
        <body>
        <?php
        if(empty($_POST)) {
            ?>
            <form action="" method="post">
                <input type="text" name="name" value="Name"><br>
                <input type="number" name="cpr"><br>
                <input type="submit" value="Create person">
            </form>
            <?php
        }else{
            $create_person_response = create_person($_POST["name"],
                $_POST["cpr"]);
            if(is_bool($create_person_response)){
                echo "<h1>SUCCESS</h1>";
            }else{
                echo "<ul>";
                foreach($create_person_response as $error){
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