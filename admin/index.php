<?php
session_start();

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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .login-form h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-logout {
            background: #e74c3c;
            padding: 10px 20px;
            width: auto;
            float: right;
        }
        
        .messages-container {
            padding: 30px;
        }
        
        .message-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .message-card.unread {
            border-left: 5px solid #3498db;
            background: #e8f4fd;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .message-info {
            flex: 1;
        }
        
        .message-info h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .message-info p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 5px 15px;
            font-size: 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn-small:hover {
            opacity: 0.8;
        }
        
        .btn-read {
            background: #27ae60;
        }
        
        .btn-delete {
            background: #e74c3c;
        }
        
        .message-content {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #3498db;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .no-messages {
            text-align: center;
            color: #7f8c8d;
            font-size: 18px;
            margin: 50px 0;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .message-actions {
                margin-top: 10px;
                width: 100%;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php if (!$logged_in): ?>
        <div class="login-form">
            <h2>🔐 Admin Login</h2>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <small>Default credentials: admin / admin123</small><br>
                <a href="../index.html" class="back-link">← Back to Portfolio</a>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="header">
                <h1>📧 Contact Messages Dashboard</h1>
                <p>Manage your portfolio contact form messages</p>
                <form method="POST" style="margin-top: 20px;">
                    <button type="submit" name="logout" class="btn-logout">Logout</button>
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
                        <p>Total Messages</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo count(array_filter($messages, function($m) { return !$m['is_read']; })); ?></h3>
                        <p>Unread Messages</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo count(array_filter($messages, function($m) { return $m['is_read']; })); ?></h3>
                        <p>Read Messages</p>
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
                                        <a href="?read=<?php echo $message['id']; ?>" class="btn-small btn-read">Mark as Read</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $message['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                                </div>
                            </div>
                            <div class="message-content">
                                <strong>Message:</strong><br>
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