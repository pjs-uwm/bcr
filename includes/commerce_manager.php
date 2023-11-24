<?php


function generateShoppingBasketSVG($cartItemCount)
{
    // Start building the SVG string
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">';

    // Basket body
    $svg .= '<path d="M30 70 L70 70 L80 30 L20 30 Z" fill="#808080" />';

    // Handles
    $svg .= '<path d="M30 30 Q50 10, 70 30" stroke="#808080" stroke-width="4" fill="none" />';
    $svg .= '<path d="M40 30 Q50 20, 60 30" stroke="#808080" stroke-width="4" fill="none" />';

    // Dynamic number in the center
    $svg .= '<text x="50" y="50" font-size="14" font-weight="bold" text-anchor="middle" dy="5" fill="white">' . $cartItemCount . '</text>';

    // Close the SVG
    $svg .= '</svg>';

    return $svg;
}


function generate_cart_icon()
{
    $totalItems = get_cart_items() ? count(get_cart_items()) : 0;
    $svg = generateShoppingBasketSVG($totalItems);
    $svgDataUrl = 'data:image/svg+xml,' . rawurlencode($svg);

    $cartHtml = "<div class='cart-container'>";
    $cartHtml .= "<a href='cart.php'><img src='$svgDataUrl' class='cart-icon'></a>";
    $cartHtml .= "</div>";
    return $cartHtml;
}



// Cart Structure and Methods
function init_cart()
{
    $cart = array();
    $cart['items'] = array();
    $_SESSION['cart'] = $cart;
}

function get_cart_items()
{
    return $_SESSION['cart']['items'];
}

function is_movie_in_cart($movie_id)
{
    $cart = $_SESSION['cart'];
    return isset($cart['items'][$movie_id]);
}

function add_cart_item($movie_id)
{
    // get available dvd for movie id and add to cart
    $dvd_id = get_available_dvd($movie_id);

    $cart = $_SESSION['cart'];
    $cart['items'][$movie_id] = $dvd_id;
    $_SESSION['cart'] = $cart;
}


function remove_cart_item($movie_id)
{
    $cart = $_SESSION['cart'];
    unset($cart['items'][$movie_id]);
    $_SESSION['cart'] = $cart;
}

// ratings management

function add_rating($customer_id, $movie_id, $rating)
{

    // check if rating exists for customer and movie already
    if (get_rating($movie_id, $customer_id)) {
        if ($rating == -1) {
            $ratingDelete = "DELETE FROM movie_ratings WHERE customer_id = $customer_id AND movie_id = $movie_id";
            query($ratingDelete);
        } else {
            $ratingUpdate = "UPDATE movie_ratings SET rating = $rating WHERE customer_id = $customer_id AND movie_id = $movie_id";
            query($ratingUpdate);
        }
    } else {
        $ratingInsert = "INSERT INTO movie_ratings (customer_id, movie_id, rating) VALUES ($customer_id, $movie_id, $rating)";
        query($ratingInsert);
    }
}

function get_rating($movie_id, $customer_id)
{
    $ratingSelect = "SELECT * FROM movie_ratings WHERE customer_id = $customer_id AND movie_id = $movie_id";
    $ratingResult = query_arr($ratingSelect);
    if (count($ratingResult) > 0) {
        return $ratingResult[0]['rating'];
    } else {
        return NULL;
    }
}

function get_average_rating($movie_id)
{
    $ratingSelect = "SELECT AVG(rating) as average_rating FROM movie_ratings WHERE movie_id = $movie_id";
    $ratingResult = query_arr($ratingSelect);
    if (count($ratingResult) > 0) {
        return $ratingResult[0]['average_rating'];
    } else {
        return NULL;
    }

}

function get_customer_ledger($customer_id)
{
    $ledgerSelect = "SELECT * FROM customer_ledger WHERE customer_id = $customer_id";
    $ledgerResult = query_arr($ledgerSelect);
    if (count($ledgerResult) > 0) {
        return $ledgerResult[0];
    } else {
        return NULL;
    }
}

function get_outstanding_balance($customer_id)
{
    $ledgerSelect = "SELECT SUM(amount) as outstanding_balance FROM customer_ledger WHERE customer_id = $customer_id";
    $ledgerResult = query_arr($ledgerSelect);
    if (count($ledgerResult) > 0) {
        $balance = $ledgerResult[0]['outstanding_balance'] ?? 0;
        return number_format($balance, 2, '.', '');
    } else {
        return '0.00';
    }
}

