<?php
// Database connection parameters
$servername = "192.168.29.244";
$username = "admin";
$password = "Admin@123";
$dbname = "messenger_hub";

try {
    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Variables for table names and queries
    /*$yesterday_date = date('d_m_Y', strtotime('-1 day'));
    $new_table_name = "obd_cdr_1_" . $yesterday_date;*/
     $today_date = date('d_m_Y');  // Get today's date in the format dd_mm_yyyy
     $new_table_name = "obd_cdr_1_" . $today_date;  // Create the table name with today's date


    // Create a new table with yesterday's date in the name
    $create_table_sql = "CREATE TABLE IF NOT EXISTS $new_table_name LIKE obd_cdr_1";
    $conn->exec($create_table_sql);
    echo "Table $new_table_name created successfully\n";

    // Insert records from obd_cdr_1 to the new table
    $insert_into_table_sql = "INSERT INTO $new_table_name SELECT * FROM obd_cdr_1";
    $conn->exec($insert_into_table_sql);
    echo "Records copied to $new_table_name successfully\n";

    // Optionally: Truncate the original obd_cdr_1 table
     $truncate_table_sql = "TRUNCATE TABLE obd_cdr_1";
     $conn->exec($truncate_table_sql);
     echo "Original table obd_cdr_1 truncated successfully\n";

    // Close connection
    $conn = null;
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>


