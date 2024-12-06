<?php
/*
Authendicated users only allow to view this Approve Campaign  page.
This page is used to Add List the Sender ID and its Status.
Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table

Version : 1.0
Author : 
Date : 
*/
session_start(); // To start session
error_reporting(0); // The error reporting function
include_once ('api/configuration.php'); //  Include configuration.php
extract($_REQUEST); // Extract the request

// If the Session is not available redirect to index page
  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }
$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . $current_date); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Prompt Approve List ::
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
  .btn-outline-success {}

  .modal-body {
    /*max-height: 120px; Adjust this value as needed */
    overflow-y: auto;
    /* Enable vertical scrollbar when content overflows */
  }

  #id_approve_campaign_list {
    position: relative;
    height: 950px;
  }

  .dataTables_filter label,
  .previous,
  .next {
    font-weight: bolder;
  }
        .play-icon::before {
            content: "\25B6"; /* Unicode character for play symbol */
        }
        .stop-icon::before {
            content: "\25A0"; /* Unicode character for stop symbol */
        }
        .modal-footer.custom-center {
    display: flex;
    justify-content: center;
}
</style>

<body>
  <div class="loading" style="display:none;">Loading&#8230;</div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <!-- include header function adding -->
      <? include ("libraries/site_header.php"); ?>
      <!-- include sitemenu function adding -->
      <? include ("libraries/site_menu.php"); ?>
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <!-- Title & Breadcrumb Panel -->
          <div class="section-header">
            <h1>Prompt Approve List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Prompt Approve List</div>
            </div>
          </div>
          <!-- List Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive" id="id_approve_campaign_list"> <!-- List from API -->
                      Loading ..
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Footer Panel -->
      <? include ("libraries/site_footer.php"); ?>

    </div>
  </div>
  <!-- Confirmation details content Reject-->
  <div class="modal" tabindex="-1" role="dialog" id="reject-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#" method="post"
            enctype="multipart/form-data">

            <div class="form-group mb-2 row">
              <label class="col-sm-3 col-form-label">Reason <label style="color:#FF0000">*</label></label>
              <div class="col-sm-9">
                <input class="form-control form-control-primary" type="text" name="reject_reason" id="reject_reason"
                  maxlength="50" title="Reason to Reject" tabindex="12" placeholder="Reason to Reject"
                  onkeypress="return clsAlphaNoOnly(event)">
                    <label
                  style="color:#FF0000">[Min length: 5 & Max length: 50]</label>
              </div>
            </div>
          </form>
          <p>Are you sure you want to reject ?</p>
        </div>
        <div class="modal-footer">
          <span class="error_display" id='id_error_reject'></span>
          <button type="button" class="btn btn-success reject_btn" data-dismiss="model">Reject</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation details content Approve-->
  <div class="modal" tabindex="-1" role="dialog" id="approve-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <p class="p-3">Are you sure you want to approve ?</p>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-footer custom-center" >
         <span class="error_display" id='id_error_display'></span>
    <button type="button" class="btn btn-success approve_btn">Approve</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
        approve_campaign_list();
        $('.loading').hide();
      });

      // To Display the RCS NO List
      function approve_campaign_list() {
        $.ajax({
          type: 'post',
          url: "ajax/display_functions.php?call_function=approve_prompt_obd",
          dataType: 'html',
          success: function (response) { // Success
            $("#id_approve_campaign_list").html(response);
          },
          error: function (response, status, error) {
            // window.location = 'logout';
          } // Error
        });
      }
      //setInterval(approve_campaign_list, 60000); // Every 1 min (60000), it will call        
     
      /*let audio = null;
        let isPlaying = false;

        function toggleAudio(audio_path, indicator_id) {
            const indicatorElement = document.getElementById(indicator_id);
            if (!isPlaying) {
                // Play audio
                audio = new Audio(audio_path);
                audio.play();
                indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                // Pause audio
	                audio.pause();
                audio.currentTime = 0; // Reset audio to start
                indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
            }
            isPlaying = !isPlaying;
        }*/

var currentAudio = null;
var currentIndicator = null;

