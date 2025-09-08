<?php
// Database credentials (use environment variables in Render)
$dbhost = getenv('DB_HOST'); // Render PostgreSQL hostname
$dbport = getenv('DB_PORT') ?: 5432;
$dbname = getenv('DB_NAME'); // Render database name
$dbuser = getenv('DB_USER'); // Render username
$dbpass = getenv('DB_PASSWORD'); // Render password

$conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass");

if (!$conn) {
    die("Database connection failed!");
}

$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    services TEXT,
    date DATE,
    time TIME,
    payment_screenshot TEXT
);";

$result = pg_query($conn, $sql);

if ($result) {
    echo "Table created successfully!";
} else {
    echo "Error creating table: " . pg_last_error($conn);
}

pg_close($conn);
?>

