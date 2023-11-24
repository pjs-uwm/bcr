<?php


# code to reset password
$page_title = 'Password Reset';
require_once('includes/config.php');
require_once('includes/header.php');


// Check if the form has been submitted.
if (isset($_POST['submitted'])) {
	$errors = array(); // Initialize error array.

	// Check for an email address.
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter your email address.';
	} else {
		$email = mysqli_real_escape_string($dbc, $_POST['email']);
	}

	if (empty($errors)) { // If everything's okay.
		// Check for previous registration.
		$new_password = generateSecurePassword();
		$pepper_password = hash_hmac("sha256", $new_password, $pepper);
		$hashed_password = password_hash($pepper_password, PASSWORD_ARGON2ID);
		$password_changed = date('Y-m-d H:i:s');
		$updateSQL = "UPDATE users SET password='$hashed_password', password_changed='$password_changed' WHERE email='$email'";

		$result = query($updateSQL);
		if ($result) {

			// Send an email, if desired.
			$to = $email;
			$subject = "Brew City Rentals Account Recovery";
			$body = "Thank you very much for being a customer of BCR. We value your business!\n\n
			Here is your reset information.\n\n
			Password: " . $new_password . "\n\n
			\n\n
			Please, change your password after you login.\n\n";
			$headers = "From: BCR Account Recovery <pjsinger@uwm.edu>\n";  // <-- Replace this to your email address!!!
			mail($to, $subject, $body, $headers); // SEND the message!  

			// Print a message.
			echo '<h1 id="mainhead">Thank you!</h1>
			<p>Please, check your email to get reset password.</p>';

			// Include the footer and quit the script (to not show the form).
			require_once('includes/footer.php');
			exit();
		} else { // Not registered.
			echo '<font color=red><h4>Error!</h4>
			<p>The email address is not in our database.</p></font>';
		}

	} else { // Report the errors.
		echo '<font color=red><h4>Error!</h4>
		<p>The following error(s) occurred:<br />';
		foreach ($errors as $msg) { // Print each error.
			echo " - $msg<br />\n";
		}
		echo '</p><p>Please try again.</p><p><br /></p></font>';
	} // End of if (empty($errors)) IF.

} // End of the main Submit conditional.

?>

<h3>Reset Account Password</h3>
<form action="forgot.php" method="post">
	Email Address: <input type="text" name="email" size="20" maxlength="40" value="<?php if (isset($_POST['email']))
		echo $_POST['email']; ?>" />
	<input type="submit" name="submit" value="Submit" /></p>
	<input type="hidden" name="submitted" value="TRUE" />
</form>
<p>

	<?php
	require_once('includes/footer.php');

	?>