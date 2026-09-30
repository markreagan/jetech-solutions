<?php

session_start();

if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    /*
    Temporary local testing credentials.
    We will improve this later using a database.
    */

    $correct_username = "admin";
    $correct_password = "Jetech@123";

    if (
        $username === $correct_username &&
        $password === $correct_password
    ) {
        $_SESSION["admin_logged_in"] = true;

        header("Location: admin.php");
        exit;
    } else {
        $error = "Incorrect username or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jetech Admin Login</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0284c7, #0f172a);
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
        }

        h1 {
            color: #0f172a;
            margin-bottom: 10px;
        }

        p {
            color: #64748b;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 18px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 16px;
        }

        input:focus {
            outline: 2px solid #0284c7;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #0284c7;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #0369a1;
        }

        .error {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #0284c7;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="login-box">

        <h1>Admin Login</h1>

        <p>Sign in to manage Jetech Solutions messages.</p>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin-login.php">

            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">
                Login
            </button>

        </form>

        <a class="back-link" href="index.html">
            Back to Website
        </a>

    </div>

</body>

</html>