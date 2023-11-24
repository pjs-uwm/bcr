<?php
session_start();
$page_title = 'Edit Customer Record';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$base_security_level = $ROLE_CUSTOMER;


//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}
	include("../includes/header.php");

	if (isset($_POST['submitted'])) {
		#execute UPDATE statement
		$customer = array();
		$id = mysqli_real_escape_string($dbc, $_POST['id']);
		$customer['phone_number'] = mysqli_real_escape_string($dbc, $_POST['phone_number']);
		$customer['street_one'] = mysqli_real_escape_string($dbc, $_POST['street_one']);
		$customer['street_two'] = mysqli_real_escape_string($dbc, $_POST['street_two']);
		$customer['city'] = mysqli_real_escape_string($dbc, $_POST['city']);
		$customer['state'] = mysqli_real_escape_string($dbc, $_POST['state']);
		$customer['zip'] = mysqli_real_escape_string($dbc, $_POST['zip']);
		if ($_SESSION['role_id'] != $ROLE_CUSTOMER) {
			$customer['user_id'] = mysqli_real_escape_string($dbc, $_POST['user']);
			$customer['verification_id_one'] = mysqli_real_escape_string($dbc, $_POST['verification_id_one']);
			$customer['verification_id_two'] = mysqli_real_escape_string($dbc, $_POST['verification_id_two']);
			$customer['verification_address_one'] = mysqli_real_escape_string($dbc, $_POST['verification_address_one']);
		}

		$result = update('customers', 'customer_id', $id, $customer);

		if ($result) {
			echo "<center><p><b>Customer been updated.</b></p>";
			echo "<a href=index.php>Customers</a></center>";
			log_event($_SESSION['user_id'], "Updated Customer $id");
		} else {
			print($result);
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		if ($_SESSION['role_id'] == $ROLE_CUSTOMER && isset($_SESION['customer_id'])) {
			$id = $_SESSION['customer_id'];
		} else {
			$id = $_GET['id'];
		}
		$result = read('customers', $id, 'customer_id');
		$num = $result != null ? count($result) : 0;
		if ($num != 1) {
			echo "<p>There is no such Customer in the database.</p>";
			exit();
		} else {
			$row = $result[0];
		}

		?>

		<form action="update.php" method="post">
			User Association:
			<?php
			$usersQuery = "SELECT * FROM users WHERE user_id NOT IN (SELECT user_id FROM customers) OR user_id = " . $row['user_id'] . " ORDER BY user_id ASC";
			$usersResult = query($usersQuery);
			$disabled = $_SESSION['role_id'] == $ROLE_CUSTOMER ? 'disabled' : '';
			echo '<select name="user" id="user" ' . $disabled . '>';

			if (mysqli_num_rows($usersResult) < 1) {
				echo '<option value="">-- No Active Users Available --</option>';
			} else {
				echo '<option value="">-- Select User to Associate--</option>';
				foreach ($usersResult as $user) {
					$userID = $user['user_id'];
					$userDisplay = $user['user_id'] . ' - ' . $user['email'];
					$selected = ($userID == $row['user_id']) ? 'selected' : '';
					if ($user['active'] == 1) {
						echo '<option value="' . $userID . '" ' . $selected . '>' . $userDisplay . ' </option>';
					} else {
						echo '<option value="' . $userID . '" ' . $selected . '>' . $userDisplay . ' (Inactive)</option>';
					}
				}
			}

			echo '</select>';
			?>
			<p>
				Phone Number: <input required name="phone_number" size=50 value="<?php echo $row['phone_number']; ?>">
			<p>
				Street Address: <input required name="street_one" size=50 value="<?php echo $row['street_one']; ?>">
			<p>
				Street Address 2: <input name="street_two" size=50 value="<?php echo $row['street_two']; ?>">
			<p>
				City: <input required name="city" size=50 value="<?php echo $row['city']; ?>">
			<p>
				State: <input required name="state" size=2 value="<?php echo $row['state']; ?>">
			<p>
				Zip Code: <input required name="zip" size=50 value="<?php echo $row['zip']; ?>">
			<p>

				Primary Verification Method Provided : <input name="verification_id_one" size=50 <?php echo $disabled ?>
					value="<?php echo $row['verification_id_one']; ?>">
			<p>
				Secondary Verification Method Provided : <input name="verification_id_two" size=50 <?php echo $disabled ?>
					value="<?php echo $row['verification_id_two']; ?>">
			<p>
				Verified Address Provided : <input name="verification_address_one" size=50 <?php echo $disabled ?>
					value="<?php echo $row['verification_address_one']; ?>">
			<p>
			<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $id; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>