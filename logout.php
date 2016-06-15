<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 13-Jun-16
 * Time: 20:06
 */
include("functions.php");
session_destroy();
header("Location: index.php");