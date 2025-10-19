<?php
require_once 'config.php';

// Initialize variables
$message = '';
$edit_user = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    
    // Create or Update user
    if (isset($_POST['action']) && $_POST['action'] === 'save') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        // Validate input
        if (empty($name) || empty($email)) {
            $message = "Name and email are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } else {
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update existing user
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
                $stmt->bind_param("ssi", $name, $email, $id);
                
                if ($stmt->execute()) {
                    $message = "User updated successfully!";
                } else {
                    error_log("Error updating user: " . $stmt->error);
                    $message = "Error updating user. Please try again.";
                }
                $stmt->close();
            } else {
                // Create new user
                $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $email);
                
                if ($stmt->execute()) {
                    $message = "New user created successfully!";
                } else {
                    error_log("Error creating user: " . $stmt->error);
                    $message = "Error creating user. Please try again.";
                }
                $stmt->close();
            }
        }
    }
    
    // Delete user
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "User deleted successfully!";
        } else {
            error_log("Error deleting user: " . $stmt->error);
            $message = "Error deleting user. Please try again.";
        }
        $stmt->close();
    }
    
    $conn->close();
}

// Get user for editing
if (isset($_GET['edit'])) {
    $conn = getConnection();
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
    }
    $stmt->close();
    $conn->close();
}

// Fetch all users
$conn = getConnection();
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP MySQL CRUD Application</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #007bff;
            color: white;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .actions form {
            display: inline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP MySQL CRUD Application</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="save">
            <?php if ($edit_user): ?>
                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['name']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" 
                       required>
            </div>
            
            <button type="submit" class="btn btn-success">
                <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
            </button>
            <?php if ($edit_user): ?>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td class="actions">
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
