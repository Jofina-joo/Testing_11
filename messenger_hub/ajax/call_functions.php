<?php
session_start();
error_reporting(0);
// Include configuration.php
include_once('../api/configuration.php');
extract($_REQUEST);

$current_date = date("Y-m-d H:i:s");
$milliseconds = round(microtime(true) * 1000);

// Index Page Signin - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "signin") {
  // Get data
  $uname = htmlspecialchars(strip_tags(isset($_REQUEST['txt_username']) ? $conn->real_escape_string($_REQUEST['txt_username']) : ""));
  $password = htmlspecialchars(strip_tags(isset($_REQUEST['txt_password']) ? $conn->real_escape_string($_REQUEST['txt_password']) : ""));
  $upass = md5($password);
  $ip_address = $_SERVER['REMOTE_ADDR'];
  site_log_generate("Index Page : Username => ".$uname." trying to login on ".date("Y-m-d H:i:s"), '../');

  $replace_txt = '{
    "txt_username" : "'.$uname.'",
    "txt_password" : "'.$password.'",
    "request_id" : "'.rand(1000000000, 9999999999).'"
  }';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30, // Set timeout to 20 seconds
    CURLOPT_CONNECTTIMEOUT => 30, // Set timeout for the connection phase to 5 seconds
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));
  site_log_generate("Index Page : ".$uname." Execute the service [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
// Check for cURL errors and timeouts
if (curl_errno($curl) == CURLE_OPERATION_TIMEDOUT) {
 $json = array("status" => 0, "msg" => "Service not running, Kindly check the service!!");
} elseif (curl_errno($curl)) {
 $json = array("status" => 0, "msg" => "Service not running, Kindly check the service!!");
}
 curl_close($curl);

  $state1 = json_decode($response, false);
  site_log_generate("Index Page : ".$uname." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');

  if($state1->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }

  if($state1->response_status == 200) {
    for($indicator = 0; $indicator <= 1; $indicator++) {
      $_SESSION['yjwatsp_parent_id'] = $state1->parent_id;
      $_SESSION['yjwatsp_user_id'] = $state1->user_id;
      $_SESSION['yjwatsp_user_master_id'] = $state1->user_master_id;
      $_SESSION['yjwatsp_user_name'] = $state1->user_name;
      $_SESSION['yjwatsp_user_status'] = $state1->user_status;
      $_SESSION['yjwatsp_bearer_token'] = $state1->bearer_token;
    }

    site_log_generate("Index Page : ".$uname." logged in success on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 1, "info" => $result);
  } else {
    site_log_generate("Index Page : ".$uname." logged in failed [Sign in Failed] on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 0, "msg" => $state1->response_msg);
  }
}
// Index Page Signin - End

// Manage Users Page signup - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "onboarding_signup") {
  // Get data
  $user_name 			    = htmlspecialchars(strip_tags(isset($_REQUEST['clientname_txt']) ? $_REQUEST['clientname_txt'] : ""));
  $user_email 		    = htmlspecialchars(strip_tags(isset($_REQUEST['email_id_contact']) ? $_REQUEST['email_id_contact'] : ""));
  $user_mobile 		    = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_no_txt']) ? $_REQUEST['mobile_no_txt'] : ""));
  $loginid 				    = htmlspecialchars(strip_tags(isset($_REQUEST['login_id_txt']) ? $_REQUEST['login_id_txt'] : ""));
  $user_password= htmlspecialchars(strip_tags(isset($_REQUEST['txt_user_password']) ? $_REQUEST['txt_user_password'] : ""));
  $txt_confirm_password   = htmlspecialchars(strip_tags(isset($_REQUEST['txt_confirm_password']) ? $_REQUEST['txt_confirm_password'] : ""));


  site_log_generate("Manage Users Page : ".$loginid." trying to create a new account in our site on ".date("Y-m-d H:i:s"), '../');

  $replace_txt = '{
    "user_name" : "' . $user_name . '",
    "user_email" : "' . $user_email . '",
    "user_mobile" : "' . $user_mobile . '",
    "login_password" : "' . $user_password . '",
    "login_id" : "' . $loginid . '"
  }';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url . '/login/signup',
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
      'Content-Type: application/json'
    ),
  )
  );
  site_log_generate("Manage Users Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
 /* if($response == ''){?>
    <script>window.location="logout"</script>
  <? }*/

  $header = json_decode($response, false);
  site_log_generate("Manage Users Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
  if ($header->response_status == 403) { ?>
    <script>window.location="logout"</script>
  <? } 
  if ($header->num_of_rows > 0) {
      site_log_generate("Manage Users Page : ".$user_name." account created successfully on ".date("Y-m-d H:i:s"), '../');
      $json = array("status" => 1, "msg" => "New User created. Kindly login!!");
  }
  else {
      site_log_generate("Manage Users Page : ".$user_name." account creation Failed [$header->response_msg] on ".date("Y-m-d H:i:s"), '../');
      $json = array("status" => 0, "msg" => $header->response_msg);
  }
}
// Manage Users Page signup - End

// Change Password Page change_pwd - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $pwd_call_function == "change_pwd") {
  site_log_generate("Change Password Page : User : ".$_SESSION['yjwatsp_user_name']." access the page on ".date("Y-m-d H:i:s"), '../');
  // Get data
  $ex_password = htmlspecialchars(strip_tags(isset($_REQUEST['txt_ex_password']) ? $_REQUEST['txt_ex_password'] : ""));
  $new_password = htmlspecialchars(strip_tags(isset($_REQUEST['txt_new_password']) ? $_REQUEST['txt_new_password'] : ""));
  $ex_pass = $ex_password;
  $upass = $new_password;
  $request_id = $_SESSION['yjwatsp_user_id']."_".date("Y")."".date('z', strtotime(date("d-m-Y")))."".date("His")."_".rand(1000, 9999);
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].''; // add the bearer

  $replace_txt = '{
    "user_id" : "'.$_SESSION['yjwatsp_user_id'].'",
    "ex_password" : "'.$ex_pass.'",
    "new_password" : "'.$upass.'",
    "request_id":"'.$request_id.'"
  }';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/list/change_password',
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
  site_log_generate("Change Password Page : ".$_SESSION['yjwatsp_user_name']." Execute the service [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }

  $header = json_decode($response, false);

  if($header->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  site_log_generate("Change Password Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');

  $json = array("status" => $header->response_code, "msg" => $header->response_msg);
}
// Change Password Page change_pwd - End

// Sign Up Page signup - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "edit_onboarding") {
  // Get data
  $user_email = htmlspecialchars(strip_tags(isset($_REQUEST['email_id_contact']) ? $_REQUEST['email_id_contact'] : ""));
  $login_id = htmlspecialchars(strip_tags(isset($_REQUEST['login_id_txt']) ? $_REQUEST['login_id_txt'] : ""));
  $user_mobile = htmlspecialchars(strip_tags(isset($_REQUEST['mobile_no_txt']) ? $_REQUEST['mobile_no_txt'] : ""));
  $user_name = htmlspecialchars(strip_tags(isset($_REQUEST['clientname_txt']) ? $_REQUEST['clientname_txt'] : ""));
  site_log_generate("Sign Up Page : ".$loginid." trying to create a new account in our site on ".date("Y-m-d H:i:s"), '../');
  $request_id = rand(10000000, 99999999)."_".rand(10000000, 99999999);
  $replace_txt = '{
 "user_id":"'.$_SESSION['yjwatsp_user_id'].'",
    "login_id" : "'.$login_id.'",
    "user_name" : "'.$user_name.'",
    "user_email" : "'.$user_email.'",
    "user_mobile" : "'.$user_mobile.'"
  }';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url.'/list/edit_profile',
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
        'Content-Type: application/json'
      ),
    )
  );
  site_log_generate("Sign Up Page : ".$_SESSION['yjwatsp_user_name']." Execute the service [$replace_txt] on ".date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);

  $header = json_decode($response, false);
  site_log_generate("Sign Up Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');
  if($header->response_status == 403) { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
  if($header->response_status == 200) {
    site_log_generate("Sign Up Page : ".$user_name." account created successfully on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 1, "msg" => "User detailes updated successfully!");
  } else {
    site_log_generate("Sign Up Page : ".$user_name." account creation Failed [$header->response_msg] on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 0, "msg" => $header->response_msg);
  }
}
// Sign Up Page signup - End

// Message Credit Page find get_available_balance - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "get_available_balance") {
  // Get data
  $txt_receiver_user = htmlspecialchars(
    strip_tags(
      isset($_GET["txt_receiver_user"])
      ? $conn->real_escape_string($_GET["txt_receiver_user"])
      : ""
    )
  );
  $product_id = htmlspecialchars(
    strip_tags(
      isset($_GET["product_id"])
      ? $conn->real_escape_string($_GET["product_id"])
      : ""
    )
  );

  $expl = explode("~~", $txt_receiver_user); // explode function using
// To Send the request API 
  $replace_txt =
    '{
"user_id" : "'.$_SESSION["yjwatsp_user_id"].'",
"select_user_id" : "'.$expl[0].'",
"product_id" :  "'.$product_id.'"
}';

  // Add bearer token
  $bearer_token = "Authorization: ".$_SESSION["yjwatsp_bearer_token"]."";
  // It will call "available_credits" API to verify, can we view the available credits to the particular user
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $api_url."/purchase_credit/available_credits",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => [$bearer_token, "Content-Type: application/json"],
  ]);
  // Send the data into API and execute
  site_log_generate(
    "Message Credit Page : ".
    $_SESSION["yjwatsp_user_name"].
    " Execute the service [$replace_txt] on ".
    date("Y-m-d H:i:s"),
    "../"
  );
  $response = curl_exec($curl);
  curl_close($curl);
  // After got response decode the JSON result
  $header = json_decode($response, false);
  site_log_generate(
    "Message Credit Page : ".
    $_SESSION["yjwatsp_user_name"].
    " get the Service response [$response] on ".
    date("Y-m-d H:i:s"),
    "../"
  );

  if($header->num_of_rows > 0) { // If the response is success to execute this condition
    for($indicator = 0; $indicator < $header->num_of_rows; $indicator++) {
      // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition are false to stop the process
      $stateData = $header->report[$indicator]->available_credits;
    }
    $json = ["status" => 1, "msg" => "Available Credits : ".$stateData];
  } else if($header->response_status == 204) {
    site_log_generate("Add Message Credit Page   : ".$user_name."get the Service response [$header->response_status] on ".date("Y-m-d H:i:s"), '../');
    $json = array("status" => 2, "msg" => $header->response_msg);
  } else if($header->response_status == 403) { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
 else { // Otherwise It willbe execute
    site_log_generate("Add Message Credit Page  : ".$user_name." get the Service response [$header->response_msg] on  ".date("Y-m-d H:i:s"), '../');
    $json = [
      "status" => 0,
      "msg" =>
        "Invalid Inputs. Kindly try again with the correct Inputs!",
    ];
  }
}
// Message Credit Page find get_available_balance - End


