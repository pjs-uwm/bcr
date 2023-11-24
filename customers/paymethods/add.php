<?php
session_start();
$page_title = 'Add Payment Method';

require_once('../../includes/config.php');
$base_security_level = $ROLE_CUSTOMER;

if (isset($_GET['id'])) {
	$customer = $_GET['id'];
} else if (isset($_POST['customer'])) {
	$customer = $_POST['customer'];
} else {
	echo "<p>No Customer ID Provided.";
	exit();
}

//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	} else if ($_SESSION['role_id'] == $ROLE_CUSTOMER && $_SESSION['customer_id'] != $customer) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}


	//include the header
	include("../../includes/header.php");

	require_once('../../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$customer = $_POST['customer'];
		$paymentMethod = array();
		$paymentMethod['customer_id'] = $customer;
		$paymentMethod['pm_description'] = mysqli_escape_string($dbc, $_POST['pm_description']);
		$paymentMethod['pm_account_number'] = mysqli_escape_string($dbc, $_POST['pm_account_number']);
		$paymentMethod['expiration_date'] = date("Y-m-d", strtotime($_POST['expiration_date']));
		$insert = create('payment_methods', $paymentMethod);
		if ($insert) {
			echo "<center><p><b>New Payment Method " . $paymentMethod['pm_description'] . " has been added.</b></p>";
			echo "<a href=index.php?id=$customer>Show All Payment Methods</a></center>";
			log_event($_SESSION['user_id'], "Added New Payment Method for $customer - " . $paymentMethod['pm_description']);
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);

	if (isset($_GET['id'])) {
		$customer = $_GET['id'];
	} else if (isset($_POST['customer'])) {
		$customer = $_POST['customer'];
	} else {
		echo "<p>No Customer ID Provided.";
		exit();
	}
	?>
	<form action="add.php" method="post">
		Account Description : <input name="pm_description" size=100>
		<p>
			Account Number : <input name="pm_account_number" size=25>
		<p>
			Expiration Date : <input type="date" name="expiration_date">
		<p>
		<p>
			<input type=hidden value=<?php echo $customer; ?> name=customer>
			<input type=submit value=submit>
			<input type=reset value=reset>
			<input type=hidden name=submitted value=true>
	</form>
	<?php
	//include the footer
	include("../../includes/footer.php");
}
?>