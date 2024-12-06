<?php
/*
Authendicated users only allow to view this Approve Campaign  page.
This page is used to Add List the Sender ID and its Status.
Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table

Version : 1.0
Author : Arun Rama Balan.G (YJ0005)
Date : 07-Jul-2023
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
site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SMS Campaign List ::
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
</style>

<body>
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
            <h1>SMS Campaign List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item active"><a href="add_senderid">Add Sender ID</a></div>
              <div class="breadcrumb-item">SMS Campaign List</div>
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
            <div class="container" style="display:none;">
              <span class="error_display" style='font-size: 12px;' id='qrcode_display'></span><Br>
              <img src='./assets/img/loader.gif' id="id_qrcode" alt='QR Code'>
              <!-- QR Code display Panel -->
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
              <label class="col-sm-6 col-form-label">Reason <label style="color:#FF0000">*</label></label>
              <div class="col-sm-6">
                <input class="form-control form-control-primary" type="text" name="reject_reason" id="reject_reason"
                  maxlength="50" title="Reason to Reject" tabindex="12" placeholder="Reason to Reject"  onkeypress="return clsAlphaNoOnly(event)">
              </div>

            </div>
          </form>
          <p>Are you sure you want to reject ?</p>
        </div>
        <div class="modal-footer">
          <span class="error_display" id='id_error_reject'></span>
          <button type="button" class="btn btn-success reject_btn">Reject</button>
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
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#" method="post"
            enctype="multipart/form-data">
            <div class="form-group mb-2 row">
              <label class="col-sm-6 col-form-label">Message Sender ID <label style="color:#FF0000">*</label></label>
              <div class="col-sm-6" style="top:10px;">
                <?
                $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
                $replace_txt = '{
"user_product": "GSM SMS"
}';
                $curl = curl_init();
                curl_setopt_array(
                  $curl,
                  array(
                    CURLOPT_URL => $api_url . '/sender_id/sender_id_limits',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYPEER => 1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $replace_txt,
                    CURLOPT_HTTPHEADER => array(
                      $bearer_token,
                      'Content-Type: application/json'
                    ),
                  )
                );
                site_log_generate("Add Contacts in Group Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service  [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
                $response = curl_exec($curl);
                curl_close($curl);
                if ($response == '') { ?>
                  <script>window.location = "logout"</script>
                <? }
                $state1 = json_decode($response, false);
                site_log_generate("Add Contacts in Group Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
                ?>
                <table style="width: 100%;">

                  <? $counter = 0;
                  if ($state1->response_status == 403) { ?>
                    <script>window.location = "logout"</script>
                  <? }

                  $firstid = 0;
                  if ($state1->response_status == 200) { ?>
                    <input type="checkbox" onclick="toggle1(this);" value="multiselect-all" checked class="cls_checkbox1"
                      style="border: 2px solid black;"> <label class="form-label" style="margin-left:5px;"> Select All
                    </label>
                    <? for ($indicator = 0; $indicator < count($state1->sender_id); $indicator++) {
                      if ($state1->sender_id[$indicator]->sender_id_status == 'Y' && $state1->sender_id[$indicator]->is_qr_code == 'N') {
                        if ($counter % 1 == 0) { ?>
                          <tr>
                          <? } ?>
                          <td>
                            <input type="checkbox" <? if ($state1->sender_id[$indicator]->sender_id_status == 'Y' && $state1->sender_id[$indicator]->is_qr_code == 'N') { ?>checked<? } else { ?>disabled<? } ?>
                              class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="txt_whatsapp_mobno"
                              tabindex="1" autofocus value="<?= $state1->sender_id[$indicator]->mobile_no ?>">

                            <label class="form-label">
                              <?= $state1->sender_id[$indicator]->mobile_no ?>
                            </label>
                          </td>
                          <?
                          if ($counter % 1 == 1) { ?>
                          </tr>
                        <? }
                          $counter++;
                      }
                    }
                  }
                  ?>
                </table>
              </div>
            </div>
            <input type="hidden" class="form-control" name='product_name' id='product_name' value='SMS' />
          </form>
          <div class="form-group mb-2 row">
            <div class="col-sm-5"> <label class="col-form-label">Message<label style="color:#FF0000"></label></label>
            </div>
            <div class="col-sm-7">
              <div id="divVideo" style="display:none;">
                <center><video width="200" height="200" controls>
                    <source type="video/mp4" src="">
                    </source>
                  </video></center>
              </div>
              <img id="image_url" src="" style="display: none;">
              <div id="msg_content" class="modal-body"
                style="white-space: pre-line; word-wrap: break-word; word-break: break-word;"></div>
            </div>
            <p class="p-3">Are you sure you want to compose message approve ?</p>
          </div>
          <div class="modal-footer">
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
      $(function () {
        $('#id_qrcode').fadeOut("slow");
      });

      // On loading the page, this function will call
      $(document).ready(function () {
        approve_campaign_list();
      });

    function func_download_rc_no(encodedDatas, indicatori) {
        try {
          const encodedDataArray = JSON.parse(encodedDatas);
          const uint8Array = new Uint8Array(encodedDataArray);
          // Use TextDecoder to decode the binary data
          const decoder = new TextDecoder('utf-8');
          const txtData = decoder.decode(uint8Array);
          var lines = txtData.split('\n');
          const csvData = lines.map(line => line.replace(/,/g, '\n')).join('\n');
          const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'approve_campaign_sms.csv';
          a.textContent = 'Download Receiver Numbers CSV';
          a.style.display = 'none';
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
        } catch (error) {
          console.error('Error: ' + error.message);
        }
      }

      function toggle1(source) {
        let isChecked = source.checked
        var checkboxes = document.querySelectorAll('input[class="cls_checkbox1"]');
        for (var i = 0; i < checkboxes.length; i++) {
          checkboxes[i].checked = source.checked;
        }
      }

      // To Display the Whatsapp NO List
      function approve_campaign_list() {
        $.ajax({
          type: 'post',
          url: "ajax/display_functions.php?call_function=approve_campaign_list_sms",
          dataType: 'html',
          success: function (response) { // Success
            $("#id_approve_campaign_list").html(response);
          },
          error: function (response, status, error) { } // Error
        });
      }
      setInterval(approve_campaign_list, 60000); // Every 1 min (60000), it will call

      //popup function
 var indicatoris, compose_message_ids, campaign_names, user_ids, messages, msg_types, media_urls;
      function func_save_phbabt_popup(indicatori, compose_message_id, campaign_name, user_id, message, msg_type, media_url) {
  indicatoris = indicatori; compose_message_ids = compose_message_id; campaign_names = campaign_name; user_ids = user_id;
        console.log(msg_type)
        if (!message) {
          message = "-"
        }
        $('#image_url').css('display', 'none');
        $('#image_url').attr('src', "");
        $("#divVideo").css("display", "none");
        $('#divVideo video source').attr('src', "")
        $('#divVideo video')[0].load();
        $(".btn-outline-danger").remove();
        $("#id_error_display").html("");
        $('#approve-Modal').modal({ show: true });
        // Call remove_senderid function with the provided parameters
        $('#msg_content').html(message);
        if (media_url) {
          if (msg_type == 'VIDEO') {
            $("#divVideo").css("display", "block");
            $('#divVideo video source').attr('src', media_url)
            $('#divVideo video')[0].load()
          }
          if (msg_type == 'IMAGE') {
            $('#image_url').css('display', 'block');
            $('#image_url').css('width', '200px');
            $('#image_url').css('height', '200px');
            $('#image_url').attr('src', media_url);
          }
        }

      }

      $('#approve-Modal').find('.btn-success').on('click', function () {
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
            $('.approve_btn').attr("data-dismiss", "modal");
          }
          $('#approve-Modal').modal({ show: false });
          var product_name = $('#product_name').val();
          console.log(indicatoris, compose_message_ids, campaign_names, mobile_numbers, user_ids);
          var send_code = "&compose_message_id=" + indicatoris + "&campaign_name=" + campaign_names + "&mobile_numbers=" + mobile_numbers + "&select_user_id=" + user_ids + "&product_name =" + product_name;
          $.ajax({
            type: 'post',
            url: "ajax/message_call_functions.php?tmpl_call_function=send_approve_campaign_sms" + send_code,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
              $('#id_qrcode').show();
            },
            complete: function () {
              $('#id_qrcode').hide();
            },
            success: function (response) { // Success
              if (response.status == '0' || response.status == 0) {
                alert(response.msg);
              } else if (response.status == '2' || response.status == 2) {
                alert(response.msg);
              } else { // Success
                alert("SMS Send Successfully..!");
                setTimeout(function () {
                  window.location = 'approve_campaign_list_sms';
                }, 3000); // Every 3 seconds it will check
                $('.theme-loader').hide();
              }
            },
            error: function (response, status, error) { // Error
              $('#id_qrcode').show();
            }
          });
          // func_save_phbabt(indicatori, compose_message_id, campaign_name, mobile_numbers, user_id);
        }
      });
      var indicatoris, compose_message_ids, user_ids;
      //popup function
      function cancel_popup(indicatori, compose_message_id, user_id) {
console.log(indicatori, compose_message_id, user_id);
        indicatoris = indicatori, compose_message_ids = compose_message_id, user_ids = user_id;
        $(".btn-outline-danger").remove();
        $("#id_error_reject").html("");
        $('#reject-Modal').modal({ show: true });
      }

      // Call remove_senderid function with the provided parameters
      $('#reject-Modal').find('.btn-success').on('click', function () {
        var reason = $('#reject_reason').val();
        if (reason == "") {
          //$('#reject-Modal').modal({ show: true });
          $("#id_error_reject").html("Please enter reason to reject");
        }else if (reason.length < 4 || reason.length > 50) {
        $('#reject-Modal').modal({ show: true });
        $("#id_error_reject").html("Reason to reject must be between 4 and 50 characters.");
      } else {
          $('.reject_btn').attr("data-dismiss", "modal");
          $('#reject-Modal').modal({ show: false });
          var product_name = $('#product_name').val();
          var send_code = "&compose_message_id=" + indicatoris + "&reason=" + reason + "&select_user_id=" + user_ids + "&product_name =" + product_name;
          $.ajax({
            type: 'post',
            url: "ajax/message_call_functions.php?tmpl_call_function=send_reject_campaign_sms" + send_code,
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
                alert(response.msg);
              } else if (response.status == '2' || response.status == 2) {
                alert(response.msg);
              } else { // Success
                alert("Campaign rejected!");
                $('#reject_reason').val("");
                setTimeout(function () {
                  window.location = 'approve_campaign_list_sms';
                }, 1000); // Every 3 seconds it will check
                $('.theme-loader').hide();
              }
            },
            error: function (response, status, error) { // Error
              $('#id_qrcode').show();
            }
          });
        }
      });

      $('#approve-Modal').on('hidden.bs.modal', function () {
        // Clear the text fields
        $('.cls_checkbox1').prop('checked', false); // Replace with your actual text field IDs
      });

      $('#reject-Modal').on('hidden.bs.modal', function () {
        $('#reject_reason').val("");
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
