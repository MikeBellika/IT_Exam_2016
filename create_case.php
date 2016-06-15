<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 04-May-16
 * Time: 00:15
 */
require("functions.php");
function create_case($title, $content, $people_id, $user_id){
    global $mysqli;
    $errors = array();

    if(empty($title) || empty($content) || empty($people_id) || empty($user_id)){
        array_push($errors, "All fields must be filled");
    }


    $stmt = $mysqli->prepare("SELECT id FROM people WHERE id = ?");
    $stmt->bind_param("i", $people_id);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows() == 0){
        array_push($errors, "No person with that ID");
        return $errors;
    }
    $stmt->close();

    if(count($errors) == 0){
        $query = "INSERT INTO cases (title, content, people_id) VALUES (?,?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssi', $title, $content, $people_id);
        if(!$stmt->execute()){
            $stmt->close();
            array_push($errors, "Something went wrong when creating case. Please contact a system administrator");
            return $errors;
        }
        $new_case_id = $mysqli->insert_id;
        $stmt->close();

        $query = "INSERT INTO case_users (users_id, cases_id) VALUES (?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $people_id, $new_case_id);
        $stmt->execute();
        $stmt->close();
    }else{
        return $errors;
    }

    return true;


}

if(isset($_SESSION["id"])){
    $can_create_case = get_user_rights($_SESSION["id"])["create_case"];
    if($can_create_case){
        top("Admin - Create case");
        if(empty($_POST)) {
            ?>
            On this page you can create a case 
            <form action="" method="POST">
                <table>
                    <tr>
                        <td>Title:</td>
                        <td style="width:100%;"><input style="width:100%;" type="text" name="title""></td>
                    </tr>
                    <tr>
                        <td>Person:</td>
                        <td><select name="person_id">
                                <option>Select person</option>
                                <?php
                                $stmt = $mysqli->prepare("SELECT id, first_name, last_name FROM people ORDER BY last_name");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while($row = $result->fetch_assoc()){
                                    echo "<option value=\"" . $row["id"] . "\">".$row["first_name"]." ".$row["last_name"]."</option>";
                                }
                                $stmt->close();
                            ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Content:</td>
                        <td><textarea style="width:100%; height:200px;"
                                      name="content"></textarea></td>
                    </tr>
                    <tr>
                        <td>Case user</td>
                        <td><select name="user_id">
                                <option>Select user</option>
                                <?php
                                $stmt = $mysqli->prepare("SELECT users.username, users.id, user_rights.create_case FROM users
                                                      LEFT JOIN user_rights ON users.user_rights_id=user_rights.id");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()) {
                                    if($row["create_case"] == 1){
                                        echo "<option value=\"" . $row["id"] . "\">" . $row["username"] . "</option>";
                                    }
                                }
                                $stmt->close();
                                ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit"></td>
                    </tr>
                </table>
            </form>
            <?php
        }else{
            $create_case = create_case($_POST["title"],
                $_POST["content"],
                $_POST["person_id"],
                $_POST["user_id"]);
            if($create_case === true){
                echo "<h1>SUCCESS</h1>";
                log_event("CASE_CREATE", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $_POST["person_id"]);
            }elseif(is_array($create_case)){
                log_event("CASE_CREATE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $_POST["person_id"]);
                echo "<ul>";
                foreach($create_case as $error){
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