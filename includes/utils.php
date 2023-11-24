<?php

function generateSecurePassword($length = 12)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=<>?";
    $password = "";

    for ($i = 0; $i < $length; $i++) {
        $randomChar = $chars[rand(0, strlen($chars) - 1)];
        $password .= $randomChar;
    }

    return $password;
}

function impersonate_customer($customer_id)
{
    $_SESSION['base_cart'] = $_SESSION['cart'];
    $_SESSION['impersonation_mode'] = true;
    $_SESSION['customer_id'] = $customer_id;
    $_SESSION['customer_name'] = get_customer_via_id($customer_id)['customer_name'];
    init_cart();
}

function unimpersonate_customer()
{
    $_SESSION['impersonation_mode'] = false;
    $_SESSION['customer_id'] = $_SESSION['base_customer_id'];
    $_SESSION['cart'] = $_SESSION['base_cart'];
    $_SESSION['customer_name'] = $_SESSION['base_customer_name'];
}
function get_customer($user_id)
{
    $userResult = read('users', $user_id, 'user_id');
    $customerResult = read('customers', $user_id, 'user_id');
    if ($customerResult && count($customerResult) == 1 && $userResult && count($userResult) == 1) {
        $customer = $customerResult[0];
        $user = $userResult[0];
        if ($customer) {
            return array('customer_id' => $customer['customer_id'], 'customer_name' => $user['first_name'] . ' ' . $user['last_name']);
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_employee_id($user_id)
{
    $employeeResult = read('employees', $user_id, 'user_id');
    if ($employeeResult && count($employeeResult) == 1) {
        $employee = $employeeResult[0];
        if ($employee) {
            return $employee['employee_id'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_customer_via_id($customer_id)
{

    $customerResult = read('customers', $customer_id, 'customer_id');
    if ($customerResult && count($customerResult) == 1) {
        $customerResult = $customerResult[0];
        $userResult = read('users', $customerResult['user_id'], 'user_id');
        if ($userResult && count($userResult) == 1) {
            $customer = $customerResult;
            $user = $userResult[0];
        }
        if ($customer) {
            return array('customer_id' => $customer['customer_id'], 'customer_name' => $user['first_name'] . ' ' . $user['last_name']);
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_payment_methods($customer_id)
{
    $paymentQuery = "SELECT * FROM payment_methods WHERE customer_id = $customer_id";
    $paymentResult = query_arr($paymentQuery);
    if (count($paymentResult) > 0) {
        return $paymentResult;
    } else {
        return NULL;
    }

}

function get_movie_category($movie_id)
{
    if ($movie_id == NULL) {
        return NULL;
    }
    $categoryQuery = "SELECT rental_category_id FROM movies WHERE movie_id = $movie_id";
    $categoryResult = query_arr($categoryQuery);
    if (count($categoryResult) == 1) {
        $category = $categoryResult[0];
        if ($category) {
            return $category['rental_category_id'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_category($category_id)
{
    if ($category_id == NULL) {
        return NULL;
    }
    $categoryQuery = "SELECT rental_category FROM rental_category WHERE rental_category_id = $category_id";
    $categoryResult = query_arr($categoryQuery);
    if (count($categoryResult) == 1) {
        $category = $categoryResult[0];
        if ($category) {
            return $category['rental_category'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_category_premium($category_id)
{
    if ($category_id == NULL) {
        return NULL;
    }
    $categoryQuery = "SELECT category_premium FROM rental_category WHERE rental_category_id = $category_id";
    $categoryResult = query_arr($categoryQuery);
    if (count($categoryResult) == 1) {
        $category = $categoryResult[0];
        if ($category) {
            return $category['category_premium'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_employee($employee_id)
{
    if ($employee_id == NULL) {
        return NULL;
    }
    $employeeQuery = "SELECT e.*, concat(u.first_name,' ', u.last_name) as employee_name FROM employees as e JOIN users as u ON e.user_id = u.user_id WHERE e.employee_id = $employee_id";

    $employeeResult = query_arr($employeeQuery);
    if (count($employeeResult) == 1) {
        $employee = $employeeResult[0];
        if ($employee) {
            return $employee;
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }

}

function get_movie($dvd_id)
{
    $movieQuery = "SELECT * FROM movies JOIN dvds ON movies.movie_id = dvds.movie_id WHERE dvds.dvd_id = $dvd_id";
    $movieResult = query_arr($movieQuery);
    if (count($movieResult) == 1) {
        $movie = $movieResult[0];
        if ($movie) {
            return $movie;
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function get_credits($movie_id)
{
    $creditsQuery = "SELECT * FROM movie_person_roles mpr JOIN person p ON mpr.person_id = p.person_id WHERE mpr.movie_id = $movie_id ORDER BY mpr.leading_role DESC";
    $creditsResult = query_arr($creditsQuery);
    if (count($creditsResult) > 0) {
        return $creditsResult;
    } else {
        return NULL;
    }
}

function total_movie_copies($movie_id)
{
    $totalQuery = "SELECT COUNT(*) as total FROM dvds WHERE movie_id = $movie_id and is_lost is null";
    $totalResult = query_arr($totalQuery);
    if (count($totalResult) == 1) {
        $total = $totalResult[0];
        if ($total) {
            return $total['total'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function current_movie_rentals($movie_id)
{
    $totalRentals = "SELECT COUNT(transactions.dvd_id) as total FROM transactions JOIN dvds ON transactions.dvd_id = dvds.dvd_id WHERE dvds.movie_id = $movie_id AND transactions.rental_return IS NULL";
    $totalResult = query_arr($totalRentals);
    if (count($totalResult) == 1) {
        $total = $totalResult[0];
        if ($total) {
            return $total['total'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function available_movie_copies($movie_id)
{
    $total = total_movie_copies($movie_id);
    $current = current_movie_rentals($movie_id);
    return $total - $current;
}

function get_available_dvd($movie_id)
{
    $availableDvd = "SELECT dvd_id FROM dvds WHERE dvds.movie_id = $movie_id AND dvd_id NOT IN (SELECT dvd_id FROM transactions WHERE rental_return IS NULL) LIMIT 1";
    $availableResult = query_arr($availableDvd);
    if (count($availableResult) == 1) {
        $available = $availableResult[0];
        if ($available) {
            return $available['dvd_id'];
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function sag_compliance_check($first_name, $last_name, $middle_name = NULL, $person_id = NULL)
{
    // check SAG compliance for listed name 
    $errors = array();
    if ($middle_name) {
        $errors[] = "Middle name is not allowed for SAG compliance";
    }
    $sagNameQuery = "SELECT * FROM person WHERE LOWER(first_name) = '$first_name' AND LOWER(last_name) = '$last_name' and sag_status = true";
    if ($person_id) {
        $sagNameQuery .= " AND person_id != $person_id";
    }
    $sagNameResult = query_arr($sagNameQuery);
    if (count($sagNameResult) > 0) {
        $errors[] = "SAG compliance check failed for <b> $first_name $last_name </b>- SAG Status Person Already Exists";
    }
    return $errors;
}

function get_ledger_transactions($ledger_id)
{
    $ledgerQuery = "SELECT * FROM transactions WHERE ledger_id = $ledger_id";
    $ledgerResult = query_arr($ledgerQuery);
    if (count($ledgerResult) > 0) {
        return $ledgerResult;
    } else {
        return NULL;
    }

}

?>