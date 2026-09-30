<?php

require_once "db.php";

/*
|--------------------------------------------------------------------------
| Allow POST requests only
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}


/*
|--------------------------------------------------------------------------
| Collect submitted form data
|--------------------------------------------------------------------------
*/

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$service = trim($_POST["service"] ?? "");
$message = trim($_POST["message"] ?? "");


/*
|--------------------------------------------------------------------------
| Validate required fields
|--------------------------------------------------------------------------
*/

if (
    $name === "" ||
    $email === "" ||
    $phone === "" ||
    $service === "" ||
    $message === ""
) {
    die("Please complete all required fields.");
}


/*
|--------------------------------------------------------------------------
| Validate email address
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}


/*
|--------------------------------------------------------------------------
| Validate phone number
|--------------------------------------------------------------------------
*/

if (!preg_match("/^[0-9+ ]{10,20}$/", $phone)) {
    die("Please enter a valid WhatsApp phone number.");
}


/*
|--------------------------------------------------------------------------
| Save message into the database
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO contact_messages
    (
        name,
        email,
        phone,
        service,
        message
    )
    VALUES (?, ?, ?, ?, ?)
";

$statement = $conn->prepare($sql);

$statement->bind_param(
    "sssss",
    $name,
    $email,
    $phone,
    $service,
    $message
);

$statement->execute();


/*
|--------------------------------------------------------------------------
| Prepare safe name for the success page
|--------------------------------------------------------------------------
*/

$safe_name = htmlspecialchars(
    $name,
    ENT_QUOTES,
    "UTF-8"
);


/*
|--------------------------------------------------------------------------
| Display success page
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Message Sent | Jetech Solutions
    </title>

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
            background: #f1f5f9;
        }

        .success-box {
            width: 100%;
            max-width: 550px;
            padding: 40px;
            text-align: center;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .success-icon {
            margin-bottom: 20px;
            font-size: 55px;
        }

        h1 {
            margin-bottom: 15px;
            color: #0f172a;
        }

        p {
            margin-bottom: 25px;
            color: #64748b;
            line-height: 1.7;
        }

        a {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 8px;
            background: #0284c7;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            background: #0369a1;
        }

    </style>

</head>

<body>

    <div class="success-box">

        <div class="success-icon">
            ✅
        </div>

        <h1>
            Message Received
        </h1>

        <p>
            Thank you, <?= $safe_name ?>.
            Your message has been received.
            Jetech Solutions will contact you soon.
        </p>

        <a href="index.html">
            Return to Website
        </a>

    </div>

</body>

</html>

<?php

/*
|--------------------------------------------------------------------------
| Close database resources
|--------------------------------------------------------------------------
*/

$statement->close();
$conn->close();

?>