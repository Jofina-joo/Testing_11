<?php
/*
Primary Admin user only allow to view this users list page.
This page is used to view the list of Users and its Status.
Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table

Version : 1.0
Author : Madhubala (YJ0009)
Date : 03-Jul-2023
*/

session_start(); // start session
error_reporting(0); // The error reporting function

include_once('api/configuration.php'); // Include configuration.php
extract($_REQUEST); // Extract the request

// If the Session is not available redirect to index page
  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }

// If logged in users is not primary admin, then it will redirect to Dashboard page
if ($_SESSION['yjwatsp_user_master_id'] != 1) { ?>
  <script>window.location = "dashboard";</script>
  <? exit();
}

$site_page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate("Manage Users List Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s"));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Users List ::
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
  #id_manage_users_list {
    height: 750px;
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
          <!-- Title and Breadcrumbs -->
          <div class="section-header">
            <h1>Users List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item active"><a href="manage_users">Users</a></div>
              <div class="breadcrumb-item">Users List</div>
            </div>
          </div>

          <!-- List Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive" id="id_manage_users_list">
                      Loading..
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>
      </div>

      <!-- include site footer -->
      <? include("libraries/site_footer.php"); ?>

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
              <label class="col-sm-5 col-form-label">Select Users <label style="color:#FF0000">*</label></label>
              <div class="col-sm-" style="top:10px;">
                <?
                $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
                $curl = curl_init();
                curl_setopt_array(
                  $curl,
                  array(
                    CURLOPT_URL => $api_url . '/list/manageusers_list',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYPEER => 1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
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
                  if ($state1->num_of_rows > 0) {  // If the response is success to execute this condition?>
 <input type="checkbox" onclick="toggle1(this);"
                    value="multiselect-all" class="cls_checkbox1"
                    style="border: 2px solid black;"> <label class="form-label" style="margin-left:5px;"> Select All Users </label>
                   <? for ($indicator = 0; $indicator < $state1->num_of_rows; $indicator++) {
                      if ($state1->report[$indicator]->user_type == 'User' && $state1->report[$indicator]->parent_id == '1') {
                        if ($counter % 1 == 0) { ?>
                          <tr>
                          <? } ?>
                          <td>
                            <input type="checkbox" class="cls_checkbox1" id="users_id_<?= $indicator ?>"
                              name="users_id" tabindex="1" autofocus value="<?= $state1->report[$indicator]->user_id ?>">
                            <label class="form-label">
                              <?= $state1->report[$indicator]->user_name ?>
                            </label>
                          <? } ?>
                        </td>
                        <?
                        if ($counter % 1 == 0) { ?>
                        </tr>
                      <? }
                        $counter++;
                    }
                  }
                  ?>
                </table>
              </div>
            </div>
          </form>
          <p class="p-3">Are you sure you want to Add Users ?</p>
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
    // On loading the page, this function will call
    $(document).ready(function () {
      find_manage_users_list();
    });

    // To list the Users from API
    function find_manage_users_list() {
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?call_function=manage_users_list",
        dataType: 'html',
        success: function (response) {
          $("#id_manage_users_list").html(response);
        },
        error: function (response, status, error) { }
      });
    }
    setInterval(find_manage_users_list, 300000); // Every 5 mins (300000), it will call

    function toggle1(source) {
      let isChecked = source.checked
      var checkboxes = document.querySelectorAll('input[class="cls_checkbox1"]');
      for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
      }
    }


 var select_user_ids, resellerids, indicatoris;
    //popup function
    function addusers_popup(select_user_id, resellerid, indicatori) {
      select_user_ids = select_user_id; resellerids = resellerid, indicatoris = indicatori;
      $('#approve-Modal').modal({ show: true });
    }

    $('#approve-Modal').find('.btn-success').on('click', function () {
      var users_id = $('input[name="users_id"]:checked').serialize();
      if (users_id == "") {
        $("#id_error_display").html("Please Select Users");
      }
      else {
        var users_id_split = users_id.split("&")
        for (var i = 0; i < users_id_split.length; i++) {
          var users_split_id = users_id_split[i].split("=")
          if (i == 0) {
            usersid = users_split_id[1]
          }
          else {
            usersid = usersid + "," + users_split_id[1]
          }
          $('.approve_btn').attr("data-dismiss", "modal");
        }
        $('#approve-Modal').modal({ show: false });
        var send_code = "&resellerid=" + resellerids + "&indicatori=" + indicatoris + "&select_user_id=" + select_user_ids + "&usersid=" + usersid;
        $.ajax({
          type: 'post',
          url: "ajax/call_functions?call_function=apprej_onboarding" + send_code,
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
              alert(response.msg);
              window.location = 'users_list';
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
    $("#approve-Modal").on('hide.bs.modal', function () {
      $('.cls_checkbox1').prop('checked', false);
    });

    function logoimage(inputElement, txt_user) {
      var uploadedFile = inputElement.files[0];
      console.log("User ID: " + txt_user);
      console.log("Uploaded File: ", uploadedFile);

      // For demonstration purposes, you can also use FormData to send the file
      var formData = new FormData();
      formData.append('file_input', uploadedFile);
      formData.append('txt_user', txt_user);

      $.ajax({
        url: "ajax/call_functions.php?call_function=apprej_onboarding",
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          alert(response.msg);
          $('#file_input').val('');
          // setTimeout(function () {
            window.location = 'users_list';
          // }, 2000); // Every 3 seconds it will check
        },
        error: function (error) {
          console.error(error);
          // Handle errors
        }
      });
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
