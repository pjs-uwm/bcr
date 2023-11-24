<?php
session_start();
$page_title = 'Brew City Rentals - Transactions';

require_once('../includes/config.php');

//include the header
require_once('../includes/header.php');
require_once('../includes/entity_manager.php');

$base_security_level = $ROLE_CUSTOMER;

?>

<style>
	.overdue {
		color: red;
		font-weight: bold;
	}

	.lost {
		color: #8b0000;
		font-weight: bold;
	}
</style>
<?php

//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}


	log_event($_SESSION['user_id'] ?? 0, "Viewed Transactions");
	if ($_SESSION['role_id'] == $ROLE_CUSTOMER) {
		$customer_id = $_SESSION['customer_id'];
	} else if (isset($_GET['customer_id'])) {
		$customer_id = $_GET['customer_id'];
	} else {
		$customer_id = null;
	}


	if (isset($_SESSION['customer_id']) && isset($_GET['action']) || isset($_POST['action'])) {
		$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];
		switch ($action) {
			case "pickup_movie":
				$transaction_id = $_GET['id'];
				pickup_movie($transaction_id);
				break;
			case "return_movie":
				$transaction_id = $_GET['id'];
				return_movie($transaction_id);
				break;
			case "lost_movie":
				$transaction_id = $_GET['id'];
				lost_movie($transaction_id);
				break;
		}
	}

	echo ("<center>");
	echo ("<h3>Transactions History</h3><p>");

	//Set the number of records to display per page
	$display = 25;
	//Count the number of records;
	$query = 'SELECT COUNT(transaction_id) as total_count FROM transactions';
	if ($customer_id) {
		$query .= " WHERE customer_id = $customer_id";
	}

	$result = query_arr($query);
	$records = $result[0]["total_count"]; //get the number of records	

	echo "Total Transactions - $records <br>";

	//Check if the number of required pages has been determined
	if (isset($_GET['p']) && is_numeric($_GET['p'])) { //Already been determined
		$pages = $_GET['p'];
	} else { //Need to determine


		//Calculate the number of pages ...
		if ($records > $display) { //More than 1 page is needed
			$pages = ceil($records / $display);
		} else {
			$pages = 1;
		}

	} // End of p IF.



	//Determine where in the database to start returning results
	if (isset($_GET['s']) && is_numeric($_GET['s'])) {
		$start = $_GET['s'];
	} else {
		$start = 0;
	}

	//Make the paginated query;
	$query = "SELECT * FROM transactions";
	if ($customer_id) {
		$query .= " WHERE customer_id = $customer_id";
	}
	$query .= " ORDER BY rental_date DESC LIMIT $start, $display";

	$result = query($query);


	//Table header:
	echo "<table cellpadding=5 cellspacing=5 border=1 class=table-list><tr>
	<th>Customer</th><th>Movie (DVD)</th><th>Employee</th><th>Rental Date</th><th>Rental Due</th><th>Pending Pickup</th><th>Rental Returned</th><th>Lost Rental</th></tr>";

	//Fetch and print all the records...
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$isOverdue = (date("Y-m-d") > $row['rental_due'] && !$row['rental_return']) || $row['rental_return'] > $row['rental_due'];
		$statusClass = '';
		if ($row['is_lost']) {
			$statusClass = 'lost';
		} else if ($isOverdue) {
			$statusClass = 'overdue';
		}
		$overdueDays = $isOverdue ? round((strtotime(date("Y-m-d")) - strtotime($row['rental_due'])) / (60 * 60 * 24)) : 0;
		echo "<tr><td>" . get_customer_via_id($row['customer_id'])['customer_name'] . "</td>";
		echo "<td>" . get_movie($row['dvd_id'])['title'] . " (" . $row['dvd_id'] . ")</td>";
		echo "<td>" . (get_employee($row['employee_id'])['employee_name'] ?? "-") . "</td>";
		echo "<td>" . date("n/j/Y", strtotime($row['rental_date'])) . "</td>";
		echo "<td " . ($statusClass ? "class=" . $statusClass : "") . ">" . date("n/j/Y", strtotime($row['rental_due'])) . "</td>";
		if ($_SESSION['role_id'] <= $ROLE_EMPLOYEE) {
			echo "<td>" . ($row['pending_pickup'] ? '<a href=?action=pickup_movie&id=' . $row['transaction_id'] . '>Yes</a>' : 'No') . "</td>";
			echo "<td>" . ($row['rental_return'] && !$row['is_lost'] ? date("n/j/Y", strtotime($row['rental_return'])) : '<a href=?action=return_movie&id=' . $row['transaction_id'] . '>Return</a>') . "</td>";
			echo "<td>" . ($overdueDays > 10 && !$row['rental_return'] && !$row['is_lost'] ? "<a href='?action=lost_movie&id=" . $row['transaction_id'] . "'>Lost</a>" : "") . "</td>";
		} else {
			echo "<td>" . ($row['pending_pickup'] ? 'Yes' : 'No') . "</td>";
			echo "<td>" . ($row['rental_return'] && !$row['is_lost'] ? date("n/j/Y", strtotime($row['rental_return'])) : 'No') . "</td>";
			echo "<td>" . ($overdueDays > 10 && !$row['rental_return'] && !$row['is_lost'] ? "Yes" : "No") . "</td>";
		}
	} // End of While statement
	echo "</table>";
	mysqli_close($dbc); 

	//Make the links to other pages if necessary.
	if ($pages > 1) {
		echo '<br/><table><tr>';
		//Determine what page the script is on:
		$current_page = ($start / $display) + 1;
		//If it is not the first page, make a Previous button:
		if ($current_page != 1) {
			echo '<td><a href="index.php?s=' . ($start - $display) . '&p=' . $pages . '"> Previous </a></td>';
		}
		//Make all the numbered pages:
		for ($i = 1; $i <= $pages; $i++) {
			if ($i != $current_page) { // if not the current pages, generates links to that page
				echo '<td><a href="index.php?s=' . (($display * ($i - 1))) . '&p=' . $pages . '"> ' . $i . ' </a></td>';
			} else { // if current page, print the page number
				echo '<td>' . $i . '</td>';
			}
		} //End of FOR loop
		//If it is not the last page, make a Next button:
		if ($current_page != $pages) {
			echo '<td><a href="index.php?s=' . ($start + $display) . '&p=' . $pages . '"> Next </a></td>';
		}

		echo '</tr></table>';  //Close the table.
	} //End of pages links

}
//include the footer
include('../includes/footer.php');
?>