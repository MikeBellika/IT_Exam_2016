<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 03-May-16
 * Time: 14:53
 */
require("functions.php");
if(isset($_SESSION["id"])){
    $can_manage_presets = get_user_rights($_SESSION["id"])["manage_user_rights_presets"];
    if($can_manage_presets){
        ?>
        <html>
        <head>
            <title>Admin - Create user rights preset</title>
        </head>
        <body>
        <?php
        if(empty($_POST)) {
        ?>
        <table>
            <tr>
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
            <tr>
                <form action="" method="POST">
                    <td><input type="text" name="preset_name"></td>
                    <td><input type="hidden" value="0" name="view_case">
                        <input type="checkbox" name="view_case" value="1"></td>
                    <td><input type="hidden" value="0" name="edit_case">
                        <input type="checkbox" name="edit_case" value="1"></td>
                    <td><input type="hidden" value="0" name="delete_case">
                        <input type="checkbox" name="delete_case" value="1"></td>
                    <td><input type="hidden" value="0" name="create_case">
                        <input type="checkbox" name="create_case" value="1"></td>
                    <td><input type="hidden" value="0" name="edit_user">
                        <input type="checkbox" name="edit_user" value="1"></td>
                    <td><input type="hidden" value="0" name="delete_user">
                        <input type="checkbox" name="delete_user" value="1"></td>
                    <td><input type="hidden" value="0" name="create_user">
                        <input type="checkbox" name="create_user" value="1"></td>
                    <td><input type="hidden" value="0" name="view_users">
                        <input type="checkbox" name="view_users" value="1"></td>
                    <td><input type="hidden" value="0" name="edit_person">
                        <input type="checkbox" name="edit_person" value="1"></td>
                    <td><input type="hidden" value="0" name="delete_person">
                        <input type="checkbox" name="delete_person" value="1"></td>
                    <td><input type="hidden" value="0" name="create_person">
                        <input type="checkbox" name="create_person" value="1"></td>
                    <td><input type="hidden" value="0" name="view_people">
                        <input type="checkbox" name="view_people" value="1"></td>
                    <td><input type="hidden" value="0" name="view_logs">
                        <input type="checkbox" name="view_logs" value="1"></td>
                    <td><input type="hidden" value="0" name="manage_user_rights_presets">
                        <input type="checkbox" name="manage_user_rights_presets" value="1"></td>
                    <td><input type="submit" value="Create preset"></td>
                </form>
            </tr>
        </table>
            <?php
        }else{
            $errors = array();
            if($_POST["manage_user_rights_presets"] != $_POST["create_user"]){
                array_push($errors, "A user with the ability to create a person must also the the ability to manage
                user rights");
            }
            if(empty($_POST["preset_name"])){
                array_push($errors, "A preset needs to have a name");
            }
            if(empty($errors)){
                $query = "INSERT INTO user_rights
                          (preset_name,
                          view_case,
                          edit_case,
                          delete_case,
                          create_case,
                          edit_user,
                          delete_user,
                          create_user,
                          view_users,
                          edit_person,
                          delete_person,
                          create_person,
                          view_people,
                          view_logs,
                          manage_user_rights_presets)
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("siiiiiiiiiiiiii",
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
                    $_POST["manage_user_rights_presets"]);
                if($stmt->execute()){
                    echo "<h2>Preset created!</h2>";
                    log_event("USER_RIGHTS_PRESET_CREATE", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
                }
                $stmt->close();
            }else{
                log_event("USER_RIGHTS_PRESET_CREATE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
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