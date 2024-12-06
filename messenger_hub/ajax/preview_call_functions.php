<?php
/*
This page has some functions which is access from Frontend.
This page is act as a Backend page which is connect with Node JS API and PHP Frontend.
It will collect the form details and send it to API.
After get the response from API, send it back to Frontend.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 01-Jul-2023
*/
session_start(); //start session
error_reporting(0); // The error reporting function
include_once('../api/configuration.php'); // Include configuration.php
include_once('site_common_functions.php');
extract($_REQUEST); // Extract the request
$bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].''; // To get bearertoken
$current_date = date("Y-m-d H:i:s"); // To get currentdate function
$milliseconds = round(microtime(true) * 1000);

// Compose Sms Preview Page - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $preview_functions == "preview_compose_sms" ) {
 $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  $txt_message_content     = htmlspecialchars(strip_tags(isset($_REQUEST['textarea']) ? $_REQUEST['textarea'] : ""));
 // To get the one by one data
 $rdo_newex_group		  = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
 $file_image_header_url		  = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));
 if($txt_list_mobno != '') {
  // Explode
  $str_arr = explode (",", $txt_list_mobno); 
  $entry_contact = '';
  for($indicatori = 0; $indicatori < count($str_arr); $indicatori++) {
    $entry_contact .= ''.$str_arr[$indicatori].',';
  }
  $entry_contact = rtrim($entry_contact, ", ");

}
$message_type = '';
if($rdo_newex_group == 'N'){
 $message_type = "Same message";
}else{
  $message_type = "Customized message";
}

if($_FILES["upload_contact"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $group_contact = $site_url . "uploads/group_contact/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);

  /* Valid extensions */
  $valid_extensions = array("csv");
  $response = 0;
  /* Check file extension */
  if (in_array(strtolower($imageFileType), $valid_extensions)) {
    /* Upload file */
    if (move_uploaded_file($_FILES['upload_contact']['tmp_name'], $location)) {
      $response = $location;
    }
  }
}


if ($_FILES["file_image_header"]["name"] != '') {
  /* Location */
  $msg_type = 'IMAGE';

  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
  $location = "../uploads/whatsapp_images/" . $filename;
$location_1 = $site_url . "uploads/whatsapp_images/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  /* Valid extensions */
  $valid_extensions = array("png", "jpg", "jpeg");

  $rspns = '';
  /* Check file extension */
  // if (in_array(strtolower($imageFileType), $valid_extensions)) {
  /* Upload file */
  if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $location)) {
    $rspns = $location;
    site_log_generate("Create Template Page : User : " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
  }
}
if($file_image_header_url){
  $location_1 = $file_image_header_url;
}	

  ?>
	<table class="table table-striped table-bordered m-0" style="table-layout: fixed; white-space: inherit; width: 100%; overflow-x: scroll;">
				<tbody>
          <? if($message_type != '' ) { ?>
						<tr>
								<th scope="row">Message Type</th>
								<td style="white-space: inherit !important;"><?=$message_type?></td>
						</tr>
					<? } ?>
					<? if($txt_message_content != '') { ?>
						<tr>
								<th scope="row">Message Content</th>
								<td style="white-space: inherit !important;"><?=$txt_message_content?></td>
						</tr>
					<? } ?>
          <? if($location_1 != '' ) { ?>
						<tr>
								<th scope="row">Upload Media</th>
								<td style="white-space: inherit !important;"><a href= "<?=$location_1?>"  target='_blank'>Media Link</a></td>
						</tr>
					<? } ?>
          <? if($group_contact != '') { ?>
						<tr>
								<th scope="row">Upload Mobile Numbers</th>
								<td style="white-space: inherit !important;"><a href= "<?=$group_contact?>"  target='_blank'>Download Mobile Numbers</a></td>
						</tr>
					<? } ?>
				</tbody>
		</table>
	<? 
  }

 //Compose Sms Preview Page - END

