<?php
/*
Authendicated users only allow to view this Manage Sender ID page.
This page is used to Add List the Sender ID and its Status.
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
site_log_generate("Manage Sender ID List Page : User : ".$_SESSION['yjwatsp_user_name']." access the page on ".date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Sender ID List ::
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
  <link rel="stylesheet" href="assets/css/components.css">
 <link rel="stylesheet" href="assets/css/loader.css">

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
    //width:100px;
  }

  #id_manage_whatsappno_list{
    height: 600px ;
  }

.dataTables_filter label,.previous,.next{
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
            <h1>Manage Sender ID List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Manage Sender ID List</div>
            </div>
          </div>

          <!-- Status Panel -->
          <div class="row">
            <div class="col-12">
              <a href="#!" class="btn btn-outline-success btn-disabled" title="Active" style="width: 100px;">Active</a>&nbsp;<a href="#!"
                class="btn btn-outline-danger btn-disabled" title="Deleted" style="width: 100px;">Deleted</a>&nbsp;<a href="#!"
                class="btn btn-outline-dark btn-disabled" title="Blocked" style="width: 100px;">Blocked</a>&nbsp;<a href="#!"
                class="btn btn-outline-danger btn-disabled" title="Inactive" style="width: 100px;">Inactive</a>&nbsp;<a
                href="#!" class="btn btn-outline-info btn-disabled" title="Processing" style="width: 100px;">Processing</a>&nbsp;
               <a href="#!" class="btn btn-outline-warning btn-disabled" title="Testing" style="width: 100px;">Testing</a>&nbsp;
            </div>
          </div>

          <!-- List Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive" id="id_manage_whatsappno_list"> <!-- List from API -->
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
      manage_whatsappno_list();
    });

    // To Display the Whatsapp NO List
    function manage_whatsappno_list() {
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?call_function=manage_whatsappno_list",
        dataType: 'html',
        success: function (response) { // Success
          $("#id_manage_whatsappno_list").html(response);
        },
        error: function (response, status, error) { 
 window.location = "logout"; } // Error
      });
    }
    setInterval(manage_whatsappno_list, 60000); // Every 1 min (60000), it will call

    var whatspp_config_ids, approve_statuss, indicatoris;

    function remove_senderid_popup(whatspp_config_id, approve_status, indicatori) {
      whatspp_config_ids = whatspp_config_id;
      approve_statuss = approve_status;
      indicatoris = indicatori;
      $('#default-Modal').modal({ show: true });
    }

    $('.btn-danger').on('click', function () {
      $('#delete-Modal').modal({ show: false });
      var send_code = "&whatspp_config_id=" + whatspp_config_ids + "&approve_status=D";
      $.ajax({
        type: 'post',
        url: "ajax/message_call_functions.php?tmpl_call_function=delete_senderid" + send_code,
        dataType: 'json',
        success: function (response) { // Success
          if (response.status == 0) { // Failure Response
            $('#id_approved_lineno_' + indicatoris).append('<a href="javascript:void(0)" class="btn disabled btn-outline-warning">Not Deleted</a>');
          } else { // Success Response
            $('#id_approved_lineno_' + indicatoris).html('<a href="javascript:void(0)" class="btn disabled btn-outline-danger">Deleted</a>');
            setTimeout(function () {
              window.location.reload(); // Window Reload
            }, 1000);
          }
        },
        error: function (response, status, error) {
          window.location = "logout";
        } // Error
      });
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
