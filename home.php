<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');


$user_email = $_SESSION['email'];
$user_query = "SELECT * FROM user_info WHERE email = '$user_email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);


$current_date = date('Y-m-d');
$future_date = date('Y-m-d', strtotime('+30 days'));
$trips_query = "SELECT * FROM trips 
                WHERE user_email = '$user_email' 
                AND end_date >= '$current_date' 
                AND start_date <= '$future_date'
                ORDER BY start_date ASC 
                LIMIT 3";
$trips_result = mysqli_query($conn, $trips_query);


$expenses_query = "SELECT e.*, t.trip_name 
                   FROM expenses e 
                   LEFT JOIN trips t ON e.trip_id = t.trip_id 
                   WHERE e.user_email = '$user_email' 
                   ORDER BY e.expense_date DESC 
                   LIMIT 5";
$expenses_result = mysqli_query($conn, $expenses_query);


$budget_data = [];
$first_trip = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM trips 
     WHERE user_email = '$user_email' 
     AND end_date >= '$current_date'
     ORDER BY start_date ASC 
     LIMIT 1"
));

if ($first_trip && $first_trip['budget'] > 0) {
    $expenses_sum = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT SUM(amount) as total 
         FROM expenses 
         WHERE trip_id = {$first_trip['trip_id']} 
         AND user_email = '$user_email'"
    ));

    $budget_data = [
        'total' => $first_trip['budget'],
        'spent' => $expenses_sum['total'] ?? 0,
        'remaining' => max(0, $first_trip['budget'] - ($expenses_sum['total'] ?? 0)),
        'percentage' => min(100, (($expenses_sum['total'] ?? 0) / $first_trip['budget'] * 100))
    ];
}


$activities_query = "SELECT 'expense' as type, expense_date as date, amount, category, notes, trip_id 
                     FROM expenses WHERE user_email = '$user_email'
                     UNION
                     SELECT 'trip' as type, created_at as date, NULL as amount, NULL as category, NULL as notes, trip_id 
                     FROM trips WHERE user_email = '$user_email'
                     ORDER BY date DESC LIMIT 4";
