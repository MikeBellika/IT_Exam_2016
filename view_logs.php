<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 28-Apr-16
 * Time: 00:34
 */
//TODO: Allow for multiple search params
require("functions.php");
if(isset($_SESSION["id"])){
    $can_view_logs = get_user_rights($_SESSION["id"])["view_logs"];
    if($can_view_logs){
        log_event("VIEW_LOGS", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
        top("Admin - View logs");
        ?>
                <form action="" method="POST">
                    <input type="text" name="search_query">
                    <select name="search_term">
                        <option value="id">ID</option>
                        <option value="action">Action</option>
                        <option value="date">Date</option>
                        <option value="ip">IP</option>
                        <option value="response">Success</option>
                        <option value="username">Username</option>
                        <option value="name">Person name</option>
                        <option value="cpr">CPR</option>
                    </select>
                    <input type="submit">
                </form>
                <table>
                    <tr>
                        <td><b>ID</b></td>
                        <td><b>Action</b></td>
                        <td><b>Date</b></td>
                        <td><b>IP</b></td>
                        <td><b>Success</b></td>
                        <td><b>User</b></td>
                        <td><b>Person name</b></td>
                        <td><b>Person cpr</b></td>
                    </tr>
                    <?php
                    if(empty($_POST)) {
                        //select everything from logs and username from the users table.
                        //the key in the users table is the id of users_id
                        $stmt = $mysqli->prepare("SELECT logs.*, users.username, people.cpr, people.name FROM logs
                                              LEFT JOIN users ON logs.users_id=users.id
                                              LEFT JOIN people ON logs.people_id=people.id
                                              ORDER BY logs.id");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $row["id"] ?></td>
                                <td><?php echo $row["action"] ?></td>
                                <td><?php echo $row["date"] ?></td>
                                <td><?php echo $row["ip"] ?></td>
                                <td><?php echo $row["response"] ?></td>
                                <td><?php echo $row["username"] ?></td>
                                <td><?php echo $row["name"] ?></td>
                                <td><?php echo decrypt_cpr($row["cpr"]) ?></td>
                            </tr>
                            <?php
                        }
                    }else{
                        if(!empty($_POST["search_query"])) {
                            $search_term = $_POST["search_term"];
                            if ($search_term == "id"
                                || $search_term == "action"
                                || $search_term == "date"
                                || $search_term == "ip"
                                || $search_term == "response"
                            ) {
                                $query = "SELECT logs.*, users.username, people.cpr, people.first_name, people.last_name 
                                              FROM logs
                                              LEFT JOIN users ON logs.users_id=users.id
                                              LEFT JOIN people ON logs.people_id=people.id
                                              WHERE logs." . $search_term . " LIKE ?
                                              ORDER BY logs.id";
                            } elseif ($search_term == "cpr"
                                || $search_term == "name"
                            ) {
                                $query = "SELECT logs.*, users.username, people.cpr, people.first_name, people.last_name 
                                              FROM logs
                                              LEFT JOIN users ON logs.users_id=users.id
                                              LEFT JOIN people ON logs.people_id=people.id
                                              WHERE people." . $search_term . " LIKE ?
                                              ORDER BY logs.id";
                            } elseif ($search_term == "username") {
                                $query = "SELECT logs.*, users.username, people.cpr, people.first_name, people.last_name
                                              FROM logs
                                              LEFT JOIN users ON logs.users_id=users.id
                                              LEFT JOIN people ON logs.people_id=people.id
                                              WHERE users." . $search_term . " LIKE ?
                                              ORDER BY logs.id";
                            } else {
                                die();
                            }
                            $search_query = "%" . $_POST["search_query"] . "%";
                            $stmt = $mysqli->prepare($query);
                            $stmt->bind_param("s", $search_query);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if($result->num_rows > 0) {

                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row["id"] ?></td>
                                        <td><?php echo $row["action"] ?></td>
                                        <td><?php echo $row["date"] ?></td>
                                        <td><?php echo $row["ip"] ?></td>
                                        <td><?php echo $row["response"] ?></td>
                                        <td><?php echo $row["username"] ?></td>
                                        <td><?php echo $row["first_name"]." ".$row["last_name"]; ?></td>
                                        <td><?php echo decrypt_cpr($row["cpr"]) ?></td>
                                    </tr>
                                    <?php
                                }
                            }else{
                                echo "<h2>No results</h2>";
                            }
                        }else{
                            echo "<h2>You need to fill in a search term";
                        }
                    }
                    ?>
                </table>
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