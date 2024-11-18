<?php
    $host = "localhost";
    $root = "root";
    $password = "";
    $db = "pats";
    $connect = mysqli_connect($host, $root, $password, $db);

    if(!$connect){
        die("Connection to the database failed!");
    }
