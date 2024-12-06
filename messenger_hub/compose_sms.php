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
  <title>Compose SMS ::
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

      <? include ("libraries/site_header.php"); ?>

      <? include ("libraries/site_menu.php"); ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Compose SMS</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Compose SMS</div>
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
                        <label class="col-sm-3 col-form-label">Message <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Choose Same Message or Personalized Message">[?]</span></label>
                        <div class="col-sm-7" style="">
                          <input type="radio" name="rdo_newex_group" id="rdo_new_group" checked value="N" tabindex="3"
                            onclick="func_open_newex_group('N')"> Generic message &nbsp;&nbsp;&nbsp;<input type="radio"
                            name="rdo_newex_group" id="rdo_ex_group" tabindex="3" value="E" <? if ($_SERVER["QUERY_STRING"] != '') { ?> checked <? } ?>onclick="func_open_newex_group('E')">
                          Personalized message
                        </div>
                        <div class="col-sm-2">
                        </div>
                      </div>
                      <!-- Media Type defined -->
                      <div class="form-group mb-2 row" id="id_personalised_video" style="display:none;">
                        <label class="col-sm-3 col-form-label" style="float: left;">Media<label
                            style="color:#FF0000"></label>
                          <span data-toggle="tooltip"
                            data-original-title="Choose Same Media or Personalized Media">[?]</span></label>
                        <div class="col-sm-7" style="float: left;">
                          <input type="radio" name="rdo_sameperson_video" id="rdo_same_video" checked value="N"
                            tabindex="3" onclick="func_open_personalised_video('N')"> No Media <span
                            data-toggle="tooltip" data-original-title="Media is not mandatory">[?] </span>
                          <input type="radio" name="rdo_sameperson_video" style="margin-left:5px;" id="rdo_same_video"
                            <? if ($_SERVER["QUERY_STRING"] != '') { ?> checked <? } ?> value="S" tabindex="3"
                            onclick="func_open_personalised_video('S')"> Same Media <span data-toggle="tooltip"
                            data-original-title="Same Media sent to all users [One Media to Many Mobile Numbers]">[?]</span><input
                            type="radio" name="rdo_sameperson_video" id="rdo_personalised_video" tabindex="3"
                            style="margin-left:10px;" value="P" <? if ($_SERVER["QUERY_STRING"] != '') { ?> checked <? } ?>onclick="func_open_personalised_video('P')"> Personalized Media <span
                            data-toggle="tooltip"
                            data-original-title="Individual Medias sent to individual users [One Media to One Mobile Number]">[?]</span>
                        </div>
                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;"></div>
                      <!-- Media Type -->
                      <div class="form-group mb-2 row media_type" style="display:none;">
                        <label class="col-sm-3 col-form-label">Media Type <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Choose Same Message or Personalized Message">[?]</span></label>
                        <div class="col-sm-7" style="margin-top:5px;">
                          <input type="radio" name="media_type" id="media_type_img" checked value="I" tabindex="3">
                          Image&nbsp;&nbsp;&nbsp;<input type="radio" name="media_type" id="media_type_vdo" tabindex="3"
                            value="V" <? if ($_SERVER["QUERY_STRING"] != '') { ?> checked <? } ?>>
                          Video
                        </div>
                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;"></div>
                      <!-- Message Content Text Box -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Message Content <label style="color:#FF0000">*</label>
                          <span data-toggle="tooltip"
                            data-original-title="Enter the text for your message in the language that you've selected.">[?]</span></label>
                        <div class="col-sm-7">
                          <div class="row">
                            <div class="col-12">
                              <!-- TEXT area alert -->
                              <textarea id="textarea" class="delete form-control" name="textarea" required
                                maxlength="1024" tabindex="11" placeholder="Enter Message Content" rows="6"
                                style="width: 100%; height: 150px !important;"></textarea>
                              <div class="row" style="right: 0px;">
                                <div class="col-sm-2" style="margin-top: 5px;">Count :
                                  <span id="current_text_value">0</span><span id="maximum">/ 1024</span>
                                </div>
                                <div class="col-sm-6" style="margin-top: 5px;"><span class="error_display variable_msg"
                                    style="display: none;"> [ Variables should be in this format {{ Numbers }} ]</span>
                                </div>
                                <div class="col-sm-4" style=" margin-top: 5px;">
                                  <a href='#!' name="btn" type="button" id="btn" tabindex="12" class="btn btn-success">
                                    + Add variable</a>
                                </div>
                              </div>
                              <!-- TEXT area alert End -->
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- Upload Media -->
                      <div class="form-group mb-2 row" id="id_upload_media" style="display:none;">
                        <label class="col-sm-3 col-form-label" style="float: left">Upload Media
                          <span data-toggle="tooltip"
                            data-original-title="Upload any Media file [JPG/JPEG/PNG/MP4] below 5 MB Size.">[?]</span></label>
                        <div class="col-sm-4 file_image_header" style="float: left;">
                          <input type="file" accept="image/*, video/mp4" class="form-control"
                            onfocus="disable_texbox('file_image_header', 'file_image_header_url')"
                            name="file_image_header" id="file_image_header" tabindex="5"
                            title="Upload any Media file [JPG/JPEG/PNG/MP4] below 5 MB Size.">
                        </div>
                        <div class="col-sm-3 file_image_header_url" style="float: left;">
                          <input class="form-control form-control-primary" type="url" name="file_image_header_url"
                            id="file_image_header_url" maxlength="100" title="Enter Media URL" tabindex="12"
                            onfocus="disable_texbox('file_image_header_url', 'file_image_header')"
                            placeholder="Enter Media URL">
                        </div>
                        <div class="col-sm-2">
                        </div>
                      </div>
                      <div style="clear: both;" style="height:50px;">&nbsp;</div>

                      <div class="form-group mb-2 row txt_message_content_area" style="display:none;">
                        <div class="col-sm-7">
                          <div id="txt_list_mobno_txt" class="text-danger"></div>
                        </div>
                        <div class="col-sm-2">
                          <div class="checkbox-fade fade-in-primary">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to Remove the Duplicates">
                              <input type="checkbox" name="chk_remove_duplicates" id="chk_remove_duplicates" checked=""
                                value="remove_duplicates" tabindex="6" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove
                                Duplicates</span>
                            </label>
                          </div>
                          <div class="checkbox-fade fade-in-primary">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to remove Invalids Mobile Nos">
                              <input type="checkbox" name="chk_remove_invalids" id="chk_remove_invalids" checked=""
                                value="remove_invalids" tabindex="7" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove
                                Invalids</span>
                            </label>
                          </div>
                        </div>
                      </div>
                      <!-- Upload Mobile Numbers  -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Upload Mobile Numbers <label
                            style="color:#FF0000;">*</label> <span data-toggle="tooltip"
                            data-original-title="Upload the Contact Downloaded CSV File Here or Enter contact Name">[?]</span>
                          <label style="color:#FF0000"></label></label>
                        <div class="col-sm-7">
                          <input type="file" class="form-control" name="upload_contact" id='upload_contact' tabindex="6"
                             onclick="chooseFile()" accept="text/csv" data-placement="top" data-html="true"
                            title="Upload the Contacts Mobile Number via CSV Files"> <label
                            style="color:#FF0000">[Upload the Mobile Number via CSV Files Only]</label>
                        </div>
                        <div class="col-sm-2">
                          <label class="j-label same_message_typ"><a href="uploads/imports/compose_whatsapp.csv"
                              download="" class="btn btn-success alert-ajax btn-outline-success" tabindex="8"><i
                                class="icofont icofont-download"></i> Sample CSV
                              File</a></label>
                          <label class="j-label dynamic_media_typ" style="display: none;"><a
                              href="uploads/imports/compose_whatsapp_media.csv" download=""
                              class="btn btn-success alert-ajax btn-outline-success" tabindex="8"><i
                                class="icofont icofont-download"></i> Sample CSV
                              File</a></label>
                          <label class="j-label customized_message_typ" style="display: none;"><a
                              href="uploads/imports/compose_whatsapps.csv" download=""
                              class="btn btn-success alert-ajax btn-outline-success" tabindex="7"><i
                                class="icofont icofont-download"></i> Sample CSV
                              File</a></label>

                          <div class="checkbox-fade fade-in-primary" style="display: none;">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to Remove the Duplicates">
                              <input type="checkbox" name="chk_remove_duplicates" id="chk_remove_duplicates" checked
                                value="remove_duplicates" tabindex="8" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove Duplicates</span>
                            </label>
                          </div>
                          <div class="checkbox-fade fade-in-primary" style="display: none;">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to remove Invalids Mobile Nos">
                              <input type="checkbox" name="chk_remove_invalids" id="chk_remove_invalids" checked
                                value="remove_invalids" tabindex="8" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove Invalids</span>
                            </label>
                          </div>
                          <div class="checkbox-fade fade-in-primary" style="display: none;">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to remove Stop Status Mobile No's">
                              <input type="checkbox" name="chk_remove_stop_status" id="chk_remove_stop_status" checked
                                value="remove_stop_status" tabindex="8" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove Stop Status Mobile
                                No's</span>
                            </label>
                          </div>

                          <div class="checkbox-fade fade-in-primary" id='id_mobupload'>
                          </div>
                        </div>
                      </div>

                    </div>
                    <div class="card-footer text-center">
                      <div class="text-center">
                        <span class="error_display" id='id_error_display'></span>
                      </div>
                      <input type="hidden" class="form-control" name='tmpl_call_function' id='tmpl_call_function'
                        value='compose_sms' />
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

    $('#btn').css("display", "none");
    $('#id_personalised_video').css("display", "none");
    $('#upload_contact').prop("required", true);
    $("#id_checkAll").click(function () {
      $('input:checkbox').not(this).prop('checked', this.checked);
    });

    var invalid_mobile_nos;
    var mobile_array = [];
    // FORM Clear value    
    function myFunction_clear() {
      document.getElementById("frm_contact_group").reset();
      window.location.reload();
    }
    const textarea = document.getElementById('textarea');
    var variable_count = 0;  // Initialize variable_count to 0
    var add_variable_count = variable_count + 1;
    textarea.addEventListener('input', updateResult);
    textarea.value = '';
    const btn = document.getElementById('btn');
    btn.disabled = checkIfButtonShouldBeDisabled(); // Update button state on page load
    btn.addEventListener('click', function handleClick() {
      if (add_variable_count <= 10) {
        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        const textBeforeCursor = textarea.value.substring(0, startPos);
        const textAfterCursor = textarea.value.substring(endPos);
        textarea.value = textBeforeCursor + '{{' + add_variable_count++ + '}}' + textAfterCursor;
        const end = textarea.value.length;
        textarea.setSelectionRange(end, end);
        textarea.focus();
        // Disable button if 10 variables are reached
        if (add_variable_count === 11) {
          btn.disabled = true;
        }
      }
    });

    function updateResult() {
      var t = textarea.value;
      var regex = /{{(\w+)}}/g;
      var matches = t.match(regex);
      variable_count = matches ? matches.length : 0;
      // If the textarea is cleared, allow adding 10 variables again
      if (t.trim() === '') {
        variable_count = 0;
        btn.disabled = false; // Enable the button
      }
      // If more than 10 variables are detected, prevent adding more
      if (variable_count > 10) {
        // Remove the last typed variable
        textarea.value = t.substring(0, t.lastIndexOf('{{')) + t.substring(t.lastIndexOf('}}') + 2);
        variable_count--;
      }
      // Update add_variable_count based on variable_count
      add_variable_count = variable_count + 1;
      // Update button state based on the current variable count
      btn.disabled = checkIfButtonShouldBeDisabled();
    }

    function checkIfButtonShouldBeDisabled() {
      return variable_count >= 10;
    }

    $(function () {
      $('.theme-loader').fadeOut("slow");
      func_open_newex_group('N');
    });
    document.body.addEventListener("click", function (evt) {
      //note evt.target can be a nested element, not the body element, resulting in misfires
      $("#id_error_display").html("");
      $("#file_image_header").prop('disabled', false);
      $("#file_image_header_url").prop('disabled', false);
    });
    function func_change_groupname(sender_id) {
      var send_code = "&sender_id=" + sender_id;
      $('#slt_group').html('');
      console.log("!!!FALSE");
    }

    function func_open_personalised_video(personalized_video) {
      if (personalized_video == 'S') {
        $('.media_type').css("display", "none");
        $('.dynamic_media_typ').css("display", "none");
        $('.same_message_typ').css("display", "none");
        $('.customized_message_typ').css("display", "block");
        // $('#id_upload_media').css("display", "block");
          document.getElementById('upload_contact').value = ''; 
      } else if (personalized_video == 'P') {
        $("#media_type_img").prop("checked", true);
        $('.media_type').css("display", "");
        $('.dynamic_media_typ').css("display", "block");
        $('#id_upload_media').css("display", "none");
        $('.same_message_typ').css("display", "none");
        $('.customized_message_typ').css("display", "none");
           document.getElementById('upload_contact').value = ''; 
      } else if (personalized_video == 'N') {
        $('.media_type').css("display", "none");
        $('.dynamic_media_typ').css("display", "none");
        $('.same_message_typ').css("display", "none");
        $('.customized_message_typ').css("display", "block");
        $('#id_upload_media').css("display", "none");
         document.getElementById('upload_contact').value = ''; 
      }
    }

    function func_open_newex_group(group_available) {
 $('#textarea').css('border-color', '');
      $('#textarea').val('');
      $('#upload_contact').val('');
      $('#file_image_header').val('');
      $('#file_image_header_url').val('');
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      if (group_avail == 'N') {
        // $('#id_upload_media').css("display", "block");
        $('.media_type').css("display", "none");
        $('.variable_msg').css("display", "none");
        $('.dynamic_media_typ').css("display", "none");
        $('.customized_message_typ').css("display", "none");
        $('.same_message_typ').css("display", "block");
        $('#btn').css("display", "none");
        $('#id_personalised_video').css("display", "none");
        $('#id_ex_groupname').css("display", "none");
        $('.txt_message_content_area').css({ display: 'none' });
        $('#textarea').prop("required", true);
        $('#slt_group').prop("required", false);
        $('.required_mn').css("visibility", "hidden");
        $('.required_mn').css("display", "none");
        $('#textarea').val('');
        $("#current_text_value").html("0");
        $('#frm_contact_group').removeClass("was-validated");
        $('#upload_contact').prop("required", true);
      } else if (group_avail == 'E') {
        $('#id_upload_media').css("display", "none");
        $("#rdo_same_video").prop("checked", true);
        $('.dynamic_media_typ').css("display", "none");
        $('#file_image_header_url').val('');
        $('.txt_message_content_area').css("display", "none");
        $('#textarea').val('');
        $('.variable_msg').css("display", "block");
        $("#current_text_value").html("0");
        $('#frm_contact_group').removeClass("was-validated");
        $('.customized_message_typ').css("display", "block");
        $('.same_message_typ').css("display", "none");
        $('#btn').css("display", "block");
        // $('#id_personalised_video').css("display", "");
        $('#textarea').val('');
        $('.required_mn').css("visibility", "visible");
        $('.required_mn').css("display", "");
        $('#id_ex_groupname').css("display", "block");
        //$('#textarea').prop("required", false);
        //$('#slt_group').prop("required", true);
        $('#upload_contact').prop("required", true);
      }
    }

 function chooseFile() {
      document.getElementById('upload_contact').value = '';
    }
    document.getElementById('upload_contact').addEventListener('change', function() {
      validateFile();
    });

