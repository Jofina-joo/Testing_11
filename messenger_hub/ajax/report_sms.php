<?php
session_start();
error_reporting(0);
// Include configuration.php
include_once('../api/configuration.php');
include_once('site_common_functions.php');
extract($_REQUEST);

$current_date = date("Y-m-d H:i:s");
$milliseconds = round(microtime(true) * 1000);

$compose_id		= htmlspecialchars(strip_tags(isset($_REQUEST['srch1']) ? $conn->real_escape_string($_REQUEST['srch1']) : ""));
  $upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $conn->real_escape_string($_REQUEST['upload_contact']) : ""));
 $campaign_value = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_value']) ? $conn->real_escape_string($_REQUEST['campaign_value']) : ""));
 $ex_campaign = explode("&",$compose_id);

$campaign = str_replace('amp;', '', $ex_campaign[1]);
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999); 
  if($upload_contact){
    $replace_txt = '{
      "selected_user_id":"'.$campaign.'",
      "compose_message_id":"'.$ex_campaign[0].'",
      "receiver_number":"'.$upload_contact.'",
"request_id":"'.$request_id.'"
    }';
  }
  else{
  $replace_txt = '{
 "selected_user_id":"'.$campaign.'",
    "compose_message_id":"'.$ex_campaign[0].'",
"request_id":"'.$request_id.'"
  }';
}
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/report/report_generation_sms',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => array(
      $bearer_token,
      'Content-Type: application/json'
    ),
  ));
  site_log_generate("Add Contacts in Group Delete Sender ID Page : ".$_SESSION['yjwatsp_user_name']." Execute the service [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == ''){?>
    <script>window.location="logout"</script>
  <? }  
  $header = json_decode($response, false);
  site_log_generate("Add Contacts in Group Delete Sender ID Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');
  
  if ($header->response_status == 403) { ?>
    <script>window.location="logout"</script>
  <? } 

  if ($header->response_status == 200) {
    $json = array("status" => 1, "msg" => "Success");
  } else {
    $json = array("status" => 0, "msg" => "Failed. ".$header->response_msg);
  }

  $conn->close();

// Output header with JSON Response
header('Content-type: application/json');
echo json_encode($json);
