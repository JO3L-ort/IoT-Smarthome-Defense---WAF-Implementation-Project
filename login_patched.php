<?php
// dashboard.php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: index_Defense Up.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* === Reset & Base === */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* === Card Container === */
        .login-container {
            width: 90%;
            max-width: 380px;
            padding: 25px 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        /* === Form Control === */
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* === Button === */
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            border: none;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #45a049;
        }

        /* === Responsive Typography & Padding === */
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }
            .login-container h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="validasi.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
