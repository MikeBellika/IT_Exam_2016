<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 28-Apr-16
 * Time: 00:34
 */
require("functions.php");
if(isset($_SESSION["id"])){
    $can_view_logs = get_user_rights($_SESSION["id"])["view_logs"];
    if($can_view_logs){
        log_event("VIEW_LOGS", 1, $_SESSION["REMOTE_ADDR"], $_SESSION["id"], NULL);
        ?>
        <html>
            <head>
                <title>Admin - View logs</title>
            </head>
            <body>
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
                    //select everything from logs and username from the users table.
                    //the key in the users table is the id of users_id
                    $stmt = $mysqli->prepare("SELECT logs.*, users.username, people.cpr, people.name FROM logs
                                              LEFT JOIN users ON logs.users_id=users.id
                                              LEFT JOIN people ON logs.people_id=people.id
                                              ORDER BY logs.id");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while($row = $result->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $row["id"]?></td>
                            <td><?php echo $row["action"]?></td>
                            <td><?php echo $row["date"]?></td>
                            <td><?php echo $row["ip"]?></td>
                            <td><?php echo $row["response"]?></td>
                            <td><?php echo $row["username"]?></td>
                            <td><?php echo $row["name"]?></td>
                            <td><?php echo decrypt_cpr($row["cpr"])?></td>
                        </tr>
                        <?php
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