// upload_apk_file Page find upload_apk_file - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "upload_apk_file") {
  // Get data
  $current_date = date("YmdHis");
  $app_version = htmlspecialchars(strip_tags(isset($_REQUEST['app_version']) ? $conn->real_escape_string($_REQUEST['app_version']) : ""));

  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);

  // Add bearer token
  $bearer_token = "Authorization: " . $_SESSION["yjwatsp_bearer_token"] . "";
      // To Send the request API 
      $replace_txt =
      '{
      "app_version" : "' . $app_version . '",
      "request_id":"' . $request_id . '"
}';
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $api_url . '/app_update/app_version_check',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $replace_txt,
    CURLOPT_HTTPHEADER => [$bearer_token, "Content-Type: application/json"],
  ]);
  // Send the data into API and execute
  site_log_generate(
    "app_version_check Page : " .
    $_SESSION["yjwatsp_user_name"] .
    " Execute the service [$replace_txt] on " .
    date("Y-m-d H:i:s"),
    "../"
  );
  $response = curl_exec($curl);
  curl_close($curl);
  // After got response decode the JSON result
  $header = json_decode($response, false);
  site_log_generate(
    "app_version_check : " .
    $_SESSION["yjwatsp_user_name"] .
    " get the Service response [$response] on " .
    date("Y-m-d H:i:s"),
    "../"
  );
  if ($response == '') {
    ?>
    <script>
      window.location = "logout"
    </script>
    <?
  } else if ($header->response_status == 200) { // If the response is success to execute this condition
    $file_name = $_FILES['apk_file_upload']['name'];
    $apk_filename = str_replace(' ', '_', $file_name);
    $apk_filename = str_replace('.apk', '', $apk_filename);

    $apk_filename = $apk_filename . "_" . $app_version . "_" . $current_date . ".apk";

    $location_1 = "../uploads/app_versions/" . $apk_filename;
    $location = $site_url . "uploads/app_versions/" . $apk_filename;
    $imageFileType = pathinfo($location_1, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);

    // Check if the file has a valid APK extension
    $allowedExtensions = ['apk'];
    if (!in_array($imageFileType, $allowedExtensions)) {
      $json = ["status" => 0, "msg" => "Invalid File!.."];
      //echo "Invalid file type. Please upload an APK file.";
    }

    if (move_uploaded_file($_FILES['apk_file_upload']['tmp_name'], $location_1)) {
      //echo "Success";
      $rspns = $location_1;
      site_log_generate("upload_apk_file Page: User: " . $_SESSION['yjwatsp_user_name'] . " APK file moved into Folder on " . date("Y-m-d H:i:s"), '../');
    } else {
      //echo "Error moving file: " . error_get_last()['message'];
      // Optionally, you can print additional error information:
      $json = ["status" => 0, "msg" => "File Cannot upload!.."];
    }
  $request_id = $_SESSION['yjwatsp_user_id'] . "_" . date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);

    $rspns = '';
    // To Send the request API 
    $replace_txt =
      '{
      "app_file_name" : "' . $location . '",
      "app_version" : "' . $app_version . '",
      "request_id":"' . $request_id . '"
}';
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => $api_url . '/app_update/upload_version',
      // CURLOPT_URL => $api_url . "/app_update/upload_version",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => [$bearer_token, "Content-Type: application/json"],
    ]);
    // Send the data into API and execute
    site_log_generate(
      "upload_apk_file Page : " .
      $_SESSION["yjwatsp_user_name"] .
      " Execute the service [$replace_txt] on " .
      date("Y-m-d H:i:s"),
      "../"
    );
    $response = curl_exec($curl);
    curl_close($curl);
    // After got response decode the JSON result
    $header = json_decode($response, false);
    site_log_generate(
      "upload_apk_file : " .
      $_SESSION["yjwatsp_user_name"] .
      " get the Service response [$response] on " .
      date("Y-m-d H:i:s"),
      "../"
    );
    if ($response == '') {
      ?>
        <script>
          window.location = "logout"
        </script>
      <?
    } else if ($header->response_status == 200) { // If the response is success to execute this condition
      $json = ["status" => 1, "msg" => "App Uploaded Successfully!"];
    } else if ($header->response_status == 403) { ?>
            <script>
              window.location = "logout"
            </script>
    <? } else if ($header->response_status == 204) {
      site_log_generate("upload_apk_file Page   : " . $user_name . "get the Service response [$header->response_status] on " . date("Y-m-d H:i:s"), '../');
      $json = array("status" => 0, "msg" => $header->response_msg);
    } // If the response_status is 201, delete the uploaded file
    else if ($header->response_status == 201) {
      site_log_generate("upload_apk_file Page  : " . $user_name . " get the Service response [$header->response_msg] on  " . date("Y-m-d H:i:s"), '../');
      if (file_exists($location_1)) {
        unlink($location_1);
      }
      $json = [
        "status" => 0,
        "msg" => $header->response_msg
      ];
    } else { // Otherwise It willbe execute
      site_log_generate("upload_apk_file Page  : " . $user_name . " get the Service response [$header->response_msg] on  " . date("Y-m-d H:i:s"), '../');
      $json = [
        "status" => 0,
        "msg" => $header->response_msg
      ];
    }

  } else if ($header->response_status == 403) { ?>
        <script>
          window.location = "logout"
        </script>
  <? } else if ($header->response_status == 204) {
    site_log_generate("app_version_check Page   : " . $user_name . "get the Service response [$header->response_status] on " . date("Y-m-d H:i:s"), '../');
    $json = array("status" => 0, "msg" => $header->response_msg);
  } // If the response_status is 201, delete the uploaded file
  else if ($header->response_status == 201) {
    site_log_generate("app_version_check Page  : " . $user_name . " get the Service response [$header->response_msg] on  " . date("Y-m-d H:i:s"), '../');
    $json = [
      "status" => 0,
      "msg" => $header->response_msg
    ];
  }

}
// upload_apk_file upload_apk_file - end

