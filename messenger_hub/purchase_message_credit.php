<?php
/*
Authendicated users only allow to view this Purchase Message Credit page.
This page is used to update the Credit to a user.
Parent user can assign the credit list to their childs.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 24-Jul-2023
*/

session_start(); // start session
error_reporting(0); // The error reporting function

include_once('api/configuration.php'); // Include configuration.php
extract($_REQUEST); // Extract the request

// If the Session is not available redirect to index page
  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }

$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate("Purchase Message Credit Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s"));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Purchase Purchase Message Credit ::
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
            <h1>Purchase Message Credit</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item active"><a href="purchase_message_list">Payment History</a></div>
              <div class="breadcrumb-item">Purchase Message Credit</div>
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

                      <div class="form-group mb-2 row" style="display: none;">
                        <label class="col-sm-3 col-form-label">Parent User</label>
                        <div class="col-sm-9">
                          <!-- Parent User Panel -->
                          <input type="text" name="txt_parent_user" id='txt_parent_user' class="form-control"
                            data-toggle="tooltip" readonly data-placement="top" required=""
                            data-original-title="Parent User" tabindex="1"
                            value="<?= $_SESSION["yjwatsp_parent_id"] ?>">
                        </div>
                      </div>

                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Choose Plan</label>
                        <div class="col-sm-9">
                          <select name="txt_pricing_plan" id='txt_pricing_plan' class="form-control"
                            data-toggle="tooltip" data-placement="top" title="" required=""
                            data-original-title="Choose Plan" tabindex="1" autofocus onchange="check_credit();"
                            onblur="check_credit();">
                            <? // To get the child user list from API
                            $replace_txt = '{
                              "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
                            }';
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add bearer Token
                            $curl = curl_init();
                            curl_setopt_array(
                              $curl,
                              array(
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
                            site_log_generate("Purchase Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
                            $response = curl_exec($curl);
                            curl_close($curl);
                            // After got response decode the JSON result
                            $state1 = json_decode($response, false);
                            site_log_generate("Purchase Message Credit Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

                            // Based on the JSON response, list in the option button
 if ($response == '') { ?>
    <script>
      window.location = "logout"
    </script>
  <? }
 $rsp_id = $state1->response_status;
  if ($rsp_id == 403) {
    ?>
    <script>
      window.location = "logout"
    </script>
                           <? }  if ($state1->num_of_rows > 0) {
                              for ($indicator = 0; $indicator < $state1->num_of_rows; $indicator++) {
                                // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process and to get the details.if the condition are false to stop the process?>
                                <option
                                  value="<?= $_SESSION['yjwatsp_user_id'] . "~~" . $state1->pricing_slot[$indicator]->pricing_slot_id . "~~" . $state1->pricing_slot[$indicator]->price_from . "~~" . $state1->pricing_slot[$indicator]->price_to . "~~" . $state1->pricing_slot[$indicator]->price_per_message ?>"
                                  <? if ($indicator == 0) { ?>selected<? } ?>>
                                  <?= $state1->pricing_slot[$indicator]->price_from . " - " . $state1->pricing_slot[$indicator]->price_to . " [Rs." . $state1->pricing_slot[$indicator]->price_per_message . "](" . $state1->pricing_slot[$indicator]->rights_name . " )" ?>
                                </option>
                                <?
                              }
                            }
                            ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Amount in INR</label>
                        <div class="col-sm-9">
                          <input type="hidden" name="txt_message_amount" id='txt_message_amount' class="form-control"
                            value="<?= $txt_message_amount ?>" tabindex="3" required maxlength="7"
                            onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                            placeholder="Amount" data-toggle="tooltip" data-placement="top" title=""
                            data-original-title="Amount">
                          <span class="error_display" id='id_count_display'></span>
                          <!-- Message Count and Error display -->
                        </div>
                      </div>

                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Comments</label>
                        <div class="col-sm-9">
                          <input type="text" name="usrcrdbt_comments" id='usrcrdbt_comments' class="form-control" minlength="3"
                            maxlength="200" style="height:40px;" pattern="[a-zA-Z0-9 -_]+" onkeypress="return clsAlphaNoOnly(event)" value="<?= $usrcrdbt_comments ?>" tabindex="4"
                            required="" placeholder="Comments" >
                          <!-- Message Count and Error display -->
                        </div>
                      </div>

                      <div class="form-group mb-2 row">
                        <div class="col-sm-12">
                          <input type="checkbox" name="chk_terms" id="chk_terms" required value="" tabindex="5">
                          <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                          <span class="text-inverse" style="color:#FF0000 !important">I read and accept <a href="#"
                              style="color:#FF0000 !important" data-toggle="tooltip" data-placement="top" title=""
                              data-original-title="Terms & Conditions." class="alert-ajax btn-outline-info">Terms &amp;
                              Conditions.</a></span>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <span class="error_display" id='id_error_display'></span><br> <!-- Error Display -->
                      <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                        value='purchase_sms_credit' />
                      <input type="submit" name="submit" id="submit" tabindex="6" value="Submit"
                        class="btn btn-success">
                      <input type="hidden" name="hdsms" id="hdsms" class="form-control" maxlength="50" value="">
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

  <!-- Modal content-->
  <div class="modal fade" id="default-Modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Terms & Conditions</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="id_modal_display">
          <h5>Welcome</h5>
          <p>Waiting for load Data..</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary waves-effect " data-dismiss="modal">Close</button>
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
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script>
    $(".alert-ajax").click(function () {
      $("#id_modal_display").load("uploads/imports/payment_terms.htm", function () {
        $('#default-Modal').modal({ show: true });
      });
    });

    function check_credit() {
      console.log("Hai");
      var gst_percentage = 0.18;
      $("#id_count_display").html("");
      let text_credit = $('#txt_pricing_plan').val();
      const split_credit = text_credit.split("~~");
      console.log("text_credit1 : " + text_credit);
      console.log("split_credit3 : " + split_credit[3]);
console.log(split_credit[4]);
      var splitarray = split_credit[3] * split_credit[4];
      console.log("splitarray : " + splitarray);
console.log(splitarray + +(splitarray * gst_percentage));
      var ttl_amt = Math.round(+splitarray + +(splitarray * gst_percentage));
      console.log("ttl_amt : " + ttl_amt);
      $("#txt_message_amount").val(ttl_amt);
      $("#hdsms").val(ttl_amt);
      $("#id_count_display").html(ttl_amt);
    }


    $("#submit").click(function (e) {
      $("#id_error_display").html("");
      $('#txt_message_amount').css('border-color', '#00000026');
      $('#usrcrdbt_comments').css('border-color', '#00000026');
      var flag = 1;

      var txt_message_amount = $('#txt_message_amount').val();
      var usrcrdbt_comments = $('#usrcrdbt_comments').val();

      if (txt_message_amount == "") {
        $('#txt_message_amount').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }

      if (usrcrdbt_comments == "") {
        $('#usrcrdbt_comments').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }else if(usrcrdbt_comments.length < 3){
        flag = false;
        $("#id_error_display").html("Comments must be a 3 characters");
        e.preventDefault();       
      }

      if ($("#chk_terms").prop('checked') == false) {
        $('#chk_terms').css('border-color', 'red');
        $("#id_error_display").html("Please select terms & conditions");
        flag = false;
        e.preventDefault();
      }

      /* If all are ok then we send ajax request to ajax/master_call_functions.php *******/
      if (flag) {
        var data_serialize = $("#frm_message_credit").serialize();
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php",
          dataType: 'json',
          data: data_serialize,
          async: true,
          beforeSend: function () {
            $('#submit').attr('disabled', true);
            $('#load_page').show();
          },
          complete: function () {
            $('#submit').attr('disabled', false);
            $('#load_page').hide();
          },
          success: function (response) {
            if (response.status == '0' || response.status == 0) {
              $('#submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
            } else if (response.status == '1' || response.status == 1) {
              $('#submit').attr('disabled', false);
              <? if (($_SESSION['yjwatsp_user_master_id'] == 3 && $_SESSION['yjwatsp_parent_id'] == 1 ) || $_SESSION['yjwatsp_user_master_id'] == 2) { ?>
                $("#id_error_display").html("Payment Processing..");

                let text_credit = $('#txt_pricing_plan').val();
                const split_credit = text_credit.split("~~");

                var getAmount = $("#txt_message_amount").val();
                console.log(getAmount);
                var product_id = split_credit[1];
                var useremail = "<?= $_SESSION['yjwatsp_user_email'] ?>";
                var totalAmount = getAmount * 100;
                var options = {
                  "key": "<?= $rp_keyid ?>", // your Razorpay Key Id
                  "amount": totalAmount,
                  "name": "<?= $_SESSION['yjwatsp_user_name'] ?>",
                  "description": "Purchase Message Credits",
                  "image": "https://www.codefixup.com/wp-content/uploads/2016/03/logo.png",
                  "handler": function (response) {
                    $.ajax({
                      url: 'ajax/rppayment_call_functions.php?action_process=razorpay_payment',
                      type: 'post',
                      dataType: 'json',
                      data: {
                        razorpay_payment_id: response.razorpay_payment_id, totalAmount: totalAmount, product_id: product_id, useremail: useremail,
                      },
                      beforeSend: function () { // Before send to Ajax
                      },
                      success: function (response) {
                        $('#txt_message_amount').val("");
                        $('#usrcrdbt_comments').val("");
                        if (response.status == '0' || response.status == 0) {
                          $("#id_error_display").html(response.msg);
                        } else if (response.status == '1' || response.status == 1) {
                          // window.location = 'ajax/rppayment_call_functions.php?action_process=razorpay_payment&status=success&productCode='+ data.productCode +'&payId='+ data.paymentID +'&userEmail='+ data.userEmail +'';
                          $("#id_error_display").html(response.msg);
                          setInterval(function () {
                            window.location = "purchase_message_list";
                          }, 1000);
                        }
                      },
                      error: function (response, status, error) {
                        window.location = "logout";
                      }
                    });
                  },
                  "theme": {
                    "color": "#528FF0"
                  }
                };
                var rzp1 = new Razorpay(options);
                rzp1.open();
                e.preventDefault();
              <? } else { ?>
                window.location = "purchase_message_list";
              <? } ?>
            }
            $('#load_page').hide();
            $("#result").hide().html(output).slideDown();
          },
          error: function (response, status, error) {
            $('#submit').attr('disabled', false);
            window.location = 'logout';
            $("#id_error_display").html(response.msg);
          }
        });
      }
    });
 function clsAlphaNoOnly(e) { // Accept only alpha numerics, no special characters 
      var key = e.keyCode;
      if ((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (key >= 48 && key <= 57) || (key == 32) || (key == 95)) {
        return true;
      }
      return false;
    }
  </script>
</body>

</html>
