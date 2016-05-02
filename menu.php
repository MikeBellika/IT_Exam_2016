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
    if(get_user_rights($id)["create_person"]){
        echo '<a href="create_person.php">Create person</a> ';
    }
    if(get_user_rights($id)["edit_user"]){
        echo '<a href="create_user.php">Create user</a> ';
    }
    if(get_user_rights($id)["create_user"]){
        echo '<a href="create_user.php">Create user</a> ';
    }
}else{

}