// Compose whatsapp Preview Page - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $preview_functions == "preview_compose_whatsapp" ) {
  $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  $txt_message_content     = htmlspecialchars(strip_tags(isset($_REQUEST['textarea']) ? $_REQUEST['textarea'] : ""));
 // To get the one by one data
 $rdo_newex_group		  = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
 $file_image_header_url		  = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));
 if($txt_list_mobno != '') {
  // Explode
  $str_arr = explode (",", $txt_list_mobno); 
  $entry_contact = '';
  for($indicatori = 0; $indicatori < count($str_arr); $indicatori++) {
    $entry_contact .= ''.$str_arr[$indicatori].',';
  }
  $entry_contact = rtrim($entry_contact, ", ");

}
$message_type = '';
if($rdo_newex_group == 'N'){
 $message_type = "Same message";
}else{
  $message_type = "Customized message";
}

if($_FILES["upload_contact"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $group_contact = $site_url . "uploads/group_contact/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);

  /* Valid extensions */
  $valid_extensions = array("csv");
  $response = 0;
  /* Check file extension */
  if (in_array(strtolower($imageFileType), $valid_extensions)) {
    /* Upload file */
    if (move_uploaded_file($_FILES['upload_contact']['tmp_name'], $location)) {
      $response = $location;
    }
  }
}


if ($_FILES["file_image_header"]["name"] != '') {
  /* Location */
  $msg_type = 'IMAGE';

  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
  $location = "../uploads/whatsapp_images/" . $filename;
$location_1 = $site_url . "uploads/whatsapp_images/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  /* Valid extensions */
  $valid_extensions = array("png", "jpg", "jpeg");

  $rspns = '';
  /* Check file extension */
  // if (in_array(strtolower($imageFileType), $valid_extensions)) {
  /* Upload file */
  if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $location)) {
    $rspns = $location;
    site_log_generate("Create Template Page : User : " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
  }
}
if($file_image_header_url){
  $location_1 = $file_image_header_url;
}	

  ?>
	<table class="table table-striped table-bordered m-0" style="table-layout: fixed; white-space: inherit; width: 100%; overflow-x: scroll;">
				<tbody>
          <? if($message_type != '' ) { ?>
						<tr>
								<th scope="row">Message Type</th>
								<td style="white-space: inherit !important;"><?=$message_type?></td>
						</tr>
					<? } ?>
					<? if($txt_message_content != '') { ?>
						<tr>
								<th scope="row">Message Content</th>
								<td style="white-space: inherit !important;"><?=$txt_message_content?></td>
						</tr>
					<? } ?>
          <? if($location_1 != '' ) { ?>
						<tr>
								<th scope="row">Upload Media</th>
								<td style="white-space: inherit !important;"><a href= "<?=$location_1?>"  target='_blank'>Media Link</a></td>
						</tr>
					<? } ?>
          <? if($group_contact != '') { ?>
						<tr>
								<th scope="row">Upload Mobile Numbers</th>
								<td style="white-space: inherit !important;"><a href= "<?=$group_contact?>"  target='_blank'>Download Mobile Numbers</a></td>
						</tr>
					<? } ?>
				</tbody>
		</table>
	<? 
  
}
 //Compose Whatsapp Preview Page - END

// Compose RCS Preview Page - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $preview_functions == "preview_compose_rcs" ) {
  $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  $txt_message_content     = htmlspecialchars(strip_tags(isset($_REQUEST['textarea']) ? $_REQUEST['textarea'] : ""));
 // To get the one by one data
 $rdo_newex_group		  = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
 $file_image_header_url		  = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));
 if($txt_list_mobno != '') {
  // Explode
  $str_arr = explode (",", $txt_list_mobno); 
  $entry_contact = '';
  for($indicatori = 0; $indicatori < count($str_arr); $indicatori++) {
    $entry_contact .= ''.$str_arr[$indicatori].',';
  }
  $entry_contact = rtrim($entry_contact, ", ");

}
$message_type = '';
if($rdo_newex_group == 'N'){
 $message_type = "Same message";
}else{
  $message_type = "Customized message";
}

