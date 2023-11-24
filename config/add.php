<?php
session_start();
$page_title = 'Add Configuration Item';

require_once('../includes/config.php');
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

	//include the header
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$config = array();
		$config['config_item'] = $_POST['config_item'];
		$config['config_value'] = $_POST['config_value'];
		$configCheckSql = "SELECT * FROM config_items WHERE config_item = '" . $config['config_item'] . "'";
		$config_check = query_arr($configCheckSql);
		if ($config_check) {
			echo "<center><p><b>Configuration Item  " . $config['config_item'] . " already exists.</b></p>";
			echo "<a href=index.php>Show All Configuration Items</a></center>";
		} else {


			$insert = create('config_items', $config);
			if ($insert) {
				echo "<center><p><b>New Configuration Item  " . $config['config_item'] . " has been added.</b></p>";
				echo "<a href=index.php>Show All Configuration Items</a></center>";
				log_event($_SESSION['user_id'], "Added New Configuration Item " . $config['config_item']);
			} else {
				echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
			}
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		Configuration Item: <input name="config_item" required size=50>
		<p>
			Configuration Value (numeric): <input type="number" name="config_value" step="0.01" required>
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