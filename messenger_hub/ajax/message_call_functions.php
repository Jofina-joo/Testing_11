<?php
session_start();
error_reporting(0);
// Include configuration.php
include_once('../api/configuration.php');
include_once('site_common_functions.php');
extract($_REQUEST);

$current_date = date("Y-m-d H:i:s");
$milliseconds = round(microtime(true) * 1000);
// Add Contacts in Group Page validateMobno - Start
if(isset($_POST['validateMobno']) == "validateMobno") {
  $mobno = str_replace('"', '', htmlspecialchars(strip_tags(isset($_POST['mobno']) ? $conn->real_escape_string($_POST['mobno']) : "")));
  $dup = htmlspecialchars(strip_tags(isset($_POST['dup']) ? $conn->real_escape_string($_POST['dup']) : ""));
  $inv = htmlspecialchars(strip_tags(isset($_POST['inv']) ? $conn->real_escape_string($_POST['inv']) : ""));

  $mobno = str_replace('\n', ',', $mobno);
  $newline = explode('\n', $mobno);

  $correct_mobno_data = [];
  $return_mobno_data = '';
  $issu_mob = '';
  $cnt_vld_no = 0;
  //$max_vld_no = 1000;
  $max_vld_no = 1000000;
  for($i = 0; $i < count($newline); $i++) {
    $expl = explode(",", $newline[$i]);
    for($ij = 0; $ij < count($expl); $ij++) {
      // echo "==".$inv."==".$expl[$ij]."==".$newline[$i]."==<br>";
      if($inv == 1) {
        $vlno = validate_phone_number($expl[$ij]);
      } else {
        $vlno = $newline[$i];
      }

      if($vlno == true) {
        if($dup == 1) {
          if(!in_array($expl[$ij], $correct_mobno_data)) {
            if($expl[$ij] != '') {
              $cnt_vld_no++;
              if($cnt_vld_no <= $max_vld_no) {
                $correct_mobno_data[] = $expl[$ij];
                $return_mobno_data .= $expl[$ij].",\n";
              } else {
                $issu_mob .= $expl[$ij].",";
              }
            } else {
              $issu_mob .= $expl[$ij].",";
            }
          } else {
            $issu_mob .= $expl[$ij].",";
          }
        } else {
          if($expl[$ij] != '') {
            $cnt_vld_no++;
            if($cnt_vld_no <= $max_vld_no) {
              $correct_mobno_data[] = $expl[$ij];
              $return_mobno_data .= $expl[$ij].",\n";
            } else {
              $issu_mob .= $expl[$ij].", ";
            }
          } else {
            $issu_mob .= $expl[$ij].", ";
          }
        }
      } else {
        $issu_mob .= $expl[$ij].",";
      }
    }
  }

  $return_mobno_data = rtrim($return_mobno_data, ",\n");
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." validated Mobile Nos ($return_mobno_data||$issu_mob) on ".date("Y-m-d H:i:s"), '../');
  $json = array("status" => 1, "msg" => $return_mobno_data."||".$issu_mob);
}

if(isset($_POST['validateMobno_contact']) == "validateMobno_contact") {
  $mobno = str_replace('"', '', htmlspecialchars(strip_tags(isset($_POST['mobno']) ? $conn->real_escape_string($_POST['mobno']) : "")));
  $dup = htmlspecialchars(strip_tags(isset($_POST['dup']) ? $conn->real_escape_string($_POST['dup']) : ""));
  $inv = htmlspecialchars(strip_tags(isset($_POST['inv']) ? $conn->real_escape_string($_POST['inv']) : ""));

  $mobno = str_replace('\n', ',', $mobno);
  $newline = explode('\n', $mobno);

  $correct_mobno_data = [];
  $return_mobno_data = '';
  $issu_mob = '';
  $cnt_vld_no = 0;
  $max_vld_no = 2;

  for($i = 0; $i < count($newline); $i++) {
    $expl = explode(",", $newline[$i]);

    for($ij = 0; $ij < count($expl); $ij++) {
      if($inv == 1) {
        $vlno = validate_phone_number($expl[$ij]);
      } else {
        $vlno = $newline[$i];
      }
      if($vlno == true) {
        if($cnt_vld_no < $max_vld_no) {
          if($dup == 1) {
            if(!in_array($expl[$ij], $correct_mobno_data)) {
              if($expl[$ij] != '') {
                $cnt_vld_no++;
                $correct_mobno_data[] = $expl[$ij];
                $return_mobno_data .= $expl[$ij].",\n";
              } else {
                $issu_mob .= $expl[$ij].",";
              }
            } else {
              $issu_mob .= $expl[$ij].",";
            }
          } else {
            if($expl[$ij] != '') {
              $cnt_vld_no++;
              $correct_mobno_data[] = $expl[$ij];
              $return_mobno_data .= $expl[$ij].",\n";
            } else {
              $issu_mob .= $expl[$ij].", ";
            }
          }
        } else {
          // Handle the case when you have too many numbers
          $issu_mob = "You can upload only 1000 numbers.";
          break; // Exit the loop
        }
      } else {
        $issu_mob .= $expl[$ij].",";
      }
    }
  }

  $return_mobno_data = rtrim($return_mobno_data, ",\n");
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." validated Mobile Nos ($return_mobno_data||$issu_mob) on ".date("Y-m-d H:i:s"), '../');

  if($issu_mob === "You can upload only 1000 numbers.") {
    // Handle the case when you have too many numbers
    $json = array("status" => 0, "msg" => "You can upload only 1000 numbers.");
  } else {
    $json = array("status" => 1, "msg" => $return_mobno_data."||".$issu_mob);
  }
}
// Add Contacts in Group Page validateMobno - End

// Add Contacts in Group Page delete_senderid - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "delete_senderid") {
  $whatspp_config_id1 = htmlspecialchars(strip_tags(isset($_REQUEST['whatspp_config_id']) ? $conn->real_escape_string($_REQUEST['whatspp_config_id']) : ""));
  $approve_status1 = htmlspecialchars(strip_tags(isset($_REQUEST['approve_status']) ? $conn->real_escape_string($_REQUEST['approve_status']) : ""));

  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';
  $replace_txt = '{
    "sender_id" : "'.$whatspp_config_id1.'",
    "request_id":"'.$request_id.'"
  }';
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/sender_id/delete_sender_id',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => array(
      $bearer_token,
      'Content-Type: application/json'
    ),
  ));
  site_log_generate("Add Contacts in Group Delete Sender ID Page : ".$_SESSION['yjwatsp_user_name']." Execute the service [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $header = json_decode($response, false);
  site_log_generate("Add Contacts in Group Delete Sender ID Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');
  if($header->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }

  if($header->response_status == 200) {
    $json = array("status" => 1, "msg" => "Success");
  } else {
    $json = array("status" => 0, "msg" => "Failed. ".$header->response_msg);
  }
}
// Add Contacts in Group Page delete_senderid - Start

// Add Contacts in Group Page generate_contacts - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "generate_contacts") {
  $txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));

  $expld = explode(",", $txt_list_mobno);
  $mblno = '';
  for($i = 0; $i < count($expld); $i++) {
    $mblno .= '"'.$expld[$i].'", ';
  }
  $mblno = rtrim($mblno, ", ");

  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
    "mobile_number":['.$mblno.'],
    "request_id":"'.$request_id.'"
  }';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/wtsp/create_csv',
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
  site_log_generate("Add Contacts in Group Generate Contacts Page : ".$_SESSION['yjwatsp_user_name']." Execute the service [$bearer_token.$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $header = json_decode($response, false);
  site_log_generate("Add Contacts in Group Generate Contacts Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');
  if($header->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  if($header->response_code == 1) {
    //$json = array("status" => 1, "msg" => "<a target='_blank' href='".$site_url.$header->file_location."' download class='error_display'>Download Contacts CSV</a>");
    $json = array("status" => 1, "msg" => $site_url.$header->file_location);
  } else {
    $json = array("status" => 0, "msg" => "Failed to Generate Contact CSV. Kindly try again!!");
  }
}
// Add Contacts in Group Page generate_contacts - Start

// Add Contacts in Group Page contact_group - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "contact_group") {
  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');
  // Get data
  $txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));
  $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  $rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
  $rdo_sameperson_video = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_sameperson_video']) ? $_REQUEST['rdo_sameperson_video'] : ""));
  $txt_group_name = urldecode($conn->real_escape_string($_REQUEST['textarea']));
  $txt_group_name = str_replace("'", "\'", $txt_group_name);
  $txt_group_name = str_replace('"', '\"', $txt_group_name);
  $txt_group_name = str_replace("\\r\\n", '\n', $txt_group_name);
  $txt_group_name = str_replace('&amp;', '&', $txt_group_name);
  $txt_group_name = str_replace(PHP_EOL, '\n', $txt_group_name);
  $txt_group_name = str_replace('\\&quot;', '"', $txt_group_name);
  $txt_group_name = str_replace('"', '\"', $txt_group_name);
  $txt_group_name = trim(preg_replace('/[ \t]+/', ' ', $txt_group_name));
  $txt_group_name = preg_replace('/\s*\n\s*/', "\n", $txt_group_name);

  $upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));
 $media_type = htmlspecialchars(strip_tags(isset($_REQUEST['media_type']) ? $_REQUEST['media_type'] : ""));
  $file_image_header = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header']) ? $_REQUEST['file_image_header'] : ""));
  $file_image_header_url = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));

  $msg_type = 'TEXT';
  $isSameTxt = 'false';
  if ($rdo_newex_group == 'N') {
    $isSameTxt = 'true';
  } else {
    // Define a regular expression pattern
    $pattern = '/{{(\w+)}}/';
    // Perform the regular expression match
    $matches_patterns = [];
    preg_match_all($pattern, $txt_group_name, $matches_patterns);
    // $matches[0] will contain an array of all matches
    $variable_values = $matches_patterns[0];
    // Output the count of valid numeric placeholders
    $variable_count = count($variable_values);
  }

  if ($rdo_sameperson_video == 'S' || $rdo_sameperson_video == 'N') {
    $isSameVdo = 'true';
    $samevdo = 0;
  } elseif ($rdo_sameperson_video == 'P') {
if($media_type == 'V'){
  $msg_type = 'VIDEO';
}else{
  $msg_type = 'IMAGE';
}
    $variable_count++;
    $samevdo = 1;
    $isSameVdo = 'false';
  }
  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');

  if ($_FILES["upload_contact"]["name"] != '') {
    $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
    $extension = $path_parts['extension'];
    $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
    /* Location */
    $location = "../uploads/group_contact/" . $filename;
    $csv_file = $full_pathurl . "uploads/group_contact/" . $filename;
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
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " CSV File Uploading on " . date("Y-m-d H:i:s"), '../');

    //if ($samevdo == 1) {
      //$msg_type = 'VIDEO';
    //}
  }

  if ($samevdo == 1) {
    $variable_count--;
  }

  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " CSV Uploaded on " . date("Y-m-d H:i:s"), '../');


  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $img_filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
  $img_location = "../uploads/whatsapp_images/" . $img_filename;
  $imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  //$location = $site_url . "uploads/whatsapp_images/" . $filename;

  /* Valid extensions */
  // $valid_extensions = array("png", "jpg", "jpeg");

  if ($file_type[1] == "mp3" || $file_type[1] == "mpeg") {
    // This code is for Audio
    $msg_type = 'AUDIO';
  } else if ($file_type[1] == "mp4" || $file_type[1] == "avi" || $file_type[1] == "mov" || $file_type[1] == "mkv") {
    // This code is for videos
    $msg_type = 'VIDEO';
  } else if ($file_type[1] == "png" || $file_type[1] == "jpg" || $file_type[1] == "jpeg") {
    // This code is for images
    $msg_type = 'IMAGE';
  }
  $valid_extensions = array("png", "jpg", "jpeg", "mp3", "mp4", "avi", "mov", "mkv");

  $rspns = '';
  /* Check file extension */
  /* Upload file */

  if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $img_location)) {
    $rspns = $img_location;
    site_log_generate("Create Template Page : User : " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
     $location_1 = '["' . $site_url . "uploads/whatsapp_images/" . $img_filename . '"]';

  }


  if ($file_image_header_url) {
    if (preg_match('/\.(jpeg|jpg|png)$/i', $file_image_header_url)) {
      // Successful match!
      $msg_type = "IMAGE";
      $location_1 = '["' . $file_image_header_url . '"]';
  } else if(preg_match('/\.(mp4|mov|mkv|avi)$/i', $file_image_header_url)){
      // An Video file extension
      $msg_type = "VIDEO";
      $location_1 = '["' . $file_image_header_url . '"]';
  }
  }

/*  if ($file_image_header_url) {
    $msg_type = "VIDEO";
    $location_1 = '["' . $file_image_header_url . '"]';
  }*/
 

  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  if ($location == '') {
    $location = '-';
  }

  if ($msg_type == 'TEXT') {
    if ($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "receiver_nos_path" : "' . $csv_file . '",
          "messages":"' . $txt_group_name . '",
          "variable_count":"' . $variable_count . '",
          "request_id":"' . $request_id . '"
          }';
    } else {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "receiver_nos_path" : "' . $csv_file . '",
          "messages":"' . $txt_group_name . '",
          "variable_count":"' . $variable_count . '",';
      $replace_txt .= '"request_id":"' . $request_id . '"
        }';
    }
  } else {
    if ($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "receiver_nos_path" : "' . $csv_file . '",      
          "messages":"' . $txt_group_name . '",
          "request_id":"' . $request_id . '",
          "variable_count":"' . $variable_count . '",
          "is_same_media":' . $isSameVdo . ',
          "media_url":' . $location_1 . '

        }';
    } else {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "messages":"' . $txt_group_name . '",
          "receiver_nos_path" : "' . $csv_file . '",
          "request_id":"' . $request_id . '",
          "variable_count":"' . $variable_count . '",
          "is_same_media":' . $isSameVdo . '';
      if ($isSameVdo == 'true' && $isSameTxt == 'false') {
        $replace_txt .= ',
          "media_url":' . $location_1 . '
        }';
      } else {
        $replace_txt .= '}';
      }
    }
  }
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/compose',
      // Create a New Group
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );


  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>
      window.location = "logout";
    </script>
  <? }
  $respobj = json_decode($response);
  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if ($respobj->response_status == 403) {?>
    <script>
      window.location = "logout";
    </script>
    <?
    $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . date("Y-m-d H:i:s"), '../');
  } elseif ($respobj->response_status == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . date("Y-m-d H:i:s"), '../');
  } elseif ($respobj->response_status == 200) {
 $responses = '';
    if ($respobj->invalid_count) {
      $responses .= "Invalid Count : " . $respobj->invalid_count;
    } ;
    $json = array("status" => 1, "msg" => "Template Created Successfully..!</br>" . $responses);
    //$json = array("status" => 1, "msg" => "Success");
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . date("Y-m-d H:i:s"), '../');
  }
}
// Add Contacts in Group Page contact_group - End --08Nov2023 - GA

