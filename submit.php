<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$servername = "sql300.infinityfree.com";
$username = "if0_39879490";
$password = "dg5Jgda752Wgyg";
$dbname = "if0_39879490_bookmysaloon";

// Telegram configuration
$botToken = "8253002661:AAHdj8G21qOm4FwMB5GDgCC-N6uBN8BoX_g";
$chatId = "908760319";  


// File upload configuration
$targetDir = "uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$date = $_POST['date'];
$time = $_POST['time'];
$services = isset($_POST['service']) ? implode(", ", $_POST['service']) : "";

// Handle file upload
if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
    $filename = time() . "_" . basename($_FILES['payment_screenshot']['name']);
    $targetFile = $targetDir . $filename;
    if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $targetFile)) {
        $screenshot = $filename;
    } else {
        die("Error uploading file.");
    }
} else {
    die("Payment screenshot is required.");
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO bookings (name, email, phone, services, date, time, payment_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $name, $email, $phone, $services, $date, $time, $screenshot);

if ($stmt->execute()) {
    echo "Booking successful!";
} else {
    echo "Error: " . $conn->error;
}

// Send Telegram message
$message = "📅 *New Booking Received!*\n\n".
           "👤 Name: $name\n".
           "✉ Email: $email\n".
           "📱 Phone: $phone\n".
           "🛠 Services: $services\n".
           "📅 Date: $date\n".
           "⏰ Time: $time\n";

$telegram_url = "https://api.telegram.org/bot$botToken/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'Markdown'
];
$options = [
    "http" => [
        "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
        "method" => "POST",
        "content" => http_build_query($data),
    ]
];
$context = stream_context_create($options);


// Debugging
$response = @file_get_contents($telegram_url, false, $context);
if ($response === FALSE) {
    echo "<br>❌ Telegram request failed!<br>";
    $error = error_get_last();
    echo "<pre>";
    print_r($error);
    echo "</pre>";
} else {
    echo "<br>✅ Telegram request sent successfully!<br>";
    echo "<pre>$response</pre>";
}

// Close connection
$stmt->close();
$conn->close();
?>

