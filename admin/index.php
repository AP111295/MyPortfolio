<?php
session_start();
// access website : http://localhost/MyPortfolio-main/
// access admin panel : http://localhost/MyPortfolio-main/admin/
// Enter the login credentials:Username: admin
// Password: admin123

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
    // Selects all contact messages from the database
    // Orders them by creation date (newest first)
    // Uses $pdo->query() since there are no parameters
    // Fetches all results into the $messages variable
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
            font-family: "Work Sans", sans-serif;
            background: linear-gradient(45deg, #C04848, #480048 ) no-repeat;
            background-size: 400% 400%;
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25), 
                        0 0 0 1px rgba(255,255,255,0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .header {
            background: linear-gradient(45deg, #C04848, #480048 ) no-repeat;;
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1.5" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .header > * {
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
            font-weight: 700;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 400;
            letter-spacing: 0.5px;
        }
        
        .login-form {
            max-width: 450px;
            margin: 50px auto;
            padding: 50px 40px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25),
                        0 0 0 1px rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .login-form h2 {
            text-align: center;
            margin-bottom: 35px;
            color: #2d3748;
            font-weight: 700;
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1),
                        0 10px 25px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }
        
        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(192, 72, 72, 0.4);
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            padding: 12px 25px;
            width: auto;
            float: right;
            border: 2px solid rgba(255,255,255,0.2);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .messages-container {
            padding: 40px;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        }
        
        .message-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 20px;
            margin-bottom: 25px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .message-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .message-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        .message-card:hover::before {
            transform: scaleY(1);
        }
        
        .message-card.unread {
            border-left: 4px solid #ff6b6b;
            background: rgba(255, 107, 107, 0.05);
            border-color: rgba(255, 107, 107, 0.3);
        }
        
        .message-card.unread::before {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            transform: scaleY(1);
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
            color: #2d3748;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .message-info p {
            color: #4a5568;
            font-size: 14px;
            font-weight: 500;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 11px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .btn-read {
            background: linear-gradient(135deg, #48bb78, #38a169);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #f56565, #e53e3e);
        }
        
        .message-content {
            background: rgba(102, 126, 234, 0.05);
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
            color: #2d3748;
            font-weight: 500;
            line-height: 1.6;
        }
        
        .alert {
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 15px;
            font-weight: 500;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(72, 187, 120, 0.1), rgba(56, 161, 105, 0.1));
            color: #2f855a;
            border-color: rgba(72, 187, 120, 0.3);
        }
        
        .alert-error {
            background: linear-gradient(135deg, rgba(245, 101, 101, 0.1), rgba(229, 62, 62, 0.1));
            color: #c53030;
            border-color: rgba(245, 101, 101, 0.3);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            color: #2d3748;
            padding: 30px 25px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .stat-card h3 {
            font-size: 3rem;
            margin-bottom: 15px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card p {
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #4a5568;
        }
        
        .no-messages {
            text-align: center;
            color: #4a5568;
            font-size: 18px;
            margin: 60px 0;
            padding: 50px 40px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .no-messages h3 {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            font-size: 2.2rem;
            font-weight: 700;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 25px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: #667eea;
            color: #ffffff;
            transform: translateY(-2px);
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
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
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .login-form {
                margin: 20px;
                padding: 30px 20px;
            }
        }
        
        /* Animation for page load */
        .container, .login-form {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Pulse animation for unread messages */
        .message-card.unread::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #C04848, #480048);
            border-radius: 12px;
            z-index: -1;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .message-card {
            position: relative;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 0.8;
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
                <small style="color: #480048; font-weight: 500;">Default credentials: admin / admin123</small><br>
                <a href="../index.html" class="back-link" style="margin-top: 15px; display: inline-block;">← Back to Portfolio</a>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="header">
                <h1>Contact Messages Dashboard</h1>
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
                                            <span style="color: #ffffff; background: linear-gradient(135deg, #ff6b6b, #ee5a52); padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);">• NEW</span>
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