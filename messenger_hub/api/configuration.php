<?php
set_time_limit(0);
error_reporting(0);

/* // Test server Credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "whatsapp_messenger"; */

// Live server Credentials
// $servername = "59.92.107.49";
$servername = "localhost";
$username = "admin";
$password = "Password@123";
$dbname = "messenger_hub";

$site_title = "Messenger Hub";
$site_url   = "http://192.168.29.244/messenger_hub/";
$api_url    = "http://localhost:10023";
$full_pathurl = "/var/www/html/messenger_hub/";
$remote_user = 'yeejai-server-6';  // Remote server username
$remote_host = 'http://192.168.29.244';  // Remote server hostname or IP address

// Razorpay TEST Configuration
$rp_keyid	= "rzp_test_3d14kxnIjpcKIz";
$rp_keysecret   = "kSusodeEnSRcjdDLmtZEe0ud";

// Razorpay LIVE Configuration
/* $rp_keyid	   = "rzp_live_pWIs8WdU8DslrS";
$rp_keysecret   = "YZ9n7AxPKNjAifkMZWr0XOOc"; */

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
mysqli_query($conn, "SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
date_default_timezone_set("Asia/Kolkata");

include_once('ajax/site_common_functions.php');

function site_log_generate($log_msg, $location = '')
{
    $max_size = 10485760; // 10 MB

    // Log File Generation with Current URL
    $log_base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
    $log_url = $log_base_url . $_SERVER["REQUEST_URI"]." : IP Address : ".$_SERVER['SERVER_ADDR']." ==> ";

    $log_filename = "site_log";
    if (!file_exists($location."log/".$log_filename)) 
    {
        // create directory/folder uploads.
        mkdir($location."log/".$log_filename, 0777, true);
    }
    $log_file_data1 = $location."log/".$log_filename.'/log_'.date('d-M-Y');
    $log_file_data  = $log_file_data1.'.log';

    clearstatcache();
    $size = filesize($log_file_data);

    // echo "++".$size."++".$max_size."++";
    if($size > $max_size)
    {
        shell_exec("mv ".$log_file_data." ".$log_file_data1."-".date('YmdHis').".log");
    }

    file_put_contents($log_file_data, $log_url.$log_msg . "\n", FILE_APPEND);
}
