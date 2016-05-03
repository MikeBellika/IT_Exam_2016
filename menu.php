<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 29-Apr-16
 * Time: 14:47
 */

require("functions.php");

echo '<a href="index.php">Home</a> ';
if(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    if(get_user_rights($id)["create_user"]){
        echo '<a href="create_user.php">Create user</a> ';
    }
    if(get_user_rights($id)["view_users"]){
        echo '<a href="view_users.php">Create user</a> ';
    }
    if(get_user_rights($id)["create_person"]){
        echo '<a href="create_person.php">Create person</a> ';
    }
    if(get_user_rights($id)["manage_user_rights_presets"]){
        echo '<a href="manage_user_rights_presets.php">Manage user rights presets</a> ';
    }
    if(get_user_rights($id)["view_logs"]){
        echo '<a href="view_logs.php">Manage user rights presets</a> ';
    }

}else{

}