if($_FILES["upload_contact"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $group_contact = $site_url . "uploads/group_contact/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);

  /* Valid extensions */
  $valid_extensions = array("csv");
  $response = 0;
  /* Check file extension */
  if (in_array(strtolower($imageFileType), $valid_extensions)) {
    /* Upload file */
    if (move_uploaded_file($_FILES['upload_contact']['tmp_name'], $location)) {
      $response = $location;
    }
  }
}


if ($_FILES["file_image_header"]["name"] != '') {
  /* Location */
  $msg_type = 'IMAGE';

  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
  $location = "../uploads/whatsapp_images/" . $filename;
$location_1 = $site_url . "uploads/whatsapp_images/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  /* Valid extensions */
  $valid_extensions = array("png", "jpg", "jpeg");

  $rspns = '';
  /* Check file extension */
  // if (in_array(strtolower($imageFileType), $valid_extensions)) {
  /* Upload file */
  if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $location)) {
    $rspns = $location;
    site_log_generate("Create Template Page : User : " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
  }
}
if($file_image_header_url){
  $location_1 = $file_image_header_url;
}	

  ?>
	<table class="table table-striped table-bordered m-0" style="table-layout: fixed; white-space: inherit; width: 100%; overflow-x: scroll;">
				<tbody>
          <? if($message_type != '' ) { ?>
						<tr>
								<th scope="row">Message Type</th>
								<td style="white-space: inherit !important;"><?=$message_type?></td>
						</tr>
					<? } ?>
					<? if($txt_message_content != '') { ?>
						<tr>
								<th scope="row">Message Content</th>
								<td style="white-space: inherit !important;"><?=$txt_message_content?></td>
						</tr>
					<? } ?>
          <? if($location_1 != '' ) { ?>
						<tr>
								<th scope="row">Upload Media</th>
								<td style="white-space: inherit !important;"><a href= "<?=$location_1?>"  target='_blank'>Media Link</a></td>
						</tr>
					<? } ?>
          <? if($group_contact != '') { ?>
						<tr>
								<th scope="row">Upload Mobile Numbers</th>
								<td style="white-space: inherit !important;"><a href= "<?=$group_contact?>"  target='_blank'>Download Mobile Numbers</a></td>
						</tr>
					<? } ?>
				</tbody>
		</table>
	<? 
}
 //Compose RCS Preview Page - END


// Compose OBD Preview Page - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $preview_functions == "preview_compose_obd" ) {

  $call_retry_count = htmlspecialchars(strip_tags(isset($_REQUEST['call_retry_count']) ? $_REQUEST['call_retry_count'] : ""));
 // To get the one by one data
 $rdo_newex_group	= htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));

 $retry_time = htmlspecialchars(strip_tags(isset($_REQUEST['retry_time']) ? $_REQUEST['retry_time'] : ""));
 $slt_context = htmlspecialchars(strip_tags(isset($_REQUEST['slt_context']) ? $_REQUEST['slt_context'] : ""));
 
 $slt_context = explode("~~", $slt_context);
  $slt_context = $slt_context[1];

$message_type = '';
if($rdo_newex_group == 'G'){
 $message_type = "Generic";
}else if($rdo_newex_group == 'C'){
  $message_type = "Customized";
}else if($rdo_newex_group == 'P'){
  $message_type = "Personalized";
}

if($_FILES["upload_contact"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $group_contact = $site_url . "uploads/group_contact/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);

  /* Valid extensions */
  $valid_extensions = array("csv");
  $response = 0;
  /* Check file extension */
  if (in_array(strtolower($imageFileType), $valid_extensions)) {
    /* Upload file */
    if (move_uploaded_file($_FILES['upload_contact']['tmp_name'], $location)) {
      $response = $location;
    }
  }
}

  ?>
	<table class="table table-striped table-bordered m-0" style="table-layout: fixed; white-space: inherit; width: 100%; overflow-x: scroll;">
				<tbody>
          <? if($message_type != '' ) { ?>
						<tr>
								<th scope="row">Campaign Type</th>
								<td style="white-space: inherit !important;"><?=$message_type?></td>
						</tr>
					<? } ?>
					<? if($call_retry_count != '') { ?>
						<tr>
								<th scope="row">Call Retry Count </th>
								<td style="white-space: inherit !important;"><?=$call_retry_count?></td>
						</tr>
					<? } ?>
          <? if($retry_time != '' ) { ?>
						<tr>
								<th scope="row">Retry Time Interval</th>
                <td style="white-space: inherit !important;"><?=$retry_time?></td>
						</tr>
					<? } ?>
          <? if($slt_context != '' ) { ?>
						<tr>
								<th scope="row">Context</th>
                <td style="white-space: inherit !important;"><?=$slt_context?></td>
						</tr>
					<? } ?>
          <? if($group_contact != '') { ?>
						<tr>
								<th scope="row">Upload Mobile Numbers</th>
								<td style="white-space: inherit !important;"><a href= "<?=$group_contact?>"  target='_blank'>Download Mobile Numbers</a></td>
						</tr>
					<? } ?>
				</tbody>
		</table>
	<? 
}
 //Compose OBD Preview Page - END

