<?php
/*
Authendicated users only allow to view this Add Sender ID page.
This page is used to view the Add a New Sender ID.
It will send the form to API service and Save to Whatsapp Facebook
and get the response from them and store into our DB.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 27-Jul-2023
*/

session_start(); // start session
error_reporting(0); // The error view_usering function

include_once('api/configuration.php'); // Include configuration.php
extract($_REQUEST); // Extract the request

// If the Session is not available redirect to index page
  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }

if ($_SESSION['yjwatsp_user_master_id'] != 1) { ?>
  <script>window.location = "dashboard";</script>
  <?php exit();
}

$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate("View On Boarding Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s"));

// To Send the request  API
$replace_txt = '{
  "user_id" : "' . $_REQUEST["usr"] . '"
}';
// Add bearer token
$bearer_token = "Authorization: " . $_SESSION["yjwatsp_bearer_token"] . "";

// It will call "p_login" API to verify, can we allow to login the already existing user for access the details
$curl = curl_init();
curl_setopt_array(
  $curl,
  array(
    CURLOPT_URL => $api_url . '/list/view_user',
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
// Log file generate
site_log_generate("View On Boarding Page : " . $uname . " Execute the service [$replace_txt, $bearer_token] on " . date("Y-m-d H:i:s"), "../");
$response = curl_exec($curl);
curl_close($curl);
// After got response decode the JSON result
$state1 = json_decode($response, false);
// Log file generate
site_log_generate("View On Boarding Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), "../");

// To get the API response one by one data and assign to Session
if ($state1->response_status == 200) {

  // Looping the indicator is less than the count of response_result.if the condition is true to continue the process.if the condition are false to stop the process
  for ($indicator = 0; $indicator < count($state1->view_user); $indicator++) {
    $user_names = $state1->view_user[$indicator]->user_name;
    $user_email = $state1->view_user[$indicator]->user_email;
    $user_mobile = $state1->view_user[$indicator]->user_mobile;
    $user_type = $state1->view_user[$indicator]->user_type;
    $user_details = $state1->view_user[$indicator]->user_details;
    $user_status = $state1->view_user[$indicator]->user_status;
    $login_id = $state1->view_user[$indicator]->login_id;

  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>View On Boarding ::
    <?= $site_title ?>
  </title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">

  <!-- style include in css -->
  <style>
    .loader {
      width: 50;
      background-color: #ffffffcf;
    }

    .loader img {}

    .grid_clr_green {
      background-color: #c1e5cf1f;
      margin-right: -10px;
      margin-left: -10px;
    }

    .grid_clr_white {
      margin-right: -10px;
      margin-left: -10px;
    }
  </style>
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
            <h1>View On Boarding</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">View On Boarding</div>
            </div>
          </div>

          <!-- Form Entry Panel -->
          <div class="section-body">
            <div class="row">

              <div class="col-8 col-md-8 col-lg-8 offset-md-2">
                <div class="card" style="padding: 10px;margin-top:50px;">
                  <form class="md-float-material form-material" action="#" name="frm_edit_onboarding"
                    id='frm_edit_onboarding' class="needs-validation" novalidate="" enctype="multipart/form-data"
                    method="post">
                    <div>
                      <div class="row m-b-20">
                        <div class="col-md-12">
                          <h5 class="text-center"><i class="icofont icofont-sign-in"></i>Basic Information </h5>
                        </div>
                      </div>
                      
                      <div class="row mt-2 grid_clr_green">
                        <span class="col-6 label">
                          User Name :
                        </span>
                        <span class="col-6">
                          <?= $user_names ?>
                        </span>
                      </div>


                      <div class="row mt-2 grid_clr_white">
                        <span class="col-6 label">
                          Login Id :
                        </span>
                        <span class="col-6">
                          <?= $login_id ?>
                        </span>
                      </div>
                      <div class="row mt-2 grid_clr_green">
                        <span class="col-6 label">
                          Contact No :
                        </span>
                        <span class="col-6">
                          <?= $user_mobile ?>
                        </span>
                      </div>

                      <div class="row mt-2 grid_clr_white">
                        <span class="col-6 label">
                          Email ID :
                        </span>
                        <span class="col-6">
                          <?= $user_email ?>
                        </span>
                      </div>

                      <div class="row mt-2 grid_clr_green">
                        <span class="col-6 label">
                          User Type :
                        </span>
                        <span class="col-6">
                          <?= $user_type ?>
                        </span>
                      </div>

                      <div class="row mt-2 grid_clr_white">
                        <span class="col-6 label">
                          User Deatils :
                        </span>
                        <span class="col-6">
                          <?= $user_details ?>
                        </span>
                      </div>

                      <div class="row  m-t-30" style="margin-top:20px;">
                        <div class="col-md-4" style="text-align:center"></div>
                        <?/* if ($user_status != 'Y') { */?>
                          <div class="col-md-4" style="text-align:center">
                            <input type="hidden" class="form-control" name='txt_user' id='txt_user'
                              value='<?= $_REQUEST["usr"] ?>' />
                            <input type="hidden" class="form-control" name='call_function' id='call_function'
                              value='apprej_onboarding' />
                              <?php
// Check if the 'action' parameter is set in the URL and its value is 'viewrep'
 if ($_GET['action'] == 'active'){ ?>
  <button type="button" onclick="approve_usr_popup()"
  style="width:150px;margin-left:auto;margin-right:auto;" tabindex="30"
  title="Active Account" class="btn btn-success btn-md btn-block waves-effect waves-light text-center ">Active Account</button>
  <input type="hidden" class="form-control" name='user_status' id='user_status'
                              value='A' />
  <? } else if($_GET['action'] == 'reject'){ ?>
  <button onclick="reject_usr_popup()" type="button" title="Reject Account"
                              style="width:150px;margin-left:auto;margin-right:auto;margin-top: 0px;"
                              tabindex="31"
                              class="btn btn-danger btn-md btn-block waves-effect waves-light text-center ">Reject</button>
                              <input type="hidden" class="form-control" name='user_status' id='user_status'
                              value='R' />
 <? } else if($_GET['action'] == 'suspend'){ ?>
  <button onclick="suspend_usr_popup()" type="button" title="Suspend Account"
                              style="width:150px;margin-right:auto;margin-left:auto;margin-top: 0px;"
                              tabindex="31"
                              class="btn btn-danger btn-md btn-block waves-effect waves-light text-center ">Suspend</button>
                              <input type="hidden" class="form-control" name='user_status' id='user_status'
                              value='D' />
 <? }  else if($_GET['action'] == 'makereseller'){ ?>
  <button onclick="make_reseller_popup()" type="button" title="Make Reseller Account"
                              style="width:150px;margin-right:auto;margin-left:auto;margin-top: 0px;"
                              tabindex="31"
                              class="btn btn-danger btn-md btn-block waves-effect waves-light text-center ">Make Reseller</button>
                        
                              <input type="hidden" class="form-control" name='user_masterid' id='user_masterid'
                              value='2' />
 <? }else if($_GET['action'] == 'addusers'){ ?>
  <button onclick="add_usr_popup()" type="button" title="Add Users"
                              style="width:150px;margin-right:auto;margin-left:auto;margin-top: 0px;"
                              tabindex="31"
                              class="btn btn-danger btn-md btn-block waves-effect waves-light text-center ">Add Users</button>
                              <input type="hidden" class="form-control" name='user_ids[]' id='user_ids'
                              value='2' />
 <? }?>
                          
                          </div>
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
  <!-- Confirmation details content Reject-->
  <div class="modal" tabindex="-1" role="dialog" id="reject-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#" method="post"
            enctype="multipart/form-data">
            <div class="form-group mb-2 row">
              <label class="col-sm-3 col-form-label">Reason<label style="color:#FF0000">*</label></label>
              <div class="col-sm-9">
                <input class="form-control form-control-primary" type="text" name="txt_remarks" id="txt_remarks"
                  maxlength="100" pattern="[a-zA-Z0-9 -_]+" onkeypress="return clsAlphaNoOnly(event)"
              value=""  title="Reason to Reject" tabindex="12" placeholder="Reason to Reject">
              </div>
            </div>
          </form>
          <p>Are you sure you want to reject ?</p>
        </div>
        <div class="modal-footer">
          <span class="error_display common_information" id='id_error_reject'></span>
          <button type="button" class="btn btn-success reject_btn" >Reject</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation details content Approve-->
  <div class="modal" tabindex="-1" role="dialog" id="approve-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to Active Account ?</p>
        </div>
        <div class="modal-footer">
        <span class="error_display common_information" id='id_error_approve'></span>
          <button type="button" class="btn btn-success approve_cls">Active</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

    <!-- Confirmation details content Approve-->
    <div class="modal" tabindex="-1" role="dialog" id="suspend-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to Suspend Account ?</p>
        </div>
        <div class="modal-footer">
        <span class="error_display common_information" id='id_error_approve'></span>
          <button type="button" class="btn btn-success">Suspend</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

      <!-- Confirmation details content Approve-->
      <div class="modal" tabindex="-1" role="dialog" id="reseller-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to Make Reseller?</p>
        </div>
        <div class="modal-footer">
        <span class="error_display common_information" id='id_error_approve'></span>
          <button type="button" class="btn btn-success">Make Reseller</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
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
  </script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <!--Remove dublicates numbers -->
  <script>

    //popup function
    function reject_usr_popup() {
      $('#reject-Modal').modal({ show: true });
    }

    // Call remove_senderid function with the provided parameters
    $('#reject-Modal').find('.reject_btn').on('click', function () {
      $('#reject-Modal').modal({ show: false });
      var txt_remarks = $('#txt_remarks').val();
      var flag = true;
      if (txt_remarks == "") {
        $('#txt_remarks').css('border-color', 'red');
        flag = false;
      } else {
        account_status();
      }
    });

 $('#reject-Modal').on('hidden.bs.modal', function () {
$('#txt_remarks').val('');
    });

    function clsAlphaNoOnly(e) { // Accept only alpha numerics, no special characters 
      var key = e.keyCode;
      if ((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (key >= 48 && key <= 57) || (key == 32) || (key == 95)) {
        return true;
      }
      return false;
    }

    function account_status() {
      /* If all are ok then we send ajax request to call_functions.php *******/
      var fd = $("#frm_edit_onboarding").serialize();
      $.ajax({
        type: 'post',
        url: "ajax/call_functions.php",
        dataType: 'json',
        data: fd,
        beforeSend: function () { // Before Send to Ajax
          $('#reject_btn').attr('disabled', true);
          $('#load_page').show();
        },
        complete: function () { // After complete the Ajax
          $('#reject_btn').attr('disabled', false);
          $('#load_page').hide();
        },
        success: function (response) { // Success
          // alert(response.status)
          if (response.status == '2' || response.status == '0') { // Failure Response
            $('#reject_btn').attr('disabled', false);
            alert(response.msg);
          } else if (response.status == 1) { // Success Response
            $('#reject_btn').attr('disabled', true);
            alert(response.msg);
            // setTimeout(function () {
            window.location = "users_list";
            // }, 2000); // Every 2 seconds it will check
          }
        },
        error: function (response, status, error) { // If any error occurs
          $('#reject_btn').attr('disabled', false);
          $("#common_information").html(response.msg);
        }
      });
    }

    //approve_usr_popup function
    function approve_usr_popup() {
      $('#approve-Modal').modal({ show: true });
    }
    // Call remove_senderid function with the provided parameters
    $('#approve-Modal').find('.btn-success').on('click', function () {
      $('#approve-Modal').modal({ show: false });
      account_status();
    });
    //approve_usr_popup function
    function suspend_usr_popup() {
      $('#suspend-Modal').modal({ show: true });
    }
    // Call remove_senderid function with the provided parameters
    $('#suspend-Modal').find('.btn-success').on('click', function () {
      $('#approve-Modal').modal({ show: false });
      account_status();
    });

    //approve_usr_popup function
    function make_reseller_popup() {
      $('#reseller-Modal').modal({ show: true });
    }
    // Call remove_senderid function with the provided parameters
    $('#reseller-Modal').find('.btn-success').on('click', function () {
      $('#reseller-Modal').modal({ show: false });
      account_status();
    });

  </script>
</body>

</html>
