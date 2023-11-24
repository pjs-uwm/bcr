<?php

session_start();
require_once('includes/config.php');
require_once('includes/utils.php');

$base_security_level = $ROLE_EMPLOYEE;

//check session first
if (!isset($_SESSION['email'])) {
    header("Location: $baseUrl/login.php");
    exit();
} else {
    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
        header("Location: " . $_GET['return_url']);
        exit();
    } else {

        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            switch ($action) {
                case "impersonate":
                    $customer_id = $_GET['customer_id'];
                    impersonate_customer($customer_id);
                    header("Location: " . $_GET['return_url']);
                    break;
                case "unimpersonate":
                    unimpersonate_customer();
                    header("Location: " . $_GET['return_url']);
            }
        }
    }

}

?>