<?php

session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.php");
    exit;
}

require_once "db.php";

$delete_message = "";

/*
|--------------------------------------------------------------------------
| Delete message
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $message_id = filter_input(
        INPUT_POST,
        "message_id",
        FILTER_VALIDATE_INT
    );

    if ($message_id) {

        $delete_sql = "
            DELETE FROM contact_messages
            WHERE id = ?
        ";

        $delete_statement = $conn->prepare($delete_sql);

        $delete_statement->bind_param(
            "i",
            $message_id
        );

        $delete_statement->execute();

        if ($delete_statement->affected_rows > 0) {
            $delete_message = "Message deleted successfully.";
        } else {
            $delete_message = "Message was not found.";
        }

        $delete_statement->close();

    } else {
        $delete_message = "Invalid message ID.";
    }
}

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$total_result = $conn->query("
    SELECT COUNT(*) AS total_messages
    FROM contact_messages
");

$total_messages = $total_result->fetch_assoc()["total_messages"];


$today_result = $conn->query("
    SELECT COUNT(*) AS today_messages
    FROM contact_messages
    WHERE DATE(created_at) = CURDATE()
");

$today_messages = $today_result->fetch_assoc()["today_messages"];


$service_result = $conn->query("
    SELECT service, COUNT(*) AS service_count
    FROM contact_messages
    GROUP BY service
    ORDER BY service_count DESC
    LIMIT 1
");

$most_requested_service = "No data yet";

if ($service_result && $service_result->num_rows > 0) {

    $service_row = $service_result->fetch_assoc();

    $most_requested_service =
        $service_row["service"] .
        " (" .
        $service_row["service_count"] .
        ")";
}

/*
|--------------------------------------------------------------------------
| Retrieve messages
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT id, name, email, phone, service, message, created_at
    FROM contact_messages
    ORDER BY created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Jetech Solutions | Admin Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 30px;
        }

        .dashboard {
            width: 100%;
            max-width: 1350px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            background: #0f172a;
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        .header p {
            color: #cbd5e1;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .website-link,
        .logout {
            display: inline-block;
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            white-space: nowrap;
            font-weight: bold;
        }

        .website-link {
            background: #0284c7;
        }

        .website-link:hover {
            background: #0369a1;
        }

        .logout {
            background: #dc2626;
        }

        .logout:hover {
            background: #b91c1c;
        }

        .notice {
            margin-bottom: 20px;
            padding: 14px 18px;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .number {
            color: #0284c7;
            font-size: 34px;
            font-weight: bold;
        }

        .service-name {
            color: #0284c7;
            font-size: 20px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #0284c7;
            color: white;
            white-space: nowrap;
        }

        tr:hover {
            background: #f8fafc;
        }

        .message {
            max-width: 300px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .phone {
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .reply-button,
        .whatsapp-button,
        .delete-button {
            display: inline-block;
            min-width: 125px;
            padding: 9px 12px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .reply-button {
            background: #16a34a;
            color: white;
            text-decoration: none;
        }

        .reply-button:hover {
            background: #15803d;
        }

        .whatsapp-button {
            background: #25d366;
            color: white;
            text-decoration: none;
        }

        .whatsapp-button:hover {
            background: #128c7e;
        }

        .delete-button {
            border: none;
            background: #dc2626;
            color: white;
            cursor: pointer;
        }

        .delete-button:hover {
            background: #b91c1c;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 850px) {

            body {
                padding: 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 24px;
            }

        }

    </style>

</head>

<body>

    <main class="dashboard">

        <section class="header">

            <div>
                <h1>Jetech Solutions Admin Dashboard</h1>

                <p>
                    Monitor and manage customer enquiries.
                </p>
            </div>

            <div class="header-actions">

                <a
                    class="website-link"
                    href="index.html"
                >
                    Website
                </a>

                <a
                    class="logout"
                    href="admin-logout.php"
                >
                    Logout
                </a>

            </div>

        </section>

        <?php if ($delete_message !== ""): ?>

            <div class="notice">
                <?php echo htmlspecialchars($delete_message); ?>
            </div>

        <?php endif; ?>

        <section class="stats">

            <div class="stat-card">

                <h3>Total Messages</h3>

                <div class="number">
                    <?php echo htmlspecialchars($total_messages); ?>
                </div>

            </div>

            <div class="stat-card">

                <h3>Messages Today</h3>

                <div class="number">
                    <?php echo htmlspecialchars($today_messages); ?>
                </div>

            </div>

            <div class="stat-card">

                <h3>Most Requested Service</h3>

                <div class="service-name">
                    <?php echo htmlspecialchars($most_requested_service); ?>
                </div>

            </div>

        </section>

        <section class="table-container">

            <?php if ($result && $result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service</th>
                            <th>Message</th>
                            <th>Date Received</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php

                            $whatsapp_number = preg_replace(
                                "/[^0-9]/",
                                "",
                                $row["phone"]
                            );

                            $whatsapp_message = rawurlencode(
                                "Hello " .
                                $row["name"] .
                                ",\n\n" .
                                "Thank you for contacting Jetech Solutions regarding " .
                                $row["service"] .
                                ".\n\n" .
                                "We have received your enquiry and will assist you shortly."
                            );

                            $whatsapp_link =
                                "https://wa.me/" .
                                $whatsapp_number .
                                "?text=" .
                                $whatsapp_message;

                            $gmail_link =
                                "https://mail.google.com/mail/?view=cm&fs=1" .
                                "&to=" .
                                rawurlencode($row["email"]) .
                                "&su=" .
                                rawurlencode(
                                    "Re: Your enquiry to Jetech Solutions"
                                ) .
                                "&body=" .
                                rawurlencode(
                                    "Hello " .
                                    $row["name"] .
                                    ",\n\n" .
                                    "Thank you for contacting Jetech Solutions.\n\n"
                                );

                            ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row["id"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["email"]); ?>
                                </td>

                                <td class="phone">
                                    <?php echo htmlspecialchars($row["phone"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["service"]); ?>
                                </td>

                                <td class="message">
                                    <?php echo htmlspecialchars($row["message"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["created_at"]); ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            class="whatsapp-button"
                                            href="<?php echo htmlspecialchars($whatsapp_link, ENT_QUOTES, "UTF-8"); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            WhatsApp
                                        </a>

                                        <a
                                            class="reply-button"
                                            href="<?php echo htmlspecialchars($gmail_link, ENT_QUOTES, "UTF-8"); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Reply by Email
                                        </a>

                                        <form
                                            method="POST"
                                            action="admin.php"
                                            onsubmit="return confirm('Delete this message permanently?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="message_id"
                                                value="<?php echo htmlspecialchars($row["id"]); ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-button"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">
                    No customer messages have been received yet.
                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>