// user_based_product Page find user_based_product - Start
if($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "user_based_product") {
  // Get data
  $txt_receiver_user = htmlspecialchars(
    strip_tags(
      isset($_GET["txt_receiver_user"])
      ? $conn->real_escape_string($_GET["txt_receiver_user"])
      : ""
    )
  );
  $expl = explode("~~", $txt_receiver_user); // explode function using
  $replace_txt = '{
"select_user_id":"'.$expl[0].'"
}'; // Send the User ID
  $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].''; // Add bearer Token
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url.'/list/products_name',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
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
  $state1 = json_decode($response, false);
  site_log_generate("Message Credit Page : ".$_SESSION['yjwatsp_user_name']." get the Service response [$response] on ".date("Y-m-d H:i:s"), '../');

  // Based on the JSON response, list in the option button
  if($state1->num_of_rows > 0) {
    // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
    for($indicator = 0; $indicator < $state1->num_of_rows; $indicator++) {
      $data .= '<option value="'.$state1->product_name[$indicator]->rights_id." ~~".$state1->product_name[$indicator]->rights_name.'"'.($indicator == 0 || $slot_id == $state1->product_name[$indicator]->rights_name ? 'selected' : '').'>'.$state1->product_name[$indicator]->rights_name.'</option>';
    }
    $json = ["status" => 1, "msg" => $data];
  }else if ($state1->response_status == 403){ ?>
    <script>
      window.location = "logout"
    </script>
  <? }
