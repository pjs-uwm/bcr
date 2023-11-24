<?php
require_once('includes/config.php');

session_start();

//check session first
$page_title = 'Access Denied';
$base_security_level = $ROLE_CUSTOMER;

if (!isset($_SESSION['email'])) {
    header("Location: $baseUrl/login.php");
}


include('includes/header.php');

?>


<div id=h2>
    <?php
    echo "Access to Resource Denied. Please contact your system administrator.";
    ?>

</div>
<?php
include('includes/footer.php');
?>