<?php

// This file contains the database access information.
// This file also establishes a connection to MySQL
// and selects the database.

// Set the database access information as constants:
DEFINE('DB_USER', 'XXXXX');
DEFINE('DB_PASSWORD', 'X');
DEFINE('DB_HOST', 'X');
DEFINE('DB_NAME', 'X');

// Make the connection:
$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die('Could not connect to MySQL: ' . mysqli_connect_error());


?>