function get_customer_transactions($customer_id)
{
    $ledgerSelect = "SELECT * FROM transactions WHERE customer_id = $customer_id";
    $ledgerResult = query_arr($ledgerSelect);
    if (count($ledgerResult) > 0) {
        return $ledgerResult;
    } else {
        return NULL;
    }
}

function get_overdue_transactions($customer_id)
{
    $ledgerSelect = "SELECT * FROM transactions WHERE customer_id = $customer_id AND (rental_due < CURDATE() AND rental_return IS NULL and is_lost = false) ";
    $ledgerResult = query_arr($ledgerSelect);
    if (count($ledgerResult) > 0) {
        return $ledgerResult;
    } else {
        return NULL;
    }
}

function get_config_item($config_item)
{
    $configSelect = "SELECT * FROM config_items WHERE config_item = '$config_item'";
    $configResult = query_arr($configSelect);
    if (count($configResult) > 0) {
        return $configResult[0]['config_value'];
    } else {
        return NULL;
    }
}

function calculate_rental_amount($movies)
{
    $total = 0;
    foreach ($movies as $movie) {
        $total += get_config_item('RENTAL_RATE');
    }
    return number_format($total, 2, '.', '');

}

function calculate_rental_premium($movies)
{

    $total = 0;
    foreach ($movies as $movie) {
        $total += get_category_premium(get_movie($movie)['rental_category_id']) ?? 0;
    }
    return number_format($total, 2, '.', '');

}

function get_due_date()
{
    $dueDate = date('m/d/Y', strtotime("+" . get_config_item('RENTAL_PERIOD') . " days"));
    return $dueDate;
}

function get_tax_amount($amount)
{
    $tax = $amount * (get_config_item('TAX_RATE') / 100);
    return $tax;
}

function get_rented_movies($ledger_id)
{
    $moviesSelect = "SELECT * FROM movies m INNER JOIN dvds d ON m.movie_id = d.movie_id LEFT OUTER JOIN transactions t ON d.dvd_id = t.dvd_id WHERE t.ledger_id = $ledger_id";
    $moviesResult = query_arr($moviesSelect);
    if (count($moviesResult) > 0) {
        return $moviesResult;
    } else {
        return NULL;
    }
}

function get_payment_method($pm_id)
{
    $pmSelect = "SELECT * FROM payment_methods WHERE pm_id = $pm_id";
    $pmResult = query_arr($pmSelect);
    if (count($pmResult) > 0) {
        return $pmResult[0];
    } else {
        return NULL;
    }
}

function generate_receipt($ledger_id)
{
    $ledgerSelect = "SELECT * FROM customer_ledger WHERE ledger_id = $ledger_id";
    $ledgerResult = query_arr($ledgerSelect);
    if (count($ledgerResult) == 1) {
        $ledger = $ledgerResult[0];
        $customer = get_customer_via_id($ledger['customer_id']);
        $movies = get_rented_movies($ledger['ledger_id']);
        $payment_method = get_payment_method($ledger['pm_id'])["pm_description"];



        $receipt = "<div class='receipt'>";
        $receipt .= "<div class='receipt-main'>";
        $receipt .= "<h3>Brew City Rentals Receipt</h3>";
        $receipt .= "<h4>123 Main St.<br>";
        $receipt .= "Milwaukee, WI 53444<br>";
        $receipt .= "Phone: 414-2421-1244</h4>";
        $receipt .= "</div>";
        $receipt .= "<div class='receipt-body'>";
        $receipt .= "<div class='receipt-body-header'>";
        $receipt .= "<h3>Customer: " . $customer['customer_name'] . "</h3>";
        $receipt .= "<h3>Ledger ID: " . $ledger['ledger_id'] . "</h3>";
        $receipt .= "<h3>Transaction Date: " . date("n/j/Y g:iA", strtotime($ledger['post_date'])) . "</h3>";
        $receipt .= "</div>";
        $receipt .= "<div class='receipt-movies'>";
        $receipt .= "<table class='receipt' width='100%'>";
        $receipt .= "<tr>";
        $receipt .= "<th>Movie Title</th>";
        $receipt .= "<th>Due Date</th>";
        $receipt .= "<th>Amount</th>";
        $receipt .= "<th>Premium</th>";
        $receipt .= "</tr>";
        $subtotal = 0;
        foreach ($movies as $movie) {
            $premium = get_category_premium($movie['rental_category_id']);
            $receipt .= "<tr>";
            $receipt .= "<td>" . $movie['title'] . "</td>";
            $receipt .= "<td>" . date("n/j/Y", strtotime($movie['rental_due'])) . "</td>";
            $receipt .= "<td>$" . number_format(get_config_item('RENTAL_RATE'), 2, '.', '') . "</td>";
            $subtotal += get_config_item('RENTAL_RATE') + $premium;
            $receipt .= "<td>$" . number_format($premium, 2, '.', '') . "</td>";
            $receipt .= "</tr>";
        }
        $receipt .= "<tr>";
        $receipt .= "<td colspan='2'>Subtotal</td>";
        $receipt .= "<td>". number_format($subtotal, 2, '.', '')."</td>";
        $receipt .= "</tr>";
        $receipt .= "<tr>";
        $receipt .= "<td colspan='2'>Tax</td>";
        $receipt .= "<td><u>$" . number_format(get_tax_amount(get_config_item('RENTAL_RATE') + $premium), 2, '.', '') . "</u></td>";
        $receipt .= "</tr>";
        $receipt .= "<tr>";
        $receipt .= "<td colspan='2'>Total</td>";
        $receipt .= "<td>$" . number_format(calculate_rental_amount($movies) + get_tax_amount(get_config_item('RENTAL_RATE')), 2, '.', '') . "</u></td>";



        $receipt .= "</tr>";

        $receipt .= "<tr>";
        $receipt .= "<td colspan='2'>Amount Paid</td>";
        $receipt .= "<td>$" . number_format($ledger['amount'] * -1, 2, '.', '') . "</td>";
        $receipt .= "</tr>";
        $receipt .= "</table>";
        $receipt .= "<p><div>Payment Method : " . $payment_method . "</div>";
        $receipt .= "<p><div><h3>Thank You for Your Business!</div>";
        $receipt .= "</div>";
        $receipt .= "</div>";
        $receipt .= "</div>";
        return $receipt;
    } else {
        return "No Receipt Found for Ledger ID $ledger_id";
    }


}

