<?php
/*
Authendicated users only allow to view this Campaign List Stop page.
This page is used to Add List the Sender ID and its Status.
Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table

Version : 1.0
Author : Arun Rama Balan.G (YJ0005)
Date : 07-Jul-2023
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
site_log_generate("Campaign List Stop Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Stop Campaign List ::
    <?= $site_title ?>
  </title>
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="assets/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="assets/css/searchPanes.dataTables.min.css">
  <link rel="stylesheet" href="assets/css/select.dataTables.min.css">
  <link rel="stylesheet" href="assets/css/colReorder.dataTables.min.css">
  <link rel="stylesheet" href="assets/css/buttons.dataTables.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/loader.css">
  <link rel="stylesheet" href="assets/css/components.css">
</head>
<style>
  .btn-disabled {
    white-space: pre-wrap;
    /* css-3 */
    white-space: -moz-pre-wrap;
    /* Mozilla, since 1999 */
    white-space: -pre-wrap;
    /* Opera 4-6 */
    white-space: -o-pre-wrap;
    /* Opera 7 */
    word-wrap: break-word;

  }

  /* width:100px; */
  .modal-content {
    max-width: 400px;
  }

  #id_stop_campaign_list {
    position: relative;
    height: 800px;
  }

  .dataTables_filter label,
  .previous,
  .next {
    font-weight: bolder;
  }
</style>

