<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 03-May-16
 * Time: 17:12
 */
require("functions.php");
if(isset($_SESSION["id"])) {
    $can_edit_user = get_user_rights($_SESSION["id"])["edit_user"];
    if ($can_edit_user) {
        if(!empty($_GET["id"])){
            $id = $_GET["id"];
            $stmt = $mysqli->prepare("SELECT *, user_rights.preset_name FROM users
                                          LEFT JOIN user_rights ON users.user_rights_id=user_rights.id
                                          WHERE users.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                die("<h1>No user with that id found</h1>");
            }
            $row = $result->fetch_assoc();
            $stmt->close();
            if(empty($_POST)){
                top("Editing user ".$row["username"]);
                ?>
                <form action="" method="POST">
                    <table>
                        <tr>
                            <td>Username:</td>
                            <td><input type="text" name="username"value="<?php echo $row["username"]; ?>"></td>
                        </tr>
                        <tr>
                            <td>User rights:</td>
                            <td><select name="userrights_id">
                                    <option value="<?php echo $row["user_rights_id"]?>"><?php echo $row["preset_name"]?></option>
                                    <?php
                                    $current_rights_id = $row["user_rights_id"];
                                    $stmt = $mysqli->prepare("SELECT preset_name, id FROM user_rights");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while($row = $result->fetch_assoc()){
                                        if($row["id"] != $current_rights_id) {
                                            ?>
                                            <option value="<?php echo $row["id"] ?>"><?php echo $row["preset_name"] ?></option>
                                            <?php
                                        }
                                    }
                                    $stmt->close();
                                    ?>
                                </select></td>
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
                if(empty($_POST["username"])){
                    array_push($errors, "Username can't be empty");
                }
                if(empty($_POST["userrights_id"])){
                    array_push($errors, "User rights id can't be empty");
                }
                $stmt = $mysqli->prepare("SELECT id FROM user_rights WHERE id = ?");
                $stmt->bind_param("i", $userrights_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if($result->num_rows == 0){
                    array_push($errors, "No user_right with that id");
                }
                $stmt->close();
                if(empty($errors)){
                    $stmt = $mysqli->prepare("UPDATE users SET username = ?, user_rights_id = ? WHERE id = ?");
                    $stmt->bind_param("sii", $_POST["username"], $_POST["userrights_id"], $id);
                    $stmt->execute();
                    echo "<h2>User updated!</h2>";
                    log_event("EDIT_USER", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                }else{
                    foreach($errors as $error){
                        echo "<li>".$error."</li>";
                    }
                    echo "</ul>";
                }

            }
        }else{
            log_event("EDIT_USER", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
            echo "<h2>You need to specify a user id.</h2>";
        }
    }else{
        log_event("EDIT_USER", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
        echo "<h2>You do not have permission to view this page.</h2>";
    }
}else{
    log_event("EDIT_USER", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
    echo "<h2>You do not have permission to view this page.</h2>";
}