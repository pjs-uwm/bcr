<?php
session_start();
$page_title = 'Edit Rental Category';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
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
	include("../includes/header.php");

	if (isset($_POST['submitted'])) {
		#execute UPDATE statement
		$id = mysqli_real_escape_string($dbc, $_POST['id']);
		$rental_category = mysqli_real_escape_string($dbc, $_POST['rental_category']);
		$category_premium = mysqli_real_escape_string($dbc, $_POST['category_premium']);

		$query = "UPDATE rental_category SET rental_category='$rental_category', category_premium=$category_premium WHERE rental_category_id='$id'";
		$result = query($query);

		if ($result) {
			echo "<center><p><b>The selected rental category has been updated.</b></p>";
			echo "<a href=index.php>Rental Categories</a></center>";
			log_event($_SESSION['user_id'], "Updated Rental Category $rental_category");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM rental_category WHERE rental_category_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such rental category in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			Rental Category: <input required name="rental_category" size=50 value="<?php echo $row['rental_category']; ?>">
			<p>
				Category Premium: <input required type="number" size=5 name="category_premium" step="0.01"
					value="<?php echo $row['category_premium']; ?>">
			<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['rental_category_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>