// Add Contacts in Group Page contact_group - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "contact_groupssss") {
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));
  $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  //$variable_count = htmlspecialchars(strip_tags(isset($_REQUEST['variable_count']) ? $_REQUEST['variable_count'] : 0));
  $rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
  $rdo_sameperson_video = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_sameperson_video']) ? $_REQUEST['rdo_sameperson_video'] : ""));
  $txt_group_name = htmlspecialchars(strip_tags(isset($_REQUEST['textarea']) ? $conn->real_escape_string($_REQUEST['textarea']) : ""));
  $txt_group_name = str_replace("'", "\'", $txt_group_name);
  $txt_group_name = str_replace('"', '\"', $txt_group_name);
  $txt_group_name = str_replace("\\r\\n", '\n', $txt_group_name);
  $txt_group_name = str_replace('&amp;', '&', $txt_group_name);
  $txt_group_name = str_replace(PHP_EOL, '\n', $txt_group_name);
  $upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));

  $file_image_header = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header']) ? $_REQUEST['file_image_header'] : ""));
  $file_image_header_url = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));

  $msg_type = 'TEXT';
  $isSameTxt = 'false';
  if($rdo_newex_group == 'N') {
    $isSameTxt = 'true';
  }else{
// Define a regular expression pattern
$pattern = '/{{\d+}}/';
// Perform the regular expression match
$matches_patterns = [];
preg_match_all($pattern, $txt_group_name, $matches_patterns);
// $matches[0] will contain an array of all matches
$variable_values = $matches_patterns[0];
// Output the count of valid numeric placeholders
// print_r($variable_values);
$variable_count = count($variable_values);
}

  if($rdo_sameperson_video == 'S') {
    $isSameVdo = 'true';
    $samevdo = 0;
  } elseif($rdo_sameperson_video == 'P') {
    $variable_count++;
    $samevdo = 1;
    $isSameVdo = 'false';
  }
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  if($_FILES["upload_contact"]["name"] != '') {
    $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
    $extension = $path_parts['extension'];
    $filename = $_SESSION['yjwatsp_user_id']."_csv_".$milliseconds.".".$extension;
    /* Location */
    $location = "../uploads/group_contact/".$filename;
    $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);
    // $csvfile = $full_pathurl."uploads/group_contact/".$filename;

    /* Valid extensions */
    $valid_extensions = array("csv");
    $response = 0;
    /* Check file extension */
    if(in_array(strtolower($imageFileType), $valid_extensions)) {
      /* Upload file */
      if(move_uploaded_file($_FILES['upload_contact']['tmp_name'], $location)) {
        //echo $location;
        $response = $location;
      }
    }
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." CSV File Uploading on ".date("Y-m-d H:i:s"), '../');
    $csvFile = fopen($location, 'r') or die("can't open file");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." CSV File Uploading on ".date("Y-m-d H:i:s"), '../');

    // Skip the first line
    fgetcsv($csvFile);

    $var_array = array();
    $duplicates_array = array();
    // Parse data from CSV file line by line
    while(($line = fgetcsv($csvFile)) !== FALSE) {
      $small_array = array();
      $vrble .= "[";
      // Get row data
      $tmp = '';
      for($txt_variable_counti = 0; $txt_variable_counti <= $variable_count; $txt_variable_counti++) {
        // Looping the txt_variable_counti is less than the count of txt_variable_counti.if the condition is true to continue the process.if the condition are false to stop the process
        if($samevdo == 1 && $txt_variable_counti == 1) {
          $msg_type = 'VIDEO';
          if(filter_var($line[$txt_variable_counti], FILTER_VALIDATE_URL) === FALSE) {
            // If the mobile number is invalid, set validRow to false and break
            $number_tmp = str_replace('"'.$line[($txt_variable_counti - 1)].'",', '', $number_tmp);
            $validRow = false;
            break;
          } else {
            $videourl_tmp .= '"'.$line[$txt_variable_counti].'",';
          }
        } else {
          if($txt_variable_counti > 0) {
            if($line[$txt_variable_counti] == '') {
              $tmp .= '"'.$default_variale_msg.'", ';
            } else {
              array_push($small_array, $line[$txt_variable_counti]);
              $tmp .= '"'.$line[$txt_variable_counti].'", ';
            }
          } else {
            if(validate_phone_number($line[$txt_variable_counti])) {
              //$number_tmp .= '"' . $line[$txt_variable_counti] . '",';
              $current_number = $line[$txt_variable_counti];
              // Check if the phone number has already been seen
              if(!isset($duplicates_array[$current_number])) {
                // Mark the phone number as seen
                $duplicates_array[$current_number] = true;
                $number_tmp .= '"'.$current_number.'",';
              } else {
                // Skip the row if the phone number is a duplicate
                continue 2; // Continue with the next iteration of the outer loop
              }
            } else {
              // If the mobile number is invalid, set validRow to false and break
              $validRow = false;
              break;
            }
          }
        }
      }
      array_push($var_array, $small_array);
      $tmp = rtrim($tmp, ", ");
      $vrble .= $tmp."], ";
    }
    // Close opened CSV file
    fclose($csvFile);
    $number_array = '['.substr($number_tmp, 0, -1).']';
    $videourl_array = '['.substr($videourl_tmp, 0, -1).']';
  }

  if($samevdo == 1) {
    $variable_count--;
  }

  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." CSV Uploaded on ".date("Y-m-d H:i:s"), '../');


  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $img_filename = $_SESSION['yjwatsp_user_id']."_".$milliseconds.".".$file_type[1];
  $img_location = "../uploads/whatsapp_images/".$img_filename;
  $location_1 = '["'.$site_url."uploads/whatsapp_images/".$img_filename.'"]';
  $imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  //$location = $site_url . "uploads/whatsapp_images/" . $filename;

  /* Valid extensions */
  // $valid_extensions = array("png", "jpg", "jpeg");

  if($file_type[1] == "mp3" || $file_type[1] == "mpeg") {
    // This code is for Audio
    $msg_type = 'AUDIO';
  } else if($file_type[1] == "mp4" || $file_type[1] == "avi" || $file_type[1] == "mov" || $file_type[1] == "mkv") {
    // This code is for videos
    $msg_type = 'VIDEO';
  } else if($file_type[1] == "png" || $file_type[1] == "jpg" || $file_type[1] == "jpeg") {
    // This code is for images
    $msg_type = 'IMAGE';
  }
  $valid_extensions = array("png", "jpg", "jpeg", "mp3", "mp4", "avi", "mov", "mkv");

  $rspns = '';
  /* Check file extension */
  /* Upload file */

  if(move_uploaded_file($_FILES['file_image_header']['tmp_name'], $img_location)) {
    $rspns = $img_location;
    site_log_generate("Create Template Page : User : ".$_SESSION['yjwatsp_user_name']." whatsapp_images file moved into Folder on ".date("Y-m-d H:i:s"), '../');
  }
  if($file_image_header_url) {
    $location_1 = '["'.$file_image_header_url.'"]';
  }
  if($samevdo == 1) {
    $location_1 = $videourl_array;
  }

  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';
  if($location == '') {
    $location = '-';
  }

  $var_array = array_filter($var_array);
  $variable_array = '[[';
  $variable_array = $variable_array.'"'.implode('"],["', array_map(function ($a) {
    return implode('","', $a);
  }, $var_array));
  $variable_array = $variable_array.'"'.']]';


  if($msg_type == 'TEXT') {
    if($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_nos_path" : "'.$location.'",
          "messages":"'.$txt_group_name.'",
          "receiver_numbers":'.$number_array.',
          "variable_count":"'.$variable_count.'",
          "request_id":"'.$request_id.'"
          }';
    } else {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_nos_path" : "'.$location.'",
          "messages":"'.$txt_group_name.'",
          "receiver_numbers":'.$number_array.',
          "variable_count":"'.$variable_count.'",';

      if($variable_array != '[[""]]') {
        $replace_txt .= '"variable_values":'.$variable_array.',';
      }

      $replace_txt .= '"request_id":"'.$request_id.'"
        }';
    }
  } else {
    if($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_nos_path" : "'.$location.'",      
          "receiver_numbers":'.$number_array.',
          "messages":"'.$txt_group_name.'",
          "request_id":"'.$request_id.'",
          "variable_count":"'.$variable_count.'",
          "is_same_media":'.$isSameVdo.',
          "media_url":'.$location_1.'
        }';
    } else {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_numbers":'.$number_array.',
          "messages":"'.$txt_group_name.'",
          "receiver_nos_path" : "'.$location.'",
          "request_id":"'.$request_id.'",';

      if($variable_array != '[[""]]') {
        $replace_txt .= '"variable_values":'.$variable_array.',';
      }

      $replace_txt .= '"variable_count":"'.$variable_count.'",
          "is_same_media":'.$isSameVdo.',
          "media_url":'.$location_1.'
        }';
    }
  }
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url.'/compose',
      // Create a New Group
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );


  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
