<?php
// Include header.php
$page_title = 'Account Registration';
require_once('includes/config.php');
require_once("includes/header.php");

// Check if the form has been submitted.
if (isset($_POST['submitted'])) {
	require_once('includes/entity_manager.php'); // Connect to the db.
	$errors = array(); // Initialize error array.
	$user = array(); // Initialize user array.
	$customer = array(); // Initialize customer array.

	// Check for a first name.
	if (empty($_POST['first_name'])) {
		$errors[] = 'You forgot to enter your first name.';
	} else {
		$user['first_name'] = mysqli_real_escape_string($dbc, $_POST['first_name']);
	}

	// Check for a last name.
	if (empty($_POST['last_name'])) {
		$errors[] = 'You forgot to enter your last name.';
	} else {
		$user['last_name'] = mysqli_real_escape_string($dbc, $_POST['last_name']);
	}

	// Check for an email address.
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter your email address.';
	} else {
		$user['email'] = mysqli_real_escape_string($dbc, $_POST['email']);
	}

	// Check for a password and match against the confirmed password.
	if (!empty($_POST['password1']) && !empty($_POST['password2'])) {
		if ($_POST['password1'] != $_POST['password2']) {
			$errors[] = 'Your password did not match the confirmed password.';
		} else {
			$pepper_password = hash_hmac("sha256", mysqli_escape_string($dbc, $_POST['password1']), $pepper);
			$hashed_password = password_hash($pepper_password, PASSWORD_ARGON2ID);
			$user['password'] = $hashed_password;
		}
	} else {
		$errors[] = 'You forgot to enter your password.';
	}

	if (empty($_POST['phone_number'])) {
		$errors[] = 'You forgot to enter your phone number.';
	} else {
		$customer['phone_number'] = mysqli_real_escape_string($dbc, $_POST['phone_number']);
	}

	if (empty($_POST['street_one'])) {
		$errors[] = 'You forgot to enter your street address.';
	} else {
		$customer['street_one'] = mysqli_real_escape_string($dbc, $_POST['street_one']);
	}

	if (empty($_POST['city'])) {
		$customer[] = 'You forgot to enter your city.';
	} else {
		$customer['city'] = mysqli_real_escape_string($dbc, $_POST['city']);
	}

	if (empty($_POST['state'])) {
		$errors[] = 'You forgot to enter your state.';
	} else {
		$customer['state'] = mysqli_real_escape_string($dbc, $_POST['state']);
	}

	if (empty($_POST['zip'])) {
		$errors[] = 'You forgot to enter your zip code.';
	} else {
		$customer['zip'] = mysqli_real_escape_string($dbc, $_POST['zip']);
	}


	if (empty($errors)) { // If everything's OK.
		// Register the user in the database.
		// Check for previous registration.
		$query = "SELECT user_id FROM users WHERE email='" . $user['email'] . "'";
		$result = query_arr($query);
		if (count($result) == 0) { // if there is no such email address
			// Make the query.
			$user['role_id'] = $ROLE_CUSTOMER;
			$user['active'] = 1;
			$user['password_changed'] = date('Y-m-d H:i:s');
			$userCreate = create_id_return('users', $user);
			if ($userCreate) {
				$customer['user_id'] = $userCreate;
				$customerCreate = create_id_return('customers', $customer);
				if ($customerCreate) {
					echo "<p>You are now registered. Please, login to use our great service.</p>";
					echo "<a href=login.php>Login</a>";
					include("includes/footer.php");
					exit();
				} else {
					$errors[] = 'You could not be registered due to a system error. We apologize for any inconvenience.'; // Public message.
					$errors[] = mysqli_error($dbc); // MySQL error message.
				}

			} else { // If it did not run OK.
				$errors[] = 'You could not be registered due to a system error. We apologize for any inconvenience.'; // Public message.
				$errors[] = mysqli_error($dbc); // MySQL error message.
			}

		} else { // Email address is already taken.
			$errors[] = 'The email address has already been registered.';
		}

	} // End of if (empty($errors)) IF.

	mysqli_close($dbc); // Close the database connection.

} else { // Form has not been submitted.
	$errors = NULL;
} // End of the main Submit conditional.

// Begin the page now.
if (!empty($errors)) { // Print any error messages.
	echo '<h1>Error!</h1>
	<p>The following error(s) occurred:<br />';
	foreach ($errors as $msg) { // Print each error.
		echo "$msg<br />";
	}
	echo '</p>';
	echo '<p>Please try again.</p>';
}

// Create the form.
?>

<h2>Register an Customer Account with Brew City Rentals</h2>
<h4>Fields marked with an asterisk (*) are required.</h4>
<form action="register.php" method="post">
	First Name*: <input required type="text" name="first_name" size="15" maxlength="15"
		value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : null; ?>" />
	<p>
		Last Name*: <input required type="text" name="last_name" size="15" maxlength="30"
			value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : null; ?>" />
	<p>
		Email Address*: <input required type="text" name="email" size="20" maxlength="40"
			value="<?php echo isset($_POST['email']) ? $_POST['email'] : null; ?>" />
	<p>
		Password*: <input required type="password" name="password1" size="10" maxlength="20" />
	<p>
		Confirm Password*: <input required type="password" name="password2" size="10" maxlength="20" />
	<p>


		<!-- other demographics -->
		Phone Number*: <input required name="phone_number" size=50
			value="<?php echo isset($_POST['phone_number']) ? $_POST['phone_number'] : null; ?>">
	<p>
		Street Address*: <input required name="street_one" size=50
			value="<?php echo isset($_POST['street_one']) ? $_POST['street_one'] : null; ?>">
	<p>
		Street Address 2: <input name="street_two" size=50
			value="<?php echo isset($_POST['street_two']) ? $_POST['street_two'] : null; ?>">
	<p>
		City*: <input required name="city" size=50 value="<?php echo isset($_POST['city']) ? $_POST['city'] : null; ?>">
	<p>
		State*: <input required name="state" size=2
			value="<?php echo isset($_POST['state']) ? $_POST['state'] : null; ?>">
	<p>
		Zip Code*: <input required name="zip" size=10 value="<?php echo isset($_POST['zip']) ? $_POST['zip'] : null; ?>">
	<p>

		</table>
	<p><input type="submit" name="submit" value="Register" />
		<input type="reset" name="reset" value="Reset" />
	</p>
	<input type="hidden" name="submitted" value="TRUE" />
</form>

<?php
// Include footer.php
include("includes/footer.php");
?>