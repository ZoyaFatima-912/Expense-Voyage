<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: trips.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');


$user_email = $_SESSION['email'];
$user_query = "SELECT * FROM user_info WHERE email = '$user_email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);


$trip_id = $_GET['id'];
$trip_query = "SELECT * FROM trips WHERE trip_id = '$trip_id' AND user_email = '$user_email'";
$trip_result = mysqli_query($conn, $trip_query);

if (mysqli_num_rows($trip_result) == 0) {
    header("Location: trips.php");
    exit();
}

$trip = mysqli_fetch_assoc($trip_result);


$expenses_query = "SELECT * FROM expenses WHERE trip_id = '$trip_id' AND user_email = '$user_email' ORDER BY expense_date DESC";
$expenses_result = mysqli_query($conn, $expenses_query);


$total_query = "SELECT SUM(amount) as total FROM expenses WHERE trip_id = '$trip_id' AND user_email = '$user_email'";
$total_result = mysqli_query($conn, $total_query);
$total_expenses = mysqli_fetch_assoc($total_result)['total'] ?? 0;


$budget_percentage = 0;
if ($trip['budget'] && $trip['budget'] > 0) {
    $budget_percentage = min(100, ($total_expenses / $trip['budget']) * 100);
}


$categories_query = "SELECT category, SUM(amount) as total FROM expenses 
                    WHERE trip_id = '$trip_id' AND user_email = '$user_email' 
                    GROUP BY category ORDER BY total DESC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($trip['trip_name']); ?> - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .progress {
            height: 10px;
        }
        .category-progress {
            height: 5px;
        }
        .trip-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://via.placeholder.com/1200x400') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <div class="container py-4">
        <!-- Trip Header -->
        <div class="trip-header text-center mb-4">
            <h1><?php echo htmlspecialchars($trip['trip_name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($trip['destination']); ?></p>
            <p>
                <?php echo date('M j, Y', strtotime($trip['start_date'])); ?> - 
                <?php echo date('M j, Y', strtotime($trip['end_date'])); ?>
            </p>
        </div>
        
        <!-- Budget Summary -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Budget Summary</h5>
            </div>
            <div class="card-body">
                <?php if ($trip['budget']): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Total Budget: <?php echo $user['currency'] . ' ' . number_format($trip['budget'], 2); ?></h6>
                            <h6>Total Expenses: <?php echo $user['currency'] . ' ' . number_format($total_expenses, 2); ?></h6>
                            <h6>Remaining: <?php echo $user['currency'] . ' ' . number_format(max(0, $trip['budget'] - $total_expenses), 2); ?></h6>
                        </div>
                        <div class="col-md-6">
                            <div class="progress mt-3">
                                <div class="progress-bar <?php echo $budget_percentage > 80 ? 'bg-danger' : ($budget_percentage > 50 ? 'bg-warning' : 'bg-success'); ?>" 
                                     style="width: <?php echo $budget_percentage; ?>%">
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <small><?php echo number_format($budget_percentage, 1); ?>% of budget used</small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No budget set for this trip.</p>
                    <a href="edit-trip.php?id=<?php echo $trip_id; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus me-1"></i>Add Budget
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Expenses by Category -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Expenses by Category</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($categories_result) > 0): ?>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo htmlspecialchars($category['category']); ?></span>
                                <span><?php echo $user['currency'] . ' ' . number_format($category['total'], 2); ?></span>
                            </div>
                            <div class="progress category-progress">
                                <div class="progress-bar bg-info" 
                                     style="width: <?php echo ($category['total'] / $total_expenses) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No expenses recorded for this trip yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Trip Expenses -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Trip Expenses</h5>
                <a href="add-expense.php?trip_id=<?php echo $trip_id; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Expense
                </a>
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
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($expense = mysqli_fetch_assoc($expenses_result)): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                        <td><?php echo $expense['currency'] . ' ' . number_format($expense['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($expense['category']); ?></td>
                                        <td><?php echo $expense['notes'] ? htmlspecialchars(substr($expense['notes'], 0, 20) . '...') : '--'; ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="edit-expense.php?id=<?php echo $expense['expense_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="expenses.php?delete=<?php echo $expense['expense_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this expense?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No expenses recorded for this trip yet.</p>
                        <a href="add-expense.php?trip_id=<?php echo $trip_id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Your First Expense
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>