else if($state1->response_status == 201) {
 $json = ["status" => 1, "msg" => 'No data available'];
}
}
// user_based_product Page find user_based_product - ENd

// View On Boarding Page apprej_onboarding - Start
if ($_SERVER["REQUEST_METHOD"] == "POST" and $call_function == "apprej_onboarding") {
  site_log_generate("View On Boarding Page : User : " . $_SESSION["yjwatsp_user_name"] . " access the page on " . date("Y-m-d H:i:s"), "../");
  // Get data

  $txt_user = htmlspecialchars(strip_tags(isset($_REQUEST["txt_user"]) ? $conn->real_escape_string($_REQUEST["txt_user"]) : ""));
  $txt_remarks = htmlspecialchars(strip_tags(isset($_REQUEST["txt_remarks"]) ? $conn->real_escape_string($_REQUEST["txt_remarks"]) : ""));
  $user_status = htmlspecialchars(strip_tags(isset($_REQUEST["user_status"]) ? $conn->real_escape_string($_REQUEST["user_status"]) : ""));
  $user_masterid = htmlspecialchars(strip_tags(isset($_REQUEST["user_masterid"]) ? $conn->real_escape_string($_REQUEST["user_masterid"]) : ""));

  $resellerid = htmlspecialchars(strip_tags(isset($_REQUEST['resellerid']) ? $conn->real_escape_string($_REQUEST['resellerid']) : ""));
  $select_user_id = htmlspecialchars(strip_tags(isset($_REQUEST["select_user_id"]) ? $conn->real_escape_string($_REQUEST["select_user_id"]) : ""));
  $usersid = htmlspecialchars(strip_tags(isset($_REQUEST["usersid"]) ? $conn->real_escape_string($_REQUEST["usersid"]) : ""));
  $users_resellerids = str_replace(',', '","', $usersid);
  $rep_txt_remarks = "";

  if ($txt_remarks != '') {
    $rep_txt_remarks = '"txt_remarks" : "' . $txt_remarks . '",';
  }
  $makeresller = "";

  if ($user_masterid) {
    $makeresller = '"reseller_masterid" : "' . $user_masterid . '",';
  }
  if ($user_status) {
    $user_status = '"aprj_status" : "' . $user_status . '",';
  }

  if ($select_user_id) {
    $txt_user = $select_user_id;
  }
  if ($_FILES['file_input']) {
    $image_size = $_FILES['file_input']['size'];
    $image_type = $_FILES['file_input']['type'];
    $file_type = explode("/", $image_type);

    $img_filename = $_SESSION['yjwatsp_user_id'] . "_" . $milliseconds . "." . $file_type[1];
    $img_location = "../uploads/logo_images/" . $img_filename;
    $location_1 = '"' . $site_url . 'uploads/logo_images/' . $img_filename . '"';
    $imageFileType = pathinfo($img_location, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);

    if (move_uploaded_file($_FILES['file_input']['tmp_name'], $img_location)) {
      $rspns = $img_location;
      site_log_generate("apprej_onboarding : User : " . $_SESSION['yjwatsp_user_name'] . " whatsapp_images file moved into Folder on " . date("Y-m-d H:i:s"), '../');
    }
  }
  if ($location_1) {
    $replace_txt = '{
      "user_id" : "' . $_SESSION["yjwatsp_user_id"] . '",
      "change_user_id" : "' . $txt_user . '",
      "media_url":' . $location_1 . ',
      "request_id" : "' . $_SESSION["yjwatsp_user_short_name"] . "_" . $year . $julian_dates . $hour_minutes_seconds . "_" . $random_generate_three . '"
    }';
  } else {
    // To Send the request API
    $replace_txt = '{
          "user_id" : "' . $_SESSION["yjwatsp_user_id"] . '",
          "change_user_id" : "' . $txt_user . '",
          "reselleruserids" : ["' . $users_resellerids . '"],
          ' . $rep_txt_remarks . '
          ' . $makeresller . '
          ' . $user_status . '
          "request_id" : "' . $_SESSION["yjwatsp_user_short_name"] . "_" . $year . $julian_dates . $hour_minutes_seconds . "_" . $random_generate_three . '"
          }';
  }
  $replace_txts = str_replace(' ', '', $replace_txt);
  // echo  $replace_txts;
  // Add bearer token
  $bearer_token = "Authorization: " . $_SESSION["yjwatsp_bearer_token"] . "";

  // To Get Api Response URL
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/approve_reject_onboarding',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $replace_txts,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  // Send the data into API and execute
  site_log_generate("View On Boarding Page : " . $_SESSION["yjwatsp_user_name"] . " Execute the service approve_reject_onboarding [$replace_txt, $bearer_token] on " . date("Y-m-d H:i:s"), "../");
  $response = curl_exec($curl);
  curl_close($curl);
  // echo $response;


  // After got response decode the JSON result
  $header = json_decode($response, false);
  site_log_generate("View On Boarding Page : " . $_SESSION["yjwatsp_user_name"] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), "../");

  // To get the response message
  if ($header->response_status == 200) {
    site_log_generate("View On Boarding Page : " . $user_name . " On Boarding form updation Success on " . date("Y-m-d H:i:s"), '../');
    $json = array("status" => 1, "msg" => $header->response_msg);
  } else if ($header->response_status == 201) {
    site_log_generate("View On Boarding Page : " . $user_name . " get the Service response [$header->response_status] on " . date("Y-m-d H:i:s"), '../');
    $json = array("status" => 2, "msg" => $header->response_msg);
  } else {
    site_log_generate("View On Boarding Page : " . $user_name . " On Boarding form updation Failed [Invalid Inputs] on " . date("Y-m-d H:i:s"), '../');
    $json = array("status" => 0, "msg" => "On Boarding form updation failed [Invalid Inputs]. Kindly try again with the correct Inputs!");
  }
}
// View On Boarding Page apprej_onboarding - End


