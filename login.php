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

    <style>
        :root {
            --deep-ocean: #023047;
            --golden-sand: #F4A261;
            --sunset-coral: #E76F51;
            --sky-white: #FAFAFA;
            --sea-mist: #E5E5E5;
        }

        body {
            background: linear-gradient(135deg, var(--deep-ocean), var(--sunset-coral));
            font-family: 'Poppins', sans-serif;
            color: var(--sky-white);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-form {
            background: rgba(2, 48, 71, 0.92);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(244, 162, 97, 0.4);
            width: 100%;
            max-width: 450px;
        }

        .bon-voyage-img {
            max-width: 180px;
        }

        .login-form-input {
            width: 100%;
            border-radius: 10px;
            border: 1px solid var(--sea-mist);
            background-color: rgba(250, 250, 250, 0.08);
            color: var(--sky-white);
            padding: 10px 12px;
        }

        .login-form-input::placeholder {
            color: var(--sea-mist);
        }

        .login-form-input:focus {
            border-color: var(--golden-sand);
            box-shadow: 0 0 8px var(--golden-sand);
            background-color: rgba(250, 250, 250, 0.15);
        }

        .login-button {
            background: var(--golden-sand);
            color: var(--deep-ocean);
            font-weight: 600;
            border-radius: 8px;
            padding: 0.6rem;
            border: none;
            transition: all 0.3s ease;
        }

        .login-button:hover {
            background: var(--sunset-coral);
            color: var(--sky-white);
            transform: scale(1.05);
        }

        .register-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--golden-sand);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-link:hover {
            color: var(--sunset-coral);
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-form">
        <!-- Bon Voyage logo -->
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

            <button type="submit" class="login-button w-100" name="login-btn">Login</button>

            <div style="color:rgb(162, 0, 0); margin-top: 10px;">
                <?php
                if (isset($_SESSION['error'])) {
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                }
                unset($_SESSION['email']);
                ?>
            </div>
        </form>

        <!-- Register link -->
        <a href="signup.php" class="register-link">Don’t have an account? Register here</a>
    </div>
</body>

</html>
