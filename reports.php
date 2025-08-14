<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$user_email = $_SESSION['email'];
$user_query = "SELECT * FROM user_info WHERE email = '$user_email'";
$user_result = mysqli_query($conn, $user_query);

if (!$user_result) {
    die("Error fetching user data: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($user_result);

 
$trips_query = "SELECT * FROM trips WHERE user_email = '$user_email' ORDER BY start_date DESC";
$trips_result = mysqli_query($conn, $trips_query);

if (!$trips_result) {
    die("Error fetching trips: " . mysqli_error($conn));
}


$trip_filter = isset($_GET['trip_id']) ? $_GET['trip_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';


$expenses_query = "SELECT e.*, t.trip_name FROM expenses e 
                   LEFT JOIN trips t ON e.trip_id = t.trip_id 
                   WHERE e.user_email = '$user_email'";

if (!empty($trip_filter)) {
    $expenses_query .= " AND e.trip_id = '$trip_filter'";
}

if (!empty($start_date) && !empty($end_date)) {
    $expenses_query .= " AND e.expense_date BETWEEN '$start_date' AND '$end_date'";
} elseif (!empty($start_date)) {
    $expenses_query .= " AND e.expense_date >= '$start_date'";
} elseif (!empty($end_date)) {
    $expenses_query .= " AND e.expense_date <= '$end_date'";
}

$expenses_query .= " ORDER BY e.expense_date DESC";

$expenses_result = mysqli_query($conn, $expenses_query);

if (!$expenses_result) {
    die("Error fetching expenses: " . mysqli_error($conn));
}


$totals_query = "SELECT SUM(e.amount) as total_amount, COUNT(*) as count
                 FROM expenses e
                 LEFT JOIN trips t ON e.trip_id = t.trip_id
                 WHERE e.user_email = '$user_email'";


if (!empty($trip_filter)) {
    $totals_query .= " AND e.trip_id = '$trip_filter'";
}

if (!empty($start_date) && !empty($end_date)) {
    $totals_query .= " AND e.expense_date BETWEEN '$start_date' AND '$end_date'";
} elseif (!empty($start_date)) {
    $totals_query .= " AND e.expense_date >= '$start_date'";
} elseif (!empty($end_date)) {
    $totals_query .= " AND e.expense_date <= '$end_date'";
}

$totals_result = mysqli_query($conn, $totals_query);
if (!$totals_result) {
    die("Error calculating totals: " . mysqli_error($conn));
}
$totals = mysqli_fetch_assoc($totals_result);


$categories_query = "SELECT e.category, SUM(e.amount) as category_total
                     FROM expenses e
                     LEFT JOIN trips t ON e.trip_id = t.trip_id
                     WHERE e.user_email = '$user_email'";


if (!empty($trip_filter)) {
    $categories_query .= " AND e.trip_id = '$trip_filter'";
}

if (!empty($start_date) && !empty($end_date)) {
    $categories_query .= " AND e.expense_date BETWEEN '$start_date' AND '$end_date'";
} elseif (!empty($start_date)) {
    $categories_query .= " AND e.expense_date >= '$start_date'";
} elseif (!empty($end_date)) {
    $categories_query .= " AND e.expense_date <= '$end_date'";
}

$categories_query .= " GROUP BY e.category ORDER BY category_total DESC";

$categories_result = mysqli_query($conn, $categories_query);
if (!$categories_result) {
    die("Error fetching categories: " . mysqli_error($conn));
}


$categories_count = 0;
if ($categories_result) {
    $categories_count = mysqli_num_rows($categories_result);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Reports - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
    --deep-ocean: #023047;
    --golden-sand: #F4A261;
    --sunset-coral: #E76F51;
    --sky-white: #FAFAFA;
    --sea-mist: #E5E5E5;
}

/* Global page background */
body {
    background-color: var(--sky-white);
    color: var(--deep-ocean);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Headings */
h2, h5, h3 {
    color: var(--deep-ocean);
}

/* Card styling */
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
    background-color: var(--sky-white);
}

.card-header {
    background-color: var(--deep-ocean) !important;
    color: var(--sky-white);
}

/* Special colored borders for summary cards */
.border-primary {
    border-top: 5px solid var(--deep-ocean) !important;
}
.border-success {
    border-top: 5px solid var(--golden-sand) !important;
}
.border-info {
    border-top: 5px solid var(--sunset-coral) !important;
}

/* Buttons */
.btn-primary {
    background-color: var(--golden-sand);
    border: none;
    color: var(--deep-ocean);
    font-weight: 600;
}
.btn-primary:hover {
    background-color: var(--sunset-coral);
    color: var(--sky-white);
}

.btn-outline-secondary {
    border-color: var(--deep-ocean);
    color: var(--deep-ocean);
}
.btn-outline-secondary:hover {
    background-color: var(--deep-ocean);
    color: var(--sky-white);
}

/* Table styling */
.table thead {
    background-color: var(--deep-ocean);
    color: var(--sky-white);
}
.table-hover tbody tr:hover {
    background-color: var(--sea-mist);
}

/* Filter card header */
.card-header.bg-primary {
    background-color: var(--deep-ocean) !important;
}

/* Chart container border */
.chart-container {
    border: 1px solid var(--sea-mist);
    border-radius: 8px;
    padding: 10px;
    background-color: var(--sky-white);
}

/* Error message */
.error-message {
    background-color: rgba(231, 111, 81, 0.1);
    border-left: 5px solid var(--sunset-coral);
    color: var(--sunset-coral);
}

/* Links inside the page */
a {
    color: var(--sunset-coral);
    text-decoration: none;
}
a:hover {
    color: var(--golden-sand);
    text-decoration: underline;
}

    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <div class="container py-5">
        <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Expense Reports</h2>
        
        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Expenses</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="reports.php">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="trip_id" class="form-label">Filter by Trip</label>
                            <select class="form-select" id="trip_id" name="trip_id">
                                <option value="">All Trips</option>
                                <?php 
                                if ($trips_result && mysqli_num_rows($trips_result) > 0) {
                                    mysqli_data_seek($trips_result, 0);
                                    while ($trip = mysqli_fetch_assoc($trips_result)): ?>
                                        <option value="<?php echo $trip['trip_id']; ?>" <?php echo $trip_filter == $trip['trip_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trip['trip_name']); ?>
                                        </option>
                                    <?php endwhile;
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary me-md-2">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        <a href="reports.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary"><i class="fas fa-receipt me-2"></i>Total Expenses</h5>
                        <h3 class="card-text"><?php echo $user['currency'] . ' ' . number_format($totals['total_amount'] ?? 0, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success"><i class="fas fa-list-ol me-2"></i>Number of Expenses</h5>
                        <h3 class="card-text"><?php echo $totals['count'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100 border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title text-info"><i class="fas fa-tags me-2"></i>Categories Used</h5>
                        <h3 class="card-text"><?php echo $categories_count; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Expenses by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Expenses Over Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="timeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Expense Details</h5>
                <a href="add-expense.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Expense
                </a>
            </div>
            <div class="card-body">
                <?php if (($totals['count'] ?? 0) > 0): ?>
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
                                mysqli_data_seek($expenses_result, 0);
                                while ($expense = mysqli_fetch_assoc($expenses_result)): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                        <td><?php echo $expense['currency'] . ' ' . number_format($expense['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($expense['category']); ?></td>
                                        <td><?php echo $expense['trip_name'] ? htmlspecialchars($expense['trip_name']) : '--'; ?></td>
                                        <td><?php echo $expense['notes'] ? htmlspecialchars(substr($expense['notes'], 0, 20)) . '...' : '--'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No expenses found matching your filters.</p>
                        <a href="add-expense.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Your First Expense
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryLabels = [];
        const categoryData = [];
        const categoryColors = [];
        
        <?php 
        if ($categories_result && mysqli_num_rows($categories_result) > 0) {
            mysqli_data_seek($categories_result, 0);
            while ($category = mysqli_fetch_assoc($categories_result)): 
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        ?>
            categoryLabels.push('<?php echo addslashes($category['category']); ?>');
            categoryData.push(<?php echo $category['category_total']; ?>);
            categoryColors.push('<?php echo $color; ?>');
        <?php 
            endwhile;
        } else { ?>
            categoryLabels.push('No Data');
            categoryData.push(1);
            categoryColors.push('#cccccc');
        <?php } ?>
        
        const categoryChart = new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: categoryColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${<?php echo $user['currency']; ?>}${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        
        const timeCtx = document.getElementById('timeChart').getContext('2d');
        const timeChart = new Chart(timeCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Monthly Expenses',
                    data: [1200, 1900, 1500, 2000, 1800, 2200, 2400],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>