// View On Boarding Page apprej_onboarding - Start
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_GET['call_function'] == "context_list") {

  $type_request = htmlspecialchars(strip_tags(isset($_REQUEST["type"]) ? $conn->real_escape_string($_REQUEST["type"]) : ""));

  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/active_prompt_list',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  // Send the data into API and execute                          
  site_log_generate("Compose OBD Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }

  // After got response decode the JSON result
  $header = json_decode($response);
  site_log_generate("Compose OBD Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . $current_date, '../');
  if ($header->response_status == 403) {

  } else if ($header->response_code == 1) {
    $options = array();
    for ($indicator = 0; $indicator < count($header->campaign_list); $indicator++) {
      if($header->campaign_list[$indicator]->prompt_status == "Y"){
      $context = $header->campaign_list[$indicator]->context;
      $prompt_id = $header->campaign_list[$indicator]->prompt_id;
      $campaign_type = $header->campaign_list[$indicator]->campaign_type;
      $prompt_path=$header->campaign_list[$indicator]->prompt_path;
      $audio_duration=$header->campaign_list[$indicator]->audio_duration;

      if ($type_request == $campaign_type) {
        $options[] = array(
          'context' => $context,
          'prompt_id' => $prompt_id,
          'prompt_path' => $prompt_path,
          'audio_duration' => $audio_duration
        );
      }
      }
    }
    $json = array("status" => 1, 'data' => $options);
  }
}

// OBD CALL Report Generation - START 
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_GET['call_function'] == "get_campaignlist") {
  $user_id = htmlspecialchars(strip_tags(isset($_REQUEST["user_id"]) ? $conn->real_escape_string($_REQUEST["user_id"]) : ""));
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token']; // Add Bearer Token
  // To get the logged in user and their child users. Primary Admin can view all user
  $replace_txt = json_encode([
    "selected_user_id" => $user_id,
    "user_product" => "OBD CALL SIP"
  ]); // Add user id
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/obd_campaign_list',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET', // Change to POST to send data
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );
  // Send the data into API and execute                          
  site_log_generate("Business Detailed Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }

  // After getting response, decode the JSON result
  $header = json_decode($response, false);
  site_log_generate("Business Detailed Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . $current_date, '../');

  if ($header->response_status == 403) { ?>
    <script>
      window.location = "logout"
    </script>
  <? } else if ($header->response_status == 204) {

  } else if (count($header->campaign_list) > 0) {
    $json = array("campaign_list" => $header->campaign_list);
  }
}
// OBD CALL Report Generation - END 


// copy_file Page copy_file - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $storecopy_file == "copy_file") {
  site_log_generate("copy_file Page : User : " . $_SESSION['yjwatsp_user_name'] . " " . date("Y-m-d H:i:s"), '../');
  // Check if the request contains the copied file

  if (isset($_FILES['copiedFile']) && $_FILES['copiedFile']['error'] === UPLOAD_ERR_OK) {

    // Get the file information
    $path_parts = pathinfo($_FILES["copiedFile"]["name"]);
    $extension = $path_parts['extension'];
    $filename = $_SESSION['yjwatsp_user_id'] . "_csv_" . $milliseconds . "." . $extension;
    /* Location */
    $location = "../uploads/group_contact/" . $filename;
    $file_location = $full_pathurl . "uploads/group_contact/" . $filename;

    $location_1 = "../uploads/compose_variables/" . $filename;
    $file_location_1 = $full_pathurl . "uploads/compose_variables/" . $filename;

    $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);
    /* Valid extensions */
    $valid_extensions = array("csv");
    $response = 0;
    /* Check file extension */
    if (in_array(strtolower($imageFileType), $valid_extensions)) {
      /* Upload file */
      if (move_uploaded_file($_FILES['copiedFile']['tmp_name'], $location)) {
        // Copy the file to backup location
        if (copy($location, $location_1)) {
          $response = $location; // You can set this to any of the locations
          $response = $location_1;
          // Set file permissions
          chmod($filename, 0777);
          $csvFile = fopen($location, 'r') or die("can't open file");
          $json = array("status" => 1, "msg" => "File uploaded successfully", "file_location" => $file_location);
        } else {
          $json = array("status" => 0, "msg" => "Failed to copy the uploaded file to backup location");
        }
      } else {
        $json = array("status" => 0, "msg" => "Failed to move the uploaded file");
      }
    } else {
      $json = array("status" => 0, "msg" => "Invalid file extension. Only CSV files are allowed.");
    }
  } else {
    $json = array("status" => 0, "msg" => "No file uploaded or an error occurred during upload");
  }
  // Output JSON response
  header('Content-Type: application/json');
}
// copy_file copy_file - end


