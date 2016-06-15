<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 26-Apr-16
 * Time: 23:35
 */
require("functions.php");

function create_person($first_name, $last_name, $cpr){
    global $mysqli;
    $error = array();

    if(empty($cpr) || empty($first_name) || empty($last_name)){
        array_push($error, "All fields must be filled");
    }
    if(strlen($cpr) != 10){
        array_push($error, "CPR needs to be 10 characters long and must only consist of numbers");
    }



    $stmt = $mysqli->prepare("SELECT cpr FROM people");
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        if(decrypt_cpr($row["cpr"]) === $cpr){
            array_push($error, "There is already a user with that CPR");
            return $error;
        }
    }
    $stmt->close();

    $cpr = encrypt_cpr(preg_replace('/\D/', '', $cpr));

    if(count($error) == 0){
        $query = "INSERT INTO people (first_name, last_name, cpr) VALUES (?,?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sss', $first_name, $last_name, $cpr);
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
        top("Admin - Create person");
        if(empty($_POST)) {
            ?>
            <form action="" method="post">
                <table>
                    <tr>
                        <td><label for="first_name">First name</label></td>
                        <td><input type="text" name="first_name" placeholder="First name"></td>
                    </tr>
                    <tr>
                        <td><label for="last_name">Last name</label></td>
                        <td><input type="text" name="last_name" placeholder="Last name"></td>
                    </tr>
                    <tr>
                        <td><label for="cpr">CPR</label></td>
                        <td><input type="number" name="cpr"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="Create person"></td>
                    </tr>
                </table>

            </form>
            <?php
        }else{
            $create_person_response = create_person($_POST["first_name"],
                $_POST["last_name"],
                $_POST["cpr"]);
            if($create_person_response === true){
                log_event("PERSON_CREATE", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                echo "<h1>SUCCESS</h1>";
            }elseif(is_array($create_person_response)){
                log_event("PERSON_CREATE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
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
    }else{
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        header("Location: not_found.php");
    }
}else{
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    header("Location: not_found.php");
}
?>