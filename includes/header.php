<!DOCTYPE html>
<html lang="en">

<head>
	<!-- INFOST 490 - Final -->
	<meta charset="UTF-8">
	<meta name="robots" content="nofollow, noindex">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="media/favicon/favicon.ico" sizes="16x16" type="image/x-icon">
	<link rel="icon" href="media/favicon/favicon.ico" sizes="32x32" type="image/x-icon">
	<link rel="apple-touch-icon" href="media/favicon/favicon.ico" sizes="180x180">
	<link rel="manifest" href="media/favicon/site.webmanifest">
	<title>
		<?php echo "Brew City Rentals | " . (isset($page_title) ? $page_title : ""); ?>
	</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<style>
		body {
			background-color: #f6f6f6;
		}

		a {
			text-decoration: underline dotted;
			color: #33334d;
		}

		a:hover {
			text-decoration: underline dashed;
			color: #2952a3;
			font-weight: bold;
		}

		a:active {
			text-decoration: none;
		}

		a:visited {
			text-decoration: none;
		}

		.table-list {
			width: 100%;
			text-align: center;
			border-collapse: collapse;

		}

		.container {
			width: 100%;
			text-align: unset;
		}

		.menu {
			padding-right: 5em;
		}

		.cart-icon {
			width: 50px;
			height: 50px;
		}

		.logo-img {
			width: 200px;
			height: 200px;
		}

		/* Catalog Display */
		.movie-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-gap: 20px;
			padding: 20px;
			align-items: stretch;
		}


		.movie-tile {
			position: relative;
			background-color: #f9f9f9;
			border: 1px solid #ccc;
			padding: 20px;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

		}

		.movie-category {
			position: absolute;
			top: 5px;
			left: 5px;
			max-width: 50%;
			background-color: #2952a3;
			color: #fff;
			padding: 5px;
			font-size: 12px;
			font-weight: bold;
			border-radius: 2px;
		}

		.tag {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 20px;
			background-color: #007BFF;
			color: white;
			font-size: 12px;
			text-align: center;
			margin-right: 1em;
		}




		/* Cart Display */
		.cart-icon {
			width: 75px;
			/* Adjust the width and height as needed */
			height: 75px;
		}

		.cart-grid {
			display: grid;
			grid-template-columns: 1fr;
			/* Single column for vertical layout */
			grid-gap: 20px;
			padding: 20px;
			align-items: stretch;
		}

		.cart-item {
			background-color: #f9f9f9;
			border: 1px solid #ccc;
			padding: 20px;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		}

		.cart-thumbnail-container {
			width: 100px;
			height: 100px;
			overflow: hidden;
			order: 1;
		}

		.cart-thumbnail {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.item-details {
			flex: 1;
			margin-left: 20px;
		}
	</style>
</head>

<body>
	<center>
		<?php
		echo ("<img src='$baseUrl/media/logo.png' alt='Brew City Rentals Logos' title='Brew City Rentals Logo' class='logo-img'/><br>");
		?>

		<table class="container" width="100%" cellpadding="10">
			<tr>
				<td align="right">

					<?php
					if (!isset($_SESSION['email'])) {
						echo ("<a href=$baseUrl/login.php>Login</a> | ");
						echo ("<a href=$baseUrl/register.php>Register</a> | ");
						echo ("<a href=$baseUrl/forgot.php>Forgot Password?</a>");
					} else {
						$displayName = $_SESSION['base_customer_name'] ?? strtolower($_SESSION['email']);
						echo ("Welcome " . $displayName . " - <a href=$baseUrl/logout.php>Logout</a>");
						if (isset($_SESSION['impersonation_mode']) && $_SESSION['impersonation_mode']) {
							echo ("<p> <a href=$baseUrl/impersonate.php?action=unimpersonate&return_url=" . $_SERVER['REQUEST_URI'] . ">Stop Impersonation of {$_SESSION['customer_name']}</a>");
						}
					}

					?>

					<p>
				</td>
			</tr>
		</table>

		<table class="container" width="100%" cellpadding="10" class="menu">
			<td valign="top">

				<?php

				echo ("<a href=$baseUrl/>Home</a><p>");
				echo ("<a href=$baseUrl/catalog/>Movie Catalog</a><p>");
				if (isset($_SESSION['email'])) {
					if ($_SESSION['role_id'] <= $ROLE_CUSTOMER) {
						// customers
						echo ("<a href=$baseUrl/customers/>Customer Information</a><p>");
						echo ("<a href=$baseUrl/users/>User Information</a><p>");
						echo ("<a href=$baseUrl/catalog/transactions.php>Transactions</a><p>");
					}


					if ($_SESSION['role_id'] <= $ROLE_EMPLOYEE) {
						// employees
						echo ("<a href=$baseUrl/genres/>Genres</a><p>");
						echo ("<a href=$baseUrl/movies/>Movies</a><p>");
						echo ("<a href=$baseUrl/rentalcategories/>Rental Categories</a><p>");
						echo ("<a href=$baseUrl/dvds/>DVDs</a><p>");
						echo ("<a href=$baseUrl/people/>People</a><p>");
						echo ("<a href=$baseUrl/reports/>Reports</a><p>");
					}



					if ($_SESSION['role_id'] <= $ROLE_ADMIN) {
						// admins
						echo ("<a href=$baseUrl/roles/>Roles</a><p>");
						echo ("<a href=$baseUrl/employees/>Employees</a><p>");
						echo ("<a href=$baseUrl/config/>Configuration</a><p>");
					}

					if ($_SESSION['role_id'] <= $ROLE_EMPLOYEE) {
						// impersonation mode
						// get list of customers
						$customersQuery = "SELECT u.user_id, c.customer_id, u.last_name, u.first_name FROM customers c LEFT OUTER JOIN users u ON c.user_id = u.user_id WHERE u.role_id = $ROLE_CUSTOMER AND u.active = 1 ORDER BY u.last_name, u.first_name";
						$customersResult = query_arr($customersQuery);
						if ($customersResult) {
							echo ("<form method='GET' action='$baseUrl/impersonate.php'>");
							echo ("<select name='customer_id'>");
							foreach ($customersResult as $customer) {
								echo ("<option value='" . $customer['customer_id'] . "'>" . $customer['last_name'] . ", " . $customer['first_name'] . "</option>");
							}
							echo ("</select><br>");
							echo ("<input type='hidden' name='action' value='impersonate' />");
							echo ("<input type='hidden' name='return_url' value='" . $_SERVER['REQUEST_URI'] . "' />");
							echo ("<input type='submit' value='Impersonate' />");
							echo ("</form>");
						}

					}


				}
				?>

			</td>
			<td valign="top">