<body>
  <div class="loading" style="display:none;">Loading&#8230;</div>
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
            <h1>Whatsapp List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Whatsapp List</div>
            </div>
          </div>

          <!-- List Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive" id="id_stop_campaign_list"> <!-- List from API -->
                      Loading ..
                    </div>
                  </div>
                </div>
              </div>
            </div>


          </div>
        </section>
      </div>

      <!-- Confirmation details content-->
      <div class="modal" tabindex="-1" role="dialog" id="default-Modal">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Confirmation details</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" style="height: 50px;">
              <p>Are you sure you want to delete ?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation details senderid model stop -->
      <div class="modal" tabindex="-1" role="dialog" id="senderid-Modal-stop">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header" style="height: 50px;">
              <h4 class="modal-title">
                <!-- <label class="col-form-label">Sender ID <label
                            style="color:#FF0000">*</label></label>   -->
                <label class="form-label">Select All Sender ID </label> <input type="checkbox" onclick="toggle1(this);"
                  value="multiselect-all" class="cls_checkbox1"
                  style="width: 30px;height: 20px; border: 2px solid black;">
              </h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#"
                method="post" enctype="multipart/form-data">
                <div class="form-group mb-2 row">
                  <div class="col-sm-12 mobile_no_chkbox" style="top:10px; display:none;">
                  </div>
                </div>
              </form>
              <div class="modal-footer" style="margin-right:25%;">
                <span class="error_display" id='id_error_display'></span>
                <button type="button" class="btn btn-success">Submit</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation details senderid model restart -->
      <div class="modal" tabindex="-1" role="dialog" id="senderid-Modal-restart">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header" style="height: 50px;">
              <h4 class="modal-title">
                <!-- <label class="col-form-label">Sender ID <label
                            style="color:#FF0000">*</label></label>   -->
                <label class="form-label">Select All Sender ID </label> <input type="checkbox" onclick="toggle1(this);"
                  value="multiselect-all" class="cls_checkbox1"
                  style="width: 30px;height: 20px; border: 2px solid black;">
              </h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#"
                method="post" enctype="multipart/form-data">
                <div class="form-group mb-2 row">
                  <div class="col-sm-12 mobile_no_chkbox" style="top:10px; display:none;">
                  </div>
                </div>
              </form>
              <div class="modal-footer" style="margin-right:25%;">
                <span class="error_display" id='id_error_display'></span>
                <button type="button" class="btn btn-success">Submit</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>
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

  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>

  <script>
    // On loading the page, this function will call
    $(document).ready(function () {
      campaign_list_stop();
    });

    // To Display the Whatsapp NO List
    function campaign_list_stop() {
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?call_function=campaign_list_stop",
        dataType: 'html',
        success: function (response) { // Success
          $("#id_stop_campaign_list").html(response);
        },
        error: function (response, status, error) { // Error
          window.location = 'logout';
        }

      });
    }
    // setInterval(campaign_list_stop, 60000); // Every 1 min (60000), it will call

    function toggle1(source) {
      let isChecked = source.checked
      var checkboxes = document.querySelectorAll('input[class="cls_checkbox1"]');
      for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
      }
    }

    var campaign_names, indicatoris, send_code;
    // when click the stop campaign button using this function
    function sender_id_popup(campaign_name, indicatori) {
      campaign_names = campaign_name;
      var send_code = "&campaign_name=" + campaign_names;
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?tmpl_call_function=stop_senderid_list" + send_code,
        dataType: 'html',
        beforeSend: function () {
          $(".loading").css('display', 'block');
          $('.loading').show();
        },
        complete: function () {
          $(".loading").css('display', 'none');
          $('.loading').hide();
        },
        success: function (response) { // Success
          if (response == 204 || response == 201) {
            $(".mobile_no_chkbox").html("");
            $(".form-label").html("No Data Available");
            $(".cls_checkbox1").css("display", "none");
            $(".modal-footer").css("display", "none");
          } else {
            $(".mobile_no_chkbox").css("display", "");
            $(".mobile_no_chkbox").html(response);
          }
          $('#senderid-Modal-stop').modal({ show: true });
        }
      })
    }

    // Call remove_senderid function with the provided parameters
    $('#senderid-Modal-stop').find('.btn-success').on('click', function () {
      var txt_whatsapp_mobno = $('input[name="txt_whatsapp_mobno"]:checked').serialize();
      if (txt_whatsapp_mobno == "") {
        $("#id_error_display").html("Please Select Sender ID");
      }
      else {
        var mobile_split = txt_whatsapp_mobno.split("&")
        var mobile_numbers;
        for (var i = 0; i < mobile_split.length; i++) {
          var mobile_no_split = mobile_split[i].split("=")
          if (i == 0) {
            mobile_numbers = mobile_no_split[1]
          }
          else {
            mobile_numbers = mobile_numbers + "," + mobile_no_split[1]
          }
        }
        var send_code = "&mobile_numbers=" + mobile_numbers + "&campaign_name=" + campaign_names;
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php?tmpl_call_function=send_stop_campaign" + send_code,
          contentType: false,
          processData: false,
          dataType: 'json',
          beforeSend: function () {
            $(".loading").css('display', 'block');
            $('.loading').show();
          },
          complete: function () {
            $(".loading").css('display', 'none');
            $('.loading').hide();
          },
          success: function (response) { // Success
            if (response.status == '0' || response.status == 0) {
              $("#id_error_display").html(response.msg);
              setInterval(function () {     // if the status is '1' after close the model
                $('#senderid-Modal-stop').modal({ show: false });
              }, 2000);
            } else if (response.status == '2' || response.status == 2) {
              $("#id_error_display").html(response.msg);
              setInterval(function () {     // if the status is '2' after close the model
                $('#senderid-Modal-stop').modal({ show: false });
              }, 2000);
            } else { //  otherwise display the success message
              $("#id_error_display").html("Campaign Stop Successfully!..");
              setInterval(function () {
                window.location.reload(); // Every 3 seconds it will check
              }, 4000);
              $('.theme-loader').hide();
            }
          },
          error: function (response, status, error) { // Error
            $('#id_qrcode').show();
            window.location = 'logout';
          }
        });
      }
    });

    // if the model is close clear the checkbox value and clear the error message
    $("#senderid-Modal-stop").on('hide.bs.modal', function () {
      $(".modal-footer").css("display", "");
      $('.cls_checkbox1').prop('checked', false);
      $(".form-label").html("Select All Sender ID ");
      $(".cls_checkbox1").css("display", "");
      $("#id_error_display").html("");
    });

    // if the model is close clear the checkbox value and clear the error message
    $("#senderid-Modal-restart").on('hide.bs.modal', function () {
      $(".modal-footer").css("display", "");
      $('.cls_checkbox1').prop('checked', false);
      $(".form-label").html("Select All Sender ID ");
      $(".cls_checkbox1").css("display", "");
      $("#id_error_display").html("");
    });

    var campaign_names, indicatoris, compose_message_ids;
    // when click the stop campaign button using this function
    function start_sender_id_popup(campaign_name, indicatori, compose_message_id) {
      campaign_names = campaign_name; indicatoris = indicatori; compose_message_ids = compose_message_id;
      send_code = "&campaign_name=" + campaign_names;
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?tmpl_call_function=start_senderid_list" + send_code,
        dataType: 'html',
        beforeSend: function () {
          $(".loading").css('display', 'block');
          $('.loading').show();
        },
        complete: function () {
          $(".loading").css('display', 'none');
          $('.loading').hide();
        },
        success: function (response) { // Success
          if (response == 204 || response == 201) {
            $(".mobile_no_chkbox").html("");
            $(".modal-footer").css("display", "none");
            $(".form-label").html("No Data Available");
            $(".cls_checkbox1").css("display", "none");
          } else {
            $(".mobile_no_chkbox").css("display", "");
            $(".mobile_no_chkbox").html(response);
          }
          $('#senderid-Modal-restart').modal({ show: true });
        }
      })
    }

    // Call remove_senderid function with the provided parameters
    $('#senderid-Modal-restart').find('.btn-success').on('click', function () {
      var txt_whatsapp_mobno = $('input[name="txt_whatsapp_mobno"]:checked').serialize();
      if (txt_whatsapp_mobno == "") {
        $("#id_error_display").html("Please Select Sender ID");
      }
      else {
        var mobile_split = txt_whatsapp_mobno.split("&")
        for (var i = 0; i < mobile_split.length; i++) {
          var mobile_no_split = mobile_split[i].split("=")
          if (i == 0) {
            mobile_numbers = mobile_no_split[1]
          }
          else {
            mobile_numbers = mobile_numbers + "," + mobile_no_split[1]
          }
        }
        // To Start the campaign using on mobile_numbers, campaign_name
        var send_code = "&mobile_numbers=" + mobile_numbers + "&compose_message_id=" + compose_message_ids;
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php?tmpl_call_function=send_restart_campaign" + send_code,
          contentType: false,
          processData: false,
          dataType: 'json',
          beforeSend: function () {
            $(".loading").css('display', 'block');
            $('.loading').show();
          },
          complete: function () {
            $(".loading").css('display', 'none');
            $('.loading').hide();
          },
          success: function (response) { // Success
            if (response.status == '0' || response.status == 0) {
              $("#id_error_display").html(response.msg);
              setInterval(function () {     // if the status is '1' after close the model
                $('#senderid-Modal-restart').modal({ show: false });
              }, 2000);
            } else if (response.status == '2' || response.status == 2) {
              $("#id_error_display").html(response.msg);
              setInterval(function () {     // if the status is '2' after close the model
                $('#senderid-Modal-restart').modal({ show: false });
              }, 2000);
            } else { //  otherwise display the success message
              alert("Campaign Restart Successfully!..");
              $("#id_error_display").html("Campaign Restart Successfully!..");
              setInterval(function () {
                window.location.reload(); // Every 3 seconds it will check
              }, 4000);
              $('.theme-loader').hide();
            }
          },
          error: function (response, status, error) { // Error
            window.location = 'logout';
            $('#id_qrcode').show();
          }
        });
      }
    });
    // To Show Datatable with Export, search panes and Column visible
    $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        }
      }, {
        extend: 'searchPanes',
        config: {
          cascadePanes: true
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: false
        },
        targets: [0]
      }]
    });
  </script>
</body>

</html>

