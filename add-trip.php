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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_trip'])) {
    $trip_name = trim($_POST['trip_name']);
    $destination = trim($_POST['destination']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $budget = !empty($_POST['budget']) ? trim($_POST['budget']) : NULL;
    $notes = trim($_POST['notes']);
    
    
    if (empty($trip_name) || empty($destination) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } elseif ($start_date > $end_date) {
        $_SESSION['error'] = 'End date must be after start date.';
    } else {
        $insert_query = "INSERT INTO trips (user_email, trip_name, destination, start_date, end_date, budget, notes) 
                         VALUES ('$user_email', '$trip_name', '$destination', '$start_date', '$end_date', " . ($budget ? "'$budget'" : "NULL") . ", " . ($notes ? "'$notes'" : "NULL") . ")";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = 'Trip added successfully!';
            header("Location: trips.php");
            exit();
        } else {
            $_SESSION['error'] = 'Error adding trip: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Trip - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="addtrip-container">
        <div class="addtrip-card">
            <div class="addtrip-header">
                <h4><i class="fas fa-suitcase me-2"></i>Plan New Trip</h4>
            </div>
            <div class="addtrip-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="addtrip-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="add-trip.php" class="addtrip-form">
                    
                    <div class="addtrip-field">
                        <label for="trip_name">Trip Name</label>
                        <input type="text" id="trip_name" name="trip_name" required>
                    </div>

                    <div class="addtrip-field">
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" required>
                    </div>

                    <div class="addtrip-row">
                        <div class="addtrip-field" style="flex:1;">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        <div class="addtrip-field" style="flex:1;">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="addtrip-field">
                        <label for="budget">Budget (<?php echo $user['currency']; ?>) (optional)</label>
                        <div class="addtrip-inputgroup">
                            <span><?php echo $user['currency']; ?></span>
                            <input type="number" step="0.01" id="budget" name="budget">
                        </div>
                    </div>

                    <div class="addtrip-field">
                        <label for="notes">Notes (optional)</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="addtrip-actions">
                        <button type="submit" name="add_trip" class="addtrip-btn-primary">
                            <i class="fas fa-save me-2"></i>Save Trip
                        </button>
                        <a href="home.php" class="addtrip-btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
        document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