function toggleAudio(audio_path, indicator_id) {
    const indicatorElement = document.getElementById(indicator_id);

    if (currentAudio && currentIndicator === indicatorElement) {
        // If the same audio is clicked again, pause it
        if (currentAudio.paused) {
            currentAudio.play();
            indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
            currentAudio.pause();
            indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
        }
        return;
    }

    if (currentAudio) {
        // Pause the currently playing audio
        currentAudio.pause();
        currentAudio.currentTime = 0;
        currentIndicator.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentIndicator = null;
    }

    // Play new audio
    currentAudio = new Audio(audio_path);
    currentAudio.play();
    currentIndicator = indicatorElement;
    indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';

    currentAudio.addEventListener('ended', function() {
        // Audio has ended when this function is executed.
        indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentIndicator = null;
    }, false);
}

      var indicatoris, prompt_ids, contexts;
      //popup function
      function func_save_phbabt_popup(prompt_id,indicatori, context) {
        indicatoris = indicatori; prompt_ids = prompt_id; contexts = context;
        $('#approve-Modal').modal({ show: true });
      }

      $('#approve-Modal').find('.btn-success').on('click', function () {     
          $('#approve-Modal').modal({ show: false });
          var send_code = "&indicatori=" + indicatoris + "&context=" + contexts + "&prompt_id=" + prompt_ids+ "&prompt_status=Y";
          $.ajax({
            type: 'post',
            url: "ajax/message_call_functions.php?tmpl_call_function=send_approve_prompt" + send_code,
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
            },
            success: function (response) { // Success
              if (response.status == '0' || response.status == 0) {
                 $("#id_error_display").html(response.msg);
              } else if (response.status == '2' || response.status == 2) {
                 $("#id_error_display").html(response.msg);
              } else { // Success
                 $("#id_error_display").html("Prompt Approved Successfully..!");
                setTimeout(function () {
                  window.location = 'approve_prompt_obd';
                }, 2000); // Every 3 seconds it will check
                $('.theme-loader').hide();
              }
            },
            error: function (response, status, error) { // Error
              $('#id_qrcode').show();
              // window.location = 'logout';
            }
          });
      });

      var indicatori_rej, prompt_id_rej, context_rej;
      //popup function
      function cancel_popup(prompt_id,indicatori, context) {
        indicatori_rej = indicatori, prompt_id_rej = prompt_id, context_rej = context;
        $("#id_error_reject").html("");
        $('#reject_reason').val('');
        $('#reject-Modal').modal({ show: true });
      }

      // Call remove_senderid function with the provided parameters
      $('#reject-Modal').find('.btn-success').on('click', function () {
        var reason = $('#reject_reason').val();
        if (reason == "") {
          $('#reject-Modal').modal({ show: true });
          $("#id_error_reject").html("Please enter reason to reject");
        }  else if(reason.length< 5){
        $('#reject_reason').css('border-color', 'red');
      }
        else {
          $('.reject_btn').attr("data-dismiss", "modal");
          $('#reject-Modal').modal({ show: false });
          var send_code = "&prompt_id=" + prompt_id_rej + "&reason=" + reason + "&context=" + context_rej+ "&prompt_status=R";
          $.ajax({
            type: 'post',
            url: "ajax/message_call_functions.php?tmpl_call_function=send_approve_prompt" + send_code,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
              $(".loading").css('display', 'block');
              $('.loading').show();
            $('.reject_btn').attr('disabled', true);
            },
            complete: function () {
              $(".loading").css('display', 'none');
              $('.loading').hide();
            },
            success: function (response) { // Success
              if (response.status == '0' || response.status == 0) {
                 $("#id_error_reject").html(response.msg);
                setTimeout(function () {
                  window.location = 'approve_prompt_obd';
                }, 1000); // Every 3 seconds it will check
              } else if (response.status == '2' || response.status == 2) {
                 $("#id_error_reject").html(response.msg);
                setTimeout(function () {
                  window.location = 'approve_prompt_obd';
                }, 1000); // Every 3 seconds it will check
              } else { // Success
                 $("#id_error_reject").html("Prompt rejected!");
                $('#reject_reason').val("");
                setTimeout(function () {
                  window.location = 'approve_prompt_obd';
                }, 2000); // Every 3 seconds it will check
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

      $('#reject-Modal').on('hidden.bs.modal', function () {
        $('#reject_reason').val("");
      });


      $('#approve-Modal').on('hidden.bs.modal', function () {
        // Clear the text fields
        $('.cls_checkbox1').prop('checked', false); // Replace with your actual text field IDs
      });


      function clsAlphaNoOnly(e) { // Accept only alpha numerics, no special characters 
        var key = e.keyCode;
        if ((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (key >= 48 && key <= 57) || (key == 32) || (key == 95)) {
          return true;
        }
        return false;
      }

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
