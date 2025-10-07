<?php
session_start();

// Include database configuration
require_once 'config/database.php';

// Function to validate and sanitize input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Initialize variables
$firstName = $lastName = $email = $message = "";
$errors = array();
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $firstName = validateInput($_POST['first_name']);
    $lastName = validateInput($_POST['last_name']);
    $email = validateInput($_POST['email']);
    $message = validateInput($_POST['message']);
    
    // Validation
    if (empty($firstName)) {
        $errors[] = "First name is required";
    } elseif (strlen($firstName) < 2) {
        $errors[] = "First name must be at least 2 characters";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    } elseif (strlen($lastName) < 2) {
        $errors[] = "Last name must be at least 2 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters";
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contact_messages (first_name, last_name, email, message) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$firstName, $lastName, $email, $message]);
            
            $success = true;
            $_SESSION['success_message'] = "Thank you for your message! We'll get back to you soon.";
            
            // Clear form data on success
            $firstName = $lastName = $email = $message = "";
            
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Response</title>
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
        
        .response-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 40px;
            background: rgba(246, 253, 195, 0.1);
            border: 2px solid aquamarine;
            box-shadow: 10px 10px 30px aquamarine;
            border-radius: 15px;
            backdrop-filter: blur(10px);
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
        
        h2, h3 {
            color: #F6FDC3;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        h2 {
            font-size: 2rem;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        p, li {
            color: #F6FDC3;
            line-height: 1.6;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .success {
            color: #F6FDC3;
            background: rgba(46, 204, 113, 0.2);
            border: 2px solid #2ecc71;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .error {
            color: #F6FDC3;
            background: rgba(231, 76, 60, 0.2);
            border: 2px solid #e74c3c;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .error ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .btn-back {
            background: linear-gradient(45deg, #C04848, #480048);
            color: #F6FDC3;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
            border: 2px solid aquamarine;
            font-weight: bold;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px aquamarine;
            text-decoration: none;
        }
        
        .form-container {
            margin-top: 30px;
            padding: 30px;
            background: rgba(246, 253, 195, 0.05);
            border: 1px solid aquamarine;
            border-radius: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #F6FDC3;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid aquamarine;
            border-radius: 8px;
            background: rgba(246, 253, 195, 0.1);
            color: #F6FDC3;
            font-family: "Work Sans", sans-serif;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(246, 253, 195, 0.6);
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
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
            animation: pulse 2s infinite;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px aquamarine;
        }
        
        @media (max-width: 768px) {
            .response-container {
                margin: 20px;
                padding: 25px;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="response-container">
        <?php if ($success): ?>
            <div class="success">
                <h2>🎉 Message Sent Successfully!</h2>
                <p>Thank you for reaching out! Your message has been received and stored securely. I'll get back to you as soon as possible.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <h2>❌ Please fix the following errors:</h2>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <a href="index.html" class="btn-back">🏠 Back to Portfolio</a>
        
        <?php if (!$success): ?>
            <div class="form-container">
                <h3>🔄 Try Again:</h3>
                <form action="process_contact.php" method="POST">
                    <div class="form-group">
                        <label for="first_name">👤 First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" placeholder="Your first name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">👤 Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" placeholder="Your last name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">📧 Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="your.email@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">💬 Message:</label>
                        <textarea id="message" name="message" placeholder="Your message here..." required><?php echo htmlspecialchars($message); ?></textarea>
                    </div>
                    
                    <input type="submit" value="🚀 Send Message" class="btn">
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>