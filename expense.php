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

$expenses_query = "SELECT e.*, t.trip_name FROM expenses e 
                   LEFT JOIN trips t ON e.trip_id = t.trip_id 
                   WHERE e.user_email = '$user_email' 
                   ORDER BY e.expense_date DESC";
$expenses_result = mysqli_query($conn, $expenses_query);

$count_query = "SELECT COUNT(*) as total FROM expenses WHERE user_email = '$user_email'";
$count_result = mysqli_query($conn, $count_query);
$count = mysqli_fetch_assoc($count_result)['total'];

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $expense_id = $_GET['delete'];
    $delete_query = "DELETE FROM expenses WHERE expense_id = '$expense_id' AND user_email = '$user_email'";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = 'Expense deleted successfully.';
        header("Location: expenses.php");
        exit();
    } else {
        $_SESSION['error'] = 'Error deleting expense.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Expenses - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>My Expenses</h2>
            <a href="add-expense.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Expense
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if ($count > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle expense-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Trip</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($expense = mysqli_fetch_assoc($expenses_result)): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                                <td class="fw-bold"><?php echo $expense['currency'] . ' ' . number_format($expense['amount'], 2); ?></td>
                                <td>
                                    <span class="expense-category">
                                        <?php echo htmlspecialchars($expense['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $expense['trip_name'] ? htmlspecialchars($expense['trip_name']) : '--'; ?>
                                </td>
                                <td>
                                    <?php echo $expense['notes'] ? htmlspecialchars(substr($expense['notes'], 0, 20) . '...') : '--'; ?>
                                </td>
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
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-4x no-expense-icon mb-4"></i>
                <h4 class="no-expense">No expenses found</h4>
                <p class="no-expense">You haven't recorded any expenses yet.</p>
                <a href="add-expense.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Add Your First Expense
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
