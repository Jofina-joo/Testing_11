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
site_log_generate("Add Contacts in Group Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s"));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Report Generation ::
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
                        <label class="col-sm-3 col-form-label">Compose name: <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Choose compose name">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da; " id="srch1" name="srch1"
                            class="search">
                            <option value="">Choose Compose name</option>
                            <? // To get the logged in user and their child users. Primary Admin can view all user
                            $replace_txt = '{
                              "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
                               "user_product" : "RCS"
                            }'; // Add user id
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
                            $curl = curl_init();
                            curl_setopt_array(
                              $curl,
                              array(
                                CURLOPT_URL => $api_url . '/list/campaign_list',
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
                            site_log_generate("Business Detailed Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
                            $response = curl_exec($curl);
                            curl_close($curl);
                            if ($response == '') { ?>
                              <script>window.location = "logout"</script>
                            <? }
                            // After got response decode the JSON result
                            $header = json_decode($response, false);
                            site_log_generate("Business Detailed Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
                            if ($header->response_status == 403) { ?>
                              <script>window.location = "logout"</script>
                            <? } else if ($header->response_status == 204) { ?>

                            <? } else if (count($header->campaign_list) > 0) {  // To display the response data into option button
                              // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition are false to stop the process
                              for ($indicator = 0; $indicator < count($header->campaign_list); $indicator++) {
                                $compose_whatsapp_id = $header->campaign_list[$indicator]->compose_message_id;
                                $user_id = $header->campaign_list[$indicator]->user_id;
                                $campaign_name = $header->campaign_list[$indicator]->campaign_name . " [" . $header->campaign_list[$indicator]->total_mobile_no_count . "]";
                                ?>
                                    <option value="<?= $compose_whatsapp_id . "&" . $user_id ?>" <? if ($_REQUEST['srch1'] == $compose_whatsapp_id) { ?> selected <? } ?>>
                                  <?= $campaign_name ?>
                                    </option>
                              <? }
                            } ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Enter Mobile Numbers : <span data-toggle="tooltip"
                            data-original-title="Enter mobile number">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <input type="text" class="form-control" name="upload_contact" id='upload_contact' tabindex="7"
                            data-toggle="tooltip" data-placement="top" maxlength="12" data-html="true" title=""
                            data-original-title="Enter mobile number">
                        </div>
                      </div>
                      <div class="card-footer text-center">
                        <span class="error_display" id='id_error_display'></span><br>
                        <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                          value='contact_group' />
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
      init();
    });

    $(document).on("submit", "form#frm_contact_group", function (e) {
      e.preventDefault();
      $('#report_submit').prop('disabled', false);
      var flag = true;
      var camp_id = $('#srch1 option:selected').text();
      var campaign_value = $("#srch1 option:selected").val();
      var mobile_number = $("#upload_contact").val();
      var regex = /^[9][1][6-9][0-9]{9}$/;
      if (!campaign_value) {
        $('#srch1').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#srch1').css('border-color', 'green');
      }
      if (mobile_number && !regex.test(mobile_number)) {
        $('#upload_contact').css('border-color', 'red');
        flag = false;
        $("#id_error_display").html("Invalid Mobile Numbers");
      }
      else {
        $('#upload_contact').css('border-color', 'green');
      }
      /* If all are ok then we send ajax request to ajax/master_call_functions.php *******/
      if (flag) {
        var fd = new FormData(this);
        console.log(fd)
        $.ajax({
          type: 'post',
          url: "ajax/report_rcs.php",
          dataType: 'json',
          data: fd,
          contentType: false,
          processData: false,
          beforeSend: function () {
            $('#report_submit').attr('disabled', true);
            $('.theme-loader').show();
          },
          complete: function () {
            $('#report_submit').attr('disabled', false);
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
              $('#report_submit').attr('disabled', false);
              $("#id_error_display").html("Report generation process started.");
              setInterval(function () {
                window.location = 'report_generation_rcs';
              }, 2000);
            }
            $('.theme-loader').hide();
          },
          error: function (response, status, error) {
            window.location = 'logout';
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

