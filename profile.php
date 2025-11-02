<?php

ob_start();
session_start();
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'expense_voyage');
// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$success_msg = '';
$error_msg = '';
$passwordPattern = "/^[A-Za-z0-9!@#$%^&*_-]{8,}$/";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save-btn'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);

    if (empty($first_name) || empty($last_name) || empty($new_email)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        $profile_pic = null;
        if (!empty($_FILES['profile_pic']['name'])) {
            $upload_dir = 'profile-pics/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024;

            $file_info = $_FILES['profile_pic'];

            if (!in_array($file_info['type'], $allowed_types)) {
                $error_msg = "Only JPG, PNG, and GIF images are allowed.";
            } elseif ($file_info['size'] > $max_size) {
                $error_msg = "Image size must be less than 2MB.";
            } else {
                $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($file_info['tmp_name'], $destination)) {
                    $profile_pic = $filename;
                    $stmt = $conn->prepare("SELECT profile_pic FROM user_info WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $old_pic = $result->fetch_assoc()['profile_pic'];

                    if ($old_pic && $old_pic !== 'default.jpg' && file_exists($upload_dir . $old_pic)) {
                        unlink($upload_dir . $old_pic);
                    }
                } else {
                    $error_msg = "Failed to upload profile picture.";
                }
            }
        }

        // Handle password update
        $hashed_password = null;
        if (!empty($new_password)) {
            if (preg_match($passwordPattern, $new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $error_msg = "Password must be at least 8 characters long and can contain letters, numbers, and !@#$%^&*_- characters.";
            }
        }

        if (empty($error_msg)) {
            if ($profile_pic && $hashed_password) {
                $stmt = $conn->prepare("UPDATE user_info SET first_name = ?, last_name = ?, email = ?, profile_pic = ?, password = ? WHERE email = ?");
                $stmt->bind_param("ssssss", $first_name, $last_name, $new_email, $profile_pic, $hashed_password, $email);
            } elseif ($profile_pic) {
                $stmt = $conn->prepare("UPDATE user_info SET first_name = ?, last_name = ?, email = ?, profile_pic = ? WHERE email = ?");
                $stmt->bind_param("sssss", $first_name, $last_name, $new_email, $profile_pic, $email);
            } elseif ($hashed_password) {
                $stmt = $conn->prepare("UPDATE user_info SET first_name = ?, last_name = ?, email = ?, password = ? WHERE email = ?");
                $stmt->bind_param("sssss", $first_name, $last_name, $new_email, $hashed_password, $email);
            } else {
                $stmt = $conn->prepare("UPDATE user_info SET first_name = ?, last_name = ?, email = ? WHERE email = ?");
                $stmt->bind_param("ssss", $first_name, $last_name, $new_email, $email);
            }

            if ($stmt->execute()) {
                if ($new_email !== $email) {
                    $_SESSION['email'] = $new_email;
                    $email = $new_email;
                }
                $success_msg = "Profile updated successfully!";
            } else {
                $error_msg = "Error updating profile: " . $conn->error;
            }
        }
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_pic FROM user_info WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (empty($user['profile_pic']) || $user['profile_pic'] === 'profile-pic') {
    $user['profile_pic'] = 'default.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ExpenseVoyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --deep-ocean: #023047;
            --golden-sand: #F4A261;
            --sunset-coral: #E76F51;
            --sky-white: #FAFAFA;
            --sea-mist: #E5E5E5;
        }
        
        body {
            background-color: var(--sky-white);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(2, 48, 71, 0.1);
        }
        
        .card-header {
            background-color: var(--deep-ocean);
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .profile-pic {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            
            box-shadow: 0 4px 12px rgba(244, 162, 97, 0.3);
            transition: all 0.3s ease;
        }
        
        .profile-pic:hover {
            transform: scale(1.05);
        }
        
        .profile-pic-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        
        .profile-pic-edit {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--sunset-coral);
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid var(--sky-white);
        }
        
        .profile-pic-edit:hover {
            background: var(--deep-ocean);
            transform: scale(1.1);
        }
        
        .form-control {
            border: 1px solid var(--sea-mist);
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 1.25rem;
            background-color: var(--sky-white);
        }
        
        .form-control:focus {
            border-color: var(--golden-sand);
            box-shadow: 0 0 0 0.25rem rgba(244, 162, 97, 0.25);
        }
        
        .btn-primary {
            background-color: var(--deep-ocean);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--sunset-coral);
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(2, 48, 71, 0.1);
            border-color: var(--deep-ocean);
            color: var(--deep-ocean);
        }
        
        .alert-danger {
            background-color: rgba(231, 111, 81, 0.1);
            border-color: var(--sunset-coral);
            color: var(--sunset-coral);
        }
        
        label {
            font-weight: 600;
            color: var(--deep-ocean);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        h4 {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .container-fluid {
            padding: 0 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header text-white" style="background-color: #023047;">
                        <h4 class="mb-0"><i class="fa-solid fa-user-pen me-2" ></i>Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_msg): ?>
                            <div class="alert alert-success"><?php echo $success_msg; ?></div>
                        <?php endif; ?>
                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" class="px-3">
                            <div class="text-center mb-4">
                                <div class="profile-pic-container mx-auto">
                                    <img src="profile-pics/<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                                         class="profile-pic" 
                                         alt="Profile Picture"
                                         id="profile-pic-preview">
                                    <div class="profile-pic-edit" onclick="document.getElementById('profile_pic').click()">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                <input type="file" name="profile_pic" id="profile_pic" class="d-none" accept="image/*">
                                <small class="text-muted d-block mt-2">Click on the camera icon to change your profile picture</small>
                            </div>

                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       placeholder="Enter first name"
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                       placeholder="Enter last name"
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="Enter email"
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">New Password (optional)</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Enter new password (leave blank to keep old)">
                                <small class="text-muted">Password must be at least 8 characters</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" name="save-btn">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview profile picture before upload
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('profile-pic-preview');
                    preview.src = event.target.result;
                    preview.classList.add('animate__animated', 'animate__pulse');
                    setTimeout(() => {
                        preview.classList.remove('animate__animated', 'animate__pulse');
                    }, 1000);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

<?php
ob_end_flush();
?>