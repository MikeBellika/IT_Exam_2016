<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 29-Apr-16
 * Time: 14:56
 */
require("functions.php");

if(isset($_SESSION["id"])){
    $can_view_logs = get_user_rights($_SESSION["id"])["view_users"];
    if($can_view_logs){
        log_event("VIEW_USERS", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], NULL);
        top("Admin - View users");
        ?>

        <form action="" method="POST">
            <input type="text" name="search_query">
            <select name="search_term">
                <option value="id">ID</option>
                <option value="username">Username</option>
                <option value="user_rights_id">User rights ID</option>
            </select>
            <input type="submit">
        </form>
        <table>
            <tr>
                <td><b>ID</b></td>
                <td><b>Username</b></td>
                <td><b>User rights ID</b></td>
                <td><b>Name</b></td>
                <td><b>CPR</b></td>
            </tr>
            <?php
            if(empty($_POST)) {
                $stmt = $mysqli->prepare("SELECT users.*, people.first_name, people.last_name, people.cpr FROM USERS 
                                          LEFT JOIN people ON users.people_id=people.id
                                          ORDER BY users.id");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $row["id"] ?></td>
                        <td><?php echo $row["username"] ?></td>
                        <td><?php echo $row["user_rights_id"] ?></td>
                        <td><a href="view_person.php?id=<?php echo $row["people_id"]?>"><?php echo $row["first_name"]." ".$row["last_name"]; ?></a></td>
                        <td><?php echo $row["cpr"] ?></td>
                    </tr>
                    <?php
                }
            }else{
                if(!empty($_POST["search_query"])) {
                    $search_term = $_POST["search_term"];
                    $query = "SELECT users.*, people.name, people.cpr FROM users
                              LEFT JOIN people ON users.people_id=people.id
                              WHERE " . $search_term . " LIKE ? ORDER BY users.id";
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
                                <td><?php echo $row["username"] ?></td>
                                <td><?php echo $row["user_rights_id"] ?></td>
                                <td><a href="view_person.php?id=<?php echo $row["people_id"]?>"><?php echo $row["first_name"]." ".$row["last_name"]; ?></a></td>
                                <td><?php echo $row["cpr"] ?></td>
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