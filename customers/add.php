<?php
session_start();
$page_title = 'Add Customer Record';

require_once('../includes/config.php');
$base_security_level = $ROLE_EMPLOYEE;


//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}

	// check if current user  already has customer record
	$user_id = $_SESSION['user_id'];
	$query = "SELECT * FROM customers WHERE user_id = $user_id";
	$result = query($query);
	if ($result && $_SESSION['role_id'] == $ROLE_CUSTOMER) {
		header('Location: edit.php');
		exit();
	}



	//include the header
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$customer = array();
		$customer['phone_number'] = $_POST['phone_number'];
		$customer['street_one'] = $_POST['street_one'];
		$customer['street_two'] = $_POST['street_two'];
		$customer['city'] = $_POST['city'];
		$customer['state'] = $_POST['state'];
		$customer['zip'] = $_POST['zip'];
		$customer['user_id'] = $_POST['user'];
		$customer['verification_id_one'] = mysqli_real_escape_string($dbc, $_POST['verification_id_one']);
		$customer['verification_id_two'] = mysqli_real_escape_string($dbc, $_POST['verification_id_two']);
		$customer['verification_address_one'] = mysqli_real_escape_string($dbc, $_POST['verification_address_one']);

		$insert = create('customers', $customer);

		if ($insert) {
			echo "<center><p><b>New Customer has been added.</b></p>";
			echo "<a href=index.php>Show All Customers</a></center>";
			log_event($_SESSION['user_id'], "Added New Customer for User ID " . $customer['user_id']);
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		User Association:
		<?php
		$usersQuery = "SELECT * FROM users WHERE user_id NOT IN (SELECT user_id FROM customers)";
		$usersResult = query_arr($usersQuery);
		echo '<select name="user" id="user">';

		if (count($usersResult) == 0) {
			echo '<option value="">-- No Active Users Available --</option>';
		} else {
			echo '<option value="">-- Select User to Associate--</option>';
			foreach ($usersResult as $user) {
				$userID = $user['user_id'];
				$userDisplay = $user['user_id'] . ' - ' . $user['email'];
				if ($user['active'] == 1) {
					echo '<option value="' . $userID . '">' . $userDisplay . '</option>';
				} else {
					echo '<option value="' . $userID . '">' . $userDisplay . ' (Inactive)</option>';
				}
			}
		}

		echo '</select>';
		?>
		<p>
			Phone Number: <input required name="phone_number" size=50>
		<p>
			Street Address: <input required name="street_one" size=50>
		<p>
			Street Address 2: <input name="street_two" size=50>
		<p>
			City: <input required name="city" size=50>
		<p>
			State: <input required name="state" size=2>
		<p>
			Zip Code: <input required name="zip" size=50>
		<p>


			Primary Verification Method Provided : <input required name="verification_id_one" size=50>
		<p>
			Secondary Verification Method Provided : <input required name="verification_id_two" size=50>
		<p>
			Verified Address Provided: <input required name="verification_address_one" size=50>
		<p>
		<p>
			<input type=submit value=submit>
			<input type=reset value=reset>
			<input type=hidden name=submitted value=true>
	</form>
	<?php
	//include the footer
	include("../includes/footer.php");
}
?>