// compose prompt preview funtion - start 
if ($_SERVER['REQUEST_METHOD'] == "POST" and $preview_functions == "preview_compose_prompt" ) {

 $rdo_newex_group	= htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
$language_code = htmlspecialchars(strip_tags(isset($_REQUEST['language_code']) ? $conn->real_escape_string($_REQUEST['language_code']) : ""));
$location_state = htmlspecialchars(strip_tags(isset($_REQUEST['location']) ? $_REQUEST['location'] : ""));
$type = htmlspecialchars(strip_tags(isset($_REQUEST['type']) ? $_REQUEST['type'] : ""));
$upload_prompt = htmlspecialchars(strip_tags(isset($_REQUEST['upload_prompt']) ? $_REQUEST['upload_prompt'] : ""));
$company_name = htmlspecialchars(strip_tags(isset($_REQUEST['company_name']) ? $_REQUEST['company_name'] : ""));
$context = htmlspecialchars(strip_tags(isset($_REQUEST['context_value']) ? $_REQUEST['context_value'] : ""));
$prompt_remarks = htmlspecialchars(strip_tags(isset($_REQUEST['prompt_remarks']) ? $_REQUEST['prompt_remarks'] : ""));

$message_type = '';
if($rdo_newex_group == 'G'){
 $message_type = "Generic message";
}else if($rdo_newex_group == 'C'){
  $message_type = "Customized URL";
}else if($rdo_newex_group == 'P'){
  $message_type = "Personalized Name";
}

if($_FILES["upload_prompt"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_prompt"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $SESSION['yjwatsp_user_id'] . "_csv" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $group_contact = $site_url . "uploads/group_contact/" . $filename;
  $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);

  /* Valid extensions */
  // $valid_extensions = array("csv");
  $response = 0;
  /* Check file extension */
  if (strtolower($imageFileType)) {
    /* Upload file */
    if (move_uploaded_file($_FILES['upload_prompt']['tmp_name'], $location)) {
      $response = $location;
    }
  }
}

  ?>
	<table class="table table-striped table-bordered m-0" style="table-layout: fixed; white-space: inherit; width: 100%; overflow-x: scroll;">
				<tbody>
          <? if($message_type != '' ) { ?>
						<tr>
								<th scope="row">Message Type</th>
								<td style="white-space: inherit !important;"><?=$message_type?></td>
						</tr>
					<? } ?>   
          <? if($group_contact != '') { ?>
						<tr>
								<th scope="row">Upload Prompt</th>
								<td style="white-space: inherit !important;"><a href= "<?=$group_contact?>"  target='_blank'>Download Prompt</a></td>
						</tr>
					<? } ?>
          <? if($company_name != '') { ?>
						<tr>
								<th scope="row"> Company Name </th>
								<td style="white-space: inherit !important;"><?=$company_name?></td>
						</tr>
					<? } ?>

					<? if($location_state != '') { ?>
						<tr>
								<th scope="row"> Location </th>
								<td style="white-space: inherit !important;"><?=$location_state?></td>
						</tr>
					<? } ?>
          <? if($language_code != '') { ?>
						<tr>
								<th scope="row"> Language </th>
								<td style="white-space: inherit !important;"><?=$language_code?></td>
						</tr>
					<? } ?>

          <? if($type != '') { ?>
						<tr>
								<th scope="row"> Type </th>
								<td style="white-space: inherit !important;"><?=$type?></td>
						</tr>
					<? } ?>

          
          <? if($context != '' ) { ?>
						<tr>
								<th scope="row">Context</th>
                <td style="white-space: inherit !important;"><?=$context?></td>
						</tr>
					<? } ?>
          <? if($prompt_remarks != '' ) { ?>
						<tr>
								<th scope="row">Remarks</th>
                <td style="white-space: inherit !important;"><?=$prompt_remarks?></td>
						</tr>
					<? } ?>
         
				</tbody>
		</table>
	<? 
}
// compose prompt preview funtion - end

// Finally Close all Opened Mysql DB Connection
$conn->close();

// Output header with HTML Response
header('Content-type: text/html');
echo $result_value;
