<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 01-May-16
 * Time: 02:55
 */
require("functions.php");
if(isset($_SESSION["id"])) {
    $can_view_people = get_user_rights($_SESSION["id"])["view_people"];
    if ($can_view_people) {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
            log_event("VIEW_PERSON", 1, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $id);
            $stmt = $mysqli->prepare("SELECT * FROM people WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                die("<h1>No person found</h1>");
            }

            $row = $result->fetch_assoc();
            $stmt->close();
            ?>

            <html>
            <head>
                <title><?php echo $row["first_name"]." ".$row["last_name"]; ?></title>
            </head>
            <body>
                <table>
                    <tr>
                        <td>Name:</td>
                        <td><?php echo $row["first_name"]." ".$row["last_name"]; ?></td>
                    </tr>
                    <tr>
                        <td>CPR:</td>
                        <td><?php echo $row["cpr"]; ?></td>
                    </tr>
                </table>
            <h2>Cases:</h2>
            <table>
                <?php
                $stmt = $mysqli->prepare("SELECT * FROM cases WHERE people_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 0) {
                    echo "<h3>No cases for this person</h3>";
                }else{
                    ?>
                    <table>
                    <tr>
                        <td><b>ID</b></td>
                        <td><b>Case name</b></td>
                        <td><b>Creation date</b></td>
                    </tr>
                    <?php
                    while($row = $result->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $row["id"] ?></td>
                            <td><?php echo $row["title"] ?></td>
                            <td><?php echo $row["creation_date"] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </table>
                    <?php
                }


                ?>

            </table>
            </body>
            </html>
            <?php
        }
    }else{
        log_event("VIEW_PERSON", 0, $_SERVER["REMOTE_ADDR"], $_SESSION["id"], $_GET["id"]);
        echo "<h2>You do not have permission to view this page.</h2>";
    }
}else{
    log_event("VIEW_PERSON", 0, $_SERVER["REMOTE_ADDR"], NULL, $id);
    echo "<h2>You need to login to view this page.</h2>";
}
?>