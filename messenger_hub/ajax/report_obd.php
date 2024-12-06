<?php
session_start();
error_reporting(0);

// Include configuration.php and site_common_functions.php
include_once('../api/configuration.php');
include_once('site_common_functions.php');

// Validate and sanitize inputs
$campaign_id = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_id']) ? $conn->real_escape_string($_REQUEST['campaign_id']) : ""));
if (!$campaign_id) {
    die(json_encode(["status" => 0, "msg" => "Invalid campaign_id"]));
}

$campaign_id = explode("&", $campaign_id);
$user_id = str_replace('amp;', '', $campaign_id[1]);

if (!isset($_SESSION['yjwatsp_bearer_token']) || !isset($_SESSION['yjwatsp_user_id']) || !isset($_SESSION['yjwatsp_user_name'])) {
    die(json_encode(["status" => 0, "msg" => "Session variables not set"]));
}

$bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'];
$request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . date('z', strtotime(date("d-m-Y"))) . date("His") . "_" . rand(1000, 9999);

$replace_txt = json_encode([
    "selected_user_id" => $user_id,
    "campaign_id" => $campaign_id[0],
    "request_id" => $request_id
]);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $api_url . '/report/report_generation_obd',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => [
        $bearer_token,
        'Content-Type: application/json'
    ],
]);

site_log_generate("Report Generate Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date('Y-m-d H:i:s'), '../');
$response = curl_exec($curl);
curl_close($curl);

if (!$response) {
    echo '<script>window.location = "logout"</script>';
    exit;
}

$header = json_decode($response, true);

if ($header && $header['response_status'] == 200) {
    // Report generation successful, proceed to create CSV and ZIP
    $reportData = $header['report'];

    // Example CSV creation (uncomment and adjust as needed)
    /*
    $csvFileName = $reportData[0]['cdr_campaign_name'] . '.csv';
    $csvFilePath = '/var/www/html/messenger_hub/uploads/obd_call_report_csv/' . $csvFileName;

    $csvFile = fopen($csvFilePath, 'w');
    if ($csvFile === false) {
        die(json_encode(["status" => 0, "msg" => "Error: Unable to create CSV file."]));
    }

    fputcsv($csvFile, ['No', 'Campaign Name', 'Receiver Mobile No', 'Sender Mobile No', 'Call Status', 'Retry Count', 'Call Duration (In Secs)', 'Context', 'Call Time', 'Answered Time', 'End Time']);
    
    foreach ($reportData as $index => $row) {
        fputcsv($csvFile, [
            $index + 1,
            $row['cdr_campaign_name'],
            $row['dst'],
            $row['src'],
            $row['disposition'],
            $row['retry_count'],
            $row['billsec'] ?? '',
            $row['cdr_context'],
            date('d-m-Y h:i:s A', strtotime($row['calldate'])),
            date('d-m-Y h:i:s A', strtotime($row['answerdate'])),
            date('d-m-Y h:i:s A', strtotime($row['hangupdate'])),
        ]);
    }
    fclose($csvFile);
    */

    // Example ZIP creation (uncomment and adjust as needed)
    /*
    $zipFileName = $reportData[0]['cdr_campaign_name'] . '.zip';
    $zipFilePath = '/var/www/html/messenger_hub/uploads/obd_call_report_csv/' . $zipFileName;

    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE) !== true) {
        die(json_encode(["status" => 0, "msg" => "Error: Unable to create ZIP file."]));
    }
    if (!$zip->addFile($csvFilePath, basename($csvFilePath))) {
        die(json_encode(["status" => 0, "msg" => "Error: Unable to add CSV file to ZIP archive."]));
    }
    if ($zip->close() === false) {
        die(json_encode(["status" => 0, "msg" => "Error: Unable to finalize ZIP file."]));
    }

    // Download the ZIP file
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=' . $zipFileName);
    header('Content-Length: ' . filesize($zipFilePath));
    readfile($zipFilePath);
    */

    echo json_encode(["status" => 1, "msg" => "Report Generation Completed!"]);
} else {
    // Handle API response error
    site_log_generate("Report Generate Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date('Y-m-d H:i:s'), '../');
    echo json_encode(["status" => 0, "msg" => "Report Generation Failed"]);
}

$conn->close();
?>

