<?php
/*
Authendicated users only allow to view this Summary Report page.
This page is used to view the list of Whatstapp Summary Report.
Here we can Filter, Copy, Export CSV, Excel, PDF, Search, Column visibility the Table

Version : 1.0
Author : 
Date : 
*/

session_start(); //start session
error_reporting(0); // The error reporting function

include_once "api/configuration.php"; // Include configuration.php
extract($_REQUEST); // Extract the request

// If the Session is not available redirect to index page
  if (!isset($_SESSION['yjwatsp_user_id']) || empty($_SESSION['yjwatsp_user_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
  }

$site_page_name = pathinfo($_SERVER["PHP_SELF"], PATHINFO_FILENAME); // Collect the Current page name
site_log_generate(
  "Summary Report Page : User : ".
  $_SESSION["yjwatsp_user_name"].
  " access the page on ".
  $current_date
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Prompt List ::
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
  <!-- style include in css -->
  <style>
    .search {
      width: 200px;
      margin-right: 50px;
    }

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
<div class="loading" style="display:none;">Loading&#8230;</div>
  <div class="theme-loader"></div>
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
            <h1>Prompt List</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="dashboard">Dashboard</a></div>
              <div class="breadcrumb-item">Prompt List</div>
            </div>
          </div>
          <!-- Report Filter and list panel -->
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <form method="post">
                      <div id="table-1_filter" class="dataTables_filter">
                        <!-- date filter -->
                       <div style="width: 20%; padding-right:1%; float: left;">Date :<input type="text" name="dates"
                            id="dates" value="<?= $_REQUEST[
                              "dates"
                            ] ?>" class="search" placeholder=""
                            style="width: 100%;height:30px;background-color: #e9ecef;border :1px solid #ced4da; "
                            aria-controls="table-1" readonly /></div>
                        <!-- submit button -->
                        <div style="width: 20%; padding-right:1%; float: left;">
                          <input type="submit" name="submit_1" id="submit_1" tabindex="10" value="Search"
                            class="btn btn-success " style="height:30px; margin-top: 20px;">
                        </div> 
                      </div>
                    </form>
                    <div class="table-responsive" id="id_prompt_List" style="padding-top: 20px;">
                      Loading…
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
  <script src="assets/multi-select/bootstrap.min.js"></script>
  <script src="assets/multi-select/bootstrap-multiselect.js"></script>
  <script src="assets/multi-select/multiple-select.min.js"></script>
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
  <script type="text/javascript">

    $(document).ready(function () {
      prompt_List();
    });

/*let currentAudio = null;
let currentIndicator = null;

function toggleAudio(audio_path, indicator_id) {
    const indicatorElement = document.getElementById(indicator_id);
 console.log('Button clicked:', indicator_id); // Debug: Log button click
    console.log('Audio path:', audio_path); // Debug: Log audio path

    if (currentAudio && currentIndicator === indicatorElement) {
        // If the same audio is clicked again, pause it
        if (currentAudio.paused) {
console.log('Resuming audio'); 
            currentAudio.play();
            indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
console.log('Pausing audio');
            currentAudio.pause();
            indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
        }
        return;
    }

    if (currentAudio) {
console.log('Pausing current audio');
        // Pause the currently playing audio
        currentAudio.pause();
        currentAudio.currentTime = 0;
        currentIndicator.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentIndicator = null;
    }

    // Play new audio
console.log('Playing new audio');
    currentAudio = new Audio(audio_path);
    currentAudio.play();
    currentIndicator = indicatorElement;
//     indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';
currentIndicator.innerHTML = 'HAI';

    currentAudio.addEventListener('ended', function() {
 console.log('Audio ended');
        // Audio has ended when this function is executed.
        indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentIndicator = null;
    }, false);
}*/
let currentAudio = null;
let currentIndicator = null;

function toggleAudio(audio_path, indicator_id) {
    const indicatorElement = document.getElementById(indicator_id);

    if (currentAudio && currentIndicator === indicatorElement) {
        if (currentAudio.paused) {
            currentAudio.play();
            indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
            currentAudio.pause();
            currentAudio.currentTime = 0; // Reset audio to start
            indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
        }
        return;
    }

    if (currentAudio) {
        currentAudio.pause();
        currentAudio.currentTime = 0;
        currentIndicator.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentIndicator = null;
    }

    currentAudio = new Audio(audio_path);
    currentAudio.play();
    currentIndicator = indicatorElement;
    indicatorElement.innerHTML = '<i class="fas fa-pause"></i>';

    currentAudio.addEventListener('ended', function() {
        indicatorElement.innerHTML = '<i class="fas fa-play"></i>';
        currentAudio = null;
        currentIndicator = null;
    }, false);
}


    var dates;
    $("#submit_1").click(function (e) {
      e.preventDefault();
      dates = $('#dates').val();
      prompt_List();
    });


    // prompt_List func
    function prompt_List() {
      dates = $('#dates').val();
      $.ajax({
        type: 'post',
        url: "ajax/display_functions.php?call_function=compose_obd_prompt_list&dates=" + dates + "",
        dataType: 'html',
        beforeSend: function () {
          $('.theme-loader').show();
        },
        complete: function () {
          $('.theme-loader').hide();
        },
        success: function (response) {
          $("#id_prompt_List").html(response);
        },
        error: function (response, status, error) { }
      });
    }


    // date picker function adding
    $(function () {
      var today = moment();

      function cb(start, end) {
        $('input[name="dates"]').html(start.format('D MMMM, YYYY') + ' - ' + end.format('D MMMM, YYYY'));
      }
      $('input[name="dates"]').daterangepicker({
        autoUpdateInput: true,
        startDate: today,  // Set the default start date to today
        endDate: today,    // Set the default end date to today
        maxDate: today,    // Set the maximum date to today
        minDate: moment().subtract(1, 'month'),  // Set the minimum date to one month ago
        locale: {
          cancelLabel: 'Clear',
          format: 'YYYY/MM/DD'
        }
      });

      $('input[name="dates"]').on('apply.daterangepicker', function (ev, picker) {
        // You can access the selected dates using picker.startDate and picker.endDate
        $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        var first_date = picker.startDate.format('YYYY/MM/DD');
        var second = picker.endDate.format('YYYY/MM/DD');
        campaign_name();
        e.preventDefault();
      });

      $('input[name="dates"]').on('cancel.daterangepicker', function (ev, picker) {
        //$(this).val('');
      });
    });


    // function adding to the filters
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

