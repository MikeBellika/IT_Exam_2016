<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 15-Jun-16
 * Time: 04:22
 */
include("functions.php");

class User
{
    public function generate_hash($password){
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function login($username, $password){
        global $mysqli;
        $errors = array();

        $stmt = $mysqli->prepare("SELECT id,password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        if($stmt->execute()){
            $stmt->bind_result($id, $password_result);
            $stmt->fetch();
            if(!password_verify($password, $password_result)){
                array_push($errors, "Invalid username or password");

                log_event("LOGIN", 0, $_SERVER["REMOTE_ADDR"], NULL, NULL);
                echo "<ul>";
                foreach($errors as $error){
                    echo "<li>".$error."</li>";
                }
                echo "</ul>";
            }else{
                $stmt->close();
                echo "Logged in";
                log_event("LOGIN", 1, $_SERVER["REMOTE_ADDR"], $id, NULL);
                $_SESSION["username"] = $_POST["username"];
                $_SESSION["id"] = $id;
                header("refresh:0");
            }
        }else{
            array_push($errors, "Invalid username or password");

            log_event("LOGIN", 0, $_SERVER["REMOTE_ADDR"], NULL, NULL);
            echo "<ul>";
            foreach($errors as $error){
                echo "<li>".$error."</li>";
            }
            echo "</ul>";
        }
    }
    
    public function create_user($people_id, $username, $password, $userrights_id){
        global $mysqli;
        $error = array();

        if(empty($people_id) || empty($username) || empty($password) || empty($userrights_id)){
            array_push($error, "All fields must be filled");
        }

        if(strlen($password) < 8){
            array_push($error, "Password has to be at least 8 characters");
        }

        $stmt = $mysqli->prepare("SELECT id FROM people WHERE id = ?");
        $stmt->bind_param("i", $people_id);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows() == 0){
            array_push($error, "No person with that ID");
            return $error;
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT id FROM users WHERE people_id = ?");
        $stmt->bind_param("i", $people_id);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows() > 0){
            array_push($error, "There is already a user with that ID");
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows() > 0){
            array_push($error, "There is already a user with that username");
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT id FROM user_rights WHERE id = ?");
        $stmt->bind_param("i", $userrights_id);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows() == 0){
            array_push($error, "No user_right with that id");
        }
        $stmt->close();

        if(count($error) == 0){
            $password = generate_hash($password);
            $query = "INSERT INTO users (username, password, user_rights_id, people_id) VALUES (?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ssii', $username, $password, $userrights_id, $people_id);
            if(!$stmt->execute()){
                $stmt->close();
                array_push($error, "Something went wrong when creating user. Please contact a system administrator");
                return $error;
            }
        }else{
            return $error;
        }
        $stmt->close();
        return true;


    }
}