<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExpenseVoyage</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&family=Lora&display=swap"
        rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="login-form">

        <!-- Bon Voyage typography -->
        <div class="text-center mb-3">
            <img src="./Assets/Bon-voyage.png" alt="Bon Voyage" class="bon-voyage-img">
        </div>

        <form id="loginForm" method="post" action="register-query.php">
            <div class="mb-3">
                <input type="email" id="email" class="login-form-input" placeholder="Email" name="email" required
                    value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
            </div>
            <div class="mb-3">
                <input type="password" id="password" class="login-form-input" name="password" placeholder="Password"
                    required>
            </div>
            <!-- <select id="currency" class="form-control mb-3" name="role" required>
                <option value="">Select your role</option>
                <?php
                if (isset($_SESSION['login-role']) && $_SESSION['login-role'] === 'User') {
                    ?>
                    <option value="User" selected>User</option>
                    <option value="Admin">Admin</option>
                    <?php
                }
                ?>
                <?php
                if (isset($_SESSION['login-role']) && $_SESSION['login-role'] === 'Admin') {
                    ?>
                    <option value="User">User</option>
                    <option value="Admin" selected>Admin</option>
                    <?php
                } else {
                    ?>
                    <option value="User">User</option>
                    <option value="Admin">Admin</option>
                    <?php
                }
                ?>
            </select> -->

            <button type="submit" class="login-button w-100" name="login-btn">Login</button>
            <div style="color:rgb(162, 0, 0); margin-top: 10px;">
                <?php
                if (isset($_SESSION['error'])) {
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                }

                unset($_SESSION['email']); // Clear email session after displaying error
                ?>
            </div>

        </form>
    </div>

</body>

</html>