<?php
// Database connection parameters
$servername = "localhost";
$username = "admin";
$password = "Password@123";
$dbname = "messenger_hub";

try {
    // Connect to MySQL database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Array of tables to backup
    $tables = ['obd_cdr_1', 'obd_cdr_2', 'obd_cdr_3'];

    // Get yesterday's date
    $yesterday_date = date('d_m_Y', strtotime('-1 day'));

    // Loop through each table and perform the backup
    foreach ($tables as $table) {
        // Check if the table has any records
        $count_sql = "SELECT COUNT(*) FROM $table";
        $stmt = $conn->query($count_sql);
        $record_count = $stmt->fetchColumn();

        if ($record_count > 0) {
            // Create a new table name by appending yesterday's date to the original table name
            $new_table_name = $table . "_" . $yesterday_date;

            // Create a new table with yesterday's date in the name
            $create_table_sql = "CREATE TABLE IF NOT EXISTS $new_table_name LIKE $table";
            $conn->exec($create_table_sql);
            echo "Table $new_table_name created successfully\n";

            // Insert records from the original table to the new table
            $insert_into_table_sql = "INSERT INTO $new_table_name SELECT * FROM $table";
            $conn->exec($insert_into_table_sql);
            echo "Records copied to $new_table_name successfully\n";

            // Instead of creating a new table with the same name as the existing one,
            // you can truncate the table to clear its contents for today.
            $truncate_table_sql = "TRUNCATE TABLE $table";
            $conn->exec($truncate_table_sql);
            echo "Table $table truncated successfully for today's records.\n";
        } else {
            echo "Table $table has no records, skipping backup.\n";
        }
    }

    // Close connection
    $conn = null;
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
