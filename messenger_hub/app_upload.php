<?php
session_start(); // To start session
error_reporting(0); // The error reporting function

include_once('api/configuration.php'); //  Include configuration.php
extract($_REQUEST); // Extract the request

// If the Session is not available redirect to index page
  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }

$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate("App UpadtePage : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>App Manage ::
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
<style>
     .loader {
    width: 50;
    background-color: #ffffffcf;
  }
  .error-border {
    border-color: red;
  }
  .loader img {}
    .theme-loader {
      display: block;
      position: fixed;
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
<body>
<div class="theme-loader" style="display:none;"></div>
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
          <!-- Title & Breadcrumb Panel -->
          <div class="section-header">
            <h1>App Upload</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item active"><a href="app_details_list">App Details List</a></div>
              <div class="breadcrumb-item">App upload</div>
            </div>
          </div>
          <!-- Entry Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12 col-md-8 col-lg-8 offset-2">
                <div class="card">
                  <form class="needs-validation" novalidate="" id="frm_store" name="frm_store" action="#" method="post"
                    enctype="multipart/form-data">
                    <div class="card-body">
                      <!-- Mobile No start -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">APP Version <label style="color:#FF0000"> * </label> <span data-toggle="tooltip"
                            data-original-title="App Version Include on numbers only">[?]</span></label>
                        <div class="col-sm-9">
                          <input type="text" style="width:75%; float:left; margin-left:2%;" name="app_version"
                            id='app_version' class="form-control" value="" tabindex="2" autofocus required=""
                            maxlength="10" placeholder="App Version" data-toggle="tooltip" data-placement="top" title=""
                            data-original-title="App Version"  onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))">
                        </div>
                      </div>
                      <!-- Mobile No End -->
                      <!-- File Upload Start -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">File Upload <label style="color:#FF0000"> * </label> <span data-toggle="tooltip"
                            data-original-title="APK File size only 50MB">[?]</span></label>
                        <div class="col-sm-9">
                          <input type="file" style="width:75%; float:left; margin-left:2%;" name="apk_file_upload"
                            accept=".apk" id='apk_file_upload' class="form-control" value="" tabindex="2" autofocus
                            required="" placeholder="APK File size only 50MB" data-toggle="tooltip" data-placement="top"
                            title="" data-original-title="APK File size only 50MB" onchange="validateFile()">
                        </div>
                      </div>
                      <!-- File Upload End -->
                      <div class="card-footer text-center">
                        <span class="error_display" id='id_error_display'></span><br> <!-- Error Display -->
                        <input type="submit" name="submit" id="submit" tabindex="10" value="Submit"
                          class="btn btn-success submit_btn">
                        <!-- Submit Button -->

                        <div class="container">
                        </div>
                      </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Footer Panel -->
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
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <!--Remove duplicates numbers -->
  <script>
    // On loading the page, this function will call
    $("#app_version").keyup(function () {
      $("#id_error_display").html("");
    })


    function validateFile() {
      var input = document.getElementById('apk_file_upload');
      var file = input.files[0];
      console.log(file);
      var allowedExtensions = /\.apk$/i;
      var maxSizeInBytes = 50 * 1024 * 1024; // 100MB
       if (!allowedExtensions.test(file.name)) {
           document.getElementById('id_error_display').innerHTML = 'Invalid file type. Please select an .apk file.';
           input.value = ''; // Clear the file input
       } else if (file.size > maxSizeInBytes) {
        document.getElementById('id_error_display').innerHTML = 'File size exceeds the maximum limit (50MB).';
        input.value = ''; // Clear the file input
      } else {
        document.getElementById('id_error_display').innerHTML = ''; // Clear any previous error message      
      }

    }


    // To Submit the Mobile No and display the QR Code From API
    $(document).on("submit", "#frm_store", function (e) {
      var flag = true;
      e.preventDefault(); // Prevent the default form submission
      var app_version = $("#app_version").val();
      var apk_file_upload = $("#apk_file_upload").val();
      if (app_version == '') {
        $('#app_version').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      if (apk_file_upload == '') {
        $('#apk_file_upload').css('border-color', 'red');
        flag = false;
        e.preventDefault();
      }
      if (flag) {
        var formData = new FormData(this);
        formData.append('app_version', app_version);
        $.ajax({
          type: 'post',
          url: "ajax/call_functions.php?tmpl_call_function=upload_apk_file",
          dataType: 'json',
          data: formData,
          contentType: false, // Not to set any content type header
          processData: false,
          beforeSend: function () { // Before send to Ajax
          $('.theme-loader').show();
          $('.theme-loader').css("display", "block");
          $('#submit').attr('disabled', true);
          },
          complete: function () { // After complete the Ajax
          $('.theme-loader').hide();
          $('.theme-loader').css("display", "none"); 
          $('#submit').attr('disabled', false);
          },
          success: function (response) {
            if (response.status == '1') { // Success Response
              $("#id_error_display").html(response.msg);
            $('#submit').attr('disabled', true);
           setInterval(function () {
                window.location = "app_details_list";
              }, 2000);
            }
            else if (response.status == '0') { // Failure Response
              $("#id_error_display").html(response.msg);
           $('#submit').attr('disabled', true);
            }
          },
          error: function (response, status, error) { // Error
            window.location = 'logout';
            $('#app_version').val('');
            $('#submit').attr('disabled', true);
            $("#id_error_display").html(response.msg);
          }
        })
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
