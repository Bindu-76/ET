<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all users
$sql = "SELECT id, name, gender, country, email, subscription, join_date, last_login, is_active, profile_pic FROM users ORDER BY join_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Users - Eka Tatva Wellness Admin</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            padding: 20px;
        }
        .profile-pic {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">All Registered Users</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Country</th>
                    <th>Email</th>
                    <th>Subscription</th>
                    <th>Join Date</th>
                    <th>Last Login</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['profile_pic']) && file_exists('uploads/profiles/' . $row['profile_pic'])): ?>
                                    <img src="<?php echo 'uploads/profiles/' . htmlspecialchars($row['profile_pic']); ?>" alt="Profile Pic" class="profile-pic" />
                                <?php else: ?>
                                    <img src="assets/default-profile.png" alt="Default Profile" class="profile-pic" />
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['gender'])); ?></td>
                            <td><?php echo htmlspecialchars($row['country']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subscription'] ?: 'None'); ?></td>
                            <td><?php echo date("d M Y", strtotime($row['join_date'])); ?></td>
                            <td>
                                <?php 
                                    if (!empty($row['last_login'])) {
                                        echo date("d M Y H:i", strtotime($row['last_login']));
                                    } else {
                                        echo "Never";
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['is_active'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="admin.php" class="btn btn-primary mt-3">Back to Admin Dashboard</a>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
