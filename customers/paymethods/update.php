<?php
session_start();
$page_title = 'Edit Payment Method';

require_once('../../includes/config.php');
require_once('../../includes/entity_manager.php');
$base_security_level = $ROLE_CUSTOMER;


if (isset($_GET['customer_id'])) {
	$customer_id = $_GET['customer_id'];
} else if (isset($_POST['customer_id'])) {
	$customer_id = $_POST['customer_id'];
}


//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	} else if ($_SESSION['role_id'] == $ROLE_CUSTOMER && $_SESSION['customer_id'] != $customer_id) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}
	include("../../includes/header.php");

	if (isset($_POST['submitted'])) {
		# execute UPDATE statement
		$paymentMethod = array();
		$customer_id = mysqli_real_escape_string($dbc, $_POST['customer_id']);
		$pm_id = mysqli_real_escape_string($dbc, $_POST['pm_id']);

		$paymentMethod['pm_description'] = mysqli_real_escape_string($dbc, $_POST['pm_description']);
		$paymentMethod['pm_account_number'] = mysqli_real_escape_string($dbc, $_POST['pm_account_number']);
		$paymentMethod['expiration_date'] = date("Y-m-d", strtotime($_POST['expiration_date']));
		$paymentMethod['customer_id'] = $customer_id;
		$update = update('payment_methods', 'pm_id', $pm_id, $paymentMethod);
		if ($update) {
			echo "<center><p><b>The selected Payment Method has been updated.</b></p>";
			echo "<a href=index.php?id=$customer_id>Payment Methods</a></center>";
			log_event($_SESSION['user_id'], "Updated Payment Method $pm_id for Customer $customer_id.");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$customer_id = $_GET['customer_id'];
		$pm_id = $_GET['pm_id'];
		$query = "SELECT * FROM payment_methods WHERE customer_id=$customer_id AND pm_id=$pm_id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such Payment Method in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			Account Description : <input name="pm_description" size=100 value="<?php echo $row['pm_description']; ?>">
			<p>
				Account Number : <input name="pm_account_number" size=25 value="<?php echo $row['pm_account_number']; ?>">
			<p>
				Expiration Date : <input type="date" name="expiration_date" value="<?php echo $row['expiration_date']; ?>">
			<p>


				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="customer_id" value="<?php echo $row['customer_id']; ?>">
				<input type=hidden name="pm_id" value="<?php echo $row['pm_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../../includes/footer.php");
}
?>