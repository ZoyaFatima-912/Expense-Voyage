<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');

//  info
$user_email = $_SESSION['email'];
$user_query = "SELECT * FROM user_info WHERE email = '$user_email'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);



$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);


$trips_query = "SELECT * FROM trips WHERE user_email = '$user_email' ORDER BY start_date DESC";
$trips_result = mysqli_query($conn, $trips_query);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $amount = trim($_POST['amount']);
    $category = trim($_POST['category']);
    $expense_date = trim($_POST['expense_date']);
    $trip_id = !empty($_POST['trip_id']) ? $_POST['trip_id'] : NULL;
    $notes = trim($_POST['notes']);


    if (empty($amount) || empty($category) || empty($expense_date)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['error'] = 'Please enter a valid amount.';
    } else {
        $insert_query = "INSERT INTO expenses (user_email, trip_id, amount, category, expense_date, notes, currency) 
                         VALUES ('$user_email', " . ($trip_id ? "'$trip_id'" : "NULL") . ", '$amount', '$category', '$expense_date', " . ($notes ? "'$notes'" : "NULL") . ", '{$user['currency']}')";

        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = 'Expense added successfully!';
            header("Location: home.php");
            exit();
        } else {
            $_SESSION['error'] = 'Error adding expense: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="addexpense-container">
        <div class="addexpense-row">
            <div class="addexpense-col">
                <div class="addexpense-card">
                    <div class="addexpense-card-header">
                        <h4><i class="fas fa-plus-circle"></i> Add New Expense</h4>
                    </div>
                    <div class="addexpense-card-body">

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="addexpense-alert-error">
                                <?php echo $_SESSION['error'];
                                unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="add-expense.php" class="addexpense-form">

                            <div class="addexpense-form-group">
                                <label for="amount">Amount (<?php echo $user['currency']; ?>)</label>
                                <div class="addexpense-input-group">
                                    <span class="addexpense-input-prefix"><?php echo $user['currency']; ?></span>
                                    <input type="number" step="0.01" id="amount" name="amount" required>
                                </div>
                            </div>

                            <div class="addexpense-form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?php echo htmlspecialchars($category['category_name']); ?>">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="addexpense-form-group">
                                <label for="expense_date">Date</label>
                                <input type="date" id="expense_date" name="expense_date" required
                                    max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="addexpense-form-group">
                                <label for="trip_id">Associated Trip (optional)</label>
                                <select id="trip_id" name="trip_id">
                                    <option value="">No trip association</option>
                                    <?php while ($trip = mysqli_fetch_assoc($trips_result)): ?>
                                        <option value="<?php echo $trip['trip_id']; ?>">
                                            <?php echo htmlspecialchars($trip['trip_name']); ?> (<?php echo date('M j, Y', strtotime($trip['start_date'])); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="addexpense-form-group">
                                <label for="notes">Notes (optional)</label>
                                <textarea id="notes" name="notes"></textarea>
                            </div>

                            <div class="addexpense-btn-group">
                                <button type="submit" name="add_expense" class="addexpense-btn-primary">
                                    <i class="fas fa-save"></i> Save Expense
                                </button>
                                <a href="home.php" class="addexpense-btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>