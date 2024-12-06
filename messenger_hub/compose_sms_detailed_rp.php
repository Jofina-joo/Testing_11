<?php
/*
Authendicated users only allow to view this Campaign Report page.
This page is used to view the List of Campaign Report.
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
site_log_generate("Campaign Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " access the page on " . date("Y-m-d H:i:s")); // Log File
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Detailed Report ::
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
  <!--Date picker -->
  <script type="text/javascript" src="assets/js/daterangepicker.min.js" defer></script>
  <link rel="stylesheet" type="text/css" href="assets/css/daterangepicker.css" />

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/loader.css">

</head>
<style>
  element.style {}

  .card .card-header,
  .card .card-body,
  .card .card-footer {
    padding: 20px;
  }

  .custom-file,
  .custom-file-label,
  .custom-select,
  .custom-file-label:after,
  .form-control[type="color"],
  select.form-control:not([size]):not([multiple]) {
    height: calc(2.25rem + 6px);
  }

  .input-group-text,
  select.form-control:not([size]):not([multiple]),
  .form-control:not(.form-control-sm):not(.form-control-lg) {
    Loading… ￼ font-size: 14px;
    padding: 5px 15px;
  }

  .search {
    width: 200px;
    margin-right: 50px;
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

  .preloader-wrapper {
    display: flex;
    justify-content: center;
    background: rgba(22, 22, 22, 0.3);
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    width: 100%;
    height: 100%;
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

  .dataTables_filter label,
  .previous,
  .next {
    font-weight: bolder;
  }

  .image-container {
    width: 300px;
    height: auto;
    overflow: hidden;
  }

  .image-container img {
    width: 100%;
    height: auto;
    display: block;
  }
  .video-container {
    width: 400px;
    height: auto;
    overflow: hidden;
  }

  .video-container video {
    width: 100%;
    height: auto;
    display: block;
  }
</style>

<body>
  <div class="loading" style="display:none;">Loading&#8230;</div>
  <div class="theme-loader"></div>
  <div class="preloader-wrapper" style="display:none;">
    <div class="preloader">
    </div>
    <div class="text" style="color: white; background-color:#f27878; padding: 10px; margin-left:400px;">
      <b>Reports generating...<br /> Please wait.</b>
    </div>
  </div>
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
            <h1>Detailed Report</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Detailed Report</div>
            </div>
          </div>

          <!-- List Panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <!-- Choose User -->
                    <form method="post">
                      <div id="table-1_filter" class="dataTables_filter">
                        <!-- date filter -->
                        <div style="width: 20%; padding-right:1%; float: left;">Date : <input type="search" name="dates"
                            id="dates" value="<?= $_REQUEST['dates'] ?>" class="search_1" aria-controls="table-1"
                            style="width: 100%;height:30px;background-color: #e9ecef;border :1px solid #ced4da;" />
                        </div>
                        <!-- submit button -->
                        <div style="width: 20%; padding-right:1%; float: left;">
                          <input type="submit" name="submit_1" id="submit_1" tabindex="10" value="Search"
                            class="btn btn-success " style="height:30px; margin-top: 20px;">
                        </div>
                      </div>
                    </form>
                    <div class="table-responsive" id="id_campaign_report" style="padding-top: 20px;">
                      <!-- List from API -->
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
  <!-- Confirmation details content-->
  <div class="modal" tabindex="-1" role="dialog" id="csvdownload-Modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="height: 50px;">
          <p> Reports more than 10,000. So, it can't display.Are you sure you want to download?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Download</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="default-Modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style=" max-width: 75% !important;">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Message Content</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
   <center>
          <div class="video-container" style="display:none;" id="divVideo">
            <video controls>
              <source type="video/mp4" src="">
              </source>
            </video>
          </div>
          <div class="image-container" style="display:none;">
            <img id="img_modal" style="display:none;" src="" alt=""
              onerror="this.src='assets/img/page_not_found.jpg';" />
          </div>
        </center>

       <?/* <div id="divVideo" style="display:none;height: auto;overflow-x: scroll;">
          <center><video controls>
              <source type="video/mp4" src="">
              </source>
            </video></center>
        </div>
        <center class="img_model" style="overflow-y: scroll;height: 80%;"><img id="img_modal" style="display:none;"
            src="" alt="" onerror="this.src='assets/img/page_not_found.jpg';" /></center> */?>
        <div class="modal-body" id="id_modal_display"
          style="white-space: pre-line; word-wrap: break-word; word-break: break-word;">
          <h5>No Data Available</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success waves-effect " data-dismiss="modal">Close</button>
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
      campaign_report();
    });

  function call_getsingletemplate(msg, media, msg_type) {
      console.log(msg, msg_type, media);
      $('.theme-loader').show();
      $("#img_modal").css("display", "none");
  $(".image-container").css("display", "none");
      $("#img_modal").attr("src", '');
      $("#divVideo").css("display", "none");
      $('#divVideo video source').attr('src', "")
      $('#divVideo video')[0].load()
      if (media) {
        if (msg_type == 'VIDEO') {
          $("#divVideo").css("display", "block");
          $('#divVideo video source').attr('src', media)
          $('#divVideo video')[0].load()
        }
	if (msg_type == 'IMAGE') {
 $(".image-container").css("display", "block");
          $("#img_modal").css("display", "block");
          $("#img_modal").attr("src", media);
        }
      }
      $('.theme-loader').hide();
      $("#id_modal_display").html(msg);
      $('#default-Modal').modal({ show: true });
    }


    // Call remove_senderid function with the provided parameters
    $('#csvdownload-Modal').find('.btn-danger').on('click', function () {
      // Show loader
      $('.loading').show();
      setTimeout(function () {
        var downloadLink = document.getElementById('downloadLink');
        downloadLink.click();
        $('.loading').hide();
      }, 3000);
      $('#delete-Modal').modal({ show: false });
    });


    var dates;
    // While click the Submit button
    $("#submit_1").click(function (e) {
      e.preventDefault();
      dates = $('#dates').val();
      campaign_report();
    });
    // To Display the Whatsapp NO List
    function campaign_report() {
      var date = $("#dates").val();
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?call_function=campaign_report_sms&dates=" + date,
        dataType: 'html',
        beforeSend: function () {
          $('.preloader-wrapper').show();
          $('.theme-loader').hide();
        },
        complete: function () {
          $('.preloader-wrapper').hide();
          $('.loading_error_message').css("display", "none");
          //$('.theme-loader').hide();
        },
        success: function (response) { // Success
          $("#id_campaign_report").html(response);
          var datas_count = $('#num_of_rows').val();
          if (datas_count) {
            $('#csvdownload-Modal').modal({ show: true });
          }
        },
        error: function (response, status, error) { } // Error
      });
    }
    //setInterval(campaign_report, 60000); // Every 1 min (60000), it will call


    // To show the Calendar
    $(function () {
      var today = moment();
      var oneMonthAgo = moment().subtract(1, 'month');
      var oneWeekAgo = moment().subtract(1, 'week'); // Set the default start date to one week ago

      function cb(start, end) {
        $('#dates').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
      }

      $('#dates').daterangepicker({
        startDate: oneWeekAgo,
        endDate: today,
        minDate: oneMonthAgo, // Set the minimum allowed date
        maxDate: today,      // Set the maximum allowed date
        singleDatePicker: false, // Allow only one date selection
        locale: {
          cancelLabel: 'Clear',
          format: 'YYYY/MM/DD'
        }
      }, cb);

      cb(today, today);
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
