<?php
session_start();
$page_title = 'Add Ledger Entry';

require_once('../../includes/config.php');
$base_security_level = $ROLE_EMPLOYEE;


//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	} else if ($_SESSION['role_id'] == $ROLE_CUSTOMER && $_SESSION['customer_id'] != $_GET['customer_id']) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}

	//include the header
	include("../../includes/header.php");

	require_once('../../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$customer = $_POST['customer'];
		$ledgerEntry = array();
		$ledgerEntry['customer_id'] = $customer;
		$ledgerEntry['amount'] = mysqli_escape_string($dbc, $_POST['amount']);
		if ($ledgerEntry['amount'] < 0) {
			$ledgerEntry['pm_id'] = mysqli_escape_string($dbc, $_POST['pm_id']);
		}
		$ledgerEntry['post_type'] = mysqli_escape_string($dbc, $_POST['post_type']);

		$insert = create('customer_ledger', $ledgerEntry);
		if ($insert) {
			echo "<center><p><b>New Ledger Entry " . $ledgerEntry['post_type'] . " - $" . $ledgerEntry['amount'] . " has been posted.</b></p>";
			echo "<a href=index.php?id=$customer>Show All Customer's Ledger</a></center>";
			log_event($_SESSION['user_id'], "Added New Ledger $customer - " . $ledgerEntry['post_type'] . " - $" . $ledgerEntry['amount']);
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
		Amount to Post (use negative amount to indicate payment): <input type="number" name="amount" step="0.01" required>
		<p>
			Type of Entry : <input name="post_type" size=50>
		<p>
			<?php
			// get payment methods
			$payment_methods = get_payment_methods($customer);
			if ($payment_methods) {
				echo "Payment Method - <select name='pm_id' style='width: 200px;'>";
				foreach ($payment_methods as $payment_method) {
					echo "<option value='" . $payment_method['pm_id'] . "'>" . $payment_method['pm_description'] . "</option>";
				}
				echo "</select>";
			} else {
				echo "No Payment Methods Found";
			}
			echo " <br><br><a href='$baseUrl/customers/paymethods/add.php?id={$_SESSION['customer_id']}'>Add Payment Method</a>";
			?>
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