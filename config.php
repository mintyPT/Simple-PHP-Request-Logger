<?php

    include_once 'functions.php';

$log_host      = "127.0.0.1";
$log_username  = "root";
$log_passwd    = "";
$log_dbname    = "logger";

    $con = create_db_connections($log_host, $log_username, $log_passwd, $log_dbname);