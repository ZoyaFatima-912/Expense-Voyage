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


$trips_query = "SELECT * FROM trips WHERE user_email = '$user_email' ORDER BY start_date DESC";
$trips_result = mysqli_query($conn, $trips_query);


$count_query = "SELECT COUNT(*) as total FROM trips WHERE user_email = '$user_email'";
$count_result = mysqli_query($conn, $count_query);
$count = mysqli_fetch_assoc($count_result)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .trip-card {
            transition: transform 0.3s;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .trip-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-suitcase me-2"></i>My Trips</h2>
            <a href="add-trip.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Trip
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if ($count > 0): ?>
            <div class="row">
                <?php while ($trip = mysqli_fetch_assoc($trips_result)): ?>
                    <?php
                    $current_date = date('Y-m-d');
                    $status = '';
                    if ($current_date < $trip['start_date']) {
                        $status = 'Upcoming';
                        $badge_class = 'bg-info';
                    } elseif ($current_date >= $trip['start_date'] && $current_date <= $trip['end_date']) {
                        $status = 'Ongoing';
                        $badge_class = 'bg-success';
                    } else {
                        $status = 'Completed';
                        $badge_class = 'bg-secondary';
                    }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card trip-card h-100">
                            <div class="card-header bg-primary text-white position-relative">
                                <h5 class="mb-0"><?php echo htmlspecialchars($trip['trip_name']); ?></h5>
                                <span class="badge <?php echo $badge_class; ?> status-badge"><?php echo $status; ?></span>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($trip['destination']); ?></p>
                                <p class="mb-2"><i class="far fa-calendar-alt me-2"></i>
                                    <?php echo date('M j, Y', strtotime($trip['start_date'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($trip['end_date'])); ?>
                                </p>
                                <?php if ($trip['budget']): ?>
                                    <p class="mb-0"><i class="fas fa-wallet me-2"></i>Budget: <?php echo $user['currency'] . ' ' . number_format($trip['budget'], 2); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between">
                                    <a href="trip-details.php?id=<?php echo $trip['trip_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="edit-trip.php?id=<?php echo $trip['trip_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-suitcase fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">No trips found</h4>
                <p class="text-muted">You haven't created any trips yet.</p>
                <a href="add-trip.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Create Your First Trip
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>