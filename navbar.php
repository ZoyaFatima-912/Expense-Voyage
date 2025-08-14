<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Voyage</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
    --deep-ocean: #023047;
    --golden-sand: #F4A261;
    --sunset-coral: #E76F51;
    --sky-white: #FAFAFA;
    --sea-mist: #E5E5E5;
}

.navbar-custom {
    background-color: var(--deep-ocean);
    border-bottom: 3px solid var(--golden-sand);
}

.navbar-custom .sidebar-logo span {
    display: block;
    font-weight: bold;
    color: var(--golden-sand);
    font-size: 1.1rem;
    letter-spacing: 1px;
    line-height: 1.2;
}

.navbar-custom .nav-link {
    color: var(--sky-white) !important;
    font-weight: 500;
    transition: background-color 0.3s ease, color 0.3s ease;
    border-radius: 5px;
    padding: 0.5rem 0.8rem;
}

.navbar-custom .nav-link:hover {
    background-color: var(--sunset-coral);
    color: var(--sky-white) !important;
}

.navbar-custom .nav-link.active {
    background-color: var(--golden-sand);
    color: var(--deep-ocean) !important;
    font-weight: bold;
}

.navbar-custom .dropdown-menu {
    background-color: var(--deep-ocean);
    border: none;
    border-radius: 0.5rem;
}

.navbar-custom .dropdown-item {
    color: var(--sky-white);
    transition: background-color 0.3s ease;
}

.navbar-custom .dropdown-item:hover {
    background-color: var(--golden-sand);
    color: var(--deep-ocean);
}

.navbar-toggler {
    border-color: var(--golden-sand);
}

.navbar-toggler-icon {
    background-image: none;
    width: 1.5rem;
    height: 1.5rem;
    background-color: var(--golden-sand);
    mask: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='white' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") no-repeat center / contain;
}

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="home.php">
            <div class="sidebar-logo">
                <span>EXPENSE</span><br>
                <span>VOYAGE</span>
            </div>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left nav links -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>" href="home.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'trips.php' ? 'active' : ''; ?>" href="trips.php">
                        <i class="fas fa-suitcase me-1"></i> My Trips
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'expenses.php' ? 'active' : ''; ?>" href="expenses.php">
                        <i class="fas fa-receipt me-1"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                        <i class="fas fa-chart-pie me-1"></i> Reports
                    </a>
                </li>
            </ul>

            <!-- Right nav links -->
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['email'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php
                            $conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');
                            $email = $_SESSION['email'];
                            $query = "SELECT first_name FROM user_info WHERE email = '$email'";
                            $result = mysqli_query($conn, $query);
                            if ($row = mysqli_fetch_assoc($result)) {
                                echo htmlspecialchars($row['first_name']);
                            } else {
                                echo "Account";
                            }
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
