<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
echo "madhu";
  $input = json_decode(file_get_contents('php://input'), true);
  $campaign_id = $input['campaign_id'];

  // Construct the SCP command
  $scp_command = sprintf(
    "scp 'yeejai-server-6@yeejai.in:/var/www/html/messenger_hub/uploads/obd_call_report_csv/%s' '/var/www/html/messenger_hub/uploads/obd_call_report_csv/' 2>&1",
    $campaign_id
  );

  $output = [];
  $return_var = 0;
  exec($scp_command, $output, $return_var);

  if ($return_var === 0) {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'IVR Approved successfully']);
  } else {
    $error_message = sprintf(
      "SCP command failed\nCommand: %s\nOutput: %s\nReturn Code: %d",
      $scp_command,
      implode("\n", $output),
      $return_var
    );

    error_log($error_message);
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'File transfer failed', 'details' => $output]);
  }
  exit;
}
?>