$activities_result = mysqli_query($conn, $activities_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --deep-ocean: #023047;
            --golden-sand: #F4A261;
            --sunset-coral: #E76F51;
            --sky-white: #FAFAFA;
            --sea-mist: #E5E5E5;
        }

        /* General Body */
        body {
            background-color: var(--sea-mist);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: var(--deep-ocean);
        }

        /* Sidebar */

        .sidebar-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 500;
            letter-spacing: 2px;
            color: var(--sky-white);
            margin: 20px auto 35px;
            /* 35px bottom margin for extra space */
            text-transform: uppercase;
            text-align: center;
            line-height: 1;
        }


        .sidebar-logo span {
            display: block;
        }


        .sidebar {
            background-color: var(--deep-ocean) !important;
            min-height: 100vh;
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: var(--sky-white);
            font-weight: 500;
            transition: background 0.3s ease, padding-left 0.3s ease;
            border-radius: 8px;
            margin: 4px 8px;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background-color: var(--golden-sand);
            color: var(--deep-ocean);
            padding-left: 20px;
        }

        .sidebar i {
            width: 20px;
        }

        /* Welcome Card */
        .welcome-card {
            background-color: var(--deep-ocean);
            color: var(--sky-white);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-card .btn-light {
            background-color: var(--golden-sand);
            color: var(--deep-ocean);
            font-weight: 600;
            border: none;
        }

        .welcome-card .btn-outline-light {
            color: var(--sky-white);
            border-color: var(--sky-white);
        }

        .welcome-card .btn-outline-light:hover {
            background-color: var(--sky-white);
            color: var(--deep-ocean);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            background-color: var(--sky-white);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        /* Card headers */
        .card-header {
            border-bottom: none;
            font-weight: 600;
            color: var(--deep-ocean);
            background-color: var(--sky-white) !important;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--deep-ocean);
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--sunset-coral);
        }

        .btn-outline-primary {
            border-color: var(--deep-ocean);
            color: var(--deep-ocean);
        }

        .btn-outline-primary:hover {
            background-color: var(--deep-ocean);
            color: var(--sky-white);
        }

        .btn-outline-secondary {
            border-color: var(--golden-sand);
            color: var(--golden-sand);
        }

        .btn-outline-secondary:hover {
            background-color: var(--golden-sand);
            color: var(--deep-ocean);
        }

        /* Badges */
        .badge-category {
            background-color: var(--golden-sand) !important;
            color: var(--deep-ocean);
            font-weight: 500;
        }

        /* Tables */
        .table-hover tbody tr:hover {
            background-color: var(--sea-mist);
        }

        /* Progress Bar */
        .progress {
            background-color: var(--sea-mist);
            border-radius: 20px;
            overflow: hidden;
        }

        /* Recent Activities */
        .recent-activity-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--sea-mist);
        }

        .recent-activity-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar bg-light">
                <div class="position-sticky pt-3">
                    <!-- <div class="text-center mb-4">
                        <img src="../Assets/Bon-voyage.png" alt="ExpenseVoyage" class="img-fluid" style="max-width: 150px;">
                    </div> -->
                    <div class="sidebar-logo">
                        <span>EXPENSE</span><br>
                        <span>VOYAGE</span>
                    </div>



                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="home.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="trips.php">
                                <i class="fas fa-suitcase me-2"></i>My Trips
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add-expense.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Expense
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add-trip.php">
                                <i class="fas fa-plus me-2"></i>Add Trip
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-pie me-2"></i>Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Welcome Card -->
                <!-- <div class="card welcome-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="card-title">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                                <p class="card-text">Track your travel expenses and stay within budget.</p>
                                <a href="add-expense.php" class="btn btn-light">
                                    <i class="fas fa-plus me-1"></i> Add New Expense
                                </a>
                                <a href="add-trip.php" class="btn btn-outline-light ms-2">
                                    <i class="fas fa-suitcase me-1"></i> Plan New Trip
                                </a>
                            </div>
                            <div class="col-md-4 text-center">
                                <img src="../Assets/travel-icon.png" alt="Travel" class="img-fluid" style="max-width: 120px;">
                            </div>
                        </div>
                    </div>
                </div> -->

                <h2 class="card-title">
                    Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!
                </h2>

                <p class="card-text">Track your travel expenses and stay within budget.</p>

                <!-- Flex row for name + buttons -->
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <!-- <span class="fw-bold text-warning">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </span> -->
                    <!-- <a href="add-expense.php" class="btn btn-light">
                        <i class="fas fa-plus me-1"></i> Add New Expense
                    </a>
                    <a href="add-trip.php" class="btn btn-outline-light">
                        <i class="fas fa-suitcase me-1"></i> Plan New Trip
                    </a> -->
                    <a href="add-expense.php" class="btn btn-light mb-3">
                        <i class="fas fa-plus me-1"></i> Add New Expense
                    </a>
                    <a href="add-trip.php" class="btn btn-outline-light ms-2 mb-3">
                        <i class="fas fa-suitcase me-1"></i> Plan New Trip
                    </a>

                </div>


                <div class="row">
                    <!-- Current/Upcoming Trips -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-suitcase me-2"></i>Current/Upcoming Trips
                                    <a href="trips.php" class="float-end fs-6">View All</a>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($trips_result) > 0): ?>
                                    <div class="row">
                                        <?php
                                        mysqli_data_seek($trips_result, 0); // Reset pointer
                                        while ($trip = mysqli_fetch_assoc($trips_result)):
                                            // Determine trip status
                                            $today = date('Y-m-d');
                                            $status = '';
                                            $badge_class = '';
                                            if ($today < $trip['start_date']) {
                                                $status = 'Upcoming';
                                                $badge_class = 'bg-info';
                                            } elseif ($today >= $trip['start_date'] && $today <= $trip['end_date']) {
                                                $status = 'Ongoing';
                                                $badge_class = 'bg-success';
                                            } else {
                                                $status = 'Completed';
                                                $badge_class = 'bg-secondary';
                                            }
                                        ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card trip-card h-100">
                                                    <div class="card-header bg-primary text-white position-relative">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($trip['trip_name']); ?></h6>
                                                        <span class="badge <?php echo $badge_class; ?> position-absolute top-0 end-0 m-1"><?php echo $status; ?></span>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($trip['destination']); ?></p>
                                                        <p class="mb-1"><i class="far fa-calendar-alt me-2"></i>
                                                            <?php echo date('M j', strtotime($trip['start_date'])); ?> -
                                                            <?php echo date('M j, Y', strtotime($trip['end_date'])); ?>
                                                        </p>
                                                        <?php if ($trip['budget']): ?>
                                                            <div class="mt-3">
                                                                <small class="text-muted">Budget: <?php echo $user['currency'] . ' ' . number_format($trip['budget'], 2); ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-footer bg-white">
                                                        <a href="trip-details.php?id=<?php echo $trip['trip_id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                                            View Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-suitcase fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No upcoming trips found.</p>
                                        <a href="add-trip.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Plan Your First Trip
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Expenses -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>Recent Expenses
                                    <a href="expense.php" class="float-end fs-6">View All</a>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($expenses_result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Category</th>
                                                    <th>Trip</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                mysqli_data_seek($expenses_result, 0); // Reset pointer
                                                while ($expense = mysqli_fetch_assoc($expenses_result)): ?>
                                                    <tr>
                                                        <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                                        <td class="fw-bold"><?php echo $user['currency'] . ' ' . number_format($expense['amount'], 2); ?></td>
                                                        <td>
                                                            <span class="badge bg-primary badge-category">
                                                                <?php echo htmlspecialchars($expense['category']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo $expense['trip_name'] ? htmlspecialchars($expense['trip_name']) : '--'; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $expense['notes'] ? htmlspecialchars(substr($expense['notes'], 0, 20) . '...') : '--'; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No expenses recorded yet.</p>
                                        <a href="add-expense.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add Your First Expense
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar - Recent Activities & Quick Actions -->
                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="add-expense.php" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-2"></i>Add Expense
                                    </a>
                                    <a href="add-trip.php" class="btn btn-outline-primary">
                                        <i class="fas fa-plus me-2"></i>Add Trip
                                    </a>
                                    <a href="reports.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-chart-pie me-2"></i>View Reports
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Budget Summary -->
                        <?php if (!empty($budget_data)): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Budget Summary</h5>
                                </div>
                                <div class="card-body">
                                    <h6><?php echo htmlspecialchars($first_trip['trip_name']); ?></h6>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Spent: <?php echo $user['currency'] . ' ' . number_format($budget_data['spent'], 2); ?></span>
                                        <span>Remaining: <?php echo $user['currency'] . ' ' . number_format($budget_data['remaining'], 2); ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo $budget_data['percentage'] > 80 ? 'bg-danger' : ($budget_data['percentage'] > 50 ? 'bg-warning' : 'bg-success'); ?>"
                                            style="width: <?php echo $budget_data['percentage']; ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Total Budget: <?php echo $user['currency'] . ' ' . number_format($budget_data['total'], 2); ?></small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Recent Activities -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($activities_result) > 0): ?>
                                    <?php
                                    mysqli_data_seek($activities_result, 0); // Reset pointer
                                    while ($activity = mysqli_fetch_assoc($activities_result)):
                                        $trip_name = '';
                                        if ($activity['trip_id']) {
                                            $trip_query = mysqli_query($conn, "SELECT trip_name FROM trips WHERE trip_id = {$activity['trip_id']}");
                                            if ($trip_result = mysqli_fetch_assoc($trip_query)) {
                                                $trip_name = $trip_result['trip_name'];
                                            }
                                        }
                                    ?>
                                        <div class="recent-activity-item">
                                            <small class="text-muted"><?php echo date('M j, g:i A', strtotime($activity['date'])); ?></small>
                                            <p class="mb-0">
                                                <?php if ($activity['type'] == 'expense'): ?>
                                                    Added expense of <?php echo $user['currency'] . ' ' . number_format($activity['amount'], 2); ?>
                                                    for <?php echo htmlspecialchars($activity['category']); ?>
                                                    <?php if ($trip_name): ?> in "<?php echo htmlspecialchars($trip_name); ?>"<?php endif; ?>
                                                    <?php else: ?>
                                                        Created new trip "<?php echo htmlspecialchars($trip_name); ?>"
                                                    <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No recent activities found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>