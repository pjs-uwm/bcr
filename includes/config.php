<?php

// CONSTANTS FOR USE ACROSS ALL PAGES
$ROLE_ADMIN = 0;
$ROLE_MANAGER = 10;
$ROLE_EMPLOYEE = 20;
$ROLE_CUSTOMER = 100;

// Base URL Configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host . '/490';
$thumbnailBaseUrl = $baseUrl . '/media/thumbnails/';

// Security Pepper
$pepper = "UBlFmSagqLAPCiqhjcQo";

# global includes
require_once('logger.php');
require_once('utils.php');
require_once('commerce_manager.php')

    ?>