<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 29-Apr-16
 * Time: 15:10
 */

require("functions.php");
if(isset($_SESSION["id"])){
    $can_manage_presets = get_user_rights($_SESSION["id"])["manage_user_rights_presets"];
    if($can_manage_presets){
        ?>
        <html>
        <head>
            <title>Admin - Manage user presets</title>
        </head>
        <body>
        <?php
        if(empty($_POST)) {
        ?>
            <table>
                <tr>
                    <td><b>ID</b></td>
                    <td><b>Preset name</b></td>
                    <td><b>View case</b></td>
                    <td><b>Edit case</b></td>
                    <td><b>Delete case</b></td>
                    <td><b>Create case</b></td>
                    <td><b>Edit user</b></td>
                    <td><b>Delete user</b></td>
                    <td><b>Create user</b></td>
                    <td><b>View users</b></td>
                    <td><b>Edit people</b></td>
                    <td><b>Delete people</b></td>
                    <td><b>Create people</b></td>
                    <td><b>View people</b></td>
                    <td><b>View logs</b></td>
                    <td><b>Manage user rights presets</b></td>
                    <td>Submit</td>
                </tr>
            <?php
            $stmt = $mysqli->prepare("SELECT * FROM user_rights");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <form action="" method="POST">
                        <td><input type="hidden" name="id" value="<?php echo $row["id"] ?>"></td>
                        <td><input type="text" name="preset_name" value="<?php echo $row["preset_name"] ?>"</td>
                        <td><input type="hidden" value="0" name="view_case"><input type="checkbox" name="view_case" value="1" <?php echo str_replace(1, "checked", $row["view_case"]                 )?>></td>
                        <td><input type="hidden" value="0" name="edit_case"><input type="checkbox" name="edit_case" value="1" <?php echo str_replace(1, "checked", $row["edit_case"]                 )?>></td>
                        <td><input type="hidden" value="0" name="delete_case"><input type="checkbox" name="delete_case" value="1" <?php echo str_replace(1, "checked", $row["delete_case"]               )?>></td>
                        <td><input type="hidden" value="0" name="create_case"><input type="checkbox" name="create_case" value="1" <?php echo str_replace(1, "checked", $row["create_case"]               )?>></td>
                        <td><input type="hidden" value="0" name="edit_user"><input type="checkbox" name="edit_user" value="1" <?php echo str_replace(1, "checked", $row["edit_user"]                 )?>></td>
                        <td><input type="hidden" value="0" name="delete_user"><input type="checkbox" name="delete_user" value="1" <?php echo str_replace(1, "checked", $row["delete_user"]               )?>></td>
                        <td><input type="hidden" value="0" name="create_user"><input type="checkbox" name="create_user" value="1" <?php echo str_replace(1, "checked", $row["create_user"]               )?>></td>
                        <td><input type="hidden" value="0" name="view_users"><input type="checkbox" name="view_users" value="1" <?php echo str_replace(1, "checked", $row["view_users"]                )?>></td>
                        <td><input type="hidden" value="0" name="edit_person"><input type="checkbox" name="edit_person" value="1" <?php echo str_replace(1, "checked", $row["edit_person"]               )?>></td>
                        <td><input type="hidden" value="0" name="delete_person"><input type="checkbox" name="delete_person" value="1" <?php echo str_replace(1, "checked", $row["delete_person"]             )?>></td>
                        <td><input type="hidden" value="0" name="create_person"><input type="checkbox" name="create_person" value="1" <?php echo str_replace(1, "checked", $row["create_person"]             )?>></td>
                        <td><input type="hidden" value="0" name="view_people"><input type="checkbox" name="view_people" value="1" <?php echo str_replace(1, "checked", $row["view_people"]               )?>></td>
                        <td><input type="hidden" value="0" name="view_logs"><input type="checkbox" name="view_logs" value="1" <?php echo str_replace(1, "checked", $row["view_logs"]                 )?>></td>
                        <td><input type="hidden" value="0" name="manage_user_rights_presets"><input type="checkbox" name="manage_user_rights_presets" value="1" <?php echo str_replace(1, "checked", $row["manage_user_rights_presets"])?>></td>
                        <td><input type="submit" value="Update rights"></td>
                    </form>
                </tr>
                <?php
            }
            ?>
            </table>
            <a href="create_user_rights_preset.php">Create user rights preset</a>
            <?php
            $stmt->close();
        }else{
            $errors = array();
            if($_POST["id"] == 1){
                array_push($errors, "You cannot change the Super admin preset");
            }
            if($_POST["manage_user_rights_presets"] != $_POST["create_user"]){
                array_push($errors, "A user with the ability to create a person must also the the ability to manage
                 user rights");
            }
            if(empty($errors)){
                //Ensure that the ID is valid
                $stmt = $mysqli->prepare("SELECT id FROM user_rights WHERE id=?");
                $stmt->bind_param("i", $_POST["id"]);
                if($stmt->execute()){
                    $stmt->close();
                    $query = "UPDATE user_rights
                              SET preset_name=?,
                              view_case=?,
                              edit_case=?,
                              delete_case=?,
                              create_case=?,
                              edit_user=?,
                              delete_user=?,
                              create_user=?,
                              view_users=?,
                              edit_person=?,
                              delete_person=?,
                              create_person=?,
                              view_people=?,
                              view_logs=?,
                              manage_user_rights_presets=?
                              WHERE id=?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("siiiiiiiiiiiiiii",
                        $_POST["preset_name"],
                        $_POST["view_case"],
                        $_POST["edit_case"],
                        $_POST["delete_case"],
                        $_POST["create_case"],
                        $_POST["edit_user"],
                        $_POST["delete_user"],
                        $_POST["create_user"],
                        $_POST["view_users"],
                        $_POST["edit_person"],
                        $_POST["delete_person"],
                        $_POST["create_person"],
                        $_POST["view_people"],
                        $_POST["view_logs"],
                        $_POST["manage_user_rights_presets"],
                        $_POST["id"]);
                    if($stmt->execute()){
                        echo "<h2>User rights updated!</h2>";
                        log_event("USER_RIGHTS_UPDATE", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                    }else{
                        array_push($errors, "<h2>Error: Something went wrong. 
                               If this problem persists, please contact a system administrator</h2>");
                    }
                    $stmt->close();
                }else{
                    //ID Invalid. Really only possible if the user has used developer tools to tamper with the form.
                    //This is more serious than just a normal error, so it gets its own error message.
                    log_event("USER_RIGHTS_FORM_TAMPER", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                    echo "<h2>Error: Invalid ID. This event has been logged</h2>";
                }
            }else{
                log_event("USER_RIGHTS_UPDATE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
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
    }else{
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        header("Location: not_found.php");
    }
}else{
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    header("Location: not_found.php");
}