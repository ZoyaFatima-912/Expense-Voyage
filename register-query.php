<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');

if (isset($_POST['signup-btn'])) {
    $name = trim($_POST['firstN']);
    $nameLast = trim($_POST['lastN']);
    $email = strtolower(($_POST['email']));
    $raw_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);
    $currency = trim($_POST['currency']);
    $role = 'User';

    $_SESSION['signup_name'] = $name;
    $_SESSION['signup_name_last'] = $nameLast;
    $_SESSION['signup_email'] = $email;
    $_SESSION['signup_currency'] = $currency;

    $emailPattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i";
    $passwordPattern = "/^[A-Za-z0-9!@#$%^&*_-]{8,}$/";

    $check_email = "SELECT * FROM `user_info` WHERE `email` = '$email'";
    $email_result = mysqli_query($conn, $check_email);

    if (!preg_match($emailPattern, $email)) {

        $_SESSION['error'] = 'Invalid email format.';
        header("Location: signup.php");
        exit();

    }

    if (mysqli_num_rows($email_result) > 0) {
        $_SESSION['error'] = 'An account with this email already exists. Please use a different email.';
        header("Location: signup.php");
        exit();
    }

    if (!preg_match($passwordPattern, $raw_password)) {
        $_SESSION['error'] = 'Password must be at least 8 characters long and can include letters, numbers, and special characters.';
        header("Location: signup.php");
        exit();

    }

    if ($raw_password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header("Location: signup.php");
        exit();
    }

    $password = password_hash($raw_password, PASSWORD_DEFAULT);

    if (empty($currency)) {
        $_SESSION['error'] = 'Please select a currency.';
        header("Location: signup.php");
        exit();
    }

    $insert_query = "INSERT INTO `user_info`(`first_name`, `last_name`, `email`, `password`, `currency`, `role`) VALUES ('$name','$nameLast','$email','$password','$currency', '$role')";

    $result = mysqli_query($conn, $insert_query);

    if ($result) {
        $_SESSION['success'] = 'Account created successfully!';
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = 'Something went wrong during registration.';
        header("Location: signup.php");
        exit();
    }

}
;

if (isset($_POST['login-btn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM `user_info` WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    $_SESSION['email'] = $email;

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if ($row['role'] === 'Admin') {

            if ($password === $row['password']) {
                $_SESSION['email'] = $row['email'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = 'Invalid credentials! Please try again.';
                header("Location: login.php");
                exit();
            };

        } else {
            if (password_verify($password, $row['password'])) {

                $_SESSION['email'] = $row['email'];
                header("Location: home.php");
                exit();
            } else {
                $_SESSION['error'] = 'Invalid credentials! Please try again.';
                header("Location: login.php");
                exit();
            };

        }
    } else {
        $_SESSION['error'] = 'Account not found! Please sign up.';
        header("Location: login.php");
        exit();
    }
}
;

?>