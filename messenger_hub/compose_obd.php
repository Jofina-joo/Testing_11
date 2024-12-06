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
  <title>Compose OBD ::
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
    .btn-warning,
    .btn-warning.disabled {
      width: 100% !important;
    }

    .error {
      border-color: red;
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
        .updateprocessing {
      display: flex;
      justify-content: center;
      background: rgba(22, 22, 22, 0.3);
      width: 100%;
      height: 100%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
      align-items: center;
    }

    .updateprocessing>.miniloader {
      background: transparent url("assets/img/ajaxloader.webp") no-repeat center top;
      min-width: 128px;
      min-height: 128px;
      z-index: 10;
      /* background-color:#f27878; */
      position: fixed;
    }
   .sms-options {
    display: flex;
    align-items: center;
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
    <div class="updateprocessing" style="display:none;">
    <div class="miniloader">
    </div>
    <!-- <div class="text" style="color: white; background-color:#63ed7a; padding: 10px; margin-left:400px;"> -->
    <div class="text" style="color: white; background-color:#f27878; padding: 10px; margin-left:400px;">
      <b>File update processing...<br /> Please wait.</b>
    </div>
  </div>

  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <? include ("libraries/site_header.php"); ?>

      <? include ("libraries/site_menu.php"); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Compose OBD</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Compose OBD</div>
            </div>
          </div>
          <div class="section-body">
            <div class="row">
              <!-- Message Type Defiend -->
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <form class="needs-validation" novalidate="" id="frm_contact_group" name="frm_contact_group"
                    action="#" method="post" enctype="multipart/form-data">
                    <div class="card-body">
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Type <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Choose Same Message or Personalized Message">[?]</span></label>
                        <div class="col-sm-7" style="">
                          <input type="radio" name="rdo_newex_group" id="rdo_new_group" checked value="G" tabindex="3"
                            onclick="func_open_newex_group('G')"> Generic&nbsp;&nbsp;&nbsp;<input type="radio"
                            name="rdo_newex_group" id="rdo_ex_group" tabindex="3" value="C" <? if ($_SERVER["QUERY_STRING"] != '') { ?> checked <? } ?>onclick="func_open_newex_group('C')">
                          Customised URL&nbsp;&nbsp;&nbsp;
                          <!---<input type="radio" name="rdo_newex_group" id="rdo_ex_group"
                            tabindex="3" value="P" <? if ($_SERVER["QUERY_STRING"] != '') { ?> checked <? } ?>onclick="func_open_newex_group('P')">
                          Personalized-->
                        </div>
                        <div class="col-sm-2">
                        </div>
                      </div>

                      <!-- Upload Mobile Numbers  -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Upload File <label style="color:#FF0000;">*</label> <span
                            data-toggle="tooltip"
                            data-original-title="Upload the Contact Downloaded CSV File Here or Enter contact Name">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <input type="file" class="form-control" name="upload_contact" id='upload_contact' tabindex="6"
                            onclick="chooseFile()" accept="text/csv" data-placement="top" data-html="true"
                            title="Upload the Contacts Mobile Number via CSV Files"> <label
                            style="color:#FF0000">[Upload the Mobile Number via CSV Files Only]</label>
                               <? if($_SESSION['yjwatsp_user_master_id'] == '4'){ ?>
                             <label
                              style="color:#FF0000;margin-left:20px;">[Test user have below 10 numbers]</label>
                               <? } ?>
                        </div>
                        <div class="col-sm-2">

                          <label class="j-label same_message_typ"><a href="uploads/imports/compose_generic_obd.csv"
                              download="" class="btn btn-success alert-ajax btn-outline-success" tabindex="8"><i
                                class="icofont icofont-download"></i> Sample CSV
                              File</a></label>
                          <label class="j-label personalized_media_typ" style="display: none;"><a
                              href="uploads/imports/compose_personalized_obd.csv" download=""
                              class="btn btn-success alert-ajax btn-outline-success" tabindex="8"><i
                                class="icofont icofont-download"></i> Sample CSV
                              File</a></label>
                          <label class="j-label customized_message_typ" style="display: none;"><a
                              href="uploads/imports/compose_customized_obd.csv" download=""
                              class="btn btn-success alert-ajax btn-outline-success" tabindex="7"><i
                                class="icofont icofont-download"></i> Sample CSV
                              File</a></label>

                        </div>
                      </div>


                      <!-- call retry count -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Call Retry Count<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Upload any Media file [JPG/JPEG/PNG/MP4] below 5 MB Size.">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <select class="form-control" name="call_retry_count" id="call_retry_count" tabindex="5"
                            required title="Select the number of call retries.">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <?/*<option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>*/?>
                          </select>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>

                      <!-- Retry Time Interval -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Retry Time Interval<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Min. 900 Secs &amp; Max. 3600 Secs">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <input type="text" name="retry_time" id="retry_time" required class="form-control"
                            autocomplete="off" placeholder="Min. 900 Secs &amp; Max. 3600 Secs" min="900" max="3600">
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>

                      <!-- context -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label" style="float: left">Context<label
                            style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Upload any Media file [JPG/JPEG/PNG/MP4] below 5 MB Size.">[?]</span>
                        </label>
                        <div class="col-sm-7" style="float: left;">
                          <select style="width: 100%; height:40px;border: 1px solid #ced4da;" id="slt_context"
                            name="slt_context" class="search" onclick="select_context()" onblur="select_context()">
                            <option value="">Select a Context</option>
                          </select>
                           </div>
                            <!-- Audio button -->
                          <label class="j-label same_message_typ">
                            <a href="#" class="btn btn-success alert-ajax btn-outline-success ml-2" id="btnPlayAudio">
                              <i class="fas fa-play"></i> Play
                            </a>
                            <audio id="context-audio" controls style="display: none;"></audio>
                            <a href="#" class="btn btn-danger ml-2" id="btnPauseAudio" style="display: none;">
                              <i class="fas fa-pause"></i> Pause
                            </a>
                          </label>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>
                     <? if($_SESSION['yjwatsp_user_master_id'] == '5'){ ?>
                      <div class="form-group mb-2 row">
                      <label class="col-sm-3 col-form-label">Send SMS<label style="color:#FF0000">*</label>
                        <span data-toggle="tooltip" data-original-title="">[?]</span>
                      </label>
                      <div class="col-sm-7 sms-options">
                            <div class="col-sm-3">
                              <input type="radio" id="yes" name="send_sms" value="Y" class="radio-spacing" onclick="toggleInputBox(true)">
                              <label for="yes">Yes</label>
                              <input type="radio" id="no" name="send_sms" value="N" checked class="radio-spacing" style="margin-left:20px;" onclick="toggleInputBox(false)">
                              <label for="no">No</label>
                          </div>
                          <div id="user-input-container" class="col-sm-9">
                                 <?/* <label class="form-label message_lable">Message Content<label
                            style="color:#FF0000"> *</label>
                          <span data-toggle="tooltip"
                            data-original-title="Enter the message content up to 250 characters">[?]</span></label> */?>Message Content<span style="color:#FF0000"> *</span>
                          <span data-toggle="tooltip"
                            data-original-title="Enter the message content up to 250 characters">[?]</span>
                              <input type="text" id="sms_message" name="sms_message" required class="form-control" maxlength="250" placeholder="Enter up to 250 characters">
                          </div>
                      </div></div>
                      <div class="col-sm-2"></div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>
                  <div class="form-group mb-1 row" id="duration-container">
                        <label class="col-sm-3 col-form-label" style="float: left">Duration<label
                            style="color:#FF0000">*</label>
                            <span data-toggle="tooltip" data-original-title="Max 29 seconds">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                        <input type="text" class="form-control" required name="sms_duration_sec" id="sms_duration_sec"
                            tabindex="5" title="Enter a Prompt Second." pattern="\d+"
                            placeholder="Enter a SMS Duration." minlength="1" maxlength="1"><label
                            style="color:#FF0000" class= "max_length_lable"></label>
                        </div>

                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>
                    </div>
                        <? } ?>     
                    <div class="card-footer text-center">
                      <div class="text-center">
                        <span class="error_display" id='id_error_display'></span>
                      </div>
                      <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                        value='compose_obd' />
                        <input type="hidden" name="filename_upload" id="filename_upload" value="">
                        <input type="hidden" name="prompt_second" id="prompt_second" value="">
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
              <button type="button" class="btn btn-danger save_compose_file" data-dismiss="modal">Yes</button>
              <button type="button" class="btn btn-secondary cancel_compose_file" data-dismiss="modal">No</button>
            </div>
          </div>
        </div>
      </div>
      <!-- After Submit Preview Data Modal content End-->
      <? include ("libraries/site_footer.php"); ?>

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

    $('#retry_time').on('input', function () {
      const retryTimeInput = document.getElementById('retry_time');
      const value = parseInt(retryTimeInput.value, 10);
      if (isNaN(value) || value < 900 || value > 3600) {
        $("#id_error_display").html("Please enter a value between 900 and 3600 seconds.");
        $('#retry_time').css("border-color", "red");
      } else {
        $("#id_error_display").html("");
        $('#retry_time').css("border-color", "");
      }
    });

    // This event listener ensures only numbers are entered and length is restricted to 4 digits
    document.getElementById('retry_time').addEventListener('keypress', function (event) {
      const regex = /[0-9]/;
      const key = String.fromCharCode(event.which);
      const retryTimeInput = event.target;
      if (!regex.test(key) || retryTimeInput.value.length >= 4) {
        event.preventDefault();
      }
    });


    document.addEventListener('DOMContentLoaded', function () {
      const callRetryCountSelect = document.getElementById('call_retry_count');
      // Function to get the current selected value
      function getSelectedValue() {
        const selectedValue = callRetryCountSelect.value;
        if (selectedValue == '0') {
          $('#retry_time').attr("readonly", true);
          $('#retry_time').val("0");
        } else {
          $('#retry_time').attr("readonly", false);
          $('#retry_time').val("");
        }
      }
      // Add event listener for change event
      callRetryCountSelect.addEventListener('change', getSelectedValue);
      // Initial check
      getSelectedValue();
    });

    $('#btn').css("display", "none");
    $('#upload_contact').prop("required", true);

    var invalid_mobile_nos;
    var mobile_array = [];
    // FORM Clear value    
    function myFunction_clear() {
      document.getElementById("frm_contact_group").reset();
      window.location.reload();
    }


    $(function () {
      $('.theme-loader').fadeOut("slow");
      func_open_newex_group('G');
    });

    document.body.addEventListener("click", function (evt) {
      // csvfile();
      //note evt.target can be a nested element, not the body element, resulting in misfires
      // $("#id_error_display").html("");
    });


     function select_context() {
            var selectedOption = $('#slt_context').val();
            //console.log(selectedOption);
            if (selectedOption) {
                console.log("coming");
                var parts = selectedOption.split('~~');
                var prompt_id = parts[0];
                var context = parts[1];
                var prompt_path = parts[2];
                var audio_duration = parts[3];
                //console.log(audio_duration);
                 if(audio_duration >= 7){
                 $("#prompt_second").val("7");
                  $(".max_length_lable").text("[Min second: 1 & Max length: 7"); // Use .text() for labels
                 }else{
                $("#prompt_second").val(audio_duration);
                 $(".max_length_lable").text("[Min second: 1 & Max length: " + (audio_duration - 1) + "]"); // Use .text() for labels
                }
            }else{
  $(".max_length_lable").text("[Min second: - & Max length: -]"); // Use .text() for labels       
}
        }

    function func_open_newex_group(group_available) {
      $.ajax({
        type: 'POST',
        url: "ajax/call_functions.php?call_function=context_list",
        data: {
          type: group_available // Correct key as expected by PHP
        },
        success: function (response) { // Success
          // No need to parse response if it's already a JavaScript object
          if (typeof response === 'string') {
            try {
              response = JSON.parse(response);
            } catch (e) {
              console.error('Response is not valid JSON', e);
              return;
            }
          }
          // Populate the select box with the options
          var select = $('#slt_context');
          select.empty(); // Clear existing options
          select.append('<option value="">Select a Context</option>');
          $.each(response.data, function (index, item) {
            //select.append('<option value="' + item.prompt_id + '~~' + item.context + '">' + item.context + '</option>');
            select.append('<option value="' + item.prompt_id + '~~' + item.context + '~~' + item.prompt_path + '~~' + item.audio_duration + '">' + item.context + '</option>');
          });
        },
        error: function (response, status, error) { // Error
          console.log("error");
        }
      });

// prompt playing button
$(document).ready(function() {
  var currentlyPlayingAudio = null;

  $('#btnPlayAudio').on('click', function(event) {
    event.preventDefault(); // Prevent the default action

    var selectedOption = $('#slt_context').val();
   // console.log(selectedOption);
    if (selectedOption) {
      //console.log("coming")
      var parts = selectedOption.split('~~');
      var prompt_id = parts[0];
      var context = parts[1];
      var prompt_path = parts[2];
      var audio_duration = parts[3];
       //console.log(audio_duration)      
      // Construct the audio file path based on the prompt_path
      var audioFilePath = prompt_path;
      //console.log(audioFilePath);

      // Pause the currently playing audio if any
      if (currentlyPlayingAudio) {
        currentlyPlayingAudio.pause();
        currentlyPlayingAudio.currentTime = 0;
      }
      // Get the audio element
      var audio = $('#context-audio');
      audio.attr('src', audioFilePath);
      audio[0].play(); // Play the audio

      // Set the currently playing audio
      currentlyPlayingAudio = audio[0];

      // Show pause button and hide play button
      $('#btnPlayAudio').hide();
      $('#btnPauseAudio').show();
    } else {
      alert('Please select a context first.');
    }
  });

  $('#btnPauseAudio').on('click', function(event) {
    event.preventDefault(); // Prevent default action

    // Pause the audio
    if (currentlyPlayingAudio) {
      currentlyPlayingAudio.pause();
    }

    // Show play button and hide pause button
    $('#btnPauseAudio').hide();
    $('#btnPlayAudio').show();
  });
  });
//hide columns for if generic and customized

      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      if (group_avail == 'G') {
        $('.personalized_media_typ').css("display", "none");
        $('.customized_message_typ').css("display", "none");
        $('.same_message_typ').css("display", "block");
        $('#btn').css("display", "none");
        $('#frm_contact_group').removeClass("was-validated");
        $('#upload_contact').prop("required", true);
      } else if (group_avail == 'P') {
        $('.customized_message_typ').css("display", "none");
        $('#frm_contact_group').removeClass("was-validated");
        $('.personalized_media_typ').css("display", "block");
        $('.same_message_typ').css("display", "none");
        $('#btn').css("display", "block");
        $('#upload_contact').prop("required", true);
      } else if (group_avail == 'C') {
        $('.personalized_media_typ').css("display", "none");
        $('#frm_contact_group').removeClass("was-validated");
        $('.customized_message_typ').css("display", "block");
        $('.same_message_typ').css("display", "none");
        $('#btn').css("display", "block");
        $('#upload_contact').prop("required", true);
      }
    }

    function chooseFile() {
      document.getElementById('upload_contact').value = '';
    }

    document.getElementById('upload_contact').addEventListener('change', function () {
      validateFile();
    });

    function validateFile() {
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var input = document.getElementById('upload_contact');
      var file = input.files[0];
      var allowedExtensions = /\.csv$/i;
      var maxSizeInBytes = 100 * 1024 * 1024; // 100MB
      if (!allowedExtensions.test(file.name)) {
        $("#id_error_display").html("Invalid file type. Please select an .csv file.");
        document.getElementById('upload_contact').value = ''; // Clear the file input
      } else if (file.size > maxSizeInBytes) {
        $("#id_error_display").html("File size exceeds the maximum limit (100MB).");
        document.getElementById('upload_contact').value = '';// Clear the file input
      } else {
        $("#id_error_display").html("");// Clear any previous error message
        readFileContents(file);
      }
    }
    var copiedFile, file_location_path;
    var cleanedData = [];
    function validateNumber(number) {
      return /^[6-9]\d{9}$/.test(number);
    }
     //copy file
     function copyFile(file) {
      // Extract filename and extension
      var fileNameParts = file.name.split('.');
      const fileName = fileNameParts[0];
      var fileExtension = fileNameParts[1];
      // Append "_copy" to the filename
      var copiedFileName = fileName + "_copy." + fileExtension;
      // Create a new file with the copied filename
      var copiedFile = new File([file], copiedFileName, { type: file.type });
      // Return the copied file
      return copiedFile;
    }

    function readFileContents(file) {
      cleanedData = [];
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();

      $('.preloader-wrapper').show();
      var reader = new FileReader();
      reader.onload = function (event) {
        var contents = event.target.result;
        var workbook = XLSX.read(contents, {
          type: 'binary'
        });
           // Copy the file  
           copiedFile = copyFile(file);
           if(copiedFile === ''){
                  alert("Please reload and create another campaign");
              }
        // Use the copied file as needed
        //console.log("Copied file:", copiedFile);
        var firstSheetName = workbook.SheetNames[0];
        var worksheet = workbook.Sheets[firstSheetName];
        var data = XLSX.utils.sheet_to_json(worksheet, {
          header: 1
        });
        // array values get in invalids, duplicates
        var invalidValues = [];
        var duplicateValuesInColumnA = [];
        var valid_mobile_no = [];
        var valid_variable_values = [];
        var arrays = {};
        var uniqueValuesInColumnA = new Set();
        var validMobileCount = 0; // Variable to count valid mobile numbers
        var variableMismatchCount = 0; // Variable to count variable mismatches
        var bigArray = [];
        var Media_ulrs = [];
        var maxLength = 100;
        var urlsAboveMax = [];
        var urlsAboveMaxLength = 0;
        var mismatch_count = 0;
        //check columns count
        var totalColumns = worksheet['!ref'].split(':').reduce(function (acc, val) {
          return Math.max(acc, XLSX.utils.decode_cell(val).c + 1);
        }, 0);
        // alert(totalColumns);
          // Loop through rows and columns
    for (let i = 0; i < data.length; i++) {
      const row = data[i];

      // Create a new array for each unique row
      let smallArray = [];
      for (const columnName in row) {
        if (!arrays[columnName]) {
          arrays[columnName] = [];
        }
        const value = row[columnName];

        // Check if the current column is the first column (assuming it contains mobile numbers)
        if (columnName === Object.keys(row)[0]) {
          // Perform mobile number validation (replace with your actual validation logic)
          if (!validateNumber(value)) {
            invalidValues.push(value);
            break; // Skip the rest of the loop for invalid numbers
          } else if (uniqueValuesInColumnA.has(value)) {
            duplicateValuesInColumnA.push(value);
            break; // Skip the rest of the loop for duplicate numbers
          } else {
            valid_mobile_no.push(value);
            smallArray.push(value);
            valid_variable_values.push(value);
            uniqueValuesInColumnA.add(value);

            let jsonObject = {};
            for (let columnIndex = 0; columnIndex < totalColumns; columnIndex++) {
              let key = columnIndex; // You can customize the key names as needed
              jsonObject[key] = data[i][columnIndex];
            }
            cleanedData.push(jsonObject); // Add the JSON object to cleanedData
          }
        } else {
          // Add the value to the corresponding array
          arrays[columnName].push(value);
          smallArray.push(value);
        }
      }
      if (smallArray.length > 0) {
        bigArray.push(smallArray);
      }
    }

    //console.log(cleanedData);

 var user_master_id = <?php echo $_SESSION['yjwatsp_user_master_id']; ?>;
//console.log(user_master_id);
//console.log(valid_mobile_no.length);
        var totalCount = data.length;
        if ((invalidValues.length + duplicateValuesInColumnA.length === totalCount)) {
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".ex_msg").css("display", "none");
          $(".modal-footer").css("display", "none");
          // Show the modal
          $('#upload_file_popup').modal('show');
          setTimeout(function () {
            $('#upload_file_popup').modal('hide');
          }, 10000);
          $('#file_response_msg').html('<b>The count of valid numbers is 0. Therefore, it is not possible to create a campaign, and the file cannot be uploaded.</b>');
          document.getElementById('upload_contact').value = '';

        }else if((valid_mobile_no.length > 10) && (user_master_id == 4)){
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".ex_msg").css("display", "none");
          $(".modal-footer").css("display", "none");
          // Show the modal
          $('#upload_file_popup').modal('show');
          setTimeout(function () {
            $('#upload_file_popup').modal('hide');
          }, 10000);
          $('#file_response_msg').html('<b>Test user have below 10 numbers.</b>');
          document.getElementById('upload_contact').value = '';
        } else if ((totalColumns > 1 && group_avail == 'G') || (totalColumns < 2 && (group_avail == 'P' || group_avail == 'C'))) {
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".ex_msg").css("display", "none");
          $(".modal-footer").css("display", "none");
          // Show the modal
          $('#upload_file_popup').modal('show');
          setTimeout(function () {
            $('#upload_file_popup').modal('hide');
          }, 10000);
          $('#file_response_msg').html('<b> Invalid file format.Please check the upload file. </b>');
          document.getElementById('upload_contact').value = '';
        } else if ((invalidValues.length >= 1 && duplicateValuesInColumnA.length >= 1) !== totalCount) {
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".ex_msg").css("display", "");
          $(".modal-footer").css("display", "");
          // Show the modal
          $('#upload_file_popup').modal('show');
          $('#file_response_msg').html('<b>Invalid Numbers: \n' + JSON.stringify(invalidValues.length) + '\n Duplicate Numbers: \n' + JSON.stringify(duplicateValuesInColumnA.length) + '</b>');
           csvfile();
        } else if (duplicateValuesInColumnA.length > 0 !== totalCount) {
          $(".ex_msg").css("display", "");
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".modal-footer").css("display", "");
          // Show the modal
          $('#upload_file_popup').modal('show');
          $('#file_response_msg').html('<b>Duplicate Numbers : \n' + JSON.stringify(duplicateValuesInColumnA.length) + '\n' + '</b>');
         csvfile();
        } else if ((invalidValues.length > 0) && (invalidValues.length !== totalCount)) {
          $(".ex_msg").css("display", "");
          $(".modal-footer").css("display", "");
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          // Show the modal
          $('#upload_file_popup').modal('show');
          $('#file_response_msg').html('<b>Invalid Numbers : \n' + JSON.stringify(invalidValues.length) + '\n' + '</b>');
           csvfile();
        } else {
              csvfile();
          $(".ex_msg").css("display", "");
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
        }
      };
      reader.readAsBinaryString(file);
    }