// Add Contacts in Group Page contact_group - End --08Nov2023 - GA

// Add Contacts in Group Page contact_group - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "contact_group_old") {
  site_log_generate("Add 1Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));
  $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  $variable_count = htmlspecialchars(strip_tags(isset($_REQUEST['variable_count']) ? $_REQUEST['variable_count'] : 0));
  $rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
  $txt_group_name = htmlspecialchars(strip_tags(isset($_REQUEST['textarea']) ? $conn->real_escape_string($_REQUEST['textarea']) : ""));
  $txt_group_name = str_replace("'", "\'", $txt_group_name);
  $txt_group_name = str_replace('"', '\"', $txt_group_name);
  $txt_group_name = str_replace("\\r\\n", '\n', $txt_group_name);
  $txt_group_name = str_replace('&amp;', '&', $txt_group_name);
  $txt_group_name = str_replace(PHP_EOL, '\n', $txt_group_name);
  $upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));

  $file_image_header = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header']) ? $_REQUEST['file_image_header'] : ""));
  $file_image_header_url = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));

  $isSameTxt = 'false';

  if($rdo_newex_group == 'N') {
    $isSameTxt = 'true';
  }
  site_log_generate("Add 2Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');
  if($_FILES["upload_contact"]["name"] != '') {
    $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
    $extension = $path_parts['extension'];
    $filename = $_SESSION['yjwatsp_user_id']."_csv_".$milliseconds.".".$extension;
    /* Location */
    $location = "../uploads/group_contact/".$filename;
    $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);
    // $csvfile = $full_pathurl."uploads/group_contact/".$filename;

    /* Valid extensions */
    $valid_extensions = array("csv");
    $response = 0;
    /* Check file extension */
    if(in_array(strtolower($imageFileType), $valid_extensions)) {
      /* Upload file */
      if(move_uploaded_file($_FILES['upload_contact']['tmp_name'], $location)) {
        //echo $location;
        $response = $location;
      }

    }
    site_log_generate("Add 03Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." CSV File Uploading on ".date("Y-m-d H:i:s"), '../');
    $csvFile = fopen($location, 'r') or die("can't open file");
    site_log_generate("Add 3Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." CSV File Uploading on ".date("Y-m-d H:i:s"), '../');

    // Skip the first line
    $var_array = array();
    // Parse data from CSV file line by line
    while(($line = fgetcsv($csvFile)) !== FALSE) {
      $small_array = array();
      $vrble .= "[";
      // Get row data
      $tmp = '';
      for($txt_variable_counti = 0; $txt_variable_counti <= $variable_count; $txt_variable_counti++) {
        // Looping the txt_variable_counti is less than the count of txt_variable_counti.if the condition is true to continue the process.if the condition are false to stop the process
        if($txt_variable_counti > 0) {
          if($line[$txt_variable_counti] == '') {
            $tmp .= '"'.$default_variale_msg.'", ';
          } else {
            array_push($small_array, $line[$txt_variable_counti]);
            $tmp .= '"'.$line[$txt_variable_counti].'", ';
          }
        } else {
          if(validate_phone_number($line[$txt_variable_counti])) {
            $number_tmp .= '"'.$line[$txt_variable_counti].'",';
          } else {
            // If the mobile number is invalid, set validRow to false and break
            $validRow = false;
            break;
          }
        }
      }
      array_push($var_array, $small_array);
      $tmp = rtrim($tmp, ", ");
      $vrble .= $tmp."], ";
    }
    // Close opened CSV file
    fclose($csvFile);

    $number_array = '['.substr($number_tmp, 0, -1).']';
  }
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." CSV Uploaded on ".date("Y-m-d H:i:s"), '../');


  $msg_type = 'TEXT';

  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $img_filename = $_SESSION['yjwatsp_user_id']."_".$milliseconds.".".$file_type[1];
  $img_location = "../uploads/whatsapp_images/".$img_filename;
  $location_1 = $site_url."uploads/whatsapp_images/".$img_filename;
  $imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  //$location = $site_url . "uploads/whatsapp_images/" . $filename;

  /* Valid extensions */
  // $valid_extensions = array("png", "jpg", "jpeg");

  if($file_type[1] == "mp4" || $file_type[1] == "avi" || $file_type[1] == "mov" || $file_type[1] == "mkv") {
    // This code is for videos
    $msg_type = 'VIDEO';
  } else if($file_type[1] == "png" || $file_type[1] == "jpg" || $file_type[1] == "jpeg") {
    // This code is for images
    $msg_type = 'IMAGE';
  }
  $valid_extensions = array("png", "jpg", "jpeg", "mp4", "avi", "mov", "mkv");

  $rspns = '';
  /* Check file extension */
  /* Upload file */

  if(move_uploaded_file($_FILES['file_image_header']['tmp_name'], $img_location)) {
    $rspns = $img_location;
    site_log_generate("Create Template Page : User : ".$_SESSION['yjwatsp_user_name']." whatsapp_images file moved into Folder on ".date("Y-m-d H:i:s"), '../');
  }
  if($file_image_header_url) {
    $location_1 = $file_image_header_url;
  }

  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';
  if($location == '') {
    $location = '-';
  }

  $var_array = array_filter($var_array);
  $variable_array = '[[';
  $variable_array = $variable_array.'"'.implode('"],["', array_map(function ($a) {
    return implode('","', $a);
  }, $var_array));
  $variable_array = $variable_array.'"'.']]';

  // Remove duplicates
  $number_array = array_unique($number_array);
  print_r($number_array);
  exit;
  if($msg_type == 'TEXT') {
    if($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_nos_path" : "'.$location.'",
          "messages":"'.$txt_group_name.'",
          "receiver_numbers":'.$number_array.',
          "variable_count":"'.$variable_count.'",
          "request_id":"'.$request_id.'"
   	     }';
    } else {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_nos_path" : "'.$location.'",
          "messages":"'.$txt_group_name.'",
          "receiver_numbers":'.$number_array.',
          "variable_count":"'.$variable_count.'",
	  "variable_values":'.$variable_array.',
          "request_id":"'.$request_id.'"
        }';
    }
  } else {
    if($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_nos_path" : "'.$location.'",      
          "receiver_numbers":'.$number_array.',
          "messages":"'.$txt_group_name.'",
          "request_id":"'.$request_id.'",
          "variable_count":"'.$variable_count.'",
          "media_url":"'.$location_1.'"
        }';
    } else {
      $replace_txt = '{
          "message_type":"'.$msg_type.'",
          "is_same_msg":'.$isSameTxt.',
          "receiver_numbers":'.$number_array.',
          "messages":"'.$txt_group_name.'",
          "receiver_nos_path" : "'.$location.'",
          "request_id":"'.$request_id.'",
	  "variable_values":'.$variable_array.',
          "variable_count":"'.$variable_count.'",
          "media_url":"'.$location_1.'"
        }';
    }
  }


  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/compose', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));


  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
// Add Contacts in Group Page contact_group - End

// Compose Sms Page compose_sms - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "compose_sms") {
  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');

  // Get data
  $txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));
  $txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
  $rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
  $rdo_sameperson_video = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_sameperson_video']) ? $_REQUEST['rdo_sameperson_video'] : ""));
  //$txt_group_name = htmlspecialchars(strip_tags(isset($_REQUEST['textarea']) ? $conn->real_escape_string($_REQUEST['textarea']) : ""));
 $txt_group_name = urldecode($conn->real_escape_string($_REQUEST['textarea']));
  $txt_group_name = str_replace("'", "\'", $txt_group_name);
  $txt_group_name = str_replace('"', '\"', $txt_group_name);
  $txt_group_name = str_replace("\\r\\n", '\n', $txt_group_name);
  $txt_group_name = str_replace('&amp;', '&', $txt_group_name);
  $txt_group_name = str_replace(PHP_EOL, '\n', $txt_group_name);
   $txt_group_name = str_replace('\\&quot;', '"', $txt_group_name);
  $txt_group_name = str_replace('"', '\"', $txt_group_name);
  $txt_group_name = trim(preg_replace('/[ \t]+/', ' ', $txt_group_name));
