<?php
session_start();
$page_title = 'Edit Configuration Item';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$base_security_level = $ROLE_ADMIN;


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
		# execute UPDATE statement
		$config = array();
		$id = mysqli_real_escape_string($dbc, $_POST['id']);

		$config['config_item'] = $_POST['config_item'];
		$config['config_value'] = $_POST['config_value'];
		$configCheckSql = "SELECT * FROM config_items WHERE config_id <> $id and config_item = '" . $config['config_item'] . "'";
		$config_check = query_arr($configCheckSql);
		if ($config_check) {
			echo "<center><p><b>Configuration Item  " . $config['config_item'] . " already exists.</b></p>";
			echo "<a href=index.php>Show All Configuration Items</a></center>";
		} else {

			$update = update('config_items', 'config_id', $id, $config);
			if ($update) {
				echo "<center><p><b>The selected Configuration Item  has been updated.</b></p>";
				echo "<a href=index.php>Configuration Items</a></center>";
				log_event($_SESSION['user_id'], "Updated Configuration Item $id");
			} else {
				echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
			}

			mysqli_close($dbc);
		}
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM config_items WHERE config_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such Configuration Item in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			Configuration Item: <input name="config_item" size=50 value="<?php echo $row['config_item']; ?>">
			<p>
				Configuration Value (numeric): <input type="number" name="config_value" step="0.01" value="<?php echo $row['config_value']; ?>">

				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['config_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>