<?php
/*
Authendicated users only allow to view this Message Credit page.
This page is used to update the Credit to a user.
Parent user can assign the credit list to their childs.

Version : 1.0
Author : 
Date : 
*/

session_start(); // start session
error_reporting(0); // The error reporting function

include_once 'api/configuration.php'; // Include configuration.php
extract($_REQUEST); // Extract the request

 //print_r($_REQUEST); echo $bar; exit;
if ($bar != '') {
    $expld1 = explode("&", $bar);
    $slot_id = $expld1[0];
    $usr_vlu = $expld1[1];
    $cnt_vlu = $expld1[2];
    $usrsmscrd_id = $expld1[3];
    //echo "==".$usr_vlu."==".$cnt_vlu."==";
} else {
}

// If the Session is not available redirect to index page
if ($_SESSION['yjwatsp_user_id'] == "") { ?>
  <script>window.location = "index";</script>
  <?php exit();}

$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate(
    "Message Credit Page : User : " .
        $_SESSION['yjwatsp_user_name'] .
        " access the page on " .
        date("Y-m-d H:i:s")
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Message Credit ::
    <?= $site_title ?>
  </title>
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <!-- include header function adding -->
      <? include("libraries/site_header.php"); ?>

      <!-- include sitemenu function adding -->
      <? include("libraries/site_menu.php"); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <!-- Title and Breadcrumbs -->
          <div class="section-header">
            <h1>Message Credit</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item active"><a href="message_credit_list">Message Credit List</a></div>
              <div class="breadcrumb-item">Message Credit</div>
            </div>
          </div>

          <!-- Form Panel -->
          <div class="section-body">
            <div class="row">

              <div class="col-12 col-md-6 col-lg-6 offset-3">
                <div class="card">
                  <form class="needs-validation" novalidate="" id="frm_message_credit" name="frm_message_credit"
                    action="#" method="post" enctype="multipart/form-data">
                    <div class="card-body">
                      <!-- Admin Select menu Start-->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-4 col-form-label">Admin
                        </label>
                        <div class="col-sm-8">
                          <select name="txt_receiver_user" id='txt_receiver_user' class="form-control"
                            data-toggle="tooltip" data-placement="top" title="" required=""
                            data-original-title="Receiver User" tabindex="1" autofocus
                            onchange="user_based_product();get_available_balance();" onblur="get_available_balance();getproductid();" >
                            <? // To get the child user list from API
                            $replace_txt = '{
                              "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
                            }';
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add bearer Token
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                              CURLOPT_URL => $api_url . '/purchase_credit/slt_receiver_user',
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
                            site_log_generate("Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
                            $response = curl_exec($curl);
                            curl_close($curl);

                            // After got response decode the JSON result
                            $state1 = json_decode($response, false);
                            site_log_generate("Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

                            // Based on the JSON response, list in the option button
                            if ($state1->num_of_rows > 0) {
                              for ($indicator = 0; $indicator < $state1->num_of_rows; $indicator++) {
                                // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
                                ?>
                                <option
                                  value="<?= $state1->report[$indicator]
                                      ->user_id .
                                      "~~" .
                                      $state1->report[$indicator]
                                          ->user_name ?>" <?
                                       if ($indicator == 0 || $usr_vlu == $state1->report[$indicator]->user_id) { ?>selected<? }
                                       if ($usr_vlu != '' && $usr_vlu != $state1->report[$indicator]->user_id) { ?> disabled <? } ?> >
                                  <?= $state1->report[$indicator]->user_name ?> 
                                </option>
                                <?
                              }
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                      <!-- Admin Select menu End-->
<!-- Product Name Based If the yjwatsp_user_id ==  1.condition will be true -->
                      <? if ($bar == '' && $_SESSION['yjwatsp_user_id'] == 1) { ?>
 <div class="form-group mb-2 row">
 <label class="col-sm-4 col-form-label">Product Name</label>
 <div class="col-sm-8">
   <!-- Parent User Panel -->
   <select name="txt_product_name" id='txt_product_name' class="form-control"
     data-toggle="tooltip" data-placement="top" title="" required=""
     data-original-title="Product Name" tabindex="1" autofocus onchange="getproductid();"  onblur="getproductid();"
     >
     <? // To get the current user rights
     $replace_txt = '{
        "select_user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
     }'; // Send the User ID
     $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add bearer Token
     $curl = curl_init();
     curl_setopt_array($curl, array(
       CURLOPT_URL => $api_url . '/list/products_name',
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
     site_log_generate("Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
     $response = curl_exec($curl);
     curl_close($curl);

     // After got response decode the JSON result
     $state1 = json_decode($response, false);
     site_log_generate("Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

     // Based on the JSON response, list in the option button
     if ($state1->num_of_rows > 0) {
       // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
       for ($indicator = 0; $indicator < $state1->num_of_rows; $indicator++) { ?>
         <option
           value="<?= $state1->product_name[$indicator]->rights_id .
               " ~~" .
               $state1->product_name[$indicator]->rights_name ?>"
           <? if ($indicator == 0 || $slot_id == $state1->product_name[$indicator]->rights_name) { ?>selected<? }?> >
           <?= $state1->product_name[$indicator]->rights_name ?>
         </option>
       <? 
       }
     } 
     ?>
   </select>
 </div>
</div>
                       <? }else{       // Otherwise
                         ?>
                      <div class="form-group mb-2 row">
                        <label class="col-sm-4 col-form-label">Product Name</label>
                        <div class="col-sm-8">
                          <!-- Parent User Panel -->
                          <select name="txt_product_name" id='txt_product_name' class="form-control"
                            data-toggle="tooltip" data-placement="top" title="" required=""
                            data-original-title="Product Name" tabindex="1" autofocus onchange="getproductid();"  onblur="getproductid();"
                            >
                            <? // To get the current user rights
                            $replace_txt = '{
                               "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
                             
                            }'; // Send the User ID
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add bearer Token
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                              CURLOPT_URL => $api_url . '/purchase_credit/pricing_slot',
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
                            site_log_generate("Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
                            $response = curl_exec($curl);
                            curl_close($curl);

                            // After got response decode the JSON result
                            $state1 = json_decode($response, false);
                            site_log_generate("Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

                            // Based on the JSON response, list in the option button
                            if ($state1->num_of_rows > 0) {
                              // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
                              for ($indicator = 0; $indicator < $state1->num_of_rows; $indicator++) { ?>
                                <option
                                  value="<?= $_SESSION['yjwatsp_user_id'] .
                                      "~~" .
                                      $state1->pricing_slot[$indicator]
                                          ->pricing_slot_id .
                                      "~~" .
                                      $state1->pricing_slot[$indicator]
                                          ->price_from .
                                      "~~" .
                                      $state1->pricing_slot[$indicator]
                                          ->price_to .
                                      "~~" .
                                      $state1->pricing_slot[$indicator]
                                          ->price_per_message .
                                      " ~~" .
                                      $state1->pricing_slot[$indicator]
                                          ->rights_name ?>"
                                  <? if ($indicator == 0 || $slot_id == $state1->pricing_slot[$indicator]->pricing_slot_id) { ?>selected<? }
                                  if ($slot_id != '' && $slot_id != $state1->pricing_slot[$indicator]->pricing_slot_id) { ?> disabled <? } ?> >
                                  <?= $state1->pricing_slot[$indicator]
                                      ->price_from .
                                      " - " .
                                      $state1->pricing_slot[$indicator]
                                          ->price_to .
                                      " [Rs." .
                                      $state1->pricing_slot[$indicator]
                                          ->price_per_message .
                                      "](" .
                                      $state1->pricing_slot[$indicator]
                                          ->rights_name .
                                      " )" ?>
                                </option>
                              <? 
                              }
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                      <? } ?>
                         <!-- Required Message Count -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-4 col-form-label">Required Message Count</label>
                        <div class="col-sm-8">
                          <input <? if ($cnt_vlu != '') { ?> type="hidden" <? } else { ?> type="text" <? } ?>
                            name="txt_message_count" id='txt_message_count' class="form-control" value="<?= $cnt_vlu ?>"
                            tabindex="3" required maxlength="7"
                            onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                            placeholder="Message Count" data-toggle="tooltip" data-placement="top" title=""
                            data-original-title="Message Count">
                          <?= $cnt_vlu ?><br>
                          <span class="error_display" id='id_count_display'></span>
                          <!-- Message Count and Error display -->
                        </div>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <span class="error_display" id='id_error_display'></span><br> <!-- Error Display -->
                      <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                        value='message_credit' />
                      <input type="hidden" class="form-control" name='hid_usrsmscrd_id' id='hid_usrsmscrd_id'
                        value='<?= $usrsmscrd_id ?>' />
                      <input type="submit" name="submit" id="submit" tabindex="10" value="Submit"
                        class="btn btn-success">
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- include site footer -->
      <? include("libraries/site_footer.php"); ?>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- JS Libraies -->
  <!-- Page Specific JS File -->
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    // If we click the Submit button, validate and save the data using API
    $("#submit").click(function (e) {
      $("#id_error_display").html("");
      var txt_product_name = $('#txt_product_name').val();
      var txt_receiver_user = $('#txt_receiver_user').val();
      var txt_message_count = $('#txt_message_count').val();

      var flag = true;
      // *******validate all our form fields***********
      // Parent User field validation
      if (txt_product_name == "") {
        $('#txt_product_name').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      // Receiver field validation 
      if (txt_receiver_user == "") {
        $('#txt_receiver_user').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      // Message Count field validation 
      if (txt_message_count == "") {
        $('#txt_message_count').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      // *******Validation end here ****

      // If all are ok then we send ajax request to store_call_functions.php *******
      if (flag) {
        var data_serialize = $("#frm_message_credit").serialize();
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php",
          dataType: 'json',
          data: data_serialize,
          beforeSend: function () { // Before send to Ajax
            $('#submit').attr('disabled', true);
            $('#load_page').show();
          },
          complete: function () { // After complete the Ajax
            $('#submit').attr('disabled', true);
            $('#load_page').hide();
          },
          success: function (response) { // Success
            if (response.status == '0') { // If Failure response returns
              $('#txt_message_count').val('');
              $('#submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
            }
            else if (response.status == 2 || response.status == '2') {
              $('#txt_message_count').val('');
              $('#submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
            } else if (response.status == 1) { // If Success response returns
              $('#submit').attr('disabled', true);
              $("#id_error_display").html(response.msg);
              setInterval(function () {
               window.location = "message_credit_list";
              }, 2000);
            }
          },
          error: function (response, status, error) { // Error
            $('#txt_message_count').val('');
            $('#submit').attr('disabled', false);
            $("#id_error_display").html(response.msg);
          }
        });
      }
    });

    var product_id;
    function getproductid() {
      var txt_product_name = $("#txt_product_name").val();
      product_id = txt_product_name.split("~~")
      if(product_id[5]){
      if (product_id[5] == 'WHATSAPP') {
        product_id = 1;
      } else if (product_id[5] == 'GSM SMS') {
        product_id = 2;
      } else {
        product_id = 3;
      }
    }else{
      product_id = product_id[0];
    }
    get_available_balance(product_id);
    }

    function user_based_product(){
    var txt_receiver_user = $("#txt_receiver_user").val();
    var send_code = "&txt_receiver_user=" + txt_receiver_user + "";
    <? if($bar == '' && $_SESSION["yjwatsp_user_id"] == 1){?>
 $.ajax({
	type: 'post',
        url: "ajax/call_functions.php?tmpl_call_function=user_based_product" + send_code,
        dataType: 'json',
       success: function (response) { // Success
$("#txt_product_name").html(response.msg)
}
});
<? } ?>
    }

    // To Display the Department Admin
    function get_available_balance() {
      var txt_receiver_user = $("#txt_receiver_user").val();
      var send_code = "&product_id=" + product_id + "&txt_receiver_user=" + txt_receiver_user + "";
      $.ajax({
        type: 'post',
        url: "ajax/call_functions.php?tmpl_call_function=get_available_balance" + send_code,
        dataType: 'json',
        success: function (response) { // Success
          if (response.status == 0) { // Failure response
            $('#id_deptadmin').css("display", "block");
            $('#txt_loginid').val('');
            $('#id_loginid').html('');
            $('#submit_signup').attr('disabled', true);
            $("#id_error_display").html(response.msg);
          } else { // Success Response
            $('#submit_signup').attr('disabled', false);
            $('#id_deptadmin').css("display", "block");
            $("#id_error_display").html(response.msg);
          }
        },
        error: function (response, status, error) { // Error
        }
      });
    }
  </script>
</body>

</html>
