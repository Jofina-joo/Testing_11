<?php
session_start();
error_reporting(0);

header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

// Include configuration.php
include_once('../api/configuration.php');
// Paytm Operation - Start
extract($_REQUEST);

$bearer_token = "Authorization: " . $_SESSION["yjwatsp_bearer_token"] . ""; // To get bearertoken
$current_date = date("Y-m-d H:i:s");
$milliseconds = round(microtime(true) * 1000);

// user_management Page razorpay_payment - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $action_process == "razorpay_payment") {
  $replace_txt = '{
    "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
  }';
  //add bearer token
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';

  // It will call "add_message_credit" API to verify, can we access for the add_message_credit list  
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $api_url . '/purchase_credit/rppayment_user_id',
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
  site_log_generate("Payment Page : " . $_SESSION['yjwatsp_user_name'] . " initiated service 0 on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  // After got response decode the JSON result
  $header = json_decode($response, false);
  // print_r($header);
  site_log_generate("Payment Page : " . $_SESSION['yjwatsp_user_name'] . " received response for service 0 on " . date("Y-m-d H:i:s"), '../');

  $sms_amount = 0;
  $cda = '';
  if ($header->response_status == 200) {
    for ($indicator = 0; $indicator < $header->num_of_rows; $indicator++) {
      // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition are false to stop the process
      $cda = $header->report[$indicator]->usr_credit_id;
      $sms_amount = $header->report[$indicator]->amount;
    }
  }

  if ($cda != '') {
    $_SESSION['user_cda'] = $cda;
    $_SESSION['hid_yjwatsp_bearer_token'] = $_SESSION['yjwatsp_bearer_token'];
    $orderId = time();
    $txnAmount = $sms_amount;
    $custId = $_SESSION['yjwatsp_user_id'];
    $mobileNo = $_SESSION['yjwatsp_user_mobile'];
    $email = $_SESSION['yjwatsp_user_email'];

    $paytmParams = array();
    $paytmParams["ORDER_ID"] = $orderId;
    $paytmParams["CUST_ID"] = $custId;
    $paytmParams["MOBILE_NO"] = $mobileNo;
    $paytmParams["EMAIL"] = $email;
    $paytmParams["TXN_AMOUNT"] = $txnAmount;
    /* $paytmParams["MID"] 		        	= PAYTM_MERCHANT_MID;
    $paytmParams["CHANNEL_ID"] 	      = PAYTM_CHANNEL_ID;
    $paytmParams["WEBSITE"] 	        = PAYTM_MERCHANT_WEBSITE;
    $paytmParams["INDUSTRY_TYPE_ID"]  = PAYTM_INDUSTRY_TYPE_ID;
    $paytmParams["CALLBACK_URL"]      = PAYTM_CALLBACK_URL."&cda=".$cda;
    $paytmChecksum                    = getChecksumFromArray($paytmParams, PAYTM_MERCHANT_KEY);
    $transactionURL                   = PAYTM_TXN_URL; */

    // $transactionURL                = "https://securegw-stage.paytm.in/theia/processTransaction";
    // $transactionURL                = "https://securegw.paytm.in/theia/processTransaction"; // for production


    //check payment is authrized or not via API call

    $razorPayId = htmlspecialchars(strip_tags(isset($_POST['razorpay_payment_id']) ? $_POST['razorpay_payment_id'] : ""));
    $ch = curl_init('https://api.razorpay.com/v1/payments/' . $razorPayId . '');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_USERPWD, $rp_keyid . ":" . $rp_keysecret); // Input your Razorpay Key Id and Secret Id here
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch));
     //print_r($response->status); // authorized

    // check that payment is authorized by razorpay or not
    if ($response->status == 'authorized') {
      $respval = array('msg' => 'Payment successfully credited', 'status' => true, 'productCode' => $_POST['product_id'], 'paymentID' => $_POST['razorpay_payment_id'], 'userEmail' => $_POST['useremail']);
      $respval1 = 'msg:Payment successfully credited, status:true, productCode:' . $_POST['product_id'] . ', paymentID:' . $_POST['razorpay_payment_id'] . ', userEmail' . $_POST['useremail'];

      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "usrsmscrd_id" : "' . $_SESSION['user_cda'] . '",
            "usrsmscrd_status" : "A",
            "usrsmscrd_status_comments" : "' . $respval1 . '"
          }';
      // To Get Api URL
      $bearer_token = 'Authorization: ' . $_SESSION['hid_yjwatsp_bearer_token'] . '';
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url . '/purchase_credit/update_credit_raise_status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $replace_txt,
        CURLOPT_HTTPHEADER => array(
          $bearer_token,
          'Content-Type: application/json'
        ),
      )
      );
      site_log_generate("approve page sender_id reject Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service 2 [$replace_txt, $bearer_token] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("approve page sender_id reject Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response 2 [$response] on " . date("Y-m-d H:i:s"), '../');

      site_log_generate("Index Page : " . $uname . " logged in failed [Sign in Failed] on " . date("Y-m-d H:i:s"), '../');

      $json = array("status" => 1, "msg" => "Payment process is successfully");

    } else {
      $respval = array('msg' => 'Payment failed', 'status' => false, 'productCode' => $_POST['product_id'], 'paymentID' => $_POST['razorpay_payment_id'], 'userEmail' => $_POST['useremail']);
      $respval1 = 'msg:Payment failed, status:false, productCode:' . $_POST['product_id'] . ', paymentID:' . $_POST['razorpay_payment_id'] . ', userEmail' . $_POST['useremail'];

      $replace_txt = '{
	    "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "usrsmscrd_id" : "' . $_SESSION['user_cda'] . '",
            "usrsmscrd_status" : "F",
            "usrsmscrd_status_comments" : "' . $respval1 . '"
          }';
      // To Get Api URL
      $bearer_token = 'Authorization: ' . $_SESSION['hid_yjwatsp_bearer_token'] . '';
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url . '/purchase_credit/update_credit_raise_status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $replace_txt,
        CURLOPT_HTTPHEADER => array(
          $bearer_token,
          'Content-Type: application/json'
        ),
      )
      );
      site_log_generate("approve page sender_id reject Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service 3 [$replace_txt, $bearer_token] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("approve page sender_id reject Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response 3 [$response] on " . date("Y-m-d H:i:s"), '../');

      site_log_generate("Index Page : " . $uname . " logged in failed [Sign in Failed] on " . date("Y-m-d H:i:s"), '../');
      $json = array("status" => 0, "msg" => "payment processing is failed");
    }
  }

}
// user_management Page razorpay_payment - End

// Finally Close all Opened Mysql DB Connection
$conn->close();
// Output header with JSON Response
header('Content-type: application/json');
echo json_encode($json);


?>

