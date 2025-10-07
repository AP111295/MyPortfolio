<?php
session_start();

// access website : http://localhost/MyPortfolio-main/
// access admin panel : http://localhost:8000/admin/
// Enter the login credentials:
// Username : admin
// Password : admin123
// Simple admin authentication (you should enhance this for production)
$admin_username = 'admin';
$admin_password = 'admin123'; // Change this password!

$logged_in = false;

if (isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $logged_in = true;
    } else {
        $error_message = "Invalid credentials!";
    }
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $logged_in = true;
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Include database configuration
if ($logged_in) {
    require_once '../config/database.php';
    
    // Handle message deletion
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $delete_id = (int)$_GET['delete'];
        try {
            $sql = "DELETE FROM contact_messages WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$delete_id]);
            $success_message = "Message deleted successfully!";
        } catch(PDOException $e) {
            $error_message = "Error deleting message: " . $e->getMessage();
        }
    }
    
    // Handle mark as read
    if (isset($_GET['read']) && is_numeric($_GET['read'])) {
        $read_id = (int)$_GET['read'];
        try {
            $sql = "UPDATE contact_messages SET is_read = TRUE WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$read_id]);
            $success_message = "Message marked as read!";
        } catch(PDOException $e) {
            $error_message = "Error updating message: " . $e->getMessage();
        }
    }
    
    // Fetch all messages
    try {
        $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        $messages = $stmt->fetchAll();
    } catch(PDOException $e) {
        $error_message = "Error fetching messages: " . $e->getMessage();
        $messages = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Contact Messages</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        *, ::before, ::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Work Sans", sans-serif;
            background: linear-gradient(45deg, #C04848, #480048) no-repeat;
            background-size: 100% 100%;
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(246, 253, 195, 0.1);
            border: 2px solid aquamarine;
            box-shadow: 10px 10px 30px aquamarine;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(45deg, #C04848, #480048);
            color: #F6FDC3;
            padding: 40px;
            text-align: center;
            border-bottom: 2px solid aquamarine;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            color: #F6FDC3;
            opacity: 0.9;
        }
        
        .login-form {
            max-width: 450px;
            margin: 50px auto;
            padding: 50px;
            background: rgba(246, 253, 195, 0.1);
            border: 2px solid aquamarine;
            box-shadow: 10px 10px 30px aquamarine;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .login-form h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #F6FDC3;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #F6FDC3;
            font-size: 1.1rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid aquamarine;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(246, 253, 195, 0.1);
            color: #F6FDC3;
            transition: all 0.3s ease;
            font-family: "Work Sans", sans-serif;
        }
        
        .form-group input::placeholder {
            color: rgba(246, 253, 195, 0.6);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #F6FDC3;
            box-shadow: 0 0 15px aquamarine;
        }
        
        .btn {
            background: linear-gradient(45deg, #C04848, #480048);
            color: #F6FDC3;
            padding: 15px 30px;
            border: 2px solid aquamarine;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            font-family: "Work Sans", sans-serif;
            transition: all 0.3s ease;
            width: 100%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px aquamarine;
        }
        
        .btn-logout {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            padding: 10px 20px;
            width: auto;
            float: right;
            font-size: 14px;
            animation: none;
        }
        
        .btn-logout:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }
        
        .messages-container {
            padding: 30px;
        }
        
        .message-card {
            background: rgba(246, 253, 195, 0.1);
            border: 1px solid aquamarine;
            border-radius: 15px;
            margin-bottom: 25px;
            padding: 25px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .message-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px aquamarine;
            border-color: #F6FDC3;
        }
        
        .message-card.unread {
            border-left: 5px solid #F6FDC3;
            background: rgba(246, 253, 195, 0.2);
            box-shadow: 0 0 20px rgba(127, 255, 212, 0.3);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .message-info {
            flex: 1;
        }
        
        .message-info h3 {
            color: #F6FDC3;
            margin-bottom: 8px;
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .message-info p {
            color: aquamarine;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .message-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #F6FDC3;
            border: 1px solid aquamarine;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            font-family: "Work Sans", sans-serif;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(127, 255, 212, 0.4);
        }
        
        .btn-read {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
        }
        
        .btn-read:hover {
            background: linear-gradient(45deg, #2ecc71, #58d68d);
        }
        
        .btn-delete {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
        
        .btn-delete:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }
        
        .message-content {
            background: rgba(246, 253, 195, 0.1);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid aquamarine;
            color: #F6FDC3;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .alert {
            padding: 18px;
            margin-bottom: 25px;
            border-radius: 10px;
            border: 2px solid;
            font-weight: bold;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #F6FDC3;
            border-color: #2ecc71;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            color: #F6FDC3;
            border-color: #e74c3c;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(45deg, #C04848, #480048);
            color: #F6FDC3;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            border: 2px solid aquamarine;
            box-shadow: 5px 5px 20px rgba(127, 255, 212, 0.3);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px aquamarine;
        }
        
        .stat-card h3 {
            font-size: 3rem;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .stat-card p {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .no-messages {
            text-align: center;
            color: #F6FDC3;
            font-size: 20px;
            margin: 60px 0;
            padding: 40px;
            background: rgba(246, 253, 195, 0.1);
            border: 2px solid aquamarine;
            border-radius: 15px;
        }
        
        .no-messages h3 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #F6FDC3;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: aquamarine;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #F6FDC3;
            text-shadow: 0 0 10px aquamarine;
        }
        
        /* Loading animation for login form */
        .login-form {
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .message-actions {
                margin-top: 15px;
                width: 100%;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .login-form {
                margin: 20px;
                padding: 30px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(72, 0, 72, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #C04848, #480048);
            border-radius: 6px;
            border: 2px solid aquamarine;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #480048, #C04848);
        }
    </style>
</head>
<body>
    <?php if (!$logged_in): ?>
        <div class="login-form">
            <h2>🔐 Admin Access Portal</h2>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">👤 Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter admin username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">🔑 Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter admin password" required>
                </div>
                
                <button type="submit" name="login" class="btn">🚀 Access Dashboard</button>
            </form>
            
            <div style="margin-top: 25px; text-align: center;">
                <small style="color: aquamarine; font-size: 14px;">Default: admin / admin123</small><br>
                <a href="../index.html" class="back-link">← Back to Portfolio</a>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="header">
                <h1>📧 Portfolio Command Center</h1>
                <p>Contact Message Management Dashboard</p>
                <form method="POST" style="margin-top: 25px;">
                    <button type="submit" name="logout" class="btn-logout">🚪 Logout</button>
                </form>
            </div>
            
            <div class="messages-container">
                <a href="../index.html" class="back-link">← Back to Portfolio</a>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Statistics -->
                <div class="stats">
                    <div class="stat-card">
                        <h3><?php echo count($messages); ?></h3>
                        <p>📩 Total Messages</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo count(array_filter($messages, function($m) { return !$m['is_read']; })); ?></h3>
                        <p>🆕 Unread Messages</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo count(array_filter($messages, function($m) { return $m['is_read']; })); ?></h3>
                        <p>✅ Read Messages</p>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if (empty($messages)): ?>
                    <div class="no-messages">
                        <h3>📭 No messages yet</h3>
                        <p>When people contact you through the portfolio form, their messages will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-card <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                            <div class="message-header">
                                <div class="message-info">
                                    <h3><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></h3>
                                    <p>
                                        📧 <?php echo htmlspecialchars($message['email']); ?> | 
                                        📅 <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                        <?php if (!$message['is_read']): ?>
                                            <span style="color: #3498db; font-weight: bold;">• NEW</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="message-actions">
                                    <?php if (!$message['is_read']): ?>
                                        <a href="?read=<?php echo $message['id']; ?>" class="btn-small btn-read">✅ Mark as Read</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $message['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this message?')">🗑️ Delete</a>
                                </div>
                            </div>
                            <div class="message-content">
                                <strong>💬 Message:</strong><br>
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>