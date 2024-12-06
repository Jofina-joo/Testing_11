<?php
session_start();
error_reporting(0);
include_once ('api/configuration.php');
extract($_REQUEST);

  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }

$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . $current_date);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>CDR Report Generation ::
    <?= $site_title ?>
  </title>
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <style>
    .theme-loader {
      display: block;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 100;
      width: 100%;
      height: 100%;
      background-color: rgba(192, 192, 192, 0.5);
      background-image: url("assets/img/loader.gif");
      background-repeat: no-repeat;
      background-position: center;
    }
  </style>
</head>

<body>
  <div class="theme-loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <? include ("libraries/site_header.php"); ?>

      <? include ("libraries/site_menu.php"); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Report Generation</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Report Generation</div>
            </div>
          </div>

          <div class="section-body">
            <div class="row">

              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <form class="needs-validation" novalidate="" id="frm_contact_group" name="frm_contact_group"
                    action="#" method="post" enctype="multipart/form-data">
                    <div class="card-body">

                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">User name: <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Choose compose name">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da; " id="get_user_id"
                            name="get_user_id" class="search" onchange="get_campaign_details()"
                            onblur="get_campaign_details()">
                            <option value="">Choose User name</option>
                            <? // To get the logged in user and their child users. Primary Admin can view all user
                            $replace_txt = '{
                              "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
                            }'; // Add user id
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
                            $curl = curl_init();
                            curl_setopt_array(
                              $curl,
                              array(
                                CURLOPT_URL => $api_url . '/list/get_users',
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
                            site_log_generate("Business Detailed Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
                            $response = curl_exec($curl);
                            curl_close($curl);
                            if ($response == '') { ?>
                              <script>window.location = "logout"</script>
                            <? }
                            // After got response decode the JSON result
                            $header = json_decode($response, false);
                            site_log_generate("Business Detailed Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . $current_date, '../');
                            if ($header->response_status == 403) { ?>
                              <script>window.location = "logout"</script>
                            <? } else if ($header->response_status == 204) { ?>

                            <? } else if (count($header->result) > 0) {  // To display the response data into option button
                              // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition are false to stop the process
                              for ($indicator = 0; $indicator < count($header->result); $indicator++) {
                                $user_name = $header->result[$indicator]->user_name;
                                $user_id = $header->result[$indicator]->user_id;
                                ?>
                                    <option value="<?= $user_id ?>" <? if ($_REQUEST['get_user_id'] == $user_id) { ?> selected <? } ?>>
                                  <?= $user_name ?>
                                    </option>
                              <? }
                            } ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Campaign name: <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Choose compose name">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da; " id="campaign_id"
                            name="campaign_id" class="search">
                            <option value="">Select a campaign</option>

                          </select>
                        </div>
                      </div>
                      <div class="card-footer text-center">
                        <span class="error_display" id='id_error_display'></span><br>
                        <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                          value='obd_report_generation' />
                        <input type="submit" name="report_submit" id="report_submit" tabindex="8" value="Submit"
                          class="btn btn-success">
                      </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>

      </div>

      <? include ("libraries/site_footer.php"); ?>

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

  <script src="assets/js/xlsx.core.min.js"></script>
  <script src="assets/js/xls.core.min.js"></script>

  <script>
    $(function () {
      $('.theme-loader').fadeOut("slow");
    });
    var send_code;
function get_campaign_details() {
  var userId = $('#get_user_id').val(); // Get the selected user ID
  console.log(userId);
  var $campaignSelect = $('#campaign_id');
  $campaignSelect.empty();
  if (userId) {
    send_code = { user_id: userId }; // Create the data object with the user ID
    $.ajax({
      type: 'post',
      url: "ajax/call_functions.php?call_function=get_campaignlist",
      data: send_code,
      contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
      dataType: 'json',
       beforeSend: function () {
            $('.theme-loader').show();
          },
          complete: function () {
            $('.theme-loader').hide();
          },
      success: function (response) {
        $campaignSelect.empty();
        if (response == null) {
          $campaignSelect.append('<option value="">Select a campaign</option>'); // Add default option
        } else if (response && response.campaign_list) {
          // Clear existing options
          $campaignSelect.append('<option value="">Select a campaign</option>'); // Add default option
          // Loop through the response and add options
          $.each(response.campaign_list, function (index, campaign) {
            var optionText = campaign.campaign_name + " [" + campaign.total_mobile_no_count + "]";
            var optionValue = campaign.compose_message_id + "&" + campaign.user_id;
            var isSelected = ('<?= $_REQUEST['campaign_id'] ?>' == campaign.compose_message_id) ? 'selected' : '';
            $campaignSelect.append('<option value="' + optionValue + '" ' + isSelected + '>' + optionText + '</option>');
          });
        }
      },
      error: function (response, status, error) {
        console.log('Error:', error);
        // Optionally redirect to logout or show an error message
      }
    });
  } else {
    console.log('User ID is not available');
    $campaignSelect.append('<option value="">Select a campaign</option>');
  }
}



    $(document).on("submit", "form#frm_contact_group", function (e) {
      e.preventDefault();
      $('#report_submit').prop('disabled', false);
      var flag = true;
      var user_id = $('#get_user_id option:selected').text();
      var campaign_value = $("#campaign_id option:selected").val();
      var camp_id = $('#campaign_id option:selected').text();
      var campaign_value = $("#campaign_id option:selected").val();
      var mobile_number = $("#upload_contact").val();
      var regex = /^[9][1][6-9][0-9]{9}$/;
      if (!campaign_value) {
        $('#campaign_id').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#campaign_id').css('border-color', 'green');
      }

      if (!user_id) {
        $('#get_user_id').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#get_user_id').css('border-color', 'green');
      }
      /* If all are ok then we send ajax request to ajax/master_call_functions.php *******/
      if (flag) {
        var fd = new FormData(this);
        console.log(fd)
        $.ajax({
          type: 'post',
          url: "ajax/report_obd.php",
          dataType: 'json',
          data: fd,
          contentType: false,
          processData: false,
          beforeSend: function () {
            $('#report_submit').attr('disabled', true);
            $('.theme-loader').show();
          },
          complete: function () {
            $('#report_submit').attr('disabled', true);
            $('.theme-loader').hide();
          },
          success: function (response) {
            if (response.status == '0') {
              $('#report_submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
            } else if (response.status == 2) {
              $('#report_submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
              $('#report_submit').attr('disabled', false);
            } else if (response.status == 1) {
              $('#srch1').val('');
              $('#report_submit').attr('disabled', true);
              $("#id_error_display").html("Report generation process completed.");
              setInterval(function () {
                window.location = 'report_generation_obd';
              }, 2000);
            }
            $('.theme-loader').hide();
          },
          error: function (response, status, error) {
            // window.location = 'logout';
            $('#report_submit').attr('disabled', false);
            $('.theme-loader').show();
            $("#id_error_display").html(response.msg);
          }
        });
      }
    });
  </script>
</body>

</html>