var fileName;
    function csvfile() {
      var fd = new FormData();
      // Append the copied file to the FormData object
      fd.append('copiedFile', copiedFile);
      $.ajax({
        type: 'post',
        url: "ajax/call_functions.php?storecopy_file=copy_file",
        dataType: 'json',
        data: fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
          $('.updateprocessing').show();
        },
        complete: function () {
          $('.updateprocessing').hide();
          $('.loading_error_message').css("display", "none");
        },
        success: function (response) {
          if (response.status == '0') {
            console.log("File Not copied ...failed");
            //console.log(response.msg);
          } else {
            file_location_path = response.file_location;
            //console.log("File copied Successfully");
            // Convert cleanedData to CSV format
            const productValuesArrays = cleanedData.map(obj => Object.values(obj));
            // const headers = Object.keys(cleanedData[0]);
            // productValuesArrays.unshift(headers);
            const csvContent = productValuesArrays.map(row => row.join(",")).join("\n");
            // Get the file name
           fileName = file_location_path.substring(file_location_path.lastIndexOf('/') + 1);
            //console.log("File name:", fileName);
            // Set the hidden value
            document.getElementById('filename_upload').value = fileName;
            // Convert the CSV content into a Blob
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            // Create a FormData object and append the Blob
            const formData = new FormData();
            formData.append('valid_numbers', blob);
            formData.append('filename', fileName);
            // Send the FormData to the server using AJAX
            $.ajax({
              type: 'POST',
              url: 'csvfile_write.php',
              data: formData,
              contentType: false,
              processData: false,
              success: function (response) {
                console.log('File written successfully');
              },
              error: function (xhr, status, error) {
                //console.error('Error occurred while writing the file:', error);
              }
            });
          }
        }
      });
    }



    // FORM preview value
    function preview_content() {
      var form = $("#frm_contact_group")[0]; // Get the HTMLFormElement from the jQuery selector
      var data_serialize = $("#frm_contact_group").serialize();
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var fd = new FormData(form); // Use the form element in the FormData constructor
      var upload_contact = $('#upload_contact').text();
      fd.append('upload_contact', upload_contact);

      $.ajax({
        type: 'post',
        url: "ajax/preview_call_functions.php?preview_functions=preview_compose_obd",
        data: fd,
        processData: false, // Important: Prevent jQuery from processing the data
        contentType: false, // Important: Let the browser set the content type
        success: function (response) { // Success
          $("#id_modal_display").html(response);
          //console.log(response.status);
          $('#default-Modal').modal({ show: true }); // Open in a Modal Popup window
        },
        error: function (response, status, error) { // Error
          //console.log("error");
          $("#id_modal_display").html(response.status);
          $('#default-Modal').modal({ show: true });
        }
      });
    }

   $('#upload_file_popup').find('.btn-secondary').on('click', function () {
      $('#upload_contact').val('');
       delete_file();
    });

