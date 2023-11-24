<?php
session_start();
$page_title = 'Brew City Rentals - Your Cart';

require_once('../includes/config.php');

	//include the header
	require_once ('../includes/header.php');
	require_once ('../includes/entity_manager.php');
	log_event($_SESSION['user_id'] ?? 0, "Viewed Shopping Cart");

	if (isset($_SESSION['customer_id']) && isset($_GET['action']) || isset($_POST['action'])) {
		$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];
		switch($action) {
			case "remove_movie":
				$movie_id = $_GET['movie_id'];
				remove_cart_item($movie_id);
				break;
			case "checkout":
				$payment_method_id = mysqli_escape_string($dbc, $_POST['payment_method_id']);
				$cart_items = get_cart_items();
				$rental_subtotal = calculate_rental_amount($cart_items);
				$rental_premium_subtotal = calculate_rental_premium($cart_items);
				$outstanding_balance = get_outstanding_balance($_SESSION['customer_id']);
				$tax_amount = round(get_tax_amount($rental_subtotal + $outstanding_balance),2);
				$rental_due_date = get_due_date();
				$total_amount_credit = round(($rental_subtotal + $outstanding_balance + $tax_amount + $rental_premium_subtotal * -1), 2);
				$total_amount_debit = round($rental_subtotal + $tax_amount + $rental_premium_subtotal, 2);

				$errors = array();

				// create ledger entry - debit for rental
				$ledgerDebit = array();
				$ledgerDebit['customer_id'] = $_SESSION['customer_id'];
				$ledgerDebit['pm_id'] = $payment_method_id;
				$ledgerDebit['amount'] = number_format($total_amount_debit, 2, '.', '');
				$ledgerDebit['post_type'] = "Rental Debit";
				$ledgerResultDebit = create('customer_ledger', $ledgerDebit);

				// post payment for rental
				$ledger = array();
				$ledger['customer_id'] = $_SESSION['customer_id'];
				$ledger['pm_id'] = $payment_method_id;
				$ledger['amount'] = number_format($total_amount_credit, 2, '.', '');
				$ledger['post_type'] = $outstanding_balance > 0 ? "Rental Credit / Late Fee (Payment)" : "Rental Credit (Payment)";
				$ledgerResult = create_id_return('customer_ledger', $ledger);
				if ($ledgerResult) {
					$ledger_id = $ledgerResult;
				} else {
					$errors[] = "Error Creating Ledger Entry";
				}

				// update transactions
				foreach ($cart_items as $item) {
					$transaction = array();
					$transaction['customer_id'] = $_SESSION['customer_id'];
					$transaction['dvd_id'] = $item;
					$transaction['ledger_id'] = $ledger_id;
					if (isset($_SESSION['employee_id']) && $_SESSION['employee_id'] > 0) {
						$transaction['employee_id'] = $_SESSION['employee_id'];
					} else {
						$transaction['pending_pickup'] = 1;
					}
					$transaction['rental_date'] = date("Y-m-d");
					$transaction['rental_due'] = date("Y-m-d", strtotime($rental_due_date));
				
					$transactionResult = create_id_return('transactions', $transaction);
					if ($transactionResult) {
						$transaction_id = $transactionResult;
					} else {
						$errors[] = "Error Creating Transaction";
					}
				}
				if ($errors) {
					echo "<p>Errors Processing Transaction</p>";
					foreach($errors as $error) {
						echo "<p>$error</p>";
					}
				} else {
					// clear cart
					init_cart();
					echo "<p>Rental Completed</p>";
					echo "<p>Customer Ledger ID - $ledger_id</p>";
					echo "<a href='receipt.php?ledger_id=$ledger_id' target='_blank'>View Receipt to Print</a> (Opens in New Window)";
				}


				break;
		}
	}

	echo ("<center>"); 
	echo ("<h3>BCR Cart</h3><p>");
	if (isset($_SESSION['customer_id'])) {
		echo (generate_cart_icon());
	}
	//Set the number of records to display per page
	$display = isset($_GET['display_count']) ? $_GET['display_count'] : 10;
	$records = get_cart_items() ? count(get_cart_items()) : 0; 
	$cart_items = get_cart_items();

	echo "Total Items in Cart - $records <br></center>";


	if ($cart_items) {
    echo '<div class="cart-grid">';
    foreach ($cart_items as $movie_id) {
		$movie = get_movie($movie_id);
        

        echo '<div class="cart-item">';
        $thumbnail = $movie['thumbnail_location'] ?: 'notn.png';
        echo '<div class="cart-thumbnail-container"><img class="cart-thumbnail" src="' . $thumbnailBaseUrl . '/' . $thumbnail . '" alt="' . htmlspecialchars($movie['title']) . '"></div>';
        echo '<div class="item-details"><h2>' . htmlspecialchars($movie['title']) . '</h2>';
		echo "<a href='?action=remove_movie&movie_id={$movie['movie_id']}'>Remove</a>";
		
    echo '</div></div>'; }

	$errors = array();
	$warnings = array();
	// get cart total
	$rental_subtotal = calculate_rental_amount($cart_items);
	$rental_premium_subtotal = calculate_rental_premium($cart_items);

	// check if outstanding balance
	$outstanding_balance = get_outstanding_balance($_SESSION['customer_id']) > 0 ? get_outstanding_balance($_SESSION['customer_id']) : 0.00;
	if ($outstanding_balance > 0) {
	$warnings[] = "Outstanding Balance - $$outstanding_balance - this must be paid as part of this transaction to check out.";
	}
	// display overdue transactions - gated
	$overdue_transactions =  get_overdue_transactions($_SESSION['customer_id']);
	// calculate due date for current transaction
	$due_date = get_due_date();

	// calculate tax based on balance due
	$tax_amount = round(get_tax_amount($rental_subtotal + $outstanding_balance + $rental_premium_subtotal),2);

	$cart_total = $rental_subtotal + $outstanding_balance + $tax_amount + $rental_premium_subtotal;

	// display all totals in table format
	echo "<table>";
	echo "<tr><td>Subtotal</td><td>$$rental_subtotal</td></tr>";
	echo "<tr><td>Premium Subtotal</td><td>$$rental_premium_subtotal</td></tr>";
	echo "<tr><td>Outstanding Balance</td><td>$$outstanding_balance</td></tr>";
	echo "<tr><td>Tax</td><td><u>$$tax_amount</u></td></tr>";
	echo "<tr><td>Total</td><td>$$cart_total</td></tr>";
	echo "</table>";

	// can check out if no overdue transactions
	if ($overdue_transactions) {
		$errors[] = "Overdue Transactions - You must return these movies before you can check out.";
		foreach ($overdue_transactions as $overdue_transaction) {
			echo "<p>Overdue Transaction - {$overdue_transaction['title']} - {$overdue_transaction['due_date']}</p>";
		}
	}

	// get payment methods
	$payment_methods = get_payment_methods($_SESSION['customer_id']);
	echo "<form action='cart.php' method='post'>";
	if ($payment_methods) {
		echo "Payment Method - <select name='payment_method_id' style='width: 200px;'>";
		foreach ($payment_methods as $payment_method) {
			echo "<option value='{$payment_method['pm_id']}'>{$payment_method['pm_description']}</option>";
		}
		echo "</select>";
	} else {
		$errors[] = "No Payment Methods Found - Please Add a Payment Method to Check Out";
		echo "No Payment Methods Found";
	}
	echo " <br><br><a href='$baseUrl/customers/paymethods?id={$_SESSION['customer_id']}'>Add Payment Method</a>";
	echo "<input type='hidden' name='action' value='checkout'>";

	if ($errors) {
		echo "<p><b>Errors - These Must Be Resolved to Check Out Movies with BCR</b></p>";
		foreach ($errors as $error) {
			echo "<p>$error</p>";
		}
	} else {
	if ($warnings) {
		echo "<p><b>Warnings</b></p>";
		foreach ($warnings as $warning) {
			echo "<p>$warning</p>";
		}
	}
	echo "<p><input type='submit' value='Checkout'>";
	}
	echo "</form>";

} else {
	echo "No Items in Cart";
}
	

	
	//include the footer
	include ('../includes/footer.php');
?>


