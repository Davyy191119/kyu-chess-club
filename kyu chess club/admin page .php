<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Ensure user is admin
if (!isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chess Club Admin Dashboard</title>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .member-table {
            width: 100%;
            border-collapse: collapse;
        }
        .member-table th, .member-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Members</h3>
                <p><?php echo getTotalMembers(); ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Members</h3>
                <p><?php echo getActiveMembers(); ?></p>
            </div>
            <div class="stat-card">
                <h3>New Members (This Month)</h3>
                <p><?php echo getNewMembers(); ?></p>
            </div>
        </div>

        <h2>Member Management</h2>
        <table class="member-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>FIDE ID</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(getAllMembers() as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['username']); ?></td>
                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                    <td><?php echo htmlspecialchars($member['fide_id']); ?></td>
                    <td><?php echo htmlspecialchars($member['rating']); ?></td>
                    <td><?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <button onclick="editMember(<?php echo $member['id']; ?>)">Edit</button>
                        <button onclick="toggleStatus(<?php echo $member['id']; ?>)">
                            <?php echo $member['is_active'] ? 'Deactivate' : 'Activate'; ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
