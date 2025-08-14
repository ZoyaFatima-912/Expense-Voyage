<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ExpenseVoyage</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&family=Lora&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme -->
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

        .register-form {
            background: rgba(2, 48, 71, 0.92);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(244, 162, 97, 0.4);
            width: 100%;
            max-width: 500px;
        }

        .bon-voyage-img {
            max-width: 180px;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid var(--sea-mist);
            background-color: rgba(250, 250, 250, 0.08);
            color: var(--sky-white);
        }

        .form-control::placeholder {
            color: var(--sea-mist);
        }

        .form-control:focus {
            border-color: var(--golden-sand);
            box-shadow: 0 0 8px var(--golden-sand);
            background-color: rgba(250, 250, 250, 0.15);
        }

        .register-button {
            background: var(--golden-sand);
            color: var(--deep-ocean);
            font-weight: 600;
            border-radius: 8px;
            padding: 0.6rem;
            border: none;
            transition: all 0.3s ease;
        }

        .register-button:hover {
            background: var(--sunset-coral);
            color: var(--sky-white);
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="register-form">
        <!-- Bon Voyage logo -->
        <div class="text-center mb-3">
            <img src="./Assets/Bon-voyage.png" alt="Bon Voyage" class="bon-voyage-img">
        </div>

        <form id="registerForm" action="register-query.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="text" id="firstName" class="form-control" placeholder="First Name" name="firstN" required
                    value="<?php echo isset($_SESSION['signup_name']) ? htmlspecialchars($_SESSION['signup_name']) : ''; ?>">
            </div>
            <div class="mb-3">
                <input type="text" id="lastName" class="form-control" placeholder="Last Name" name="lastN" required
                    value="<?php echo isset($_SESSION['signup_name_last']) ? htmlspecialchars($_SESSION['signup_name_last']) : ''; ?>">
            </div>
            <div class="mb-3">
                <input type="email" id="email" class="form-control" placeholder="Email" name="email" required
                    value="<?php echo isset($_SESSION['signup_email']) ? htmlspecialchars($_SESSION['signup_email']) : ''; ?>">
            </div>
            <div class="mb-3">
                <input type="password" id="password" class="form-control" placeholder="Password" name="password"
                    required>
            </div>
            <div class="mb-3">
                <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm Password"
                    name="confirm-password" required>
            </div>
            <div class="mb-3">
                <select id="currency" class="form-control" name="currency" required>
                    <option value="">Preferred Currency</option>
                    <?php
                    if (isset($_SESSION['signup_currency']) && $_SESSION['signup_currency'] === 'USD') {
                        echo '<option value="USD" selected>USD</option><option value="EUR">EUR</option><option value="PKR">PKR</option>';
                    } elseif (isset($_SESSION['signup_currency']) && $_SESSION['signup_currency'] === 'EUR') {
                        echo '<option value="USD">USD</option><option value="EUR" selected>EUR</option><option value="PKR">PKR</option>';
                    } elseif (isset($_SESSION['signup_currency']) && $_SESSION['signup_currency'] === 'PKR') {
                        echo '<option value="USD">USD</option><option value="EUR">EUR</option><option value="PKR" selected>PKR</option>';
                    } else {
                        echo '<option value="USD">USD</option><option value="EUR">EUR</option><option value="PKR">PKR</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="register-button w-100" name="signup-btn">Register</button>

            <div style="color: var(--sunset-coral); margin-top: 10px;">
                <?php
                if (isset($_SESSION['error'])) {
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                }

                unset(
                    $_SESSION['signup_name'],
                    $_SESSION['signup_name_last'],
                    $_SESSION['signup_email'],
                    $_SESSION['signup_currency']
                );
                ?>
            </div>
        </form>
    </div>
</body>

</html>