function pickup_movie($transaction_id)
{
    $transactionUpdate = "UPDATE transactions SET pending_pickup = false WHERE transaction_id = $transaction_id";
    $result = query($transactionUpdate);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function return_movie($transaction_id)
{
    // check if movie is overdue
    $transactionSelect = "SELECT * FROM transactions WHERE transaction_id = $transaction_id";
    $transactionResult = query_arr($transactionSelect);
    if (count($transactionResult) == 1) {
        $transaction = $transactionResult[0];
        $rental_due = $transaction['rental_due'];
        $rental_return = date('Y-m-d');
        if ($rental_return > $rental_due) {
            // calculate late fee
            $days_late = (strtotime($rental_due) - strtotime($rental_return)) / (60 * 60 * 24);
            if ($days_late < -5) {
                $late_fee = get_config_item('LATE_FEE_FLAT');
            } else if ($days_late < 0) {
                $late_fee = ($days_late * get_config_item('LATE_FEE_DAILY') * -1);
            } else {
                $late_fee = 0;
            }
            $customer_id = $transaction['customer_id'];
            $lateFeeInsert = "INSERT INTO customer_ledger (customer_id, amount, post_type) VALUES ($customer_id, $late_fee, 'Late Fee')";
            if ($late_fee > 0) {
                $result = query($lateFeeInsert);
            }

        }
    }

    $transactionUpdate = "UPDATE transactions SET rental_return = NOW() WHERE transaction_id = $transaction_id";
    $result = query($transactionUpdate);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function lost_movie($transaction_id)
{
    // get dvd id
    $transactionSelect = "SELECT * FROM transactions WHERE transaction_id = $transaction_id";
    $transactionResult = query_arr($transactionSelect);
    if (count($transactionResult) == 1) {
        $transaction = $transactionResult[0];
        $dvd_id = $transaction['dvd_id'];
        $dvdUpdate = "UPDATE dvds SET is_lost = true WHERE dvd_id = $dvd_id";
        $result = query($dvdUpdate);

        // charge lost dvd to ledger
        $customer_id = $transaction['customer_id'];
        $lostFeeInsert = "INSERT INTO customer_ledger (customer_id, amount, post_type) VALUES ($customer_id, " . get_config_item('LOST_MOVIE_FEE') . ", 'Lost Movie Fee')";
        $result = query($lostFeeInsert);

    }

    $transactionUpdate = "UPDATE transactions SET is_lost = true WHERE transaction_id = $transaction_id";
    $result = query($transactionUpdate);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

?>