$txt_group_name = preg_replace('/\s*\n\s*/', "\n", $txt_group_name);
  $upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));
 $media_type = htmlspecialchars(strip_tags(isset($_REQUEST['media_type']) ? $_REQUEST['media_type'] : ""));
  $file_image_header = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header']) ? $_REQUEST['file_image_header'] : ""));
  $file_image_header_url = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));

  $msg_type = 'TEXT';
  $isSameTxt = 'false';
  if ($rdo_newex_group == 'N') {
    $isSameTxt = 'true';
  } else {
    // Define a regular expression pattern
    $pattern = '/{{(\w+)}}/';
    // Perform the regular expression match
    $matches_patterns = [];
    preg_match_all($pattern, $txt_group_name, $matches_patterns);
    // $matches[0] will contain an array of all matches
    $variable_values = $matches_patterns[0];
    // Output the count of valid numeric placeholders
    $variable_count = count($variable_values);
  }

  if ($rdo_sameperson_video == 'S' || $rdo_sameperson_video == 'N') {
    $isSameVdo = 'true';
    $samevdo = 0;
  } elseif ($rdo_sameperson_video == 'P') {
if($media_type == 'V'){
  $msg_type = 'VIDEO';
}else{
  $msg_type = 'IMAGE';
}
    $variable_count++;
    $samevdo = 1;
    $isSameVdo = 'false';
  }
  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');

  if ($_FILES["upload_contact"]["name"] != '') {
    $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
    $extension = $path_parts['extension'];
    $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
    /* Location */
    $location = "../uploads/group_contact/" . $filename;
    $csv_file = $full_pathurl . "uploads/group_contact/" . $filename;
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
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " CSV File Uploading on " . date("Y-m-d H:i:s"), '../');

    //if ($samevdo == 1) {
      //$msg_type = 'VIDEO';
    //}
  }

  if ($samevdo == 1) {
    $variable_count--;
  }

  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " CSV Uploaded on " . date("Y-m-d H:i:s"), '../');


  $image_size = $_FILES['file_image_header']['size'];
  $image_type = $_FILES['file_image_header']['type'];
  $file_type = explode("/", $image_type);

  $img_filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
  $img_location = "../uploads/whatsapp_images/" . $img_filename;
  $location_1 = '["' . $site_url . "uploads/whatsapp_images/" . $img_filename . '"]';
  $imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
  $imageFileType = strtolower($imageFileType);
  //$location = $site_url . "uploads/whatsapp_images/" . $filename;

  /* Valid extensions */
  // $valid_extensions = array("png", "jpg", "jpeg");

  if ($file_type[1] == "mp3" || $file_type[1] == "mpeg") {
    // This code is for Audio
    $msg_type = 'AUDIO';
  } else if ($file_type[1] == "mp4" || $file_type[1] == "avi" || $file_type[1] == "mov" || $file_type[1] == "mkv") {
    // This code is for videos
    $msg_type = 'VIDEO';
  } else if ($file_type[1] == "png" || $file_type[1] == "jpg" || $file_type[1] == "jpeg") {
    // This code is for images
    $msg_type = 'IMAGE';
  }
  $valid_extensions = array("png", "jpg", "jpeg", "mp3", "mp4", "avi", "mov", "mkv");

  $rspns = '';
  /* Check file extension */
  /* Upload file */

  if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $img_location)) {
    $rspns = $img_location;
    site_log_generate("Create Template Page : User : " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
  }

  if ($file_image_header_url) {
    if (preg_match('/\.(jpeg|jpg|png)$/i', $file_image_header_url)) {
      // Successful match!
      $msg_type = "IMAGE";
      $location_1 = '["' . $file_image_header_url . '"]';
  } else if(preg_match('/\.(mp4|mov|mkv|avi)$/i', $file_image_header_url)){
      // An Video file extension
      $msg_type = "VIDEO";
      $location_1 = '["' . $file_image_header_url . '"]';
  }
  }

/*  if ($file_image_header_url) {
    $msg_type = "VIDEO";
    $location_1 = '["' . $file_image_header_url . '"]';
  }*/
 

  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  if ($location == '') {
    $location = '-';
  }

  if ($msg_type == 'TEXT') {
    if ($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "receiver_nos_path" : "' . $csv_file . '",
          "messages":"' . $txt_group_name . '",
          "variable_count":"' . $variable_count . '",
          "request_id":"' . $request_id . '"
          }';
    } else {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "receiver_nos_path" : "' . $csv_file . '",
          "messages":"' . $txt_group_name . '",
          "variable_count":"' . $variable_count . '",';
      $replace_txt .= '"request_id":"' . $request_id . '"
        }';
    }
  } else {
    if ($rdo_newex_group == 'N') {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "receiver_nos_path" : "' . $csv_file . '",      
          "messages":"' . $txt_group_name . '",
          "request_id":"' . $request_id . '",
          "variable_count":"' . $variable_count . '",
          "is_same_media":' . $isSameVdo . ',
          "media_url":' . $location_1 . '

        }';
    } else {
      $replace_txt = '{
          "message_type":"' . $msg_type . '",
          "is_same_msg":' . $isSameTxt . ',
          "messages":"' . $txt_group_name . '",
          "receiver_nos_path" : "' . $csv_file . '",
          "request_id":"' . $request_id . '",
          "variable_count":"' . $variable_count . '",
          "is_same_media":' . $isSameVdo . '';
      if ($isSameVdo == 'true' && $isSameTxt == 'false') {
        $replace_txt .= ',
          "media_url":' . $location_1 . '
        }';
      } else {
        $replace_txt .= '}';
      }
    }
  }

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/sms_compose',
      // Create a New Group
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );


  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>
      window.location = "logout";
    </script>
  <? }
  $respobj = json_decode($response);
  site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if ($respobj->response_status == 403) {?>
    <script>
      window.location = "logout";
    </script>
    <?
    $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . date("Y-m-d H:i:s"), '../');
  } elseif ($respobj->response_status == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . date("Y-m-d H:i:s"), '../');
  } elseif ($respobj->response_status == 200) {
 $responses = '';
    if ($respobj->invalid_count) {
      $responses .= "Invalid Count : " . $respobj->invalid_count;
    } ;
    $json = array("status" => 1, "msg" => "Template Created Successfully..!</br>" . $responses);
    //$json = array("status" => 1, "msg" => "Success");
    site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . date("Y-m-d H:i:s"), '../');
  }
}
// Compose sms Page compose_sms - End

if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_reject_campaign_wastp") {
  site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');
  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $reason = htmlspecialchars(strip_tags(isset($_REQUEST['reason']) ? $_REQUEST['reason'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $replace_txt = '{
  "request_id":"'.$request_id.'",
      "selected_user_id":"'.$select_user_id.'",
      "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
      "compose_message_id":"'.$compose_message_id.'",
       "product_name" : "WHATSAPP",
      "reason" : "'.$reason.'"
    }';
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/reject_campaign', // Create a New Group
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
      'Content-Type: application/json; charset=utf-8'
    ),
  ));

  site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}

// Approve_campaign Page Approve_campaign - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_approve_campaign_sms") {
  site_log_generate("send_approve_campaign_sms : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));


  $campaign_name = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_name']) ? $_REQUEST['campaign_name'] : ""));

  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);
  $replace_txt = '{
"request_id":"'.$request_id.'",
      "selected_user_id":"'.$select_user_id.'",
 "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
      "compose_message_id":"'.$compose_message_id.'",
      "sender_numbers" : ["'.$mobile_number.'"]
    }';
  //"campaign_name":"'.$campaign_name.'",
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/approve_usr', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));


  site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/approve_user/approve_usr] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
 site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');

  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // echo $response;
  $respobj = json_decode($response);

  site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }

}
//  Approve_campaign page Approve_campaign - end 

// Approve_campaign send_approve_campaign_rcs Page send_approve_campaign_rcs - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_approve_campaign_rcs") {
  site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $campaign_name = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_name']) ? $_REQUEST['campaign_name'] : ""));
  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);
  $replace_txt = '{
  "request_id":"'.$request_id.'",
      "selected_user_id":"'.$select_user_id.'",
      "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
      "compose_message_id":"'.$compose_message_id.'",
      "sender_numbers" : ["'.$mobile_number.'"]
    }';
  //"campaign_name":"'.$campaign_name.'",
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/approve_rcs', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));

  site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // echo $response;
  $respobj = json_decode($response);

  site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_approve_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }

}
//  Approve_campaign send_approve_campaign_rcs page send_approve_campaign_rcs - end 

// Approve_campaign Page Approve_campaign - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_approve_campaign_wastp") {
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $campaign_name = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_name']) ? $_REQUEST['campaign_name'] : ""));

  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);
  $replace_txt = '{
"request_id":"'.$request_id.'",
   "selected_user_id":"'.$select_user_id.'",
      "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
      "compose_whatsapp_id":"'.$compose_message_id.'",
      "sender_numbers" : ["'.$mobile_number.'"]
    }';

  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/approve_wtsp', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));


  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // echo $response;
  $respobj = json_decode($response);

  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) { ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }

}
//  Approve_campaign page Approve_campaign - end 

// Approve_campaign Page Approve_campaign - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_approve_campaign_old") {
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $campaign_name = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_name']) ? $_REQUEST['campaign_name'] : ""));

  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);
  $replace_txt = '{
"request_id":"'.$request_id.'",
   "selected_user_id":"'.$select_user_id.'",
      "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
      "compose_whatsapp_id":"'.$compose_message_id.'",
      "sender_numbers" : ["'.$mobile_number.'"]
    }';

  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/approve_usr', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));


  site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // echo $response;
  $respobj = json_decode($response);

  site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) { ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_approve_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }

}
//  Approve_campaign page Approve_campaign - end 


// Compose RCS Page compose_rcs - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "compose_rcsss") {

site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');
// Get data
$txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));
$txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
$rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
$rdo_sameperson_video = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_sameperson_video']) ? $_REQUEST['rdo_sameperson_video'] : ""));
$txt_group_name = urldecode($conn->real_escape_string($_REQUEST['textarea']));
$txt_group_name = str_replace("'", "\'", $txt_group_name);
$txt_group_name = str_replace('"', '\"', $txt_group_name);
$txt_group_name = str_replace("\\r\\n", '\n', $txt_group_name);
$txt_group_name = str_replace('&amp;', '&', $txt_group_name);
$txt_group_name = str_replace(PHP_EOL, '\n', $txt_group_name);
$txt_group_name = str_replace('\\&quot;', '"', $txt_group_name);
$txt_group_name = str_replace('"', '\"', $txt_group_name);
$txt_group_name = trim(preg_replace('/[ \t]+/', ' ', $txt_group_name));
$txt_group_name = preg_replace('/\s*\n\s*/', "\n", $txt_group_name);

$upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));
$media_type = htmlspecialchars(strip_tags(isset($_REQUEST['media_type']) ? $_REQUEST['media_type'] : ""));
$file_image_header = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header']) ? $_REQUEST['file_image_header'] : ""));
$file_image_header_url = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));