function validateFile() {
    var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
    if (textarea.value.trim().length >= 2) {        
    if(group_avail == 'E'){
       var pattern = /{{(\w+)}}/g;
        if (!pattern.test(textarea.value.trim())) {
         console.log("Pattern not found in the text.");
          $("#id_error_display").html("Variable count should not be zero cause it is a customized message.");
      document.getElementById('upload_contact').value = ''; // Clear the file input
           return;
        }
        }
    $('#textarea').css('border-color', '#e4e6fc');
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
/*else if(textarea.value.trim().length == '') {
      if (group_avail == 'E') {
        $('#textarea').css('border-color', 'red');
        document.getElementById('upload_contact').value = '';
        $("#id_error_display").html("Enter the message content.");
       }else {
          $('#textarea').css('border-color', '');
        }
      }*/
else if (textarea.value.trim().length == '') {
        if (group_avail == 'E') {
          $('#textarea').css('border-color', 'red');
          document.getElementById('upload_contact').value = '';
          $("#id_error_display").html("Enter the message content.");
        } else {
          $('#textarea').css('border-color', '#e4e6fc');
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
      }
else if(textarea.value.trim().length < 2 && group_avail == 'E') {
           // Show error message
           $("#id_error_display").html('Message content should have atleast 2 Characters.');
          $('#textarea').css('border-color', 'red');
           document.getElementById('upload_contact').value = '';
         }

}

    function validateNumber(number) {
      return /^91[6-9]\d{9}$/.test(number);
    }

    function readFileContents(file) {
      // Your regular expression pattern
      var Variable_count = '0';
      var pattern = /{{(\w+)}}/g;
      var patternCount = 1;
      var message_txt = textarea.value;
      var matches;
      var Variable_message = '';
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var media_type = $("input[type='radio'][name='rdo_sameperson_video']:checked").val();

      if (group_avail == 'E' && media_type == 'P') {
        patternCount = 2;
        while ((matches = pattern.exec(message_txt)) !== null) {
          patternCount++;
        }
      } else if (group_avail == 'E') {
        while ((matches = pattern.exec(message_txt)) !== null) {
          patternCount++;
        }
      }
      $('.preloader-wrapper').show();
      var reader = new FileReader();
      reader.onload = function (event) {
        var contents = event.target.result;
        var workbook = XLSX.read(contents, {
          type: 'binary'
        });
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
        if (group_avail == 'E') {
          Variable_message = '';
          // Example: Loop through rows and columns
          for (let i = 0; i < data.length; i++) {
            const row = data[i];
            const rows = data[0];
                  if (media_type == 'P' && row[1] !== undefined) {
              Media_ulrs.push(row[1]);
            }
            const totalColumns = rows.length; // Get the total column count in the current row
            // Create a new array for each unique row
            var smallArray = [];
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
                  if (validateNumber(value)) {
                    valid_mobile_no.push(value);
                    smallArray.push(value);
                    valid_variable_values.push(value);
                  }
                  uniqueValuesInColumnA.add(value);
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
          if (patternCount === 1) {
            Variable_message = "Message Content variable is required!";
          } else {
            for (let i = 0; i < bigArray.length; i++) {
              if (bigArray[i].length >= patternCount) {
              } else {
                const currentMismatchCount = patternCount - bigArray[i].length;
                mismatch_count += currentMismatchCount;

                Variable_message = "Variable count is mismatch</br> Mismatch count is : " + mismatch_count;
              }
            } if (Variable_message == '') {
              //Variable_message = "Variable count is Correctly";
            }
          }
        } else {
          var Variable_message = '';
          for (var rowIndex = 0; rowIndex < data.length; rowIndex++) {
            var valueA = data[rowIndex][0]; // Assuming column A is at index 0
            if (!validateNumber(valueA)) {
              invalidValues.push(valueA);
            } else if (uniqueValuesInColumnA.has(valueA)) {
              duplicateValuesInColumnA.push(valueA);
            } else {
              uniqueValuesInColumnA.add(valueA);
            }
          }
        }
            var totalColumns = worksheet['!ref'].split(':').reduce(function (acc, val) {
          return Math.max(acc, XLSX.utils.decode_cell(val).c + 1);
        }, 0);
        var totalCount = data.length;
   if (media_type == 'P') {
          // console.log(Media_ulrs);
          urlsAboveMax = Media_ulrs.filter(url => url.length > maxLength);
          console.log("URLs with length above 100 characters:");
          // console.log(urlsAboveMax);
          urlsAboveMaxLength = urlsAboveMax.length;

        }

        urlsAboveMaxLength = urlsAboveMaxLength ? urlsAboveMaxLength : 0;
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

        } else if ((urlsAboveMaxLength > 0) && (media_type == 'P')) {
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".ex_msg").css("display", "none");
          $(".modal-footer").css("display", "none");
          // Show the modal
          $('#upload_file_popup').modal('show');
          setTimeout(function () {
            $('#upload_file_popup').modal('hide');
          }, 10000);
          $('#file_response_msg').html('<b> Media URL length should be below 100 characters.</b>');
          document.getElementById('upload_contact').value = '';
        } else if (totalColumns > 1 && group_avail == 'N') {
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
        }else if ((invalidValues.length >= 1 && duplicateValuesInColumnA.length >= 1) !== totalCount) {
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".ex_msg").css("display", "");
          $(".modal-footer").css("display", "");
          // Show the modal
          $('#upload_file_popup').modal('show');
          $('#file_response_msg').html('<b>Invalid Numbers: \n' + JSON.stringify(invalidValues.length) + '\n Duplicate Numbers: \n' + JSON.stringify(duplicateValuesInColumnA.length) + '\n ' + Variable_message + '</b>');
        } else if (duplicateValuesInColumnA.length > 0 !== totalCount) {
          $(".ex_msg").css("display", "");
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          $(".modal-footer").css("display", "");
          // Show the modal
          $('#upload_file_popup').modal('show');
          $('#file_response_msg').html('<b>Duplicate Numbers : \n' + JSON.stringify(duplicateValuesInColumnA.length) + '\n' + Variable_message + '</b>');
        } else if ((invalidValues.length > 0) && (invalidValues.length !== totalCount)) {
          $(".ex_msg").css("display", "");
          $(".modal-footer").css("display", "");
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          // Show the modal
          $('#upload_file_popup').modal('show');
          $('#file_response_msg').html('<b>Invalid Numbers : \n' + JSON.stringify(invalidValues.length) + '\n' + Variable_message + '</b>');
        } else {
          $(".ex_msg").css("display", "");
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
        }
      };
      reader.readAsBinaryString(file);
    }

    $('#upload_file_popup').find('.btn-secondary').on('click', function () {
      $('#upload_contact').val('');
    });

    //TEXT AREA COUNT
    //$("#textarea").keyup(function () {
      //$("#current_text_value").text($(this).val().length);
    //});
    
      $("#textarea").keyup(function () {
  var trimmedText = $(this).val().trim(); // Trim the input text
  $("#current_text_value").text(trimmedText.length); // Update the length of trimmed text
});

    // FORM preview value
    function preview_content() {
      var form = $("#frm_contact_group")[0]; // Get the HTMLFormElement from the jQuery selector
      var txt_sms_mobno = $('input[name="txt_sms_mobno"]:checked').serialize()
      var data_serialize = $("#frm_contact_group").serialize();
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var fd = new FormData(form); // Use the form element in the FormData constructor
      var upload_contact = $('#upload_contact').text();
      fd.append('upload_contact', upload_contact);
      if (txt_sms_mobno == "") {
      }
      else {
        var mobile_split = txt_sms_mobno.split("&")
        for (var i = 0; i < mobile_split.length; i++) {
          var mobile_no_split = mobile_split[i].split("=")
          if (i == 0) {
            mobile_array = mobile_no_split[1]
          }
          else {
            mobile_array = mobile_array + "," + mobile_no_split[1]
          }
        }
      }
      fd.append('mobile_numbers', mobile_array);
      $.ajax({
        type: 'post',
        url: "ajax/preview_call_functions.php?preview_functions=preview_compose_sms",
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


    // To Remove the Duplicate Mobile numbers
    function call_remove_duplicate_invalid() {
      var chk_remove_duplicates = 0;
      if ($("#chk_remove_duplicates").prop('checked') == true) {
        chk_remove_duplicates = 1;
      }
      var chk_remove_invalids = 0;
      if ($("#chk_remove_invalids").prop('checked') == true) {
        chk_remove_invalids = 1;
      }
      var chk_remove_stop_status = 0;
      if ($("#chk_remove_stop_status").prop('checked') == true) {
        chk_remove_stop_status = 1;
      }
      var upload_contact = $('#upload_contact').text();
      if (upload_contact != '') {
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php",
          data: { validateMobno: 'validateMobno', dup: chk_remove_duplicates, inv: chk_remove_invalids },
          success: function (response) { // Success
            if (response.status == 1) {
              let response_msg_text = response.msg;
              const response_msg_split = response_msg_text.split("||");
              if (response_msg_split[1] != '') {
                invalid_mobile_nos = "Invalid Mobile Nos : " + response_msg_split[1] + "This Mobile Numbers Are Invalid Mobile Numbers.Are You Sure The Compose sms ?";
              }
              if (chk_remove_stop_status == 1) {
              }
            } else {
              $("#id_error_display").html(response.msg);
            }
          },
          error: function (response, status, error) { // Error
          }
        });
      }
    }

    // Define a flag to track whether the modal has been opened
    var modalOpened = false;

    $(document).on("submit", "form#frm_contact_group", function (e) {
      e.preventDefault();
      $('#compose_submit').prop('disabled', false);
      $("#id_error_display").html("");
      $('#textarea').css('border-color', '#a0a0a0');
      $('#slt_group').css('border-color', '#a0a0a0');
      $('#upload_contact').css('border-color', '#a0a0a0');
      $('#file_image_header').css('border-color', '#a0a0a0');

      //get input field values 
      var textarea = $('#textarea').val();
      textarea = textarea.trim();
      var slt_group = $('#slt_group').val();
      var upload_contact = $('#upload_contact').val();
      var group_avail = $("input[type='radio'][name='rdo_newex_group']:checked").val();
      var txt_sms_mobno = $('input[name="txt_sms_mobno"]:checked').serialize();
      var file_image_header = $('#file_image_header').val();
      var file_image_header_url = $('#file_image_header_url').val();
      var media = $("input[type='radio'][name='rdo_sameperson_video']:checked").val();
      var flag = true;

      var mobile_array = "";

      /********validate all our form fields***********/
      /* textarea field validation  */
          if (group_avail == 'N') {
        if (textarea == "") {
          $('#textarea').css('border-color', 'red');
          flag = false;
        }else if (textarea.length < 2){
        // Show error message
        $("#id_error_display").html('Message content should have atleast 2 characters .');
          $('#textarea').css('border-color', 'red');
          flag = false;
         }
      } else if (group_avail == 'E') {
          if (textarea == "") {
          $('#textarea').css('border-color', 'red');
           document.getElementById('upload_contact').value = '';
          flag = false;
        }else if (textarea.length < 2){
        // Show error message
        $("#id_error_display").html('Message content should have atleast 2 Characters.');
          $('#textarea').css('border-color', 'red');
          flag = false;
         }
       }

      if (media == 'S') {
        if (file_image_header_url == "" && file_image_header == "") {
          $('#file_image_header').css('border-color', 'red');
          flag = false;
        }
      }
      <? if ($_REQUEST['group'] == '') { ?>
      <? } ?>
      var txt_sms_mobno = $('input[name="txt_sms_mobno"]:checked').serialize();
      var txt_qr_mobno = $("input[type='radio'][name='txt_qr_mobno']:checked").val();
      var mobile_split = txt_sms_mobno.split("&")
      for (var i = 0; i < mobile_split.length; i++) {
        var mobile_no_split = mobile_split[i].split("=")
        if (i == 0) {
          mobile_array = mobile_no_split[1]
        }
        else {
          mobile_array = mobile_array + "," + mobile_no_split[1]
        }
      }

      /* If all are ok then we send ajax request to ajax/master_call_functions.php *******/
      if (flag) {
        var fd = new FormData(this);
        fd.append('mobile_numbers', mobile_array);
        fd.append('variable_count', variable_count);
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
              $('#textarea').val('');
               $('#frm_contact_group').removeClass('was-validated');
              $('#upload_contact').val('');
              $('#compose_submit').attr('disabled', false);
              // $("#id_error_display").html(response.msg);
              $('#image_display').attr('src', 'assets/img/failed.png');
              $('#campaign_compose_message').modal({ show: true });
              $("#message").html(response.msg);
            } else if (response.status == 2) {
              window.location = 'logout';
              $('#frm_contact_group').removeClass('was-validated');
              $('#compose_submit').attr('disabled', false);
              // $("#id_error_display").html(response.msg);
              $('#compose_submit').attr('disabled', false);
              $('#image_display').attr('src', 'assets/img/failed.png');
              $('#campaign_compose_message').modal({ show: true });
              $("#message").html(response.msg);
            } else if (response.status == 1) {
                $('#textarea').val('');
                $('#upload_contact').val('');
                $('#frm_contact_group').removeClass('was-validated');
              //$('#compose_submit').attr('disabled', true);
              $('#campaign_compose_message').modal({ show: true });
              $('#image_display').attr('src', 'assets/img/success.png');
              $("#message").html(response.msg);
              // $("#id_error_display").html(response.msg);
              setInterval(function () {
                window.location = 'compose_sms';
                document.getElementById("frm_contact_group").reset();
              }, 2000);
            }
            $('.theme-loader').hide();
          },
          error: function (response, status, error) {
            window.location = 'logout';
            $('#textarea').val('');
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

    $("#file_image_header").change(function () {
      $("#id_error_display").html('');

      var file = $("#file_image_header")[0].files[0];
      var fileType = file.type;
      var fileSize = Math.round(file.size / 1024 / 1024);

      var allowedTypes = ['image/jpeg', 'image/png', 'video/mp4', 'video/x-msvideo', 'video/x-matroska', 'video/quicktime'];
      if (!allowedTypes.includes(fileType)) {
        $("#id_error_display").html('Sorry, only JPG/PNG/MP4/AVI/MOV/MKV files are allowed to upload.');
        $("#file_image_header").val('');
        return false;
      }

      if (fileSize > 5) {
        $("#id_error_display").html('Sorry, Upload Media file below 5 MB size');
        $("#file_image_header").val('');
        return false;
      }

      // Additional check for video duration
      if (fileType.startsWith('video/')) {
        var reader = new FileReader();
        reader.onload = function (e) {
          var video = document.createElement('video');
          video.src = e.target.result;
          video.onloadedmetadata = function () {
            var duration = video.duration; // in seconds
            var maxDuration = 30;
            if (duration > maxDuration) {
              $("#id_error_display").html('Sorry, Upload video with duration below 30 seconds.');
              $("#file_image_header").val('');
            }
          };
        };
        reader.readAsDataURL(file);
      }
    });

$('#textarea').on('input', function (event) {
    // Get the input value
    var inputValue = $(this).val();

    // Check if backticks (`), single quotes ('), or double quotes (") are present in the input
    if (inputValue.includes('`') || inputValue.includes("'") || inputValue.includes('"')) {
        // Remove all occurrences of backticks, single quotes, and double quotes from the input
        inputValue = inputValue.replace(/[`'"]/g, '');

        // Update the input value
        $(this).val(inputValue);
    }
});
  </script>
</body>

</html>
