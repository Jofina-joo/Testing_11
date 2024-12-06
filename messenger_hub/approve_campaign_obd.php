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
  <title>OBDCall Campaign List ::
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
            <h1>OBDCall Campaign List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">OBDCall Campaign List</div>
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
  <!-- Decline Campaign content Reject-->
  <div class="modal" tabindex="-1" role="dialog" id="reject-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Decline Campaign</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#" method="post"
            enctype="multipart/form-data">
            <div class="form-group mb-2 row">
              <label class="col-sm-3 col-form-label">User Name: </label>
              <div class="col-sm-9" style="margin-top: 5px;">
                <span class="user_name"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-3 col-form-label">Context: </label>
              <div class="col-sm-9" style="margin-top: 5px;">
                <span class="context_name"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-3 col-form-label">Total Count: </label>
              <div class="col-sm-9" style="margin-top: 5px;">
                <span class="total_mobile_count"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-3 col-form-label">Reason <label style="color:#FF0000">*</label></label>
              <div class="col-sm-9">
                <input class="form-control form-control-primary" type="text" name="reject_reason" id="reject_reason"
                  maxlength="50" title="Reason to Reject" tabindex="12" placeholder="Reason to Reject" minlength="5"
                  onkeypress="return clsAlphaNoOnly(event)" style="display:block !important">
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

  <!-- Channel Status content Approve-->
  <div class="modal" tabindex="-1" role="dialog" id="approve-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Channel Status</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="needs-validation" novalidate="" id="frm_sender_id" name="frm_sender_id" action="#" method="post"
            enctype="multipart/form-data">
            <div class="form-group mb-2 row">
              <label class="col-sm-4 col-form-label">Campaign Name: </label>
              <div class="col-sm-8" style="margin-top: 5px;">
                <span class="campaign_name"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-4 col-form-label">User Name: </label>
              <div class="col-sm-8" style="margin-top: 5px;">
                <span class="user_name"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-4 col-form-label">Context: </label>
              <div class="col-sm-8" style="margin-top: 5px;">
                <span class="context_name"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-4 col-form-label">Total Count: </label>
              <div class="col-sm-8" style="margin-top: 5px;">
                <span class="total_mobile_count"></span>
              </div>
            </div>
            <div class="form-group mb-2 row">
              <label class="col-sm-4 col-form-label">Channels List <label style="color:#FF0000">*</label></label>
              <div class="col-sm-8" style="top:10px;">
                <?
                $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'];

                $curl = curl_init();

                curl_setopt_array(
                  $curl,
                  array(
                    CURLOPT_URL => $api_url . '/list/channels',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                      $bearer_token
                    ),
                  )
                );

                $response = curl_exec($curl);

                curl_close($curl);

                if ($response == '') {
                  ?>
                  <script>window.location = "logout";</script>
                  <?php
                }

                $state1 = json_decode($response);
                site_log_generate("Add Contacts in Group Page: " . $_SESSION['yjwatsp_user_name'] . " received the service response [$response] on " . $current_date, '../');

                ?>

                <table style="width: 100%;">
                  <?php
                  if ($state1->response_status == 403) {
                    ?>
                    <script>window.location = "logout";</script>
                    <?php
                  } if ($state1->response_status == 200) { ?>
    <input type="checkbox" onclick="toggleAll(this);" value="multiselect-all" style="border: 2px solid black;" id = "multiselect">
    <label class="form-label" style="margin-left:5px;">Select All</label>
    <label class="form-label channel_fields" style="margin-left:20px;display:none;">Channel Percentage(%)</label>
    <table>
        <?php for ($indicator = 0; $indicator < count($state1->reports); $indicator++) { ?>
            <tr>
                <td>
                    <input type="checkbox" class="cls_checkbox1" id="chk_<?= $indicator ?>" name="chk_server_name"
                           tabindex="1" onclick="toggleTextField(<?= $indicator ?>)" value="<?= $state1->reports[$indicator]->server_name . '~~' . $state1->reports[$indicator]->sip_id ?>">
                    <label class="form-label"><?= $state1->reports[$indicator]->server_name ?></label>
                </td>
                <td>
                    <input type="text" class="cls_checkbox1 channel_percentage form-control" id="txt_<?= $indicator ?>"
                           name="channel_percentage[]" tabindex="1" style="display: none; margin-left:2px; height:30px;"
                           maxlength="3" required
                           oninput="this.value = this.value.replace(/[^0-9]/g, ''); validatePercentage(this)"
                           placeholder="Percentage Value">
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>


                </table>
              </div>
            </div>
            <input type="hidden" class="form-control" name='product_name' id='product_name' value='OBD CALL SIP' />
          </form>
          <div class="form-group mb-2 row">
            <p class="p-3">Are you sure you want to approve ?</p>
          </div>
          <div class="modal-footer" >
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
          url: "ajax/display_functions.php?call_function=approve_campaign_list_obd",
          dataType: 'html',
          success: function (response) { // Success
            $("#id_approve_campaign_list").html(response);
          },
          error: function (response, status, error) {
            window.location = 'logout';
          } // Error
        });
      }
      //setInterval(approve_campaign_list, 60000); // Every 1 min (60000), it will call

      function validatePercentage(input) {
        let value = input.value.trim();
        let errorDisplay = $("#id_error_display");

        if (value !== "") {
          let percentage = parseInt(value);
          if (isNaN(percentage) || percentage < 0 || percentage > 100) {
            errorDisplay.html("Please enter a number between 0 and 100.");
          } else {
            errorDisplay.html("");
          }
        }
        updateTotalPercentage();
      }

      function updateTotalPercentage() {
        let total = 0;
        let inputs = document.querySelectorAll('.cls_checkbox1[type="text"]');
        let errorDisplay = $("#id_error_display");

        inputs.forEach(input => {
          if (input.style.display !== 'none' && input.value.trim() !== "") {
            let value = parseInt(input.value);
            if (!isNaN(value)) {
              total += value;
            }
          }
        });

        if (total !== 100) {
          errorDisplay.html("Total must be 100%.");
        } else {
          errorDisplay.html("");
        }
      }

      document.querySelectorAll('.cls_checkbox1[type="text"]').forEach(input => {
        input.addEventListener('input', () => validatePercentage(input));
      });

      document.querySelectorAll('.cls_checkbox1[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPercentage);
      });

      function toggleTextField(index) {
        let checkbox = document.getElementById('chk_' + index);
        let textField = document.getElementById('txt_' + index);
        let channelFieldsLabel = document.querySelector('.channel_fields');

        if (textField) {
          textField.style.display = checkbox.checked ? 'block' : 'none';
        }

        let anyChecked = Array.from(document.querySelectorAll('.cls_checkbox1[type="checkbox"]')).some(checkbox => checkbox.checked);
        channelFieldsLabel.style.display = anyChecked ? '' : 'none';
      }

      function toggleAll(source) {
        let checkboxes = document.querySelectorAll('.cls_checkbox1[type="checkbox"]');
        let channelFieldsLabel = document.querySelector('.channel_fields');

        checkboxes.forEach((checkbox, i) => {
          checkbox.checked = source.checked;
          let textField = document.getElementById('txt_' + i);
          if (textField) {
            textField.style.display = source.checked ? 'block' : 'none';
          }
        });

        channelFieldsLabel.style.display = source.checked ? '' : 'none';
      }




          // To save the Phone no id, business account id, bearer token
   function func_download_rc_no(receiver_nos_path) 
   {
			var fileName = receiver_nos_path.substring(receiver_nos_path.lastIndexOf("/") + 1);
	    		console.log(fileName);
			// Combine with the desired path
    			var filePath = "https://yeejai.in/messenger_hub/uploads/compose_variables/" + fileName;
			console.log(filePath);
			// Create a new anchor element
    			var link = document.createElement('a');
    			link.href = filePath;
    			link.download = fileName;
        		// Trigger the download
    			link.click();
          	} 

        
      var indicatoris, compose_message_ids, campaign_names, user_ids, messages, msg_types, media_urls, contxt;
      //popup function
      function func_save_phbabt_popup(indicatori, compose_message_id, campaign_name, user_id, total_mobilenos, user_name, context) {
        indicatoris = indicatori; compose_message_ids = compose_message_id; campaign_names = campaign_name; user_ids = user_id;
          contxt = context;
          // const contxt = getNonNumbers(campaign_names);
        $(".user_name").html(user_name);
        $(".context_name").html(contxt);
        $(".total_mobile_count").html(total_mobilenos);
        $(".campaign_name").html(campaign_name);
        $("#id_error_display").html("");
        $('#approve-Modal').modal({ show: true });
      }


      $('#approve-Modal').find('.btn-success').on('click', function () {
        let inputs = document.querySelectorAll('.cls_checkbox1[type="text"]');
        var chk_server_name = $('input[name="chk_server_name"]:checked').serialize();
        var product_name = $('#product_name').val();
        var channel_percentage = $('input[name="channel_percentage"]').val();

        var text_filed_values = []; // Array to store text field values

        $('.cls_checkbox1[type="text"]').each(function () {
          if ($(this).val() != '') {
            text_filed_values.push($(this).val());
          }
        });
        let checkboxes = document.querySelectorAll('.cls_checkbox1[type="checkbox"]');

        let checkedCount = 0;
        // Count checked checkboxes
        checkboxes.forEach(function (checkbox) {
          if (checkbox.checked) {
            checkedCount++;
          }
        });

        console.log(text_filed_values)
        // Convert each string to a number and sum the values
        let total_percentage = text_filed_values.reduce((sum, num) => sum + parseInt(num, 10), 0);
        console.log(total_percentage + "total values")
        console.log(text_filed_values.length + "textfiled length")
        console.log(checkedCount + "checkedCount")

        if (chk_server_name === "") {
          $("#id_error_display").html("Please Select Channel Name");

        } else if (checkedCount != text_filed_values.length || text_filed_values.length == 0) {
          $("#id_error_display").html("Please Enter Percentage Values");

        } else if (total_percentage != 100) {
          $("#id_error_display").html("Total must be 100%.");

        } else {
          var server_names = '';
          var channel_split = chk_server_name.split("&");

          for (var i = 0; i < channel_split.length; i++) {
            var server_name_split = channel_split[i].split("=");
            if (i === 0) {
              server_names = server_name_split[1];
            } else {
              server_names += "," + server_name_split[1];
            }
            $('.approve_btn').attr("data-dismiss", "modal");

          }

          // Perform further actions like form submission or other logic
          $('#approve-Modal').modal({ show: false });
          var send_code = "&compose_message_id=" + indicatoris + "&campaign_name=" + campaign_names + "&server_names=" + server_names + "&select_user_id=" + user_ids + "&product_name=" + product_name + "&context=" + contxt + "&channel_percentage=" + JSON.stringify(text_filed_values);
          $.ajax({
            type: 'post',
            url: "ajax/message_call_functions.php?tmpl_call_function=send_approve_campaign_obdsip",
            data: send_code,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
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
                $("#id_error_display").html(response.msg);
                setTimeout(function () {
                  window.location = 'approve_campaign_obd';
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

      var indicatoris, compose_message_ids, user_ids,campaign_names;
      //popup function
      function cancel_popup(indicatori, compose_message_id, user_id, total_mobile_no_count, user_name, context,campaign_name) {
        console.log(indicatori, compose_message_id, user_id, total_mobile_no_count, user_name, context);
        indicatoris = indicatori, compose_message_ids = compose_message_id, campaign_names = campaign_name, user_ids = user_id;
            //const contxt = getNonNumbers(campaign_names);
        $("#id_error_reject").html("");
        $(".user_name").html(user_name);
        $(".context_name").html(contxt);
        $(".total_mobile_count").html(total_mobile_no_count);
        $('#reject_reason').val('');
        $('#reject-Modal').modal({ show: true });
      }

      // Call remove_senderid function with the provided parameters
      $('#reject-Modal').find('.btn-success').on('click', function () {
        var reason = $('#reject_reason').val();
        $('#reject_reason').css("border-color", "")
        if (reason == "") {
          $('#reject-Modal').modal({ show: true });
          $('#reject_reason').css("border-color", "red")
          $("#id_error_reject").html("Please enter reason to reject");
        }  else if(reason.length< 5){
        $('#reject_reason').css('border-color', 'red');
        //flag = false;
      }
        else {
          $('.reject_btn').attr("data-dismiss", "modal");
          $('#reject-Modal').modal({ show: false });
          var product_name = $('#product_name').val();
          var send_code = "&compose_message_id=" + indicatoris + "&reason=" + reason + "&select_user_id=" + user_ids + "&product_name =" + product_name;
          $.ajax({
            type: 'post',
            url: "ajax/message_call_functions.php?tmpl_call_function=send_reject_campaign_obdsip" + send_code,
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
                  window.location = 'approve_campaign_obd';
                }, 1000); // Every 3 seconds it will check
              } else if (response.status == '2' || response.status == 2) {
                 $("#id_error_reject").html(response.msg);
                setTimeout(function () {
                  window.location = 'approve_campaign_obd';
                }, 1000); // Every 3 seconds it will check
              } else { // Success
                $("#id_error_reject").html("Campaign rejected!");
                $('#reject_reason').val("");
                setTimeout(function () {
                  window.location = 'approve_campaign_obd';
                }, 1000); // Every 3 seconds it will check
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
        $('.channel_percentage').val('');
        $('#multiselect').prop('checked', false); // Replace with your actual text field IDs
        $('.channel_percentage').css('display', 'none');
        $('.channel_fields').css('display', 'none');
        $('.cls_checkbox1').prop('checked', false); // Replace with your actual text field IDs
      });

      function clsAlphaNoOnly(e) { // Accept only alpha numerics, no special characters 
        var key = e.keyCode;
        if ((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (key >= 48 && key <= 57) || (key == 32) || (key == 95)) {
          return true;
        }
        return false;
      }

/*function getNonNumbers(str) {
    return str.replace(/[0-9]/g, '');
}*/

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