// Index Page Reset Password - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "resetpwd") {
  // Get data
  $user_email = htmlspecialchars(strip_tags(isset($_REQUEST['email_id_reset']) ? $_REQUEST['email_id_reset'] : ""));
  site_log_generate("Index Page : " . $user_email . " trying to reset the password on " . $current_date, '../');
  $request_id = date("Y") . "" . date('z', strtotime(date("d-m-Y"))) . "" . date("His") . "_" . rand(1000, 9999);

  $replace_txt = '{
    "user_emailid" : "' . $user_email . '",
    "request_id" : "' . $request_id . '"
  }';

  site_log_generate("Index Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service On Reset Password Request [$replace_txt] on " . $current_date, '../');

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/login/reset_password',
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
        'Content-Type: application/json'
      ),
    )
  );

  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }

  $header = json_decode($response, false);
  site_log_generate("Reset Password Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . $current_date, '../');
  if ($header->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  if ($header->num_of_rows > 0) {
    site_log_generate("Reset Password Page : " . $user_name . " Password Reseted on successfully on " . $current_date, '../');
    $json = array("status" => 1, "msg" => "New Password send it to your email. Kindly verify!!");
  } else {
    site_log_generate("Reset Password Page : " . $user_name . "  Password Reseted on Failed [$header->response_msg] on " . $current_date, '../');
    $json = array("status" => 0, "msg" => $header->response_msg);
  }
}

// Index Page Reset Password - End

// Channel List Page process_channel_list - Start
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_GET['call_function'] == "process_channel_list") {

  $campaign_id = htmlspecialchars(strip_tags(isset($_REQUEST["campaign_id"]) ? $conn->real_escape_string($_REQUEST["campaign_id"]) : ""));
 $selected_user_id = htmlspecialchars(strip_tags(isset($_REQUEST["user_id"]) ? $conn->real_escape_string($_REQUEST["user_id"]) : ""));

  $replace_txt = '{
    "campaign_id" : "' . $campaign_id . '",
    "selected_user_id" : "' .$selected_user_id . '"

  }';
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/process_server',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  // Send the data into API and execute                          
  site_log_generate("Compose OBD Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
$sms = json_decode($response, false);
  if ($response == '') { ?>
<script>window.location = "logout"</script>
<? } else if ($sms->response_status == 403) { ?>
<script>window.location = "logout"</script>
<? } else if ($sms->response_status == 200) {
    $indicatori = 0; ?>
<table style="width: 100%;">
  <? $counter = 0;
      for ($indicator = 0; $indicator < count($sms->report); $indicator++) {
        if ($counter % 2 == 0) { ?>
  <tr>
    <? } ?>
    <td>
      <input type="checkbox" class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="server_names[]"
        tabindex="1" autofocus value="<?= $sms->report[$indicator]->sip_id ?>">
      <label class="form-label">
        <?= $sms->report[$indicator]->server_name ?>
      </label>
    </td>
    <?
        if ($counter % 2 == 1) { ?>
  </tr>
  <? }
        $counter++;
      } ?>
</table>
<? } else if ($sms->response_status == 204 || $sms->response_status == 201) {
    echo $sms->response_status;
  }
}

// delete_file Page delete_file - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['delete_file_name']) && $deletecopy_file == "delete_file") {
    site_log_generate("delete_file Page : User : " . $_SESSION['yjwatsp_user_name'] . " " . date("Y-m-d H:i:s"), '../');

    // Get file name from POST request
    $file_name = htmlspecialchars(strip_tags($conn->real_escape_string($_POST['delete_file_name'])));

    // Define file paths
    $location1 = $full_pathurl . "uploads/group_contact/" . $file_name;
    $location2 = $full_pathurl . "uploads/compose_variables/" . $file_name;
    $locations = [$location1, $location2];

    $file_deleted = false;
    foreach ($locations as $location) {
        if (file_exists($location)) {
            if (unlink($location)) {
                $file_deleted = true;
            }
        }
    }

    // Prepare JSON response
    if ($file_deleted) {
        $json = ["status" => 1, "msg" => "File deleted successfully"];
    } else {
        $json = ["status" => 0, "msg" => "File does not exist or failed to delete"];
    }
}
// delete_file Page delete_file - End


// Finally Close all Opened Mysql DB Connection
$conn->close();

// Output header with JSON Response
header('Content-type: application/json');
echo json_encode($json);
