<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 03-May-16
 * Time: 17:38
 */
require("functions.php");
if(isset($_SESSION["id"])) {
    $can_edit_people = get_user_rights($_SESSION["id"])["edit_person"];
    if ($can_edit_people) {
        if(!empty($_GET["id"])){
            $id = $_GET["id"];
            $stmt = $mysqli->prepare("SELECT * FROM people WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                die("<h1>No person with that id found</h1>");
            }
            $row = $result->fetch_assoc();
            $stmt->close();
            if(empty($_POST)){
                top("Editing person: ".$row["first_name"]." ".$row["last_name"]);
                ?>
                <form action="" method="POST">
                    <table>
                        <tr>
                            <td>First name:</td>
                            <td><input type="text" name="first_name" value="<?php echo $row["first_name"]; ?>"></td>
                        </tr>
                        <tr>
                            <td>Last name:</td>
                            <td><input type="text" name="last_name" value="<?php echo $row["last_name"]; ?>"></td>
                        </tr>
                        <tr>
                            <td>Submit</td>
                            <td><input type="submit" value="Save changes"></td>
                        </tr>
                    </table>
                </form>
                <?php
            }else{
                $errors = array();
                if(empty($_POST["first_name"])){
                    array_push($errors, "First name can't be empty");
                }
                if(empty($_POST["last_name"])){
                    array_push($errors, "Last name can't be empty");
                }
                if(empty($errors)){
                    $stmt = $mysqli->prepare("UPDATE people SET first_name = ?, last_name = ? WHERE id = ?");
                    $stmt->bind_param("sss", $_POST["first_name"], $_POST["last_name"], $id);
                    $stmt->execute();
                    echo "<h2>Person updated!</h2>";
                    log_event("EDIT_PERSON", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $id);
                }else{
                    foreach($errors as $error){
                        echo "<li>".$error."</li>";
                    }
                    echo "</ul>";
                }

            }
        }else{
            log_event("EDIT_PERSON", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $id);
            echo "<h2>You need to specify a user id.</h2>";
        }
    }else{
        log_event("EDIT_PERSON", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $id);
        echo "<h2>You do not have permission to view this page.</h2>";
    }
}else{
    log_event("EDIT_PERSON", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $id);
    echo "<h2>You do not have permission to view this page.</h2>";
}