function delete_file(){
    // Send the FormData to the server using AJAX
    const formData = new FormData();
    formData.append('delete_file_name', fileName);

    $.ajax({
        type: 'POST',
        url: "ajax/call_functions.php?deletecopy_file=delete_file",
        dataType: 'json', // Expecting JSON response
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            //console.log('Response:', response);
            if (response.status === 1) {
                console.log('File deleted successfully');
            } else {
                console.error('Error:', response.msg);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error occurred while writing the file:', error);
        }
    });
}


    // Define a flag to track whether the modal has been opened
    $(document).on("submit", "form#frm_contact_group", function (e) {
      e.preventDefault();
      //console.log("Call submit")
      $('#compose_submit').prop('disabled', false);
      $("#id_error_display").html("");
      $('#upload_contact').css('border-color', '');

      //get input field values 
      var upload_contact = $('#upload_contact').val();
      var call_retry_count = $('#call_retry_count').val();
      var retry_time = $('#retry_time').val();
      var slt_context = $('#slt_context').val();
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var send_sms = $("input[type='radio'][name='send_sms']:checked").val();
      var sms_duration_sec =  $('#sms_duration_sec').val();
       var prompt_second =  $('#prompt_second').val();
       var sms_message = $('#sms_message').val();
      var flag = true;

      if (!$('#upload_contact').val()) {
        $('#upload_contact').css('border-color', 'red');
        flag = false;
      } else {
        $('#upload_contact').css('border-color', 'green');
      }

      if (!$('#call_retry_count').val()) {
        $('#call_retry_count').css('border-color', 'red');
        flag = false;
      } else {
        $('#call_retry_count').css('border-color', 'green');
      }

      if (!$('#retry_time').val()) {
        $('#retry_time').css('border-color', 'red');
        flag = false;
      } else {
        $('#retry_time').css('border-color', 'green');
      }

      if (!$('#slt_context').val()) {
        $('#slt_context').css('border-color', 'red');
        flag = false;
      }
      else {
        $('#slt_context').css('border-color', 'green');
      }
      if(retry_time != 0){
      if (isNaN(retry_time) || retry_time < 900 || retry_time > 3600) {
        $("#id_error_display").html("Please enter a value between 900 and 3600 seconds.");
        $('#retry_time').css("border-color", "red");
        flag = false;
      } else {
        $('#retry_time').css('border-color', '');
      }
    }
  
      if(send_sms == 'Y'){
        console.log(sms_duration_sec);
        console.log(prompt_second);
      if (($('#sms_duration_sec').val() == '') && send_sms == 'Y') {
        $('#sms_duration_sec').css('border-color', 'red');
        flag = false;
      }else if((sms_duration_sec > 7) && send_sms == 'Y'){
        $('#sms_duration_sec').css('border-color', 'red');
        flag = false;
       }else if((sms_duration_sec >= prompt_second) && send_sms == 'Y' && (prompt_second < '7')){
        $('#sms_duration_sec').css('border-color', 'red');
        flag = false;
       }
      else {
        $('#sms_duration_sec').css('border-color', 'green');
        //$('#sms_duration_sec').val('');
        //$('#prompt_second').val('');
      }
}
      /* If all are ok then we send ajax request to ajax/master_call_functions.php *******/
      if (flag) {
        var fd = new FormData(this);
          csvfile();
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
            $('#compose_submit').attr('disabled', false);
            $('.theme-loader').hide();
          },
          success: function (response) {
            $('#image_display').removeAttr('src');
            if (response.status == 0) {
              $('#upload_contact').val('');
              $('#compose_submit').removeAttr('disabled');
              $('#compose_submit').prop('disabled', false);
              $('#image_display').attr('src', 'assets/img/failed.png');
              $('#frm_contact_group').removeClass('was-validated');
              $('#campaign_compose_message').modal({ show: true });
              $("#message").html(response.msg);
            } else if (response.status == 2) {
              window.location = 'logout';
              $('#frm_contact_group').removeClass('was-validated');
              $('#compose_submit').prop('disabled', false);
              $('#compose_submit').removeAttr('disabled');
              $('#image_display').attr('src', 'assets/img/failed.png');
              $('#campaign_compose_message').modal({ show: true });
              $("#message").html(response.msg);
            } else if (response.status == 1) {
              $('#upload_contact').val('');
              $('#frm_contact_group').removeClass('was-validated');
              $('#campaign_compose_message').modal({ show: true });
              $('#image_display').attr('src', 'assets/img/success.png');
              $("#message").html(response.msg);
              setInterval(function () {
                window.location = 'compose_obd';
                document.getElementById("frm_contact_group").reset();
              }, 1000);
            }
            $('.theme-loader').hide();
          },
          error: function (response, status, error) {
            // window.location = 'logout';
            $('#upload_contact').val('');
            $('#compose_submit').attr('disabled', false);
            $('.theme-loader').show();
            $("#id_error_display").html(response.msg);
          }

        })
      }
    });

    function disable_texbox(my_filename, new_filename) {
      $("#" + my_filename).prop('disabled', false);
      $("#" + new_filename).val('');
      $("#" + new_filename).prop('disabled', true);
    }

    $("#upload_contact").change(function () { //csv
      if (this.files[0] == '') {
        var file = this.files[0];
        var fileType = file.type;
        var match = ['text/csv'];
        if (!((fileType == match[0]) || (fileType == match[1]) || (fileType == match[2]) || (fileType == match[3]) || (fileType == match[4]) || (fileType == match[5]))) {
          $("#id_error_display").html('Sorry, only CSV file are allowed to upload.');
          $("#upload_contact").val('');
          return false;
        }
      }
    });

  <? if($_SESSION['yjwatsp_user_master_id'] == '5'){ ?>
    function toggleInputBox(show) {
      const inputContainer = document.getElementById('user-input-container');
      var userInput = document.getElementById('sms_message');
       var sms_duration_sec = document.getElementById('sms_duration_sec');
      const durationContainer = document.getElementById('duration-container');
      
      if (show) {
          inputContainer.style.display = 'block';
          durationContainer.style.display = 'block';
          userInput.setAttribute('required', 'required');
          sms_duration_sec.setAttribute('required', 'required');

      } else {
          inputContainer.style.display = 'none';
          durationContainer.style.display = 'none';
          userInput.removeAttribute('required');
          sms_duration_sec.removeAttribute('required');
          // Optionally clear input fields when hidden
          document.getElementById('sms_message').value = '';
          document.getElementById('sms_duration_sec').value = '';
      }
  }
  
  // Initialize the form based on the default selected radio button
  document.addEventListener('DOMContentLoaded', () => {
      const noRadioButton = document.getElementById('no');
      if (noRadioButton.checked) {
          toggleInputBox(false);
      } else {
          toggleInputBox(true);
      }
  });

  document.addEventListener('DOMContentLoaded', function () {
  var prompt_second = document.getElementById('sms_duration_sec');
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

<? } ?>
  </script>
</body>

</html>