$msg_type = 'TEXT';
$isSameTxt = 'false';
if ($rdo_newex_group == 'N') {
  $isSameTxt = 'true';
} else {
  // Define a regular expression pattern
  $pattern = '/{{(\w+)}}/';
  // Perform the regular expression match
  $matches_patterns = [];
  preg_match_all($pattern, $txt_group_name, $matches_patterns);
  // $matches[0] will contain an array of all matches
  $variable_values = $matches_patterns[0];
  // Output the count of valid numeric placeholders
  $variable_count = count($variable_values);
}


if ($rdo_newex_group == 'P') {
  if ($rdo_sameperson_video == 'S' ) {
    $isSameVdo = 'true';
    $samevdo = 0;
  } elseif ($rdo_sameperson_video == 'P') {
if($media_type == 'V'){
  $msg_type = 'VIDEO';
}else{
  $msg_type = 'IMAGE';
}
    $variable_count++;
    $samevdo = 1;
    $isSameVdo = 'false';
  }
}else{
  $isSameVdo = 'true';
  $samevdo = 0;
}


site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');

if ($_FILES["upload_contact"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $csv_file = $full_pathurl . "uploads/group_contact/" . $filename;
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
  site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " CSV File Uploading on " . date("Y-m-d H:i:s"), '../');


}

if ($samevdo == 1) {
  $variable_count--;
}

site_log_generate("Compose RCS Page:  User : " . $_SESSION['yjwatsp_user_name'] . " CSV Uploaded on " . date("Y-m-d H:i:s"), '../');


$image_size = $_FILES['file_image_header']['size'];
$image_type = $_FILES['file_image_header']['type'];
$file_type = explode("/", $image_type);

$img_filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
$img_location = "../uploads/whatsapp_images/" . $img_filename;
$imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
$imageFileType = strtolower($imageFileType);
//$location = $site_url . "uploads/whatsapp_images/" . $filename;

/* Valid extensions */
// $valid_extensions = array("png", "jpg", "jpeg");

if ($file_type[1] == "mp3" || $file_type[1] == "mpeg") {
  // This code is for Audio
  $msg_type = 'AUDIO';
} else if ($file_type[1] == "mp4" || $file_type[1] == "avi" || $file_type[1] == "mov" || $file_type[1] == "mkv") {
  // This code is for videos
  $msg_type = 'VIDEO';
} else if ($file_type[1] == "png" || $file_type[1] == "jpg" || $file_type[1] == "jpeg") {
  // This code is for images
  $msg_type = 'IMAGE';
}
$valid_extensions = array("png", "jpg", "jpeg", "mp3", "mp4", "avi", "mov", "mkv");

$rspns = '';
/* Check file extension */
/* Upload file */

if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $img_location)) {
  $rspns = $img_location;
  site_log_generate("Compose RCS Page " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
   $location_1 = '["' . $site_url . "uploads/whatsapp_images/" . $img_filename . '"]';

}


if ($file_image_header_url) {
  if (preg_match('/\.(jpeg|jpg|png)$/i', $file_image_header_url)) {
    // Successful match!
    $msg_type = "IMAGE";
    $location_1 = '["' . $file_image_header_url . '"]';
} else if(preg_match('/\.(mp4|mov|mkv|avi)$/i', $file_image_header_url)){
    // An Video file extension
    $msg_type = "VIDEO";
    $location_1 = '["' . $file_image_header_url . '"]';
}
}



$request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
$bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
if ($location == '') {
  $location = '-';
}

if ($msg_type == 'TEXT') {
  if ($rdo_newex_group == 'N') {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "receiver_nos_path" : "' . $csv_file . '",
        "messages":"' . $txt_group_name . '",
        "variable_count":"' . $variable_count . '",
        "request_id":"' . $request_id . '"
        }';
  } else {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "receiver_nos_path" : "' . $csv_file . '",
        "messages":"' . $txt_group_name . '",
        "variable_count":"' . $variable_count . '",';
    $replace_txt .= '"request_id":"' . $request_id . '"
      }';
  }
} else {
  if ($rdo_newex_group == 'N') {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "receiver_nos_path" : "' . $csv_file . '",      
        "messages":"' . $txt_group_name . '",
        "request_id":"' . $request_id . '",
        "variable_count":"' . $variable_count . '",
        "is_same_media":' . $isSameVdo . ',
        "media_url":' . $location_1 . '

      }';
  } else {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "messages":"' . $txt_group_name . '",
        "receiver_nos_path" : "' . $csv_file . '",
        "request_id":"' . $request_id . '",
        "variable_count":"' . $variable_count . '",
        "is_same_media":' . $isSameVdo . '';
    if ($isSameVdo == 'true' && $isSameTxt == 'false') {
      $replace_txt .= ',
        "media_url":' . $location_1 . '
      }';
    } else {
      $replace_txt .= '}';
    }
  }
}
$curl = curl_init();
curl_setopt_array(
  $curl,
  array(
    CURLOPT_URL => $api_url . '/rcs_compose',
    // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  )
);


site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
$response = curl_exec($curl);
curl_close($curl);
if ($response == '') { ?>
  <script>
    window.location = "logout";
  </script>
<? }
$respobj = json_decode($response);
site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . date("Y-m-d H:i:s"), '../');
$rsp_id = $respobj->response_status;
if ($respobj->response_status == 403) {?>
  <script>
    window.location = "logout";
  </script>
  <?
  $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
  site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . date("Y-m-d H:i:s"), '../');
} elseif ($respobj->response_status == 201) {
  $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
  site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . date("Y-m-d H:i:s"), '../');
} elseif ($respobj->response_status == 200) {
$responses = '';
  if ($respobj->invalid_count) {
    $responses .= "Invalid Count : " . $respobj->invalid_count;
  } ;
  $json = array("status" => 1, "msg" => "Template Created Successfully..!</br>" . $responses);
  //$json = array("status" => 1, "msg" => "Success");
  site_log_generate("Compose RCS Page  : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . date("Y-m-d H:i:s"), '../');
}
}
// Compose RCS Page compose_rcs - End


// Compose RCS Page compose_rcs - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "compose_rcs") {

site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');
// Get data
$txt_list_mobno = htmlspecialchars(strip_tags(isset($_REQUEST['txt_list_mobno']) ? $conn->real_escape_string($_REQUEST['txt_list_mobno']) : ""));
$txt_list_mobno = str_replace("\\r\\n", '', $txt_list_mobno);
$rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
$rdo_sameperson_video = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_sameperson_video']) ? $_REQUEST['rdo_sameperson_video'] : ""));
$txt_group_name = urldecode($conn->real_escape_string($_REQUEST['textarea']));
$txt_group_name = str_replace("'", "\'", $txt_group_name);
$txt_group_name = str_replace('"', '\"', $txt_group_name);
$txt_group_name = str_replace("\\r\\n", '\n', $txt_group_name);
$txt_group_name = str_replace('&amp;', '&', $txt_group_name);
$txt_group_name = str_replace(PHP_EOL, '\n', $txt_group_name);
$txt_group_name = str_replace('\\&quot;', '"', $txt_group_name);
$txt_group_name = str_replace('"', '\"', $txt_group_name);
// echo  $txt_group_name; exit ;

$upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));
$media_type = htmlspecialchars(strip_tags(isset($_REQUEST['media_type']) ? $_REQUEST['media_type'] : ""));
$file_image_header = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header']) ? $_REQUEST['file_image_header'] : ""));
$file_image_header_url = htmlspecialchars(strip_tags(isset($_REQUEST['file_image_header_url']) ? $_REQUEST['file_image_header_url'] : ""));

$msg_type = 'TEXT';
$isSameTxt = 'false';
if ($rdo_newex_group == 'N') {
  $isSameTxt = 'true';
} else {
  // Define a regular expression pattern
  $pattern = '/{{(\w+)}}/';
  // Perform the regular expression match
  $matches_patterns = [];
  preg_match_all($pattern, $txt_group_name, $matches_patterns);
  // $matches[0] will contain an array of all matches
  $variable_values = $matches_patterns[0];
  // Output the count of valid numeric placeholders
  $variable_count = count($variable_values);
}


if ($rdo_sameperson_video == 'S' || $rdo_sameperson_video == 'N') {
  $isSameVdo = 'true';
  $samevdo = 0;
} elseif ($rdo_sameperson_video == 'P') {
if($media_type == 'V'){
$msg_type = 'VIDEO';
}else{
$msg_type = 'IMAGE';
}
  $variable_count++;
  $samevdo = 1;
  $isSameVdo = 'false';
}

site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');

if ($_FILES["upload_contact"]["name"] != '') {
  $path_parts = pathinfo($_FILES["upload_contact"]["name"]);
  $extension = $path_parts['extension'];
  $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
  /* Location */
  $location = "../uploads/group_contact/" . $filename;
  $csv_file = $full_pathurl . "uploads/group_contact/" . $filename;
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
  site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " CSV File Uploading on " . date("Y-m-d H:i:s"), '../');


}

if ($samevdo == 1) {
  $variable_count--;
}

site_log_generate("Compose RCS Page:  User : " . $_SESSION['yjwatsp_user_name'] . " CSV Uploaded on " . date("Y-m-d H:i:s"), '../');


$image_size = $_FILES['file_image_header']['size'];
$image_type = $_FILES['file_image_header']['type'];
$file_type = explode("/", $image_type);

$img_filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
$img_location = "../uploads/whatsapp_images/" . $img_filename;
$imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
$imageFileType = strtolower($imageFileType);
//$location = $site_url . "uploads/whatsapp_images/" . $filename;

/* Valid extensions */
// $valid_extensions = array("png", "jpg", "jpeg");

if ($file_type[1] == "mp3" || $file_type[1] == "mpeg") {
  // This code is for Audio
  $msg_type = 'AUDIO';
} else if ($file_type[1] == "mp4" || $file_type[1] == "avi" || $file_type[1] == "mov" || $file_type[1] == "mkv") {
  // This code is for videos
  $msg_type = 'VIDEO';
} else if ($file_type[1] == "png" || $file_type[1] == "jpg" || $file_type[1] == "jpeg") {
  // This code is for images
  $msg_type = 'IMAGE';
}
$valid_extensions = array("png", "jpg", "jpeg", "mp3", "mp4", "avi", "mov", "mkv");

$rspns = '';
/* Check file extension */
/* Upload file */

