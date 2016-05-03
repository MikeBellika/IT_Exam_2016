<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 01-May-16
 * Time: 15:40
 */
require("functions.php");
if(isset($_SESSION["id"])) {
    $can_view_case = get_user_rights($_SESSION["id"])["view_case"];
    if ($can_view_case) {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
            log_event("VIEW_CASE", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $id);
            $stmt = $mysqli->prepare("SELECT cases.*, people.first_name, people.last_name FROM cases
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

            top("Viewing case - ".$row["title"]);
            ?>

            <html>
            <head>
                <title>Viewing case - <?php echo $row["title"]; ?></title>
            </head>
            <body>
            <table>
                <tr>
                    <td>Title:</td>
                    <td><?php echo $row["title"]; ?></td>
                </tr>
                <tr>
                    <td>Person:</td>
                    <td><?php echo $row["first_name"]." ".$row["last_name"]; ?></td>
                </tr>
                <tr>
                    <td>Content:</td>
                    <td><?php echo $row["content"]; ?></td>
                </tr>
                <tr>
                    <td>Creation date:</td>
                    <td><?php echo $row["creation_date"]; ?></td>
                </tr>
            </table>
            <?php
            if(get_user_rights($_SESSION["id"])["edit_case"]){
                echo '<a href="edit_case.php?id='.$id.'">Edit case</a>';
            }
            ?>
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
                while($row = $result->fetch_assoc()){
                    ?>

                        <tr>
                            <td><?php echo $row["username"] ?></td>
                            <td><?php echo $row["date"] ?></td>
                        </tr>
                    <?php
                }
                ?>
            </table>
            </body>
            </html>
            <?php
        }
    }else{
        log_event("VIEW_CASE", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $_GET["id"]);
        echo "<h2>You do not have permission to view this page.</h2>";
    }
}else{
    log_event("VIEW_CASE", 0, $_SERVER["REMOTE_ADDR"], NULL, $id);
    echo "<h2>You need to login to view this page.</h2>";
}