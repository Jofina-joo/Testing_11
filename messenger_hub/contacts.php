<?php
/*
Authendicated users only allow to view this Contacts page.
This page is used to Add a New Contacts by using CSV, Excel, TXT.
After fill the required mobile numbers, click the Generate Contact Button.
It will download the csv file and enable the Upload Contacts button.

This Upload contacts page redirects to GMAIL login.
We have upload those details into Gmail Contacts

Version : 1.0
Author : Arun Rama Balan.G (YJ0005)
Date : 08-Jul-2023
*/

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
site_log_generate("Contacts Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Contacts ::
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
    textarea {
      resize: none;
    }

    .btn-warning,
    .btn-warning.disabled {
      width: 100% !important;
    }

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

      <!-- include header function adding -->
      <? include("libraries/site_header.php"); ?>

      <!-- include sitemenu function adding -->
      <? include("libraries/site_menu.php"); ?>

      <!-- Main Content -->
      <div class="main-content" style="min-height: 500px;">
        <section class="section">
          <!-- Title & Breadcrumb Panel -->
          <div class="section-header">
            <h1>Contacts</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Contacts</div>
            </div>
          </div>

          <!-- Entry Panel -->
          <div class="section-body">
            <div class="row">

              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <form class="needs-validation" novalidate="" id="frm_compose_whatsapp" name="frm_compose_whatsapp"
                    action="#" method="post" enctype="multipart/form-data">
                    <div class="card-body">

                      <!-- Mobile No -->
                      <div class="form-group mb-2 row">
                        <label class="col-sm-3 col-form-label">Enter Mobile Numbers : <label
                            style="color:#FF0000">*</label> <span data-toggle="tooltip"
                            data-original-title="Mobile numbers allowed  with Country Code and without + symbol. Maximum 100 Mobile numbers only allowed. Upload Mobile numbers using Excel, CSV, TXT Files">[?]</span>
                          <label style="color:#FF0000">(With Country Code and without + symbol. New-Line Separated.
                            Maximum 1000 Numbers Allowed)</label></label>
                        <div class="col-sm-7">
                          <textarea id="txt_list_mobno" name="txt_list_mobno" tabindex="1" autofocus required=""
                            placeholder="919234567890,919234567891,919234567892,919234567893"
                            onblur="call_remove_duplicate_invalid()"
                            onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                            class="form-control form-control-primary required" data-toggle="tooltip"
                            data-placement="top" data-html="true" title=""
                            data-original-title="Enter Mobile Numbers. Each row must contains only one mobile no  with Country Code and without + symbol. For Ex : 919234567890,919234567891,919234567892,919234567893"
                            style="height: 150px !important; width: 100%;"></textarea>
                          <div id='txt_list_mobno_txt' class='text-danger'></div>
                        </div>

                        <div class="col-sm-2">
                          <div class="checkbox-fade fade-in-primary" style="display: none;">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to Remove the Duplicates">
                              <input type="checkbox" name="chk_remove_duplicates" id="chk_remove_duplicates" checked
                                value="remove_duplicates" tabindex="2" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove Duplicates</span>
                            </label>
                          </div>
                          <div class="checkbox-fade fade-in-primary" style="display: none;">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to remove Invalids Mobile Nos">
                              <input type="checkbox" name="chk_remove_invalids" id="chk_remove_invalids" checked
                                value="remove_invalids" tabindex="2" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove Invalids</span>
                            </label>
                          </div>
                          <div class="checkbox-fade fade-in-primary" style="display: none;">
                            <label data-toggle="tooltip" data-placement="top" data-html="true" title=""
                              data-original-title="Click here to remove Stop Status Mobile No's">
                              <input type="checkbox" name="chk_remove_stop_status" id="chk_remove_stop_status" checked
                                value="remove_stop_status" tabindex="2" onclick="call_remove_duplicate_invalid()">
                              <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                              <span class="text-inverse" style="color:#FF0000 !important">Remove Stop Status Mobile
                                No's</span>
                            </label>
                          </div>

                          <div class="checkbox-fade fade-in-primary" id='id_mobupload' style="margin-top:10px;">
                            <input type="file" class="form-control" name="upload_contact" id='upload_contact'
                              tabindex="2" <? if ($display_action == 'Add') { ?>required="" <? } ?> data-toggle="tooltip"
                              data-placement="top" data-html="true" title="" onblur="call_remove_duplicate_invalid()"
                              data-original-title="Upload the Mobile Numbers via Excel, CSV, Text Files"> <label
                              style="color:#FF0000">[Upload the Mobile Numbers via Excel, CSV, Text Files]</label>
                          </div>
                        </div>
                      </div>
                      <!-- onclick="call_generate_contacts()" -->
                      <div class="form-group mb-3 row">
                        <label class="col-sm-3 col-form-label" style="float: left"></label>
                        <div class="col-sm-7" style="float: left">
                          <a href="#!" tabindex="3" onclick="call_generate_contacts()"
                            style="float: left; width: 180px;" class="btn btn-info generate_contacts">Generate
                            Contacts</a><!-- Generate Contacts -->
                          <span class="error_display" id='id_error_display' style="margin-left:50px;"></span>
                          <div id='id_generate_csv' style="float: left; padding-left: 10px;"></div>
                          <a href="https://contacts.google.com/u/1/" style="display: none; width: 180px; float: right;"
                            id='id_popup_view' target="popup"
                            onclick="window.open('https://contacts.google.com/u/1/','popup','width=600,height=600'); return false;"
                            tabindex="4" class="btn btn-info">
                            Upload Contacts
                          </a><br>

                          <!-- Upload Contacts -->

                        </div>
                        <div class="col-sm-2">
                          <input type="hidden" name="hid_submit_alow" id="hid_submit_alow" value="0">

                        </div>
                      </div>

                    </div>

                    <div class="card-footer text-center">

                      <!-- Error Display -->
                      <label class="j-label customized_message_typ"><a href="uploads/imports/contacts.csv" download=""
                          class="btn btn-success alert-ajax btn-outline-success"><i
                            class="icofont icofont-download"></i> Sample CSV
                          File</a></label>
                      <label class="j-label customized_message_typ" style="margin-left:20px;"><a
                          href="uploads/imports/contacts.txt" download=""
                          class="btn btn-success alert-ajax btn-outline-success"><i
                            class="icofont icofont-download"></i> Sample TEXT File</a></label>
                      <label class="j-label customized_message_typ" style="margin-left:20px;"><a
                          href="uploads/imports/contacts.xlsx" download=""
                          class="btn btn-success alert-ajax btn-outline-success"><i
                            class="icofont icofont-download"></i> Sample EXCEL File</a></label>
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
  <!-- Page Specific JS File -->
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script src="assets/js/xlsx.core.min.js"></script>
  <script src="assets/js/xls.core.min.js"></script>

  <audio id="audio" style="display: none;"></audio>
  <input type="hidden" name="txt_media_duration" id="txt_media_duration" style="display: none;">

  <script>
    // On loading the page, this function will call
    $(function () {
      $('.theme-loader').fadeOut("slow");
      init();
    });
    var array_invalid_numbers = [];

    // To Enable / Disable the Submit Button
    document.getElementById('hid_submit_alow').addEventListener('change', function (e) {
      if ($('#hid_submit_alow').val() == 0) {
        $('#compose_submit').prop('disabled', true);
      } else {
        $('#compose_submit').prop('disabled', false);
      }
    });

    function init() {
      document.getElementById('upload_contact').addEventListener('change', handleFileSelect, false);
    }

    // handleFileSelect funct
    function handleFileSelect(event) {
      $("#txt_list_mobno_txt").html();
      var fileInput = document.getElementById('upload_contact');
      var errorMessage = document.getElementById('id_error_display');
      // Check if a file is selected
      if (fileInput.files.length === 0) {
        errorMessage.innerHTML = 'Please select a file.';
        $("#txt_list_mobno").val('');
        fileInput.value = ''; // Clear the file input
        return false;
      }

      var flenam = fileInput.value;
      var extn = flenam.split('.').pop().toLowerCase();
      // Check file extension for validation
      if (extn === 'xlsx' || extn === 'xls') {
        ExportToTable();
        const reader = new FileReader()
        reader.onload = handleFileLoad;
        reader.readAsText(event.target.files[0])
      } else if (extn === 'csv' || extn === 'txt') {
        const reader = new FileReader()
        reader.onload = handleFileLoad;
        reader.readAsText(event.target.files[0])
      } else {
        errorMessage.innerHTML = 'Invalid file type. Please upload a CSV, TXT, or Excel file.';
        $("#txt_list_mobno_txt").html();
        $("#txt_list_mobno").val('');
        fileInput.value = ''; // Clear the file input
      }
    }

    function handleFileLoad(event) {
      const fileContent = event.target.result;
      // Split the content into lines
      const lines = fileContent.split('\n');
      // Extract and validate the first column
      const firstColumn = lines.map(line => line.split(',')[0].trim());
      $('#txt_list_mobno').val(firstColumn.join(', '));
      // Set focus to the text input
      $('#txt_list_mobno').focus();
    }

    var value_list = new Array; ///this one way of declaring array in javascript
    function ExportToTable() {
      var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.xlsx|.xls)$/;
      /*Checks whether the file is a valid excel file*/
      if (regex.test($("#upload_contact").val().toLowerCase())) {
        var xlsxflag = false; /*Flag for checking whether excel is .xls format or .xlsx format*/
        if ($("#upload_contact").val().toLowerCase().indexOf(".xlsx") > 0) {
          xlsxflag = true;
        }
        /*Checks whether the browser supports HTML5*/
        if (typeof (FileReader) != "undefined") {
          var reader = new FileReader();
          reader.onload = function (e) {
            var data = e.target.result;
            /*Converts the excel data in to object*/
            if (xlsxflag) {
              var workbook = XLSX.read(data, {
                type: 'binary'
              });
            } else {
              var workbook = XLS.read(data, {
                type: 'binary'
              });
            }
            /*Gets all the sheetnames of excel in to a variable*/
            var sheet_name_list = workbook.SheetNames;
            var cnt = 0; /*This is used for restricting the script to consider only first sheet of excel*/
            sheet_name_list.forEach(function (y) {
              /*Iterate through all sheets*/
              /*Convert the cell value to Json*/
              if (xlsxflag) {
                var exceljson = XLSX.utils.sheet_to_json(workbook.Sheets[y]);
              } else {
                var exceljson = XLS.utils.sheet_to_row_object_array(workbook.Sheets[y]);
              }
              if (exceljson.length > 0 && cnt == 0) {
                BindTable(exceljson, '#txt_list_mobno');
                cnt++;
              }
            });
            $('#txt_list_mobno').show();
            $('#txt_list_mobno').focus();
          }
          if (xlsxflag) {
            /*If excel file is .xlsx extension than creates a Array Buffer from excel*/
            reader.readAsArrayBuffer($("#upload_contact")[0].files[0]);
          } else {
            reader.readAsBinaryString($("#upload_contact")[0].files[0]);
          }
        } else {
          alert("Sorry! Your browser does not support HTML5!");
        }
      } else {
        alert("Please upload a valid Excel file!");
      }
    }

    function BindTable(jsondata, tableid) {
      /*Function used to convert the JSON array to Html Table*/
      // alert("=="+jsondata+"==");
      var columns = BindTableHeader(jsondata, tableid); /*Gets all the column headings of Excel*/
      // Looping the i is less than the jsondata.length.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
      for (var i = 0; i < jsondata.length; i++) {
        //  var row$ = $('<tr/>');  
        // Looping with in the another looping the ii is less than the columns.length.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
        for (var colIndex = 0; colIndex < columns.length; colIndex++) {
          var cellValue = jsondata[i][columns[colIndex]];
          if (cellValue == null)
            cellValue = "";
          value_list.push("\n" + cellValue);
        }
      }
      $(tableid).val(value_list);
    }

    /*Function used to get all column names from JSON and bind the html table header*/
    function BindTableHeader(jsondata, tableid) {
      var columnSet = [];
      // Looping the i is less than the jsondata.length.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
      for (var i = 0; i < jsondata.length; i++) {
        var rowHash = jsondata[i];
        // Looping with in the another looping the rowHash is less than the columns.length.if the condition is true to continue the process and to get the details.if the condition are false to stop the process
        for (var key in rowHash) {
          if (rowHash.hasOwnProperty(key)) {
            if ($.inArray(key, columnSet) == -1) {
              /*Adding each unique column names to a variable array*/
              columnSet.push(key);
              value_list.push("\n" + key);
            }
          }
        }
      }
      return columnSet;
    }
    // To Remove the Duplicate Mobile numbers
    function call_remove_duplicate_invalid() {
      $("#id_error_display").html("");
      $("#txt_list_mobno").css('border-color', '');
      var txt_list_mobno = $("#txt_list_mobno").val();
      var mobile_nolength = txt_list_mobno.length;
      function validateNumber(txt_list_mobno) {
        return /^91\d{10}$/.test(txt_list_mobno);
      }
      if (!validateNumber(txt_list_mobno)) {
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
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php",
          data: { validateMobno: 'validateMobno', mobno: txt_list_mobno, dup: chk_remove_duplicates, inv: chk_remove_invalids },
          success: function (response_msg) { // Success
            let response_msg_text = response_msg.msg;
            const response_msg_split = response_msg_text.split("||");
            $("#txt_list_mobno").val(response_msg_split[0]);
            if (response_msg_split[1] !== '' && !/^,+(\d+)?$/.test(response_msg_split[1]) && mobile_nolength !== '') {
              array_invalid_numbers.push(response_msg_split[1]);
              $("#txt_list_mobno_txt").html("Invalid Mobile Nos : " + array_invalid_numbers);
            }
            if (chk_remove_stop_status == 1) {

            }
          },
          error: function (response_msg, status, error) { // Error
          }
        });
      }
    }

    //Generate the contacts page 
    function call_generate_contacts() {
      var txt_list_mobno = $("#txt_list_mobno").val();
      $("#id_error_display").html('');
      $("#id_generate_csv").html('');
      if (txt_list_mobno == '') {
        $("#txt_list_mobno").css('border-color', 'red');
        $("#id_error_display").html("Please enter mobile numbers");
      }
      if (txt_list_mobno != '') {
        var send_code = "&txt_list_mobno=" + txt_list_mobno;
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php?tmpl_call_function=generate_contacts" + send_code,
          dataType: 'json',
          success: function (response) {
            if (response.status == 0) {
              $("#id_generate_csv").html('');
              $('#id_error_display').html(response.msg);
              $('#hid_submit_alow').val('0');
              $('#compose_submit').prop('disabled', true);
              $('#id_popup_view').css("display", "none");
            } else {
              //$("#id_generate_csv").html(response.msg);
              window.location = response.msg;
              $('#id_error_display').html('');
              $('#hid_submit_alow').val('1');
              $('#compose_submit').prop('disabled', false);
              $('#id_popup_view').css("display", "block");
            }
          },
          error: function (response, status, error) {
 window.location = 'logout';         
         }
        });
      }
    }

$("#txt_list_mobno").keyup(function () {
    var inputValue = $("#txt_list_mobno").val().trim();

    if (inputValue.length === 0) {
        // If the input value is empty, reset the file input
        $("#upload_contact").val(''); // Replace 'fileInput' with the actual ID of your file input element
    }
});
  </script>
</body>

</html>