if (move_uploaded_file($_FILES['file_image_header']['tmp_name'], $img_location)) {
  $rspns = $img_location;
  site_log_generate("Compose RCS Page " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
   $location_1 = '["' . $site_url . "uploads/whatsapp_images/" . $img_filename . '"]';

}

if ($file_image_header_url) {
  if (preg_match('/\.(jpeg|jpg|png)$/i', $file_image_header_url)) {
    // Successful match!
    $msg_type = "IMAGE";
    $location_1 = '["' . $file_image_header_url . '"]';
} else if(preg_match('/\.(mp4|mov|mkv|avi)$/i', $file_image_header_url)){
    // An Video file extension
    $msg_type = "VIDEO";
    $location_1 = '["' . $file_image_header_url . '"]';
}
}

$request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
$bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
if ($location == '') {
  $location = '-';
}

if ($msg_type == 'TEXT') {
  if ($rdo_newex_group == 'N') {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "receiver_nos_path" : "' . $csv_file . '",
        "messages":"' . $txt_group_name . '",
        "variable_count":"' . $variable_count . '",
        "request_id":"' . $request_id . '"
        }';
  } else {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "receiver_nos_path" : "' . $csv_file . '",
        "messages":"' . $txt_group_name . '",
        "variable_count":"' . $variable_count . '",';
    $replace_txt .= '"request_id":"' . $request_id . '"
      }';
  }
} else {
  if ($rdo_newex_group == 'N') {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "receiver_nos_path" : "' . $csv_file . '",      
        "messages":"' . $txt_group_name . '",
        "request_id":"' . $request_id . '",
        "variable_count":"' . $variable_count . '",
        "is_same_media":' . $isSameVdo . ',
        "media_url":' . $location_1 . '

      }';
  } else {
    $replace_txt = '{
        "message_type":"' . $msg_type . '",
        "is_same_msg":' . $isSameTxt . ',
        "messages":"' . $txt_group_name . '",
        "receiver_nos_path" : "' . $csv_file . '",
        "request_id":"' . $request_id . '",
        "variable_count":"' . $variable_count . '",
        "is_same_media":' . $isSameVdo . '';
    if ($isSameVdo == 'true' && $isSameTxt == 'false') {
      $replace_txt .= ',
        "media_url":' . $location_1 . '
      }';
    } else {
      $replace_txt .= '}';
    }
  }
}
$curl = curl_init();
curl_setopt_array(
  $curl,
  array(
    CURLOPT_URL => $api_url . '/rcs_compose',
    // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  )
);


site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
$response = curl_exec($curl);
curl_close($curl);
if ($response == '') { ?>
  <script>
    window.location = "logout";
  </script>
<? }
$respobj = json_decode($response);
site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . date("Y-m-d H:i:s"), '../');
$rsp_id = $respobj->response_status;
if ($respobj->response_status == 403) {?>
  <script>
    window.location = "logout";
  </script>
  <?
  $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
  site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . date("Y-m-d H:i:s"), '../');
} elseif ($respobj->response_status == 201) {
  $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
  site_log_generate("Compose RCS Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . date("Y-m-d H:i:s"), '../');
} elseif ($respobj->response_status == 200) {
$responses = '';
  if ($respobj->invalid_count) {
    $responses .= "Invalid Count : " . $respobj->invalid_count;
  } ;
  $json = array("status" => 1, "msg" => "Template Created Successfully..!</br>" . $responses);
  //$json = array("status" => 1, "msg" => "Success");
  site_log_generate("Compose RCS Page  : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . date("Y-m-d H:i:s"), '../');
}
}
// Compose RCS Page compose_rcs - End


// send_reject_campaign_rcs - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_reject_campaign_rcs") {
  site_log_generate("send_reject_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');
  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $reason = htmlspecialchars(strip_tags(isset($_REQUEST['reason']) ? $_REQUEST['reason'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $replace_txt = '{
"request_id":"'.$request_id.'",
    "selected_user_id":"'.$select_user_id.'",
    "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
    "compose_message_id":"'.$compose_message_id.'",
    "product_name" : "RCS",
    "reason" : "'.$reason.'"
  }';

  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/reject_campaign', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));

  site_log_generate("send_reject_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_reject_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_reject_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_reject_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_reject_campaign_rcs Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_reject_campaign_rcs  - End

// send_reject_campaign_sms - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_reject_campaign_sms") {
  site_log_generate("send_reject_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');
  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $reason = htmlspecialchars(strip_tags(isset($_REQUEST['reason']) ? $_REQUEST['reason'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $replace_txt = '{
"request_id":"'.$request_id.'",
    "selected_user_id":"'.$select_user_id.'",
    "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
    "compose_message_id":"'.$compose_message_id.'",
    "product_name" : "GSM SMS",
    "reason" : "'.$reason.'"
  }';
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/approve_user/reject_campaign', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  ));

  site_log_generate("send_reject_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_reject_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_reject_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_reject_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_reject_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
// send_reject_campaign_sms - End

// purchase_sms_credit Page purchase_sms_credit - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "purchase_sms_credit") {
  site_log_generate("Purchase SMS Credit Page : User : ".$_SESSION['yjwatsp_user_name']." Purchase SMS Credit - access this page on ".date("Y-m-d H:i:s"), '../');
  // Get data
  $txt_pricing_plan = htmlspecialchars(strip_tags(isset($_REQUEST["txt_pricing_plan"]) ? $conn->real_escape_string($_REQUEST["txt_pricing_plan"]) : ""));
  $txt_message_amount = htmlspecialchars(strip_tags(isset($_REQUEST["txt_message_amount"]) ? $conn->real_escape_string($_REQUEST["txt_message_amount"]) : ""));
  $usrcrdbt_comments = htmlspecialchars(strip_tags(isset($_REQUEST["usrcrdbt_comments"]) ? $conn->real_escape_string($_REQUEST["usrcrdbt_comments"]) : "-"));

  $cnt_insrt = 0;
  $slt_expiry_date = 12; // 12 Months
  $exp_date = date("Y-m-d H:i:s", strtotime('+'.$slt_expiry_date.' month'));
  $expl = explode("~~", $txt_pricing_plan);

  /*$paid_status = 'A';
  $paid_status_cmnts = "Direct Approval. Collect the Money from them, before Credit the Messages";*/
  if($_SESSION['yjwatsp_user_master_id'] != 1) {
    $paid_status = 'W';
    $paid_status_cmnts = 'NULL';
  }

  $replace_txt = '{
    "user_id" : "'.$_SESSION['yjwatsp_user_id'].'",
    "parent_id" : "'.$_SESSION['yjwatsp_parent_id'].'",
    "pricing_slot_id" : "'.$expl[1].'",
    "exp_date" : "'.$exp_date.'",
    "slt_expiry_date" : "'.$slt_expiry_date.'",
    "raise_sms_credits" : "'.$expl[3].'",
    "sms_amount" : "'.$hdsms.'",
    "paid_status_cmnts" : "'.$paid_status_cmnts.'",
    "paid_status" : "'.$paid_status.'",
    "usrcrdbt_comments" : "'.$usrcrdbt_comments.'"
  }';

  // To Get Api URL
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url.'/purchase_credit/user_credit_raise',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );
  site_log_generate("Approve Whatsappno Page : ".$_SESSION['yjwatsp_user_name']." Execute the service (user_sms_credit_raise) [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  $sms = json_decode($response, false);
  site_log_generate("Approve Whatsappno Page : ".$_SESSION['yjwatsp_user_name']." get the Service response (user_sms_credit_raise) [$response] on ".date("Y-m-d H:i:s"), '../');
  if($sms->response_status == 200) {
    $cnt_insrt++;
  }

  if($cnt_insrt > 0) {
    site_log_generate("Purchase SMS Credit Page : User : ".$_SESSION['yjwatsp_user_name']." Purchase SMS Credit Success on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 1, "msg" => "Success");
  } else {
    site_log_generate("Purchase SMS Credit Page : User : ".$_SESSION['yjwatsp_user_name']." Purchase SMS Credit failed [Data not inserted] on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 0, "msg" => "Data not inserted. Kindly try again!!");
  }
}
// purchase_sms_credit Page purchase_sms_credit - End

// Message Credit Page message_credit - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "message_credit") {
  site_log_generate("Message Credit Page : User : ".$_SESSION['yjwatsp_user_name']." access the page on ".date("Y-m-d H:i:s"), '../');
  // Get data
  $txt_product_name = htmlspecialchars(strip_tags(isset($_REQUEST['txt_product_name']) ? $conn->real_escape_string($_REQUEST['txt_product_name']) : ""));
  $txt_receiver_user = htmlspecialchars(strip_tags(isset($_REQUEST['txt_receiver_user']) ? $conn->real_escape_string($_REQUEST['txt_receiver_user']) : ""));
  $txt_message_count = htmlspecialchars(strip_tags(isset($_REQUEST['txt_message_count']) ? $conn->real_escape_string($_REQUEST['txt_message_count']) : ""));
  $hid_usrsmscrd_id = htmlspecialchars(strip_tags(isset($_REQUEST['hid_usrsmscrd_id']) ? $conn->real_escape_string($_REQUEST['hid_usrsmscrd_id']) : ""));
  site_log_generate("Message Credit Page : Username => ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  $productid = explode("~~", $txt_product_name);
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);

  switch($productid[5] || $productid[1]) {
    case ($productid[5] == 'WHATSAPP' || $productid[1] == 'WHATSAPP'):
      $productid = 1;
      break;
    case ($productid[5] == 'GSM SMS' || $productid[1] == 'GSM SMS'):
      $productid = 2;
      break;
    case ($productid[5] == 'RCS' || $productid[1] == 'RCS'):
      $productid = 3;
      break;
     case ($productid[5] == 'OBD CALL SIP' || $productid[1] == 'OBD CALL SIP'):
        $productid = 4;
        break;
    default:

  }
  // To Send the request  API
  if($hid_usrsmscrd_id != '') {
    $replace_txt = '{
      "user_id" : "'.$_SESSION['yjwatsp_user_id'].'",
      "product_id" : "'.$productid.'",
      "parent_user" : "'.$_SESSION['yjwatsp_user_id'].'~~'.$_SESSION['yjwatsp_user_name'].'",
      "receiver_user" : "'.$txt_receiver_user.'",
      "message_count" : "'.$txt_message_count.'",
      "credit_raise_id" : "'.$hid_usrsmscrd_id.'",
      "request_id" : "'.$request_id.'"
    }'; // exit;
  } else {
    $replace_txt = '{
      "user_id" : "'.$_SESSION['yjwatsp_user_id'].'",
      "product_id" : "'.$productid.'",
      "receiver_user" : "'.$txt_receiver_user.'",
      "message_count" : "'.$txt_message_count.'",
      "request_id" : "'.$request_id.'"
    }';
  }

  //add bearer token
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';
  // It will call "add_message_credit" API to verify, can we access for the add_message_credit list  
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url.'/purchase_credit/add_message_credit',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );
  // Send the data into API and execute 
  site_log_generate("Message Credit Page : ".$_SESSION['yjwatsp_user_name']." Execute the service [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  // After got response decode the JSON result
  $header = json_decode($response, false);
  // print_r($header);
  site_log_generate("Message Credit Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');

  if($header->response_status == 200) {
    site_log_generate("Message Credit Page : ".$user_name." Message Credit updation Success on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 1, "msg" => "Message Credit updated.");
  } else if($header->response_status == 201) {
    site_log_generate("Message Credit Page : ".$user_name."get the Service response [$header->response_status] on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 2, "msg" => $header->response_msg);
  } else {
    site_log_generate("Message Credit Page : ".$user_name." Message Credit updation Failed [Invalid Inputs] on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 0, "msg" => "Message Credit updation failed [Invalid Inputs]. Kindly try again with the correct Inputs!");
  }
}
// Message Credit Page message_credit - End


// send_stop_campaign Page send_stop_campaign - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_stop_campaign") {
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);

  $campaign_name = htmlspecialchars(strip_tags(isset($_GET["campaign_name"]) ? $conn->real_escape_string($_GET["campaign_name"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
"request_id":"'.$request_id.'",
"user_product":"WHATSAPP",
"campaign_name" : "'.$campaign_name.'",
"sender_numbers" : ["'.$mobile_number.'"]
    }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/wtsp/stop_campaign',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("send_stop_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/wtsp/stop_campaign] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_stop_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_stop_campaignp Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_stop_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_stop_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_stop_campaign page send_stop_campaign - end 

// send_restart_campaign Page send_restart_campaign - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_restart_campaign") {
  site_log_generate("send_restart_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);

  $compose_message_id = htmlspecialchars(strip_tags(isset($_GET["compose_message_id"]) ? $conn->real_escape_string($_GET["compose_message_id"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
"request_id":"'.$request_id.'",
"user_product" : "WHATSAPP",
"compose_whatsapp_id" : "'.$compose_message_id.'",
"sender_numbers" : ["'.$mobile_number.'"]
    }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/wtsp/restart_campaign',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("send_restart_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/wtsp/restart_campaign] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_restart_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_restart_campaignp Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_start_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_start_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_start_campaign page send_start_campaign - end 

// app_update Page app_update - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "app_update_version") {
  site_log_generate("Add Contacts in Group Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $mobile_numbers = htmlspecialchars(strip_tags(isset($_GET['mobile_numbers']) ? $_GET['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);
  $app_update_id = htmlspecialchars(strip_tags(isset($_GET["app_update_id"]) ? $conn->real_escape_string($_GET["app_update_id"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
"request_id":"'.$request_id.'",
"app_update_id" :  "'.$app_update_id.'",
 "sender_numbers" : ["'.$mobile_number.'"]
    }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/app_update/update_version',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("app_update Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/app_update/update_version] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("app_update Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("app_update Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("app_update Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("app_update Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  app_update_version page app_update_version - end 


// send_stop_campaign_sms Page send_stop_campaign_sms - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_stop_campaign_sms") {
  site_log_generate("send_stop_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);

  $campaign_name = htmlspecialchars(strip_tags(isset($_GET["campaign_name"]) ? $conn->real_escape_string($_GET["campaign_name"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
"request_id":"'.$request_id.'",
"user_product":"GSM SMS",
"campaign_name" : "'.$campaign_name.'",
"sender_numbers" : ["'.$mobile_number.'"]
    }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/wtsp/stop_campaign',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("send_stop_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/wtsp/stop_campaign] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_stop_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_stop_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_stop_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_stop_campaign_sms Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_stop_campaign page send_stop_campaign - end 

// send_restart_campaign Page send_restart_campaign - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_restart_campaign_sms") {
  site_log_generate("send_restart_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $mobile_numbers = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_numbers']) ? $_REQUEST['mobile_numbers'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $mobile_number = str_replace(',', '","', $mobile_numbers);

  $compose_message_id = htmlspecialchars(strip_tags(isset($_GET["compose_message_id"]) ? $conn->real_escape_string($_GET["compose_message_id"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
"request_id":"'.$request_id.'",
"user_product" : "GSM SMS",
"compose_whatsapp_id" : "'.$compose_message_id.'",
"sender_numbers" : ["'.$mobile_number.'"]
    }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/wtsp/restart_campaign',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("send_restart_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/wtsp/restart_campaign] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_restart_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_restart_campaignp Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_start_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_start_campaign Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_start_campaign page send_start_campaign - end 


// compose_prompt  Page compose_prompt - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_compose_prompt") {
  site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . $current_date, '../');

  // Get data
  $call_type = htmlspecialchars(strip_tags(isset($_REQUEST['call_type']) ? $conn->real_escape_string($_REQUEST['call_type']) : ""));
  $language_code = htmlspecialchars(strip_tags(isset($_REQUEST['language_code']) ? $conn->real_escape_string($_REQUEST['language_code']) : ""));
  $location_state = htmlspecialchars(strip_tags(isset($_REQUEST['location']) ? $_REQUEST['location'] : ""));
  $type = htmlspecialchars(strip_tags(isset($_REQUEST['type']) ? $_REQUEST['type'] : ""));
  $upload_prompt = htmlspecialchars(strip_tags(isset($_REQUEST['upload_prompt']) ? $_REQUEST['upload_prompt'] : ""));
  $company_name = htmlspecialchars(strip_tags(isset($_REQUEST['company_name']) ? $_REQUEST['company_name'] : ""));
  $context = htmlspecialchars(strip_tags(isset($_REQUEST['context_value']) ? $_REQUEST['context_value'] : ""));
  $prompt_remarks = htmlspecialchars(strip_tags(isset($_REQUEST['prompt_remarks']) ? $_REQUEST['prompt_remarks'] : ""));
  $prompt_second = htmlspecialchars(strip_tags(isset($_REQUEST['prompt_second']) ? $_REQUEST['prompt_second'] : ""));

  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);

  $location_state_1 = explode("~~", $location_state);
  $location_state_id = $location_state_1[1];

  $language_code_1 = explode("~~", $language_code);
  $language_code_id = $language_code_1[1];

  $replace_txt = '{';

  if($prompt_second){
    $replace_txt .= '"prompt_second":"' . $prompt_second . '",' ;
  }

  $parts = explode("~~", $value);

  if ($_FILES["upload_prompt"]["name"] != '') {
    $path_parts = pathinfo($_FILES["upload_prompt"]["name"]);
    $extension = $path_parts['extension'];
    $filename = $_SESSION['yjwatsp_user_id'] . "_prompt_" . $milliseconds . "." . $extension;
    /* Location */
    $location = "../uploads/upload_prompt/" . $filename;
    $prompt_file = "converted_uploads/upload_prompt/" . $filename;
    $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);

    /* Valid extensions */
    // $valid_extensions = array(".wav,.mp3");
    $response = 0;
    /* Check file extension */
    /* Upload file */
    if (move_uploaded_file($_FILES['upload_prompt']['tmp_name'], $location)) {
      $response = $location;
      $replace_txt .= '"upload_prompt":"' . $prompt_file . '",';

    }
    site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " prompt File Uploading on " . $current_date, '../');
  }

$destinationFile = "../converted_uploads/upload_prompt/" . $filename;

// Ensure the source file exists
if (!file_exists($location)) {
  $json = array("status" => 0, "msg" => "File not stored!");
}

// SoX command to convert audio file
$command = "sox $location -r 8000 -c 1 $destinationFile";

// Execute the command
exec($command, $output, $return_var);

// Check if the command was successful
if ($return_var == 0) {
    // echo "Conversion successful!";
} else {
  $json = array("status" => 0, "msg" => "Wav file convertion failed!");
}

  $replace_txt .= '
      "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
      "request_id":"' . $request_id . '",
      "call_type":"' . $call_type . '",
      "language_code":"' . $language_code_id . '",
      "type":"' . $type . '",
      "company_name":"' . $company_name . '",
      "context":"' . $context . '",
      "location": "' . $location_state_id . '",
      "prompt_remarks":"' . $prompt_remarks . '"     
    }';

  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url . '/obd_call/create_prompt', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  )
  );

  site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . $current_date, '../');
  $rsp_id = $respobj->response_status;
  if ($rsp_id == 403) {
    ?>
    <!-- <script>window.location = "logout"</script> -->
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . $current_date, '../');
  } elseif ($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . $current_date, '../');
  } elseif ($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Prompt Created Successfully!!");
    site_log_generate("send_compose_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . $current_date, '../');
  }

}
// compose_prompt  Page compose_prompt - end 

// Approve_campaign Page Approve_campaign - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_approve_prompt") {
  site_log_generate("send_approve_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . $current_date, '../');

  // Get data
  $prompt_id = htmlspecialchars(strip_tags(isset($_REQUEST['prompt_id']) ? $conn->real_escape_string($_REQUEST['prompt_id']) : ""));
  $context = htmlspecialchars(strip_tags(isset($_REQUEST['context']) ? $conn->real_escape_string($_REQUEST['context']) : ""));
  $prompt_status = htmlspecialchars(strip_tags(isset($_REQUEST['prompt_status']) ? $_REQUEST['prompt_status'] : ""));

  $reason = htmlspecialchars(strip_tags(isset($_REQUEST['reason']) ? $_REQUEST['reason'] : ""));

  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
  $replace_txt .= '{
"request_id":"' . $request_id . '",
   "prompt_id":"' . $prompt_id . '",
      "context":"' . $context . '",
      "prompt_status":"' . $prompt_status . '"';

  if ($prompt_status == 'R') {
    $replace_txt .= ',"reason":"' . $reason . '"';
  }

  $replace_txt .= '}';


  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';

  site_log_generate("send_approve_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . $current_date, '../');

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url . '/list/update_prompt_status', // Create a New Group
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => array(
      $bearer_token,
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  )
  );


  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_approve_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . $current_date, '../');
  $rsp_id = $respobj->response_status;
  if ($rsp_id == 403) { ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_approve_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . $current_date, '../');
  } elseif ($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("send_approve_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . $current_date, '../');
  } elseif ($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_approve_prompt Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . $current_date, '../');
  }

}
//  Approve_campaign page Approve_campaign - end 

// compose_obd Page compose_obd - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "compose_obd") {
  site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . $current_date, '../');
  // Get data
  $call_retry_count = htmlspecialchars(strip_tags(isset($_REQUEST['call_retry_count']) ? $conn->real_escape_string($_REQUEST['call_retry_count']) : ""));
  $rdo_newex_group = htmlspecialchars(strip_tags(isset($_REQUEST['rdo_newex_group']) ? $_REQUEST['rdo_newex_group'] : ""));
  $retry_time = htmlspecialchars(strip_tags(isset($_REQUEST['retry_time']) ? $_REQUEST['retry_time'] : ""));
  $slt_context = htmlspecialchars(strip_tags(isset($_REQUEST['slt_context']) ? $_REQUEST['slt_context'] : ""));
  $upload_contact = htmlspecialchars(strip_tags(isset($_REQUEST['upload_contact']) ? $_REQUEST['upload_contact'] : ""));
  $filename_upload = htmlspecialchars(strip_tags(isset($_REQUEST['filename_upload']) ? $_REQUEST['filename_upload'] : ""));
 $send_sms = htmlspecialchars(strip_tags(isset($_REQUEST['send_sms']) ? $_REQUEST['send_sms'] : ""));
 $sms_duration_sec = htmlspecialchars(strip_tags(isset($_REQUEST['sms_duration_sec']) ? $_REQUEST['sms_duration_sec'] : ""));
 $sms_message = htmlspecialchars(strip_tags(isset($_REQUEST['sms_message']) ? $_REQUEST['sms_message'] : ""));


  $slt_context = explode("~~", $slt_context);
  $slt_context_id = $slt_context[0];

  $isSameTxt = 'false';

  
  if ($rdo_newex_group == 'G') {
    $isSameTxt = 'true';
  }

  $message_type = '';
  if ($rdo_newex_group == 'G') {
    $message_type = "Generic";
  } else if ($rdo_newex_group == 'C') {
    $message_type = "Customiz";
  } else if ($rdo_newex_group == 'P') {
    $message_type = "Personal";
  }

  site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . $current_date, '../');

$csv_file = $full_pathurl . "uploads/compose_variables/" . $filename_upload;

  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  
if($csv_file == '/var/www/html/messenger_hub/uploads/compose_variables/'){
 $json = array("status" => 2, "msg" => "Please reload the page and create the campaign");
  }

 $replace_txt .= '{';

  if ($send_sms == 'Y') {
 $replace_txt .= '"sms_message":"' . $sms_message . '",
          "sms_duration": "' . $sms_duration_sec . '",
          "send_sms" : "' . $send_sms . '",';
}

  $replace_txt .= '
          "call_retry_count":"' . $call_retry_count . '",
          "is_same_msg": "' . $isSameTxt . '",
          "receiver_nos_path" : "' . $csv_file . '",
          "retry_time" : "' . $retry_time . '",
          "slt_context_id" : "' . $slt_context_id . '",
          "request_id":"' . $request_id . '",
          "message_type":"' . $message_type . '"
          }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/obd_call/compose_obd',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );


  site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>
      window.location = "logout";
    </script>
  <? }
  $respobj = json_decode($response);
  site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . $current_date, '../');
  $rsp_id = $respobj->response_status;
  if ($respobj->response_status == 403) { ?>
    <script>
      window.location = "logout";
    </script>
    <?
    $json = array("status" => 2, "msg" => "Invalid User, Kindly try with valid User!!");
    site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . $current_date, '../');
  } elseif ($respobj->response_status == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . $current_date, '../');
  } elseif ($respobj->response_status == 200) {
    $responses = '';
    if ($respobj->invalid_count) {
      $responses .= "Invalid Count : " . $respobj->invalid_count;
    }
    ;
    $json = array("status" => 1, "msg" => "Template Created Successfully..!</br>" . $responses);
    site_log_generate("compose_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . $current_date, '../');
  }
}
// compose_obd Page compose_obd - End 

// Approve_campaign send_approve_campaign_obd Page send_approve_campaign_obd - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_approve_campaign_obdsip") {
  site_log_generate("send_approve_campaign_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . $current_date, '../');

  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $campaign_name = htmlspecialchars(strip_tags(isset($_REQUEST['campaign_name']) ? $_REQUEST['campaign_name'] : ""));
  $context = htmlspecialchars(strip_tags(isset($_REQUEST['context']) ? $_REQUEST['context'] : ""));

  $channel_names = htmlspecialchars(strip_tags(isset($_REQUEST['server_names']) ? $_REQUEST['server_names'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
  $channel_percentage = array();
  if (isset($_POST['channel_percentage'])) {
    $channel_percentages = json_decode($_POST['channel_percentage'], true);

    if (is_array($channel_percentages)) {
        foreach ($channel_percentages as $percentage) {
          $channel_percentage[] = $percentage;
        }
    }
  }
// Split the string by commas to get each channel pair
$channelPairs = explode(',', $channel_names);


$channelnames = [];
$channelids = [];

// Loop through each channel pair and split by '~~'
foreach ($channelPairs as $pair) {
    list($channel, $value) = explode('~~', $pair);
    $channelnames[] = $channel;
    $channelids[] = $value;
}


  $replace_txt = '{
  "request_id":"' . $request_id . '",
      "selected_user_id":"' . $select_user_id . '",
      "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
      "compose_message_id":"' . $compose_message_id . '",
      "channel_ids" : '.json_encode($channelids).',
      "channel_percentage" :'.json_encode($channel_percentage).'
    }';

  //"campaign_name":"'.$campaign_name.'",
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url . '/approve_user/approve_obd', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  )
  );

  site_log_generate("send_approve_campaign_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_approve_campaign_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . $current_date, '../');
  $rsp_id = $respobj->response_status;
  if ($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_approve_campaign_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . $current_date, '../');
  } elseif ($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("send_approve_campaign_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . $current_date, '../');
  } elseif ($rsp_id == 200) {
    $json = array("status" => 1, "msg" => $respobj->response_msg);
    site_log_generate("send_approve_campaign_obd Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . $current_date, '../');
  }

}
//  Approve_campaign send_approve_campaign_obd page send_approve_campaign_obd - end

// send_reject_campaign_obdsip - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_reject_campaign_obdsip") {
  site_log_generate("send_reject_campaign_obdsip Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . $current_date, '../');
  // Get data
  $compose_message_id = htmlspecialchars(strip_tags(isset($_REQUEST['compose_message_id']) ? $conn->real_escape_string($_REQUEST['compose_message_id']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST['select_user_id']) ? $conn->real_escape_string($_REQUEST['select_user_id']) : ""));
  $reason = htmlspecialchars(strip_tags(isset($_REQUEST['reason']) ? $_REQUEST['reason'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);
  $replace_txt = '{
"request_id":"' . $request_id . '",
    "selected_user_id":"' . $select_user_id . '",
    "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
    "compose_message_id":"' . $compose_message_id . '",
     "product_name" : "OBD CALL SIP",
    "reason" : "' . $reason . '"
  }';

  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url . '/approve_user/reject_campaign', // Create a New Group
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
      "cache-control: no-cache",
      'Content-Type: application/json; charset=utf-8'
    ),
  )
  );

  site_log_generate("send_reject_campaign_obdsip Page : User : " . $_SESSION['yjwatsp_user_name'] . " api request [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_reject_campaign_obdsip Page : User : " . $_SESSION['yjwatsp_user_name'] . " api response [$response] on " . $current_date, '../');
  $rsp_id = $respobj->response_status;
  if ($rsp_id == 403) {
    ?>
    <script>window.location = "logout"</script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_reject_campaign_obdsip Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Invalid User, Kindly try with valid User!!] on " . $current_date, '../');
  } elseif ($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: " . $respobj->response_msg);
    site_log_generate("send_reject_campaign_obdsip Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Failure: $respobj->response_msg] on " . $current_date, '../');
  } elseif ($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_reject_campaign_obdsip Page : User : " . $_SESSION['yjwatsp_user_name'] . " [Success] on " . $current_date, '../');
  }
}
//  send_reject_campaign_obdsip  - End

// send_stop_campaign_sip Page send_stop_campaign_sip - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_stop_campaign_sip") {
  site_log_generate("send_stop_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $sip_id = htmlspecialchars(strip_tags(isset($_REQUEST['sip_id']) ? $_REQUEST['sip_id'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $sip_id = str_replace(',', '","', $sip_id);

  $campaign_id = htmlspecialchars(strip_tags(isset($_GET["campaign_id"]) ? $conn->real_escape_string($_GET["campaign_id"]) : ""));
 $selected_user_id = htmlspecialchars(strip_tags(isset($_GET["selected_user_id"]) ? $conn->real_escape_string($_GET["selected_user_id"]) : ""));
 $context_id = htmlspecialchars(strip_tags(isset($_GET["context_id"]) ? $conn->real_escape_string($_GET["context_id"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

  $replace_txt = '{
"request_id":"'.$request_id.'",
"selected_user_id":"'.$selected_user_id.'",
"campaign_id" : "'.$campaign_id.'",
"sip_id" : ["'.$sip_id.'"],
"context_id" : "'.$context_id.'"
    }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/obd_call/stop_campaign',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("send_stop_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/wtsp/stop_campaign] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  //echo $response;
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_stop_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_stop_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_stop_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_stop_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_stop_campaign page send_stop_campaign - end 

// send_restart_campaign_sip Page send_restart_campaign_sip - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "send_restart_campaign_sip") {
  site_log_generate("send_restart_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." access this page on ".date("Y-m-d H:i:s"), '../');

  // Get data
  $sip_id = htmlspecialchars(strip_tags(isset($_REQUEST['sip_id']) ? $_REQUEST['sip_id'] : ""));
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $sip_id_array = array_unique(explode(',', $sip_id));
  // $sip_id = str_replace(',', '","', $sip_id);
  $sip_id = '"' . implode('","', $sip_id_array) . '"';
$selected_user_id = htmlspecialchars(strip_tags(isset($_GET["selected_user_id"]) ? $conn->real_escape_string($_GET["selected_user_id"]) : ""));

  $campaign_id = htmlspecialchars(strip_tags(isset($_GET["campaign_id"]) ? $conn->real_escape_string($_GET["campaign_id"]) : ""));
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].'';

 $replace_txt = '{
"request_id":"'.$request_id.'",
"selected_user_id":"'.$selected_user_id.'",
"campaign_id" : "'.$campaign_id.'",
"sip_id" : ['.$sip_id.']
    }';


  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      // Create a New Group
      CURLOPT_URL => $api_url.'/obd_call/restart_campaign',
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
        "cache-control: no-cache",
        'Content-Type: application/json; charset=utf-8'
      ),
    )
  );

  site_log_generate("send_restart_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." api request [$replace_txt, APIURL - ".$api_url."/obd_call/restart_campaign] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  $respobj = json_decode($response);

  site_log_generate("send_restart_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." api response [$response] on ".date("Y-m-d H:i:s"), '../');
  $rsp_id = $respobj->response_status;
  if($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
    $json = array("status" => 2, "msg" => $respobj->response_msg);
    site_log_generate("send_restart_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." [Invalid User, Kindly try with valid User!!] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 201) {
    $json = array("status" => 0, "msg" => "Failure: ".$respobj->response_msg);
    site_log_generate("send_restart_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." [Failure: $respobj->response_msg] on ".date("Y-m-d H:i:s"), '../');
  } elseif($rsp_id == 200) {
    $json = array("status" => 1, "msg" => "Success");
    site_log_generate("send_restart_campaign_sip Page : User : ".$_SESSION['yjwatsp_user_name']." [Success] on ".date("Y-m-d H:i:s"), '../');
  }
}
//  send_restart_campaign_sip page send_restart_campaign_sip - end

// Finally Close all Opened Mysql DB Connection
$conn->close();

// Output header with JSON Response
header('Content-type: application/json');
echo json_encode($json);
