<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 01-May-16
 * Time: 15:40
 */
require("functions.php");
if(isset($_SESSION["id"])) {
    $can_edit_case = get_user_rights($_SESSION["id"])["edit_case"];
    if ($can_edit_case) {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
            $stmt = $mysqli->prepare("SELECT id FROM case_users WHERE users_id = ? AND cases_id = ?");
            $stmt->bind_param("ii", $_SESSION["id"], $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0){
                log_event("EDIT_CASE_ID_".$id, 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                die("<h1>You are not assigned to this case</h1>");
            }
            $stmt->close();

            $stmt = $mysqli->prepare("SELECT cases.*, people.first_name,people.last_name FROM cases
                                      LEFT JOIN people ON cases.people_id=people.id
                                      WHERE cases.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                die("<h1>No case found</h1>");
            }

            $row = $result->fetch_assoc();
            $stmt->close();

            top("Editing case - ".$row["title"]);

            if(empty($_POST)) {
                ?>
                <form action="" method="POST">
                    <table>
                        <tr>
                            <td>Title:</td>
                            <td style="width:100%;"><input style="width:100%;" type="text" name="title"
                                                           value="<?php echo $row["title"]; ?>"></td>
                        </tr>
                        <tr>
                            <td>Person:</td>
                            <td><?php echo $row["first_name"]." ".$row["last_name"]; ?></td>
                        </tr>
                        <tr>
                            <td>Content:</td>
                            <td><textarea style="width:100%; height:200px;"
                                          name="content"><?php echo $row["content"]; ?></textarea></td>
                        </tr>
                        <tr>
                            <td>Creation date:</td>
                            <td><?php echo $row["creation_date"]; ?></td>
                        </tr>
                        <tr>
                            <td>Add user to case</td>
                            <td><select name="username">
                                    <option>Select user</option>
                                    <?php
                                    $stmt = $mysqli->prepare("SELECT users.username FROM users
                                                      LEFT JOIN user_rights ON users.user_rights_id=user_rights.id");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value=\"" . $row["username"] . "\">" . $row["username"] . "</option>";
                                    }
                                    ?>
                                </select></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit"></td>
                        </tr>
                    </table>
                </form>
                <h2>Associated users:</h2>
                <table>
                    <tr>
                        <td><b>Username</b</td>
                        <td><b>Assigned date</b></td>
                    </tr>
                    <?php
                    $stmt = $mysqli->prepare("SELECT case_users.date, users.username FROM case_users
                                          LEFT JOIN users ON case_users.users_id=users.id
                                          WHERE cases_id=?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $stmt->close();
                    while ($row = $result->fetch_assoc()) {
                        ?>

                        <tr>
                            <td><?php echo $row["username"] ?></td>
                            <td><?php echo $row["date"] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            }else{
                $errors = array();
                if(empty($_POST["title"]) || empty($_POST["content"])){
                    log_event("EDIT_CASE_".$id, 0, $_SERVER["REMOTE_ADDR"], $_SESSION["ID"], NULL);
                    array_push($errors, "Neither title nor content can be empty");
                }
                if(!empty($_POST["username"])){
                    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->bind_param("s", $_POST["username"]);
                    $stmt->execute();
                    //$result = $stmt->get_result();
                    $stmt->bind_result($user_id);
                    $stmt->fetch();
                    if(empty($user_id)){
                        array_push($errors, "Invalid username");
                    }
                    $stmt->close();
                    
                    $stmt = $mysqli->prepare("SELECT id FROM case_users WHERE users_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if($result->num_rows == 1){
                        array_push($errors, "User is already assigned to this case");
                    }
                    $stmt->close();
                }
                
                if(empty($errors)){
                    log_event("EDIT_CASE_ID_".$id, 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                    $stmt = $mysqli->prepare("UPDATE cases SET title = ?, content = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $_POST["title"], $_POST["content"], $id);
                    $stmt->execute();
                    $stmt->close();
                    if(!empty($_POST["username"])){
                        $stmt = $mysqli->prepare("INSERT INTO case_users (users_id, cases_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $user_id, $id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    echo "<h2>Case updated!</h2>";

                }else{
                    log_event("EDIT_CASE_ID_".$id, 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                    echo "<ul>";
                    foreach($errors as $error){
                        echo "<li>".$error."</li>";
                    }
                    echo "</ul>";
                }

            }
                ?>
            </body>
            </html>
            <?php
        }
    }else{
        log_event("EDIT_CASE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
        echo "<h2>You do not have permission to view this page.</h2>";
    }
}else{
    log_event("EDIT_CASE", 0, $_SERVER["REMOTE_ADDR"], NULL, NULL);
    echo "<h2>You need to login to view this page.</h2>";
}