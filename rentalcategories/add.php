<?php
session_start();
$page_title = 'Add Rental Category';

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

	//include the header
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$rental_category = mysqli_escape_string($dbc, $_POST['rental_category']);
		$category_premium = mysqli_escape_string($dbc, $_POST['category_premium']);

		$query = "INSERT INTO rental_category (rental_category)
			Values ('$rental_category')";
		$result = query($query);
		if ($result) {
			echo "<center><p><b>New rental category of $rental_category has been added.</b></p>";
			echo "<a href=index.php>Show All Rental Categories</a></center>";
			log_event($_SESSION['user_id'], "Added New Rental Category $rental_category");
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		Rental Category: <input name="rental_category" size=50 required>
		<p>
			Category Premium: <input required type="number" size=5 name="category_premium" step="0.01">
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