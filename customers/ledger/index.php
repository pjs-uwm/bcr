<?php
session_start();
$page_title = 'Customer Ledger';

require_once('../../includes/config.php');
$base_security_level = $ROLE_CUSTOMER;

//check session first
if (!isset($_SESSION['email'])) {
	header("Location: $baseUrl/login.php");
	exit();
} else {
	if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	} else if ($_SESSION['role_id'] == $ROLE_CUSTOMER && $_SESSION['customer_id'] != $_GET['id']) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}

	//include the header
	include('../../includes/header.php');
	require_once('../../includes/entity_manager.php');

	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		log_event($_SESSION['user_id'], "Viewed Customer Ledger");
		echo ("<center>");
		echo ("<h3>Customer Ledger for " . get_customer_via_id($id)['customer_name'] . "</h3><p>");
		//Set the number of records to display per page
		$display = 20;
		//Count the number of records;
		$query = 'SELECT COUNT(ledger_id) FROM customer_ledger WHERE customer_id=' . $id;
		$result = query($query);
		$row = @mysqli_fetch_array($result, MYSQLI_NUM);
		$records = $row[0]; //get the number of records	

		echo "Total Ledger Entries - $records <br>";
		if ($_SESSION['role_id'] != $ROLE_CUSTOMER) {
			echo ("<p><a href=add.php?id=$id>Add Ledger Entry</a><p>");
		}

		if (get_outstanding_balance($id) > 0) {
			echo "Total Oustanding Balance - $" . get_outstanding_balance($id) . "<p>";
		}

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
		$query = "SELECT * FROM customer_ledger WHERE customer_id=$id LIMIT $start, $display";
		$result = query($query);


		//Table header:
		echo "<table cellpadding=5 cellspacing=5 border=1 class=table-list><tr><th>Post Date</th>
	<th>Description</th><th>Amount</th><th>*</th>";
		if ($_SESSION['role_id'] != $ROLE_CUSTOMER) {
			echo "<th>*</th>";
		}

		echo "</tr>";

		//Fetch and print all the records...

		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			echo "<tr>";
			echo "<td>" . date("n/j/Y g:iA", strtotime($row['post_date'])) . "</td>";
			echo "<td>" . $row['post_type'] . "</td>";
			echo "<td>$" . $row['amount'] . "</td>";
			$ledger_transactions = get_ledger_transactions($row['ledger_id']);
			if ($ledger_transactions > 0) {
				echo "<td><a target='_blank' href=$baseUrl/catalog/receipt.php?ledger_id=" . $row['ledger_id'] . ">Receipt</a></td>";
			} else {
				echo "<td>-</td>";
			}

			if ($_SESSION['role_id'] != $ROLE_CUSTOMER) {
				if (get_ledger_transactions($row['ledger_id']) > 0) {
					echo "<td>-</td>";

				} else {
					echo "<td><a href=deleteconfirm.php?ledger_id=" . $row['ledger_id'] . "&customer_id=" . $row['customer_id'] . ">Delete</a></td>";
				}

			}
			echo "</tr>";
		} // End of While statement
		echo "</table>";
		mysqli_close($dbc); // Close the database connection.

		//Make the links to other pages if necessary.
		if ($pages >= 1) {
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
			//If it is not the last page, make a Next button
			if ($current_page != $pages) {
				echo '<td><a href="index.php?s=' . ($start + $display) . '&p=' . $pages . '"> Next </a></td>';
			}

			echo '</tr></table>'; //Close the table.
		} //End of pages links
		else {
			echo "<br/><p>No Current Ledger for Customer</p>";
		}
		echo "<a href='$baseUrl/customers'>Return to Customer List</a>";
		//include the footer
		include('../../includes/footer.php');
	}
}
?>