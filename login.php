<?php

require_once('includes/config.php');

// Check if the form has been submitted.
if (isset($_POST['submitted'])) {
	require_once('includes/entity_manager.php'); // Connect to the db.
	$errors = array(); // Initialize error array.
	// Check for an email address.
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter your email address.';
	} else {
		$e = mysqli_real_escape_string($dbc, trim($_POST['email']));
	}
	// Check for a password.
	if (empty($_POST['password'])) {
		$errors[] = 'You forgot to enter your password.';
	} else {
		$pepper_password = hash_hmac("sha256", mysqli_escape_string($dbc, $_POST['password']), $pepper);
	}
	if (empty($errors)) { // If everything's OK.
		/* Retrieve the user_id and first_name for 
						  that email/password combination. */
		$query = "SELECT user_id, email, password, role_id FROM users WHERE email='$e' and active = 1";
		$result = query_arr($query); // Run the query.
		if (count($result) == 1) {
			$row = $result[0];
			$password_hashed = $row['password'];
			$valid_password = password_verify($pepper_password, $password_hashed);
		} else {
			$row = NULL;
		}
		if ($row && $valid_password) { // A record was pulled from the database.

			//Set the session data:
			session_start();
			$_SESSION['user_id'] = $row['user_id'];
			$_SESSION['email'] = $row['email'];
			$_SESSION['role_id'] = $row['role_id'];

			$customer = get_customer($row['user_id']);
			$_SESSION['customer_id'] = $customer['customer_id'];
			$_SESSION['customer_name'] = $customer['customer_name'];
			$_SESSION['base_customer_id'] = $customer['customer_id'];
			$_SESSION['base_customer_name'] = $customer['customer_name'];

			$_SESSION['employee_id'] = get_employee_id($row['user_id']);


			init_cart();

			// Redirect:
			header("Location: $baseUrl/index.php");
			exit(); // Quit the script.

		} else { // No record matched the query.
			$errors[] = "Incorrect User Credentials Provided"; // Public message.
		}
	} // End of if (empty($errors)) IF.
	mysqli_close($dbc); // Close the database connection.
} else { // Form has not been submitted.
	$errors = NULL;
} // End of the main Submit conditional.

// Begin the page now.
$page_title = 'Login';
require_once('includes/config.php');
include('includes/header.php');

if (!empty($errors)) {
	echo '<h3>Error</h3>
	<p>The following error(s) occurred:<br />';
	foreach ($errors as $msg) {
		echo " - $msg<br />\n";
	}
	echo '</p><p>Please try again or use the <a href=forgot.php>Forgot Password</a> feature to reset your psasword.</p>';
}

// Create the form.
?>

<h2>Brew City Rentals Customer Access</h2>
Please use the form below to sign in to your account.<p>
<form action="login.php" method="post">
	<table class="container">
		<tr>
			<td>Email Address:</td>
			<td><input required type="text" name="email" size="20" maxlength="40" /></td>
		</tr>
		<tr>
			<td>Password: </td>
			<td><input required type="password" name="password" size="20" maxlength="20" /></td>
		</tr>
		<tr>
			<td><input type="submit" name="submit" value="Login" /></td>
		</tr>
	</table>
	<input type="hidden" name="submitted" value="TRUE" />
</form>

<?php
include('includes/footer.php');
?>