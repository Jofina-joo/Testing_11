<?php
session_start();
error_reporting(0);
include_once('api/configuration.php');
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
  <title>Create Prompt ::
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
  <script src="assets/js/jquery-3.6.4.min.js"></script>
  <style>
    textarea {
      resize: none;
    }

    .btn-warning,
    .btn-warning.disabled {
      width: 100% !important;
    }

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

    .preloader-wrapper {
      display: flex;
      justify-content: center;
      background: rgba(22, 22, 22, 0.3);
      width: 100%;
      height: 80%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 10;
      align-items: center;
    }

    .preloader-wrapper>.preloader {
      background: transparent url("assets/img/ajaxloader.webp") no-repeat center top;
      min-width: 128px;
      min-height: 128px;
      z-index: 10;
      /* background-color:#f27878; */
      position: fixed;
    }

    .submit_btn {
      width: 150px !important;

    }
  </style>
</head>

<body>
  <div class="theme-loader">
  </div>
  <div class="preloader-wrapper" style="display:none;">
    <div class="preloader">
    </div>
    <div class="text" style="color: white; background-color:#f27878; padding: 10px; margin-left:400px;">
      <b>Mobile number validation processing ...<br /> Please wait.</b>
    </div>
  </div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <? include("libraries/site_header.php"); ?>

      <? include("libraries/site_menu.php"); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Create Prompt</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Create Prompt</div>
            </div>
          </div>
          <div class="section-body">
            <div class="row">

              <!-- call Type Defiend -->
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <form class="needs-validation" novalidate="" id="frm_contact_group" name="frm_contact_group"
                    action="#" method="post" enctype="multipart/form-data">
                    <div class="card-body">
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Type <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Choose Generic or Customised URL or Personalized Name">[?]</span>
                        </label>
                        <div class="col-sm-7" style="">
                          <input type="radio" name="rdo_newex_group" id="generic_call" checked value="G" tabindex="3"
                            onclick="func_open_newex_group('G')"> Generic&nbsp;&nbsp;&nbsp;
                          <input type="radio" name="rdo_newex_group" id="customised_call" tabindex="3" value="C" <?php if ($_SERVER["QUERY_STRING"] != '') { ?> checked <?php } ?>
                            onclick="func_open_newex_group('C')"> Customised URL&nbsp;&nbsp;&nbsp;
                          <!--<input type="radio" name="rdo_newex_group" id="personalised_call" tabindex="3" value="P" <?php if ($_SERVER["QUERY_STRING"] != '') { ?> checked <?php } ?>
                            onclick="func_open_newex_group('P')"> Personalized Name--->
                        </div>
                        <div class="col-sm-2">
                        </div>
                      </div>


                      <!-- Upload Prompt -->
                      <div class="form-group mb-2 row" id="upload_prompt_container">
                        <label class="col-sm-3 col-form-label">Upload Prompt <label style="color:#FF0000;">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Upload the MP3 or WAV Prompt File Here">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <input type="file" class="form-control" name="upload_prompt" id='upload_prompt' tabindex="6"
                            accept=".mp3,.wav" onchange="validateFileType()" required data-placement="top"
                            data-html="true" title="Upload the MP3 or WAV Prompt File Here"> <label
                            style="color:#FF0000">[The File Must be MP3 or WAV]</label>
                        </div>
                        <div class="col-sm-2">


                          <div class="checkbox-fade fade-in-primary" id='id_mobupload'>
                          </div>
                        </div>
                      </div>

                      <!-- Prompt Second -->
                      <div class="form-group mb-2 row" id="prompt_second_customized">
                        <label class="col-sm-3 col-form-label" style="float: left">Prompt Second<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Enter a Prompt Second.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <input type="text" class="form-control" required name="prompt_second" id="prompt_second"
                            tabindex="5" title="Enter a Prompt Second." pattern="\d+"
                            placeholder="Enter a Prompt Second." minlength="1" maxlength="2"><label
                            style="color:#FF0000">[Min second: 5 & Max length: 60]</label>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>

                      <!-- Company Name -->
                      <div class="form-group mb-2 row" id="id_upload_media">
                        <label class="col-sm-3 col-form-label" style="float: left">Company Name<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Enter the Company Name Here.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <input type="text" class="form-control" required name="company_name" id="company_name"
                            tabindex="5" title="Enter a Company_name/ Person/ Institute."
                            placeholder="Company name/ Person/ Institute" minlength="3" maxlength="10"><label
                            style="color:#FF0000">[Min length: 3 & Max length: 10]</label>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>


                      <!-- Location -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Location<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Choose the Location.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da; " id="location"
                            name="location" class="search">
                            <option value="">Select a Location </option>
                            <? // To get the logged in user and their child users. Primary Admin can view all user
                            $replace_txt = '{
                                  "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
                                  "user_product" : "OBD"
                                }'; // Add user id
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
                            $curl = curl_init();
                            curl_setopt_array(
                              $curl,
                              array(
                                CURLOPT_URL => $api_url . '/list/location_list',
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
                                echo '<option value="">No location available</option>';
                            <? } else if (count($header->location_list) > 0) {  // To display the response data into option button
                            
                              // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition are false to stop the process
                              for ($indicator = 0; $indicator < count($header->location_list); $indicator++) {
                                $location_id = $header->location_list[$indicator]->id;

                                $location_name = $header->location_list[$indicator]->name;
                                $location_code = $header->location_list[$indicator]->state_short_name;
                                ?>
                                    <option value="<?= $location_code . "~~" . $location_id ?>" <? if ($_REQUEST['srch1'] == $location_code) { ?> selected <? } ?>>
                                  <?= $location_name ?>
                                    </option>
                              <? }
                            } ?>
                          </select>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>


                      <!-- Language Code -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Language Code<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Choose the Language Code.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da; " id="language_code"
                            name="language_code" class="search">
                            <option value="">Select a Language </option>
                            <? // To get the logged in user and their child users. Primary Admin can view all user
                            $replace_txt = '{
                                  "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
                                  "user_product" : "OBD"
                                }'; // Add user id
                            $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
                            $curl = curl_init();
                            curl_setopt_array(
                              $curl,
                              array(
                                CURLOPT_URL => $api_url . '/list/language_list',
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
                            //echo $response;
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
                                echo '<option value="">No languages available</option>';
                            <? } else if (count($header->language_list) > 0) {  // To display the response data into option button
                              // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition are false to stop the process
                              for ($indicator = 0; $indicator < count($header->language_list); $indicator++) {
                                $language_id = $header->language_list[$indicator]->language_id;
                                $language_name = $header->language_list[$indicator]->language_name;
                                $language_code = $header->language_list[$indicator]->language_code;
                                ?>
                                    <option value="<?= $language_code . "~~" . $language_id ?>" <? if ($_REQUEST['srch2'] == $language_code) { ?> selected <? } ?>>
                                  <?= $language_name ?>
                                    </option>
                              <? }
                            } ?>
                          </select>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>


                      <!-- Type -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Type<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Choose the Prompt Type.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da; " id="type" name="type"
                            class="search">
                            <option value="">Select a Type </option>
                            <option value="TRANS">TRANSACTION</option>
                            <option value="INFOR">INFORMATION</option>
                            <option value="PROMO">PROMOTION</option>
                          </select>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>


                      <!-- Context -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Context<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Enter the Context Here.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <input type="hidden" name="context" id="context" value="">
                          <input type="text" class="form-control" required name="context_value" id="context_value"
                            readonly tabindex="5" title="Enter the Context Here.">
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>


                      <!-- Remarks -->
                      <div class="form-group mb-2 row" id="id_upload_media">
                        <label class="col-sm-3 col-form-label" style="float: left">Remarks<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip" data-original-title="Enter the Remarks Here">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <input type="text" class="form-control" required name="prompt_remarks" id="prompt_remarks"
                            tabindex="5" title="Enter a remarks." placeholder="Maximum 50 characters" maxlength="50">
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>


                    </div>
                    <div class="card-footer text-center">
                      <div class="text-center">
                        <span class="error_display" id='id_error_display'></span>
                      </div>
                      <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                        value='send_compose_prompt' />
                      <input type="button" onclick="myFunction_clear()" value="Clear" class="btn btn-success submit_btn"
                        id="clr_button" tabindex="9">
                      <input type="submit" name="compose_submit" id="compose_submit" tabindex="10" value="Submit"
                        class="btn btn-success submit_btn">
                      <input type="button" value="Preview Content" onclick="preview_content()" data-toggle="modal"
                        data-target="#previewModal" class="btn btn-success submit_btn" id="pre_button" name="pre_button"
                        tabindex="11">
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>

      </div>


      <!-- Preview Data Modal content-->
      <!-- Modal content-->
      <div class="modal fade" id="default-Modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style=" max-width: 75% !important;">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Template Details</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" id="id_modal_display" style=" word-wrap: break-word; word-break: break-word;">
              <h5>No Data Available</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-success waves-effect " data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Preview Data Modal content End-->

      <!-- After Submit Preview Data Modal content-->
      <!-- Modal content-->
      <!-- Confirmation details content-->
      <div class="modal" tabindex="-1" role="dialog" id="upload_file_popup">
        <div class="modal-dialog" role="document">
          <div class="modal-content" style="width: 400px;">
            <div class="modal-body">
              <button type="button" class="close" data-dismiss="modal" style="width:30px" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <p id="file_response_msg"></p>
              <span class="ex_msg">Are you sure you want to create a campaign?</span>
            </div>
            <div class="modal-footer" style="margin-right:30%;">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Yes</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            </div>
          </div>
        </div>
      </div>
      <!-- After Submit Preview Data Modal content End-->
      <? include("libraries/site_footer.php"); ?>
    </div>
  </div>

  <!-- Confirmation details content-->
  <div class="modal" tabindex="-1" role="dialog" id="campaign_compose_message">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="width: 400px;">
        <div class="modal-body">
          <button type="button" class="close" data-dismiss="modal" style="width:30px" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="container" style="text-align: center;">
            <img alt="image" style="width: 50px; height: 50px; display: block; margin: 0 auto;" id="image_display">
            <br>
            <span id="message"></span>
          </div>
        </div>
        <div class="modal-footer" style="margin-right:40%; text-align: center;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Okay</button>
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
  <script src="assets/js/xlsx.full.min.js"></script>
  <!-- <script src="assets/js/xls.core.min.js"></script> -->

  <script>

    $(function () {
      $('.theme-loader').fadeOut("slow");
    });

    //function to disable the upload prompt when customised
    function func_open_newex_group(type) {
      var uploadPromptContainer = document.getElementById('upload_prompt_container');
      var prompt_second_customized = document.getElementById('prompt_second_customized');
      if (type === 'C') {
        uploadPromptContainer.style.display = 'none';
        $('#upload_prompt').prop("required", false);
        // prompt_second_customized
        prompt_second_customized.style.display = 'flex';
        $('#prompt_second').prop("required", true);
      } else {
        uploadPromptContainer.style.display = 'flex';
        $('#upload_prompt').prop("required", true);
        // prompt_second_customized
        prompt_second_customized.style.display = 'none';
        $('#prompt_second').prop("required", false);
      }
    }

    // Initialize the state of the upload prompt based on the selected radio button
    document.addEventListener("DOMContentLoaded", function () {
      var selectedType = document.querySelector('input[name="rdo_newex_group"]:checked').value;
      func_open_newex_group(selectedType);
    });


    //upload prompt file validation for mp3 and wav
    function validateFileType() {
      var fileInput = document.getElementById('upload_prompt');
      var filePath = fileInput.value;
      var allowedExtensions = /(\.mp3|\.wav)$/i;
      var errorDisplay = document.getElementById('id_error_display');

      if (!allowedExtensions.exec(filePath)) {
        errorDisplay.textContent = 'Invalid file. Please upload a valid MP3 or WAV file.';
        fileInput.value = '';
        return false;
      }
      errorDisplay.textContent = ''; // Clear any previous error messages
      return true;
    }

    //company name field validation for only letters(capital letters) to type
    document.addEventListener('DOMContentLoaded', function () {
      var companyNameInput = document.getElementById('company_name');

      companyNameInput.addEventListener('input', function (event) {
        var inputValue = event.target.value;
        // Use a regular expression to allow only letters and convert to uppercase
        var filteredValue = inputValue.replace(/[^a-zA-Z]/g, '').toUpperCase();

        if (inputValue !== filteredValue) {
          // Update the input value to remove invalid characters and convert to uppercase
          event.target.value = filteredValue;
        }
      });
    });


    // context append from company_name, language, location and type
    $(document).ready(function () {
      // Get references to the input fields
      var companyInput = $('#company_name');
      var locationDropdown = $('#location');
      var languageDropdown = $('#language_code');
      var typeDropdown = $('#type');
      var contextInput = $('#context');
      var context_input = $('#context_value');

      // Function to update the context based on selected values
      function updateContext() {
        var companyValue = companyInput.val() || '';
        var locationValue = locationDropdown.val() || '';
        var locationValue_1 = locationValue.split("~~")
        var locationName = locationValue_1[0];
        var languageValue = languageDropdown.val() || '';
        var languageValue_1 = languageValue.split("~~")
        var languageName = languageValue_1[0];
        var typeValue = typeDropdown.val() || '';

        var companyNamePrefix = companyValue.substring(0, 3);

        var contextValue = '';

        // Concatenate the values to form the context
        //var contextValue = companyNamePrefix + '_' + locationValue + '_' + languageValue + '_' + typeValue;

        if (companyNamePrefix || locationName || languageName || typeValue) {
          contextValue = companyNamePrefix + '_' + locationName + '_' + languageName + '_' + typeValue;
        }

        // Set the context input field value
        context_input.val(contextValue);
        contextInput.val(contextValue);

        var context = $("#context").val();
        console.log(context);

      }

      // Add change event listeners to the input fields
      companyInput.on('change', updateContext);
      locationDropdown.on('change', updateContext);
      languageDropdown.on('change', updateContext);
      typeDropdown.on('change', updateContext);

      // Trigger the initial update when the page loads
      updateContext();
    });

    document.addEventListener('DOMContentLoaded', function () {
      var prompt_second = document.getElementById('prompt_second');
      prompt_second.addEventListener('input', function (event) {
        var inputValue = event.target.value;
        // Use a regular expression to allow only numbers
        var filteredValue = inputValue.replace(/[^0-9]/g, '');

        if (inputValue !== filteredValue) {
          // Update the input value to remove invalid characters
          event.target.value = filteredValue;
        }
      });
    });


    $(document).on("submit", "form#frm_contact_group", function (e) {
      e.preventDefault();
      console.log("Form submitted!");
      $('#compose_submit').prop('disabled', false);
      var flag = true;
      var call_type = $("input[name='rdo_newex_group']:checked").val();
      var language_code = $("#language_code option:selected").val();
      var location = $("#location option:selected").val();
      var type = $("#type option:selected").val();
      var upload_prompt = $("#upload_prompt").val();
      var company_name = $("#company_name").val();
      var context_value = $("#context_value").val();
      var context = $("#context").val();
      var remarks = $("#prompt_remarks").val();
      var prompt_second = $("#prompt_second").val();
      console.log(language_code);
      console.log(location);

      if (!$('#language_code').val()) {
        $('#language_code').css('border-color', 'red');
        flag = false;
      } else {
        $('#language_code').css('border-color', 'green');
      }

      if (!$('#location').val()) {
        $('#location').css('border-color', 'red');
        flag = false;
      } else {
        $('#location').css('border-color', 'green');
      }

      if (!$('#type').val()) {
        $('#type').css('border-color', 'red');
        flag = false;
      } else {
        $('#type').css('border-color', 'green');
      }

      if (!$('#upload_prompt').val() && call_type != 'C') {
        $('#upload_prompt').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#upload_prompt').css('border-color', 'green');
      }


      if (($('#prompt_second').val() == '') && call_type == 'C') {
        $('#prompt_second').css('border-color', 'red');
        flag = false;
        console.log("*(&*^*&^&*^")
      } else if ((prompt_second < 5 || prompt_second > 60) && call_type == 'C') {
        $('#prompt_second').css('border-color', 'red');
        flag = false;
        console.log("KKDDKPO")
      }
      else {
        $('#prompt_second').css('border-color', 'green');
      }

      if (!$('#company_name').val()) {
        $('#company_name').css('border-color', 'red');
        flag = false;
      } else if (company_name.length < 3) {
        $('#company_name').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#company_name').css('border-color', 'green');
      }

      if (!$('#prompt_remarks').val()) {
        $('#prompt_remarks').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#prompt_remarks').css('border-color', 'green');
      }

      /* If all are ok then we send ajax request to ajax/master_call_functions.php *******/
      if (flag) {
        var fd = new FormData(this);
        fd.append('call_type', call_type);
        console.log(fd)
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php",
          dataType: 'json',
          data: fd,
          contentType: false,
          processData: false,
          beforeSend: function () {
            $('#compose_submit').attr('disabled', true);
            $('.theme-loader').show();
          },
          complete: function () {
            // $('#compose_submit').attr('disabled', true);
            $('.theme-loader').hide();
          },
          success: function (response) {
            if (response.status == '0') {
              $('#compose_submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
            } else if (response.status == 2) {
              $('#compose_submit').attr('disabled', false);
              $("#id_error_display").html(response.msg);
            } else if (response.status == 1) {
              $('#srch1').val('');
              // $('#compose_submit').attr('disabled', true);
              $("#id_error_display").html(response.msg);
              setInterval(function () {
                window.location = 'compose_prompt';
              }, 2000);
            }
            $('.theme-loader').hide();
          },
          error: function (response, status, error) {
            // window.location = 'logout';
            $('#compose_submit').attr('disabled', false);
            $('.theme-loader').show();
            $("#id_error_display").html(response.msg);
          }
        });
      }
    });
    // FORM Clear value    
    function myFunction_clear() {
      document.getElementById("frm_contact_group").reset();
      window.location.reload();
    }

    // FORM preview value
    function preview_content() {
      var form = $("#frm_contact_group")[0]; // Get the HTMLFormElement from the jQuery selector
      var data_serialize = $("#frm_contact_group").serialize();
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var fd = new FormData(form); // Use the form element in the FormData constructor
      var upload_contact = $('#upload_contact').text();
      var selectedText1 = '', selectedText2 = '', selectedText3 = '';
      var selectElement1 = document.getElementById("location");
      var selectedOption1 = selectElement1.options[selectElement1.selectedIndex];
      var selectedValue1 = selectedOption1.value;
      if (selectedValue1 != '') {
        selectedText1 = selectedOption1.text;
      }

      var selectElement2 = document.getElementById("language_code");
      var selectedOption2 = selectElement2.options[selectElement2.selectedIndex];
      var selectedValue2 = selectedOption2.value;
      if (selectedValue2 != '') {
        selectedText2 = selectedOption2.text;
      }

      var selectElement3 = document.getElementById("type");
      var selectedOption3 = selectElement3.options[selectElement3.selectedIndex];
      var selectedValue3 = selectedOption3.value;
      if (selectedValue3 != '') {
        selectedText3 = selectedOption3.text;
      }

      fd.append('upload_contact', upload_contact);
      fd.append('location', selectedText1);
      fd.append('language_code', selectedText2);
      fd.append('type', selectedText3);

      $.ajax({
        type: 'post',
        url: "ajax/preview_call_functions.php?preview_functions=preview_compose_prompt",
        data: fd,
        processData: false, // Important: Prevent jQuery from processing the data
        contentType: false, // Important: Let the browser set the content type
        success: function (response) { // Success
          $("#id_modal_display").html(response);
          console.log(response.status);
          $('#default-Modal').modal({ show: true }); // Open in a Modal Popup window
        },
        error: function (response, status, error) { // Error
          console.log("error");
          $("#id_modal_display").html(response.status);
          $('#default-Modal').modal({ show: true });
        }
      });
    }
    //upload prompt file validation for mp3 and wav
  </script>
</body>

</html>