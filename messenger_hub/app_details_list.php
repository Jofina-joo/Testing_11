<?php
/*
Authendicated users only allow to view this App Details page.
This page is used to Add List the App Details and its Status.
Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table

Version : 1.0
Author : 
Date : 
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
site_log_generate("Manage Sender ID List Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>APP Details List ::
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
  <link rel="stylesheet" href="assets/css/loader.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/loader.css">
  <link rel="stylesheet" href="assets/css/components.css">
</head>
<style>
  .download-link-column {
    max-width: 200px;
    /* Set the maximum width as needed */
    overflow: hidden;
    /* Hide overflow content */
    text-overflow: ellipsis;
    /* Display ellipsis (...) for overflow text */
    white-space: nowrap;
  }

  .modal-content {
    max-width: 400px;
  }

  #app_details_list {
    position: relative;
    height: 600px;
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
            <h1>APP Details List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item active"><a href="add_senderid">APP Upload</a></div>
              <div class="breadcrumb-item">APP Details List</div>
            </div>
          </div>

          <!-- Add Sender ID Panel -->
          <div class="row">
            <div class="col-12">
              <h4 class="text-right">
                <a href="app_upload" class="btn btn-success"><i class="fas fa-plus"></i>
                  Upload APP</a>
              </h4>
            </div>
          </div>

          <!-- List Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive" id="app_details_list">
                      Loading ..
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Confirmation details content Approve-->
      <div class="modal" tabindex="-1" role="dialog" id="senderid-Modal">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header" style="height: 50px;">
              <h4 class="modal-title">
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
                  <div class="col-sm-12 mobile_no_chkbox" style="top:10px;">
                  </div>
                </div>
              </form>
              <div class="modal-footer">
                <span class="error_display" id='id_error_display'></span>
                <button type="button" class="btn btn-success approve_btn">Submit</button>
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
      app_details_list();
    });

    // To Display the Whatsapp NO List
    function app_details_list() {
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?call_function=app_details_list",
        dataType: 'html',
        success: function (response) { // Success
          $("#app_details_list").html(response);
        },
        error: function (response, status, error) {
          window.location = 'logout';
        } // Error
      });
    }
    //setInterval(app_details_list, 60000); // Every 1 min (60000), it will call

    function toggle1(source) {
      let isChecked = source.checked
      var checkboxes = document.querySelectorAll('input[class="cls_checkbox1"]');
      for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
      }
    }

    $("#senderid-Modal").on('hide.bs.modal', function () {
      $('.cls_checkbox1').prop('checked', false);
      $("#id_error_display").html("");
    });

    var app_update_ids, indicatoris, app_version_files;
    // when click the stop campaign button using this function
    function sender_id_popup(app_update_id, indicatori, app_version_file) {
      app_update_ids = app_update_id, indicatoris = indicatori, app_version_files = app_version_file;
      var send_code = "&app_update_id=" + app_update_ids;
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?tmpl_call_function=app_senderid_list" + send_code,
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
          $(".mobile_no_chkbox").html(response);
          $('#senderid-Modal').modal({ show: true });
        }
      })
    }

    // Call remove_senderid function with the provided parameters
    $('#senderid-Modal').find('.btn-success').on('click', function () {
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
var send_code = "&mobile_numbers=" + mobile_numbers + "&app_version_file=" + app_version_files + "&app_update_id=" + app_update_ids;
// Log or return send_code as needed
console.log(send_code);
        $.ajax({
          type: 'post',
          url: "ajax/message_call_functions.php?tmpl_call_function=app_update_version" + send_code,
          contentType: false,
          processData: false,
          dataType: 'json',
          beforeSend: function () {
            $(".loading").css('display', 'block');
            $('.loading').show();
            $('.approve_btn').attr('disabled', true);
          },
          complete: function () {
            $(".loading").css('display', 'none');
            $('.loading').hide();
            $('.approve_btn').attr('disabled', true);
          },
          success: function (response) { // Success
            if (response.status == '0' || response.status == 0) {
              $("#id_error_display").html(response.msg);
              $('.approve_btn').attr('disabled', false);
              setInterval(function () {     // if the status is '1' after close the model
                $('#senderid-Modal').modal({ show: false });
              }, 2000);
            } else if (response.status == '2' || response.status == 2) {
              $("#id_error_display").html(response.msg);
              $('.approve_btn').attr('disabled', false);
              setInterval(function () {     // if the status is '2' after close the model
                $('#senderid-Modal').modal({ show: false });
              }, 2000);
            } else { //  otherwise display the success message
              $('.approve_btn').attr('disabled', true);
              $("#id_error_display").html("App Updated Successfully!..");
              setInterval(function () {
                window.location.reload(); // Every 3 seconds it will check
              }, 3000);
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

    // if the model is close clear the checkbox value and clear the error message
    $("#senderid-Modal").on('hide.bs.modal', function () {
      $('.cls_checkbox1').prop('checked', false);
      $("#id_error_display").html("");
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
