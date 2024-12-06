<?php
session_start();
error_reporting(0);
// Include configuration.php
include_once('../api/configuration.php');
extract($_REQUEST);

$current_date = date("Y-m-d H:i:s");

// Dashboard Page dashboard_count - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "dashboard_count") {
  site_log_generate("Dashboard Page : User : " . $_SESSION['yjwatsp_user_name'] . " access this page on " . date("Y-m-d H:i:s"), '../');
  // To Send the request  API
  $replace_txt = '{
    "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
  }';

  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // add the bearer
  // It will call "dashboard" API to verify, can we access for the dashboard details
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/dashboard/dashboard_list',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  // Send the data into API and execute 
  site_log_generate("Dashboard Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  //echo  $response;
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }

  // After got response decode the JSON result
  $state1 = json_decode($response, false);
  if ($state1->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  site_log_generate("Dashboard Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
  $total_msg = 0;
  $available_messages = 0;
  $total_success = 0;
  $total_failed = 0;
  $total_waiting = 0;
  $total_processing = 0;
  // To get the one by one data
  if ($state1->response_code == 1) { // If the response is success to execute this condition
    $data = $state1->report;
    for ($indicator = 0; $indicator < count($data); $indicator++) {
      $subArray = $data[$indicator];
      for ($indicators = 0; $indicators < count($subArray); $indicators++) {
        //Looping the indicator is less than the count of report.if the condition is true to continue the process.if the condition is false to stop the process 
        $header_title = $subArray[$indicators]->rights_name;
        $user_id = $subArray[$indicators]->user_id;
        $user_name = $subArray[$indicators]->user_name;
        $available_messages = $subArray[$indicators]->available_credits;
        //echo $available_messages;
        $total_msg = $subArray[$indicators]->total_msg;
        $total_success = $subArray[$indicators]->total_success;
        $total_failed = $subArray[$indicators]->total_failed;
        $total_waiting = $subArray[$indicators]->total_waiting;
        $total_processing = $subArray[$indicators]->total_process;

        $total_dialled = $subArray[$indicators]->total_dialled;
        $total_success = $subArray[$indicators]->total_success;
        $total_failed = $subArray[$indicators]->total_failed;
        $total_busy = $subArray[$indicators]->total_busy;
        $total_no_answer = $subArray[$indicators]->total_no_answer;

        if ($_SESSION['yjwatsp_user_master_id'] != '5') {
          if ($header_title == "WHATSAPP") { // If the userid is equal to authenticate userid success to execute this condition
            ?>
            <div class="col-lg-12 col-md-12 col-sm-12">
              <style>
                .card .card-stats .card-stats-items {
                  min-height: 50px;
                  height: auto;
                }
              </style>
              <div class="card card-statistic-2">
                <div class="card-stats">
                  <div class="card-stats-title mb-2">
                    <?= strtoupper($user_name) ?> Summary
                  </div>
                  <div class="card-stats-items" style="margin: 10px 0 20px 0;">
                    <div class="form-group mb-2 row">
                      <!-- Whatsapp -->
                      <div class="col-sm-6" style="float: left; text-align: center;"><span
                          style=" font-size:20px; font-weight: bold;">
                          <?= $header_title ?>
                        </span>
                        <div class="col-sm-12 " style="float: left;">

                          <div class="card-stats-item col-4" style="float: left;" title="Waiting">
                            <div class="card-stats-item-count">
                              <?= $total_waiting ?>
                            </div>
                            <div class="card-stats-item-label">Waiting</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="In Processing">
                            <div class="card-stats-item-count">
                              <?= $total_processing ?>
                            </div>
                            <div class="card-stats-item-label">In Processing</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="Failed">
                            <div class="card-stats-item-count">
                              <?= ($total_failed) ?>
                            </div>
                            <div class="card-stats-item-label">Failed</div>
                          </div>
                        </div>
                        <div class="col-sm-12" style="float: left;">
                          <div class="card-stats-item col-4" style="float: left;" title="Success">
                            <div class="card-stats-item-count">
                              <?= $total_success ?>
                            </div>
                            <div class="card-stats-item-label">Success</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="Available Credits">
                            <div class="card-stats-item-count">
                            <?php  if ($user_id == 1) { echo '-'; } else {  echo $available_messages; } ?> </div>
                            <div class="card-stats-item-label">Available Credits</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="Total Messages">
                            <div class="card-stats-item-count">
                              <?= $total_msg ?>
                            </div>
                            <div class="card-stats-item-label">Total Messages</div>
                          </div>
                        </div>
                      </div>
                    <? } else if ($header_title == "GSM SMS") { // otherwise it willbe execute   ?>
                        <!-- SMS -->
                        <div class="col-sm-6" style="float: left; text-align: center;"><span
                            style=" font-size:20px; font-weight: bold;">
                          <?= $header_title ?>
                          </span>
                          <div class="col-sm-12" style="float: left;">

                            <div class="card-stats-item col-4" style="float: left;" title="Waiting">
                              <div class="card-stats-item-count">
                              <?= $total_waiting ?>
                              </div>
                              <div class="card-stats-item-label">Waiting</div>
                            </div>
                            <div class="card-stats-item col-4" style="float: left;" title="In Processing">
                              <div class="card-stats-item-count">
                              <?= $total_processing ?>
                              </div>
                              <div class="card-stats-item-label">In Processing</div>
                            </div>
                            <div class="card-stats-item col-4" style="float: left;" title="Failed">
                              <div class="card-stats-item-count">
                              <?= ($total_failed) ?>
                              </div>
                              <div class="card-stats-item-label">Failed</div>
                            </div>
                          </div>
                          <div class="col-sm-12" style="float: left;">

                            <div class="card-stats-item col-4" style="float: left;" title="Success">
                              <div class="card-stats-item-count">
                              <?= $total_success ?>
                              </div>
                              <div class="card-stats-item-label">Success</div>
                            </div>
                            <div class="card-stats-item col-4" style="float: left;" title="Available Credits">
                              <div class="card-stats-item-count">
                                <?php  if ($user_id == 1) { echo '-'; } else {  echo $available_messages; } ?>
                              </div>
                              <div class="card-stats-item-label">Available Credits</div>
                            </div>
                            <div class="card-stats-item col-4" style="float: left;" title="Total Messages">
                              <div class="card-stats-item-count">
                              <?= $total_msg ?>
                              </div>
                              <div class="card-stats-item-label">Total Messages</div>
                            </div>
                          </div>
                        </div>
                    <? } else if ($header_title == "RCS") { // otherwise it willbe execute
            ?>
                          <!-- RCS -->
                          <div class="col-sm-6" style="float: left; text-align: center;margin-top:10px;">
                            <span style=" font-size:20px; font-weight: bold;">
                          <?= $header_title ?>
                            </span>
                            <div class="col-sm-12" style="float: left;">

                              <div class="card-stats-item col-4" style="float: left;" title="Waiting">
                                <div class="card-stats-item-count">
                              <?= $total_waiting ?>
                                </div>
                                <div class="card-stats-item-label">Waiting</div>
                              </div>
                              <div class="card-stats-item col-4" style="float: left;" title="In Processing">
                                <div class="card-stats-item-count">
                              <?= $total_processing ?>
                                </div>
                                <div class="card-stats-item-label">In Processing</div>
                              </div>
                              <div class="card-stats-item col-4" style="float: left;" title="Failed">
                                <div class="card-stats-item-count">
                              <?= ($total_failed) ?>
                                </div>
                                <div class="card-stats-item-label">Failed</div>
                              </div>
                            </div>
                            <div class="col-sm-12" style="float: left;">

                              <div class="card-stats-item col-4" style="float: left;" title="Success">
                                <div class="card-stats-item-count">
                              <?= $total_success ?>
                                </div>
                                <div class="card-stats-item-label">Success</div>
                              </div>
                              <div class="card-stats-item col-4" style="float: left;" title="Available Credits">
                                 <div class="card-stats-item-count">
                                    <?php  if ($user_id == 1) { echo '-'; } else {  echo $available_messages; } ?>
                                   </div>
                                <div class="card-stats-item-label">Available Credits</div>
                              </div>
                              <div class="card-stats-item col-4" style="float: left;" title="Total Messages">
                                <div class="card-stats-item-count">
                              <?= $total_msg ?>
                                </div>
                                <div class="card-stats-item-label">Total Messages</div>
                              </div>
                            </div>
                          </div>

                      <?
          } else if ($header_title == "OBD CALL SIP") { // otherwise it willbe execute
            ?>
                            <!-- OBD CALL SIP -->
                            <div class="col-sm-6" style="float: left; text-align: center;margin-top:10px;">
                              <span style=" font-size:20px; font-weight: bold;">
                          <?= $header_title ?>
                              </span>
                              <div class="col-sm-12" style="float: left;">

                                <div class="card-stats-item col-4" style="float: left;" title="Total Dialled">
                                  <div class="card-stats-item-count">
                              <?= isset($total_dialled) && $total_dialled ? $total_dialled : '0' ?>
                                  </div>
                                  <div class="card-stats-item-label">Total Calls</div>
                                </div>
                                <div class="card-stats-item col-4" style="float: left;" title="Total Success">
                                  <div class="card-stats-item-count">
                              <?= isset($total_success) && $total_success ? $total_success : '0' ?>
                                  </div>
                                  <div class="card-stats-item-label">Total Success</div>
                                </div>
                                <div class="card-stats-item col-4" style="float: left;" title="Total Failed">
                                  <div class="card-stats-item-count">
                              <?= $total_failed ? $total_failed : '0' ?>
                                  </div>
                                  <div class="card-stats-item-label">Total Failed</div>
                                </div>
                              </div>
                              <div class="col-sm-12" style="float: left;">

                                <div class="card-stats-item col-4" style="float: left;" title="Total Busy">
                                  <div class="card-stats-item-count">
                              <?= $total_busy ? $total_busy : '0' ?>
                                  </div>
                                  <div class="card-stats-item-label">Total Busy</div>
                                </div>
                                <div class="card-stats-item col-4" style="float: left;" title="Available Credits">
                                  <div class="card-stats-item-count">
                                  <?php  if ($user_id == 1) { echo '-'; } else { echo $available_messages; } ?>
                                  </div>
                                  <div class="card-stats-item-label">Available Credits</div>
                                </div>
                                <div class="card-stats-item col-4" style="float: left;" title="Total Noanswered">
                                  <div class="card-stats-item-count">
                              <?= $total_no_answer ? $total_no_answer : '0' ?>
                                  </div>
                                  <div class="card-stats-item-label">Total Noanswered</div>
                                </div>

                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
            <?
          }
        } else {
          if ($header_title == "OBD CALL SIP") { ?>
            <div class="col-lg-12 col-md-12 col-sm-12">
              <style>
                .card .card-stats .card-stats-items {
                  min-height: 50px;
                  height: auto;
                }
              </style>
              <div class="card card-statistic-2">
                <div class="card-stats">
                  <div class="card-stats-title mb-2">
                    <?= strtoupper($user_name) ?> Summary
                  </div>
                  <div class="card-stats" style="margin: 10px 0 20px 0;">
                    <div class="form-group mb-2 ">
                      <!-- OBD CALL SIP -->
                      <div class="col" style="float: left; text-align: center;"><span style=" font-size:20px; font-weight: bold;">
                          <?= $header_title ?>
                        </span>
                        <div class="col-sm-12 " style="float: left;">

                          <div class="card-stats-item col-4" style="float: left;" title="Waiting">
                            <div class="card-stats-item-count">
                              <?= $total_waiting ?>
                            </div>
                            <div class="card-stats-item-label">Waiting Calls</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="In Processing">
                            <div class="card-stats-item-count">
                              <?= $total_processing ?>
                            </div>
                            <div class="card-stats-item-label">In Processing Calls</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="Failed">
                            <div class="card-stats-item-count">
                              <?= ($total_failed) ?>
                            </div>
                            <div class="card-stats-item-label">Failed Calls</div>
                          </div>
                        </div>
                        <div class="col-sm-12" style="float: left;">
                          <div class="card-stats-item col-4" style="float: left;" title="Success">
                            <div class="card-stats-item-count">
                              <?= $total_success ?>
                            </div>
                            <div class="card-stats-item-label">Success Calls</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="Available Credits">
                            <div class="card-stats-item-count">
                           <?php  if ($user_id == 1) { echo '-'; } else { echo $available_messages; } ?>
                            </div>
                            <div class="card-stats-item-label">Available Credits</div>
                          </div>
                          <div class="card-stats-item col-4" style="float: left;" title="Total Messages">
                            <div class="card-stats-item-count">
                              <?= $total_msg ?>
                            </div>
                            <div class="card-stats-item-label">Total Calls</div>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
            </div>
          <? }
        }
      }
    }
  }
}
// Dashboard Page dashboard_count - End

// manage_whatsappno_list Page manage_whatsappno_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "manage_whatsappno_list") {
  site_log_generate("Manage Whatsappno List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User</th>
        <th>Mobile No</th>
        <th>App Version</th>
        <th>Status</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/sender_id/sender_id_list',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_SSL_VERIFYPEER => 1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->sender_id); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->sender_id[$indicator]->sender_id_entdate));
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->sender_id[$indicator]->user_name) ?></td>
            <td><?= $sms->sender_id[$indicator]->mobile_no ?></td>
            <td><?= $sms->sender_id[$indicator]->app_version ?></td>
            <td>
              <? if ($sms->sender_id[$indicator]->sender_id_status == 'Y') { ?><a href="#!"
                  class="btn btn-outline-success btn-disabled"
                  style="width:100px; text-align:center">Active</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'D') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Deleted</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'B') { ?><a
                  href="#!" class="btn btn-outline-dark btn-disabled"
                  style="width:100px; text-align:center">Blocked</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'N') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Inactive</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'M') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled" style="width:100px; text-align:center">Mobile No
                  Mismatch</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'I') { ?><a href="#!"
                  class="btn btn-outline-warning btn-disabled"
                  style="width:100px; text-align:center">Invalid</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'P') { ?><a
                  href="#!" class="btn btn-outline-info btn-disabled"
                  style="width:100px; text-align:center">Processing</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'R') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Rejected</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'X') { ?><a
                  href="#!" class="btn btn-outline-primary btn-disabled" style="width:100px; text-align:center">Need
                  Rescan</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'L') { ?><a href="#!"
                  class="btn btn-outline-info btn-disabled"
                  style="width:100px; text-align:center">Linked</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'U') { ?><a
                  href="#!" class="btn btn-outline-warning btn-disabled"
                  style="width:100px; text-align:center">Unlinked</a><? } elseif ($sms->sender_id[$indicator]->sender_id_status == 'T') { ?><a
                  href="#!" class="btn btn-outline-warning btn-disabled"
                  style="width:100px; text-align:center">Testing</a><? } ?>
            </td>
            <td><?= $entry_date ?></td>
            <td id='id_approved_lineno_<?= $indicatori ?>'>
              <?/* if(($sms->sender_id[$indicator]->sender_id_status == 'N' or $sms->sender_id[$indicator]->sender_id_status == 'M' or $sms->sender_id[$indicator]->sender_id_status == 'X') and $sms->sender_id[$indicator]->is_qr_code == 'Y') { ?>
                                                                              <a href="add_senderid?mob=<?= $sms->sender_id[$indicator]->mobile_no ?>" class="btn btn-success">Scan</a>
                                                              <? } else { ?>
                                                                              <a href="#!" class="btn btn-outline-light btn-disabled" style="cursor: not-allowed;">Scan</a>
                                                              <? }*/ ?>
              <? if ($sms->sender_id[$indicator]->sender_id_status != 'D') { ?>
                <button type="button" title="Delete Sender ID"
                  onclick="remove_senderid_popup('<?= $sms->sender_id[$indicator]->sender_id ?>', 'D', '<?= $indicatori ?>')"
                  class="btn btn-icon btn-danger" style="padding: 0.3rem 0.41rem !important;">Delete</button>
              <? } else { ?>
                <a href="#!" class="btn btn-outline-light btn-disabled"
                  style="padding: 0.3rem 0.41rem !important;cursor: not-allowed;">Delete</a>
              <? } ?>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// manage_whatsappno_list Page manage_whatsappno_list - End

// manage_group_list Page manage_group_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "manage_group_list") {
  site_log_generate("Group List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User</th>
        <th>Sender ID</th>
        <th>Group Name</th>
        <th>Total Mobile Numbers</th>
        <th>Success Mobile Numbers</th>
        <th>Failure Mobile Numbers</th>
        <th>Status</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/list/group_list',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_SSL_VERIFYPEER => 1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      site_log_generate("Group List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      $sms = json_decode($response, false);
      site_log_generate("Group List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }

      // print_r($sms); exit;
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->group_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->group_list[$indicator]->group_contact_entdate));
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->group_list[$indicator]->user_name) ?></td>
            <td><?= $sms->group_list[$indicator]->mobile_number ?></td>
            <td><?= $sms->group_list[$indicator]->group_name ?></td>
            <td><?= $sms->group_list[$indicator]->total_count ?></td>
            <td><?= $sms->group_list[$indicator]->success_count ?></td>
            <td><?= $sms->group_list[$indicator]->failure_count ?></td>
            <td>
              <? if ($sms->group_list[$indicator]->group_contact_status == 'Y') { ?><a href="#!"
                  class="btn btn-outline-success btn-disabled">Active</a><? } elseif ($sms->group_list[$indicator]->group_contact_status == 'N') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled">Inactive</a><? } ?>
            </td>
            <td><?= $entry_date ?></td>
            <td><a
                href="add_contact_group?group=<?= $sms->group_list[$indicator]->group_contact_id ?>&sender=<?= $sms->group_list[$indicator]->mobile_number ?>"
                class="btn btn-primary"><i class="fas fa-edit"></i> Update Contacts</a></td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
  <?
}
// manage_group_list Page manage_group_list - End

// approve_campaign_list Page approve_campaign_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_campaign_list") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <th>Entry Date</th>
        <!---<th>Receiver Numbers</th>-->
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
        "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
        "user_product": "GSM SMS"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
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
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }

      // print_r($sms); exit;
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->whatspp_config_entdate));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          $array_buffer = $sms->campaign_list[$indicator]->receiver_mobile_nos->data;
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->campaign_list[$indicator]->user_name) ?></td>
            <td><?= $sms->campaign_list[$indicator]->campaign_name ?></td>
            <td style="text-align:center;" id='approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
              </div>
              <div>
                <button type="button" title="Total Mobile Numbers"
                  onclick="func_download_rc_no('<?= $encodedDatas = json_encode($sms->campaign_list[$indicator]->receiver_mobile_nos->data) ?>')"
                  class="btn btn-icon btn-success">Download</button>
                <!--- <a href="#!" onclick="func_download_rc_no('<?= $encodedDatas = json_encode($sms->campaign_list[$indicator]->receiver_mobile_nos->data) ?>')">Download---->

              </div>
            </td>
            <td><?= $sms->campaign_list[$indicator]->cm_entry_date ?></td>
            <td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Approve"
                  onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>',`<?= $sms->campaign_list[$indicator]->text_title ?>`,'<?= $sms->campaign_list[$indicator]->message_type ?>','<?= $sms->campaign_list[$indicator]->media_url ?>')"
                  class="btn btn-icon btn-success">Approve campaign

                </button>
              </div>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Reject"
                  onclick="cancel_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>')"
                  class="btn btn-icon btn-danger">Reject</i></button>
              </div>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, ':visible']
          }
        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: ':visible'
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: ':visible', // Exclude the third column (index 3)
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

  </script>
  <?
}
// approve_campaign_list Page approve_campaign_list - End


// purchase_message_credit_list Page purchase_message_credit_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "purchase_message_credit_list") {
  site_log_generate("Payment History Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>
  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th>User</th>
        <th>Parent User</th>
        <th>Plan / Product Name</th>
        <th>Message Credit / Amount</th>
        <th>Comments</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?
      // To Send the request API 
      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
          }';
      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // It will call "purchase_message_credit_list" API to verify, can we can we allow to view the message credit list
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/purchase_credit/payment_history',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      // Send the data into API and execute   
      $response = curl_exec($curl);
      site_log_generate("Payment History Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("Payment History Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      // To get the one by one data
      $indicatori = 0;
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }
      if ($sms->num_of_rows > 0) { // If the response is success to execute this condition
//Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition is false to stop the process
        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->usr_credit_entry_date));
          ?>
          <tr>
            <td class="text-center"><?= $indicatori ?></td>
            <td class="text-center"><?= $sms->report[$indicator]->user_name ?></td>
            <td class="text-center"><?= $sms->report[$indicator]->parent_name ?></td>
            <td>
              <?= $sms->report[$indicator]->price_from . " - " . $sms->report[$indicator]->price_to . " [Rs." . $sms->report[$indicator]->price_per_message . "] / (" . $sms->report[$indicator]->rights_name . ")" ?>
            </td>
            <td><?= $sms->report[$indicator]->raise_credits . " / Rs." . $sms->report[$indicator]->amount ?></td>
            <td><?= $sms->report[$indicator]->usr_credit_comments ?></td>
            <td class="text-center"><? switch ($sms->report[$indicator]->usr_credit_status) {
              case 'A':
                echo '<a href="#!" class="btn btn-outline-success btn-disabled" title="Amount Paid" style="width:150px; text-align:center">Amount Paid</a>';
                break;
              case 'U':
                echo '<a href="#!" class="btn btn-outline-success btn-disabled" title="Message Credited" style="width:150px; text-align:center">Message Credited</a>';
                break;
              case 'W':
                echo '<a href="#!" class="btn btn-outline-info btn-disabled" style="width:150px; text-align:center" title="Amount Not Paid">Amount Not Paid</a>';
                break;
              case 'F':
                echo '<a href="#!" class="btn btn-outline-dark btn-disabled" title="Failed" style="width:150px; text-align:center">Failed</a>';
                break;
              case 'N':
                echo '<a href="#!" class="btn btn-outline-dark btn-disabled" title="Inactive" style="width:150px; text-align:center">Inactive</a>';
                break;
              default:
                echo '<a href="#!" class="btn btn-outline-info btn-disabled" style="width:150px; text-align:center" title="Amount Not Paid">Amount Not Paid</a>';
                break;
            } ?></td>
            <td class="text-center"><?= $entry_date ?></td>
          </tr>
          <?
        }
      } else if ($sms->response_status == 204) {
        site_log_generate("Payment History Page : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("Payment History Page : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>
    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
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
  <?
}
// purchase_message_credit_list Page purchase_message_credit_list - End

// approve_payment Page approve_payment - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_payment") {
  site_log_generate("Approve Payment Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>
  <form name="myform" id="myForm" method="post" action="message_credit">
    <input type="hidden" name="bar" id="bar" value="" />
  </form>

  <table class="table table-striped" id="table-1" style="text-align:center;">
    <thead>
      <tr>
        <th>#</th>
        <th>User</th>
        <th>User Type</th>
        <th>Parent User</th>
        <th>Plan / Product Name</th>
        <th>Message Credit / Amount</th>
        <th>Comments</th>
        <th>Status</th>
        <th>Date</th>
        <th>Payment Details</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      // To Send the request API 
      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
          }';
      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // It will call "approve_payment" API to verify, can we can we allow to view the message credit list
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/purchase_credit/approve_payment',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      // Send the data into API and execute   
      $response = curl_exec($curl);
      site_log_generate("Approve Payment Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("Approve Payment Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      // To get the one by one data
      $indicatori = 0;
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? } else if ($sms->num_of_rows > 0) { // If the response is success to execute this condition
//Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition is false to stop the process
        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->usr_credit_entry_date));
          ?>
              <tr>
                <td class="text-center"><?= $indicatori ?></td>
                <td class="text-center"><?= $sms->report[$indicator]->user_name ?></td>
                <td class="text-center"><?= $sms->report[$indicator]->user_type ?></td>
                <td class="text-center"><?= $sms->report[$indicator]->parent_name ?></td>
                <td>
              <?= $sms->report[$indicator]->price_from . " - " . $sms->report[$indicator]->price_to . " [Rs." . $sms->report[$indicator]->price_per_message . "] / (" . $sms->report[$indicator]->rights_name . ")" ?>
                </td>
                <td><?= $sms->report[$indicator]->raise_credits . " / Rs." . $sms->report[$indicator]->amount ?></td>
                <td><?= $sms->report[$indicator]->usr_credit_comments ?></td>
                <td class="text-center"><? switch ($sms->report[$indicator]->usr_credit_status) {
                  case 'A':
                    echo '<a href="#!" class="btn btn-outline-success btn-disabled" title="Approved" style="width:100px; text-align:center">Approved</a>';
                    break;
                  case 'W':
                    echo '<a href="#!" class="btn btn-outline-info btn-disabled" style="width:100px; text-align:center" title="Waiting">Waiting</a>';
                    break;
                  case 'F':
                    echo '<a href="#!" class="btn btn-outline-dark btn-disabled" title="Failed" style="width:100px; text-align:center">Failed</a>';
                    break;
                  case 'U':
                    echo '<a href="#!" class="btn btn-outline-info btn-disabled" style="width:100px; text-align:center" title="Credit Updated">Credit Updated</a>';
                    break;
                  default:
                    echo '<a href="#!" class="btn btn-outline-info btn-disabled" style="width:100px; text-align:center" title="Waiting">Waiting</a>';
                    break;
                } ?></td>
                <td class="text-center"><?= $entry_date ?></td>
                <td class="text-center text-danger"><?= $sms->report[$indicator]->usr_credit_status_cmnts ?></td>
                <td class="text-center">
              <?php if ($sms->report[$indicator]->usr_credit_status != 'U') {
 ?> <a href="#!"
                      data-val="<?= $sms->report[$indicator]->pricing_slot_id ?>&<?= $sms->report[$indicator]->user_id ?>&<?= $sms->report[$indicator]->price_to ?>&<?= $sms->report[$indicator]->usr_credit_id ?>"
                      class="btn btn-primary formAnchor">Add Message Credit</a>
              <? } else { ?>
                    <a href="#!" class="btn btn-outline-light btn-disabled"
                      style="padding: 0.3rem 0.41rem !important;cursor: not-allowed;">Add Message Credit</a>
              <? } ?>
                </td>
              </tr>
          <?
        }
      } else if ($sms->response_status == 204) {
        site_log_generate("Approve Payment Page : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("Approve Payment Page : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>
    </tbody>
  </table>

  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    $('.formAnchor').on('click', function (e) {
      e.preventDefault(); // prevents a window.location change to the href
      $('#bar').val($(this).data('val'));  // sets to 123 or abc, respectively
      $('#myForm').submit();
    });

    $(".btn_msgcrdt").click(function () {
      var link = $(this).attr('var');
      alert("link : " + link);
      $('.post').attr("value", link);
      $('.redirect').submit();
    });

    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
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
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// approve_payment Page approve_payment - End

// message_credit_list Page message_credit_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "message_credit_list") {
  site_log_generate("Message Credit List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>
  <table class="table table-striped" id="table-1" style="text-align:center">
    <thead>
      <tr>
        <th>#</th>
        <th>Parent User</th>
        <th>Receiver User</th>
        <th>User Type</th>
        <th>Product Name</th>
        <th>Message Count</th>
        <th>Details</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?
      // To Send the request API 
      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
          }';
      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // It will call "message_credit_list" API to verify, can we can we allow to view the message credit list
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/purchase_credit/message_credit_list',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      // Send the data into API and execute   
      $response = curl_exec($curl);
      site_log_generate("Message Credit List Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("Message Credit List Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      // To get the one by one data
      $indicatori = 0;
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? } else if ($sms->num_of_rows > 0) { // If the response is success to execute this condition
//Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition is false to stop the process
        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->message_credit_log_entdate));
          ?>
              <tr>
                <td><?= $indicatori ?></td>
                <td><?= $sms->report[$indicator]->parntname ?></td>
                <td><?= $sms->report[$indicator]->usrname ?></td>
                <td><?= $sms->report[$indicator]->user_type ?></td>
                <td><?= $sms->report[$indicator]->rights_name ?></td>
                <td><?= $sms->report[$indicator]->provided_message_count ?></td>
                <td><?= $sms->report[$indicator]->message_comments ?></td>
                <td><?= $entry_date ?></td>
              </tr>
          <?
        }
      } else if ($sms->response_status == 204) {
        site_log_generate("Message Credit List Page : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("Message Credit List Page : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>
    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
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
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// message_credit_list Page message_credit_list - End

// approve_campaign_list_watsp Page approve_campaign_list_watsp - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_campaign_list_watsp") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <th>Status</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
        "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
        "user_product" : "WHATSAPP"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
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
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->cm_entry_date));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          $is_same_media = $sms->campaign_list[$indicator]->is_same_media;

          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->campaign_list[$indicator]->user_name) ?></td>
            <td><?= $sms->campaign_list[$indicator]->campaign_name ?></td>
            <td style="text-align:center;" id='approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Total mobile numbers">
                <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
              </div>
              <div><button type="button" title="Total Mobile Numbers"
                  onclick="func_download_rc_no('<?= $encodedDatas = json_encode($sms->campaign_list[$indicator]->receiver_mobile_nos->data) ?>')"
                  class="btn btn-icon btn-success">Download</button>
              </div>
            </td>
            <td><? if ($sms->campaign_list[$indicator]->cm_status == 'W') { ?><a href="#!"
                  class="btn btn-outline-info btn-disabled" style="text-align:center">Waiting</a><? } ?></td>
            <td><?= $entry_date ?></td>
            <td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Approve"
                  onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>',`<?= $sms->campaign_list[$indicator]->text_title ?>`,'<?= $sms->campaign_list[$indicator]->message_type ?>','<?= $sms->campaign_list[$indicator]->media_url ?>','<?= $is_same_media ?>')"
                  class="btn btn-icon btn-success">Approve campaign
                </button>
              </div>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Reject"
                  onclick="cancel_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>')"
                  class="btn btn-icon btn-danger">Reject</i></button>
              </div>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// approve_campaign_list_watsp Page approve_campaign_list_watsp - End

// approve_campaign_list_rcs Page approve_campaign_list_rcs - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_campaign_list_rcs") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
        "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
        "user_product" : "RCS"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
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
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->whatspp_config_entdate));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->campaign_list[$indicator]->user_name) ?></td>
            <td><?= $sms->campaign_list[$indicator]->campaign_name ?></td>
            <td style="text-align:center;" id='approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
              </div>
              <div><button type="button" title="Total Mobile Numbers"
                  onclick="func_download_rc_no('<?= $encodedDatas = json_encode($sms->campaign_list[$indicator]->receiver_mobile_nos->data) ?>')"
                  class="btn btn-icon btn-success">Download</button>
              </div>
            </td>
            <td>
              <?= $sms->campaign_list[$indicator]->cm_entry_date ?>
            </td>
            <td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Approve"
                  onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>',`<?= $sms->campaign_list[$indicator]->text_title ?>`,'<?= $sms->campaign_list[$indicator]->message_type ?>','<?= $sms->campaign_list[$indicator]->media_url ?>')"
                  class="btn btn-icon btn-success">Approve campaign
                </button>
              </div>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Reject"
                  onclick="cancel_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>')"
                  class="btn btn-icon btn-danger">Reject</i></button>
              </div>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
  <?
}
// approve_campaign_list_rcs Page approve_campaign_list_rcs - End

// sms_campaign_list Page sms_campaign_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "sms_campaign_list") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <!-- <th>Action</th> -->
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
        "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
        "user_product" : "GSM SMS"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
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
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->whatspp_config_entdate));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->campaign_list[$indicator]->user_name) ?></td>
            <td><?= $sms->campaign_list[$indicator]->campaign_name ?></td>
            <td><?= $sms->campaign_list[$indicator]->total_mobile_no_count ?></td>

            <? /*<td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
                                                 <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                                   <button type="button" title="Approve" onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name?>')" class="btn btn-icon btn-success">Approve campaign
                                                   <!-- <i class="fas fa-check"></i> -->
                                                 </button>
                                                   <!-- <button type="button" title="Reject" onclick="cancel_popup('<?= $sms->report[$indicator]->whatspp_config_id ?>', 'R', '<?= $indicatori ?>')" class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button> -->
                                                 </div>
                                                 </td> */ ?>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
  <?
}
// sms_campaign_list Page sms_campaign_list - End

// whatsapp_campaign_list Page whatsapp_campaign_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "whatsapp_campaign_list") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <!-- <th>Action</th> -->
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
        "user_id":"' . $_SESSION['yjwatsp_user_id'] . '"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_SSL_VERIFYPEER => 1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->whatspp_config_entdate));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->campaign_list[$indicator]->user_name) ?></td>
            <td><?= $sms->campaign_list[$indicator]->campaign_name ?></td>
            <td><?= $sms->campaign_list[$indicator]->total_mobile_no_count ?></td>

            <? /*<td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
                                                 <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                                   <button type="button" title="Approve" onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name?>')" class="btn btn-icon btn-success">Approve campaign
                                                   <!-- <i class="fas fa-check"></i> -->
                                                 </button>
                                                   <!-- <button type="button" title="Reject" onclick="cancel_popup('<?= $sms->report[$indicator]->whatspp_config_id ?>', 'R', '<?= $indicatori ?>')" class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button> -->
                                                 </div>
                                                 </td> */ ?>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
  <?
}
// approve_campaign_list Page approve_campaign_list - End


// campaign_report Page campaign_report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "campaign_report") {
  site_log_generate("Campaign Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <? if ($_SESSION['yjwatsp_user_master_id'] != 2) { ?>
          <th>User</th>
        <? } ?>
        <th>Campaign Name</th>
        <th>Count</th>
        <th>Mobile No</th>
        <th>Entry Date</th>
        <th>Response Status</th>
        <th>Delivery Status</th>
        <th>Read Status</th>
        <th>Message</th>
      </tr>
    </thead>
    <tbody>

      <?
      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "user_product":"WHATSAPP",';
      // To Send the request API 
      if (($_REQUEST['dates'] != 'undefined') && ($_REQUEST['dates'] != '[object HTMLInputElement]') && ($_REQUEST['dates'] != '')) {
        $date = $_REQUEST['dates'];
        $td = explode('-', $date);
        $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
        $thismonth_today = date("Y/m/d", strtotime($td[1]));
        if ($date) {
          $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
        }
      } else {
        $currentDate = date('Y/m/d');
        $thirtyDaysAgo = date('Y/m/d', strtotime('-7 days', strtotime($currentDate)));
        $date = $thirtyDaysAgo . "-" . $currentDate; // 01/28/2023 - 02/27/2023 
        $replace_txt .= '"date_filter" : "' . $thirtyDaysAgo . ' - ' . $currentDate . '",';
      }

      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= ',"campaign_id" : "undefined" } ';
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/detailed_report',
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
      site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      $sms = json_decode($response, false);
      site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;

      if ($sms->response_status == 200 && $sms->num_of_rows > 10000) { ?>
        <div><input type="hidden" class="form-control" name="num_of_rows" id="num_of_rows"
            value="<?= $sms->num_of_rows ?>" /></div>
        <a id="downloadLink" href="<?= $sms->file_location ?>" download class="text-danger" style="display:none;"
          title="Download App File :<?php echo $sms->file_location; ?>"><?= $sms->file_location ?></a>
      <? } else if ($sms->response_status == 200 && $sms->num_of_rows <= 10000) {
        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          $indicatori++;
          $user_name = $sms->report[$indicator]->user_name; ?>
            <div> <input type="hidden" class="form-control" name='user_name_array[]' id='user_name_array'
                value='<?= $user_name ?>' /></div>
          <? $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->cm_entry_date));

          if ($sms->report[$indicator]->response_date != '') {
            $response_date = $sms->report[$indicator]->response_date;
          } else {
            $response_date = '';
          }

          if ($sms->report[$indicator]->delivery_date != '') {
            $delivery_date = $sms->report[$indicator]->delivery_date;
          } else {
            $delivery_date = '';
          }

          if ($sms->report[$indicator]->read_date != '') {
            $read_date = $sms->report[$indicator]->read_date;
          } else {
            $read_date = '';
          }
          $response_status = $sms->report[$indicator]->response_status;
          $response_message = $sms->report[$indicator]->response_message;

          $response_id = $sms->report[$indicator]->response_id;
          $delivery_status = $sms->report[$indicator]->delivery_status;
          $read_status = $sms->report[$indicator]->read_status;
          $response_message = $sms->report[$indicator]->response_message;
          $disp_stat = '';
          switch ($response_status) {
            case 'Y':
              $disp_stat = '<div class="badge badge-success" style="width:130px;">SENT</div><input type="hidden" class="form-control" name="response_status[]" value="SENT" />';
              break;
            case 'F':
              $disp_stat = '<div class="badge badge-danger" style="white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap; white-space: -o-pre-wrap;word-wrap: break-word;width:130px;">' . strtoupper($response_message) . '</div><input type="hidden" class="form-control" name="response_status[]" value="FAILED" />';
              break;
            case 'I':
              $disp_stat = '<div class="badge badge-warning" style="width:130px;">INVALID</div><input type="hidden" class="form-control" name="response_status[]" value="INVAILD" />';
              break;
            default:
              $disp_stat = '<div class="badge badge-info" style="width:130px;">YET TO SENT</div><input type="hidden" class="form-control" name="response_status[]" value="YET TO SENT" />';
              break;
          }

          $disp_stat1 = '';
          switch ($delivery_status) {
            case 'Y':
              $disp_stat1 = '<div class="badge badge-success" style="width:130px;">DELIVERED</div><input type="hidden" class="form-control" name="response_status[]" value="DELIVERED" />';
              break;

            default:
              $disp_stat1 = '<div class="badge badge-danger" style="width:130px;">NOT DELIVERED</div><input type="hidden" class="form-control" name="response_status[]" value="NOT DELIVERED" />';
              break;
          }

          $disp_stat2 = '';
          switch ($read_status) {
            case 'Y':
              $disp_stat2 = '<div class="badge badge-success" style="width:130px;">READ</div><input type="hidden" class="form-control" name="response_status[]" value="READ" />';
              break;

            default:
              $disp_stat2 = '<div class="badge badge-danger" style="width:130px;">NOT READ</div><input type="hidden" class="form-control" name="response_status[]" value="NOT READ" />';
              break;
          }
          ?>
            <tr>
              <td><?= $indicatori ?></td>
            <? if ($_SESSION['yjwatsp_user_master_id'] != 2) { ?>
                <td><?= strtoupper($user_name) ?></td>
            <? } ?>
              <td><?= $sms->report[$indicator]->campaign_name ?></td>
              <td>Total Mobile No : <?= $sms->report[$indicator]->total_mobile_no_count ?></td>
              <td class="text-left" style='width: 180px !important;'>
                <div>
                  <div style='float: left'>Sender : </div>
                  <div style='float: right; width:100px; margin-right: 15px;'><a href="#!"
                      class="btn btn-outline-primary btn-disabled"
                      style='width: 130px;'><?= $sms->report[$indicator]->sender_mobile_no ?></a></div>
                </div>
                <div style='clear: both;'>
                  <div style='float: left'>Receiver : </div>
                  <div style='float: right;  width:100px; margin-right: 15px;'><a href="#!"
                      class="btn btn-outline-success btn-disabled"
                      style='width: 130px;'><?= $sms->report[$indicator]->receiver_mobile_no ?></a></div>
                </div>
              </td>
              <td><?= $sms->report[$indicator]->cm_entry_date ?> </td>
              <td><?= $sms->report[$indicator]->response_date . "<br>" . $disp_stat ?></td>
              <td><?= $sms->report[$indicator]->delivery_date . "<br>" . $disp_stat1 ?></td>
              <td><?= $sms->report[$indicator]->read_date . "<br>" . $disp_stat2 ?></td>
              <td><a href="#!"
                  onclick="call_getsingletemplate(`<?= $sms->report[$indicator]->com_msg_content ?>`,'<?= $sms->report[$indicator]->com_cus_msg_media ?>','<?= $sms->report[$indicator]->message_type ?>')"><i
                    class="fa fa-eye" style="font-size:28px;color:red"></i><br>View </a></td>
            </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 8, 9]
      }]
    });

    // Event listener for button click
    $('#table-1').on('buttons-processing', function (e, settings, processing) {
      // Show/hide loader based on the processing status
      if (processing) {
        $('.loading').show();
      } else {
        $('.loading').hide();
      }
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>

  <?
}
// campaign_report Page campaign_report - End

// business_summary_report start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "business_summary_report") {
  site_log_generate("Business Summary Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>

  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th title="Date"
          style="display: flexbox; justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Date (🗓️)</th>
        <th title="User"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          User (👤)</th>
        <th title="Campaign Name"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Name (🔈)</th>
        <th title="Total Pushed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Total Pushed (📤)</th>
        <th title="Waiting"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Waiting (⌛) </th>
        <th title="In Processing"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          In Processing (⏳)</th>
        <th title="Success"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Success (✔️ )</th>
        <th title="Delivered"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Delivered (☑️)</th>
        <th title="Read"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Read (✅)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Failed (❌)</th>
      </tr>
    </thead>
    <tbody>
      <?
      $user_name_srch = $_REQUEST['user_name_srch'];
      $srch_1 = $_REQUEST['srch_1'];

      if ($_REQUEST['dates']) {
        $date = $_REQUEST['dates'];
      }
      // else {
      //   $date = date('m/d/Y') . "-" . date('m/d/Y'); // 01/28/2023 - 02/27/2023 
      // }
    
      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));

      $replace_txt .= '{
            "user_product":"WHATSAPP",';

      if (($user_name_srch != '[object HTMLSelectElement]' && empty($user_name_srch) == false) && ($user_name_srch != 'undefined')) {
        $replace_txt .= '"user_filter" : "' . $user_name_srch . '",';
      }

      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }

      if ($campaign_name_filter != 'undefined' && empty($campaign_name_filter) == false) {
        $campaign_name_filter_trim = rtrim($campaign_name_filter, ",");
        $campaigns_name = str_replace(",", '","', $campaign_name_filter_trim);
        $replace_txt .= '"campaign_filter" : ["' . $campaigns_name . '"],';
      }

      // To Send the request API 
      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';
      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // To Get Api URL
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/summary_report',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Send the data into API and execute  
      site_log_generate("Business Summary Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response);
      site_log_generate("Business Summary Report Page  : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      // To get the one by one data
      $indicatori = 0;
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }
      if ($sms->response_code == 1) {
        // If the response is success to execute this condition
        $data = $sms->report;
        for ($indicator = 0; $indicator < count($data); $indicator++) {
          $subArray = $data[$indicator];

          //Looping the indicator is less than the count of report.if the condition is true to continue the process.if the condition is false to stop the process
          $indicatori++;
          $entry_date = date('d-m-Y', strtotime($subArray->entry_date));
          $user_id = $subArray->user_id;
          $user_name = $subArray->user_name;
          $user_master_id = $subArray->user_master_id;
          $user_type = $subArray->user_type;
          $total_msg = $subArray->total_msg;
          $credits = $subArray->available_messages;
          $total_success = $subArray->total_success;
          $total_delivered = $subArray->total_delivered;
          $total_read = $subArray->total_read;
          $total_process = $subArray->total_process;
          $total_waiting = ($subArray->total_waiting) ? $subArray->total_waiting : 0;
          $total_failed = $subArray->total_failed;
          $campaign_name = $subArray->campaign_name;
          if ($total_msg == 0) {
          } else {
            if ($user_id != '') {
              $increment++; ?>
              <tr style="text-align: center !important">
                <td>
                  <?= $increment ?>
                </td>
                <td>
                  <?= $entry_date ?>
                </td>
                <td>
                  <?= strtoupper($user_name) ?>
                </td>
                <td>
                  <?= $campaign_name ?>
                </td>
                <td>
                  <?= $total_msg ?>
                </td>
                <td>
                  <?= $total_waiting ?>
                </td>

                <td>
                  <?= $total_process ?>
                </td>

                <td>
                  <?= $total_success ?>
                </td>
                <td>
                  <?= $total_delivered ?>
                </td>
                <td>
                  <?= $total_read ?>
                </td>
                <td>
                  <?= $total_failed ?>
                </td>
              </tr>

              <?
            }
          }
        }
      } else if ($sms->response_status == 204) {
        site_log_generate("Business Summary Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("Business Summary Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>

    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 8, 9]
      }]
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}


if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "camapign_name_watsp") {
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  site_log_generate("Get Campaign Name Using business_summary_report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');

  if ($_REQUEST['dates']) {
    $date = $_REQUEST['dates'];
  } else {
    $date = date('m/d/Y') . "-" . date('m/d/Y'); // 01/28/2023 - 02/27/2023 
  }
  $td = explode('-', $date);
  $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
  $thismonth_today = date("Y/m/d", strtotime($td[1]));
  // To Send the request API 
  $replace_txt = '{
   "user_product":"WHATSAPP",';

  if ($date) {
    $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
  }
  if (($user_name_srch != '[object HTMLSelectElement]' && empty($user_name_srch) == false) && ($user_name_srch != 'undefined')) {
    $replace_txt .= '"user_filter" : "' . $user_name_srch . '",';
  }

  $replace_txt = rtrim($replace_txt, ",");
  $replace_txt .= '}';

  // Add bearer token
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  // It will call "campaign_list" API to verify, can we can we allow to view the campaign_list list
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/campaign_list_report',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );
  // Send the data into API and execute   
  site_log_generate("Get Campaign Name Using business_summary_report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // After got response decode the JSON result
  $sms = json_decode($response, false);
  if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  site_log_generate("Get Campaign Name Using business_summary_report Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
  // To get the one by one data
  $indicatori = 0;
  if ($sms->response_code == 1) { // If the response is success to execute this condition
    for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
      // Looping the indicator is less than the number of rows.if the condition is true to continue the process.if the condition is false to stop the process
      $indicatori++; ?>
      <?php
      $campaign_name .= $sms->campaign_list[$indicator]->campaign_name . "$";
      $compose_message_id .= $sms->campaign_list[$indicator]->compose_message_id . "&";
    }
    $replace_campaign_name = rtrim($campaign_name, "$");
    $replace_compose_message_id = rtrim($compose_message_id, "&");
    echo $replace_campaign_name . '$';
  }
  if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }

}
// Get Campaign Name  Drop Down- End  

if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "camapign_name_sms") {
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  site_log_generate("Get Campaign Name Using camapign_name_sms Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');

  if ($_REQUEST['dates']) {
    $date = $_REQUEST['dates'];
  } else {
    $date = date('m/d/Y') . "-" . date('m/d/Y'); // 01/28/2023 - 02/27/2023 
  }
  $td = explode('-', $date);
  $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
  $thismonth_today = date("Y/m/d", strtotime($td[1]));
  // To Send the request API 
  $replace_txt = '{
  "user_product":"GSM SMS",';

  if ($date) {
    $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
  }
  if (($user_name_srch != '[object HTMLSelectElement]' && empty($user_name_srch) == false) && ($user_name_srch != 'undefined')) {
    $replace_txt .= '"user_filter" : "' . $user_name_srch . '",';
  }
  //  if ($srch_1 != 'undefined' && empty($srch_1) == false) {
  //    $replace_txt .= '"filter_department" : "' . $srch_1 . '",';
  //  }
  $replace_txt = rtrim($replace_txt, ",");
  $replace_txt .= '}';
  //  echo $replace_txt;
  //  exit;
  // Add bearer token
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  // It will call "campaign_list" API to verify, can we can we allow to view the campaign_list list
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/campaign_list_report',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );
  // Send the data into API and execute   
  site_log_generate("Get Campaign Name Using camapign_name_sms Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // After got response decode the JSON result
  $sms = json_decode($response, false);
  if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  site_log_generate("Get Campaign Name Using camapign_name_sms Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
  // To get the one by one data
  $indicatori = 0;
  if ($sms->response_code == 1) { // If the response is success to execute this condition
    for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
      // Looping the indicator is less than the number of rows.if the condition is true to continue the process.if the condition is false to stop the process
      $indicatori++; ?>
      <?php
      $campaign_name .= $sms->campaign_list[$indicator]->campaign_name . "$";
      $compose_message_id .= $sms->campaign_list[$indicator]->compose_message_id . "&";
    }
    $replace_campaign_name = rtrim($campaign_name, "$");
    $replace_compose_message_id = rtrim($compose_message_id, "&");
    echo $replace_campaign_name . '$';
  }
  if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
}
// Get Campaign Name  Drop Down- End  

// Get Campaign Name - RCS Drop Down- START 
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "campaign_name_rcs") {
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  site_log_generate("Get Campaign Name Using campaign_name_rcs Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');

  if ($_REQUEST['dates']) {
    $date = $_REQUEST['dates'];
  } else {
    $date = date('m/d/Y') . "-" . date('m/d/Y'); // 01/28/2023 - 02/27/2023 
  }
  $td = explode('-', $date);
  $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
  $thismonth_today = date("Y/m/d", strtotime($td[1]));
  // To Send the request API 
  $replace_txt = '{
  "user_product":"RCS",';

  if ($date) {
    $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
  }
  if (($user_name_srch != '[object HTMLSelectElement]' && empty($user_name_srch) == false) && ($user_name_srch != 'undefined')) {
    $replace_txt .= '"user_filter" : "' . $user_name_srch . '",';
  }
  $replace_txt = rtrim($replace_txt, ",");
  $replace_txt .= '}';
  // Add bearer token
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  // It will call "campaign_list" API to verify, can we can we allow to view the campaign_list list
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/campaign_list_report',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );
  // Send the data into API and execute   
  site_log_generate("Get Campaign Name Using campaign_name_rcs Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
  $response = curl_exec($curl);
  curl_close($curl);
  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <? }
  // After got response decode the JSON result
  $sms = json_decode($response, false);
  if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
  site_log_generate("Get Campaign Name Using campaign_name_rcs Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
  // To get the one by one data
  $indicatori = 0;
  if ($sms->response_code == 1) { // If the response is success to execute this condition
    for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
      // Looping the indicator is less than the number of rows.if the condition is true to continue the process.if the condition is false to stop the process
      $indicatori++; ?>
      <?php
      $campaign_name .= $sms->campaign_list[$indicator]->campaign_name . "$";
      $compose_message_id .= $sms->campaign_list[$indicator]->compose_message_id . "&";
    }
    $replace_campaign_name = rtrim($campaign_name, "$");
    $replace_compose_message_id = rtrim($compose_message_id, "&");
    echo $replace_campaign_name . '$';
  }
  if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
  <? }
}
// Get Campaign Name - RCS Drop Down- End 

// compose sms  summary report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_sms_summary") {
  site_log_generate("compose_sms_summary Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>
  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th title="Date"
          style="display: flexbox; justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Date (🗓️)</th>
        <th title="User"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          User (👤)</th>
        <th title="Campaign Name"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Name (🔈)</th>
        <th title="Total Pushed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Total Pushed (📤)</th>
        <th title="Waiting"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Waiting (⌛) </th>
        <th title="In Processing"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          In Processing (⏳)</th>
        <th title="Success"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Success (✔️ )</th>
        <th title="Delivered"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Delivered (☑️)</th>
        <th title="Read"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Read (✅)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Failed (❌)</th>
      </tr>
    </thead>
    <tbody>
      <?

      if ($_REQUEST['dates']) {
        $date = $_REQUEST['dates'];
      }

      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));

      $replace_txt .= '{
            "user_product":"GSM SMS",';

      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }

      // To Send the request API 
      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';

      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // To Get Api URL
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/summary_report',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Send the data into API and execute  
      site_log_generate("Business Summary Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response);
      site_log_generate("Business Summary Report Page  : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      // To get the one by one data
      $indicatori = 0;

      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }
      if ($sms->response_code == 1) {
        // If the response is success to execute this condition
        $data = $sms->report;
        for ($indicator = 0; $indicator < count($data); $indicator++) {
          $subArray = $data[$indicator];

          //Looping the indicator is less than the count of report.if the condition is true to continue the process.if the condition is false to stop the process
          $indicatori++;
          $entry_date = date('d-m-Y', strtotime($subArray->entry_date));
          $user_id = $subArray->user_id;
          $user_name = $subArray->user_name;
          $user_master_id = $subArray->user_master_id;
          $user_type = $subArray->user_type;
          $total_msg = $subArray->total_msg;
          $credits = $subArray->available_messages;
          $total_success = $subArray->total_success;
          $total_delivered = $subArray->total_delivered;
          $total_read = $subArray->total_read;
          $total_process = $subArray->total_process;
          $total_waiting = ($subArray->total_waiting) ? $subArray->total_waiting : 0;
          $total_failed = $subArray->total_failed;
          $campaign_name = $subArray->campaign_name;
          if ($total_msg == 0) {
          } else {
            if ($user_id != '') {
              $increment++; ?>
              <tr style="text-align: center !important">
                <td>
                  <?= $increment ?>
                </td>
                <td>
                  <?= $entry_date ?>
                </td>
                <td>
                  <?= strtoupper($user_name) ?>
                </td>
                <td>
                  <?= $campaign_name ?>
                </td>
                <td>
                  <?= $total_msg ?>
                </td>
                <td>
                  <?= $total_waiting ?>
                </td>

                <td>
                  <?= $total_process ?>
                </td>

                <td>
                  <?= $total_success ?>
                </td>
                <td>
                  <?= $total_delivered ?>
                </td>
                <td>
                  <?= $total_read ?>
                </td>
                <td>
                  <?= $total_failed ?>
                </td>
              </tr>

              <?
            }
          }
        }
      } else if ($sms->response_status == 204) {
        site_log_generate("Business Summary Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("Business Summary Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>

    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 8, 9]
      }]
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// compose sms  summary report - Start


// campaign_report_sms Page campaign_report_sms - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "campaign_report_sms") {
  site_log_generate("Campaign Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <? if ($_SESSION['yjwatsp_user_master_id'] != 2) { ?>
          <th>User</th>
        <? } ?>
        <th>Campaign Name</th>
        <th>Count</th>
        <th>Mobile No</th>
        <th>Entry Date</th>
        <th>Response Status</th>
        <th>Delivery Status</th>
        <!--- <th>Read Status</th> -->
        <th>Message</th>
      </tr>
    </thead>
    <tbody>
      <?
      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "user_product":"GSM SMS",';
      // To Send the request API 
      if (($_REQUEST['dates'] != 'undefined') && ($_REQUEST['dates'] != '[object HTMLInputElement]') && ($_REQUEST['dates'] != '')) {
        $date = $_REQUEST['dates'];
        $td = explode('-', $date);
        $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
        $thismonth_today = date("Y/m/d", strtotime($td[1]));
        if ($date) {
          $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
        }
      } else {
        $currentDate = date('Y/m/d');
        $thirtyDaysAgo = date('Y/m/d', strtotime('-7 days', strtotime($currentDate)));
        $date = $thirtyDaysAgo . "-" . $currentDate; // 01/28/2023 - 02/27/2023 
        $replace_txt .= '"date_filter" : "' . $thirtyDaysAgo . ' - ' . $currentDate . '",';
      }

      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/detailed_report',
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
      site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      $sms = json_decode($response, false);
      site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;

      if ($sms->response_status == 200 && $sms->num_of_rows > 10000) { ?>
        <div><input type="hidden" class="form-control" name="num_of_rows" id="num_of_rows"
            value="<?= $sms->num_of_rows ?>" /></div>
        <a id="downloadLink" href="<?= $sms->file_location ?>" download class="text-danger" style="display:none;"
          title="Download App File :<?php echo $sms->file_location; ?>"><?= $sms->file_location ?></a>
      <? } else if ($sms->response_status == 200 && $sms->num_of_rows <= 10000) {
        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          $indicatori++;
          $user_name = $sms->report[$indicator]->user_name; ?>
            <div> <input type="hidden" class="form-control" name='user_name_array[]' id='user_name_array'
                value='<?= $user_name ?>' /></div>
          <? $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->cm_entry_date));

          if ($sms->report[$indicator]->response_date != '') {
            $response_date = $sms->report[$indicator]->response_date;
          } else {
            $response_date = '';
          }

          if ($sms->report[$indicator]->delivery_date != '') {
            $delivery_date = $sms->report[$indicator]->delivery_date;
          } else {
            $delivery_date = '';
          }

          if ($sms->report[$indicator]->read_date != '') {
            $read_date = $sms->report[$indicator]->read_date;
          } else {
            $read_date = '';
          }
          $response_status = $sms->report[$indicator]->response_status;
          $response_message = $sms->report[$indicator]->response_message;

          $response_id = $sms->report[$indicator]->response_id;
          $delivery_status = $sms->report[$indicator]->delivery_status;
          $read_status = $sms->report[$indicator]->read_status;
          $response_message = $sms->report[$indicator]->response_message;
          $disp_stat = '';
          switch ($response_status) {
            case 'Y':
              $disp_stat = '<div class="badge badge-success" style="width:130px;">SENT</div><input type="hidden" class="form-control" name="response_status[]" value="SENT" />';
              break;
            case 'F':
              $disp_stat = '<div class="badge badge-danger" style="white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap; white-space: -o-pre-wrap;word-wrap: break-word;width:130px;">' . strtoupper($response_message) . '</div><input type="hidden" class="form-control" name="response_status[]" value="FAILED" />';
              break;
            case 'I':
              $disp_stat = '<div class="badge badge-warning" style="width:130px;">INVALID</div><input type="hidden" class="form-control" name="response_status[]" value="INVAILD" />';
              break;
            default:
              $disp_stat = '<div class="badge badge-info" style="width:130px;">YET TO SENT</div><input type="hidden" class="form-control" name="response_status[]" value="YET TO SENT" />';
              break;
          }

          $disp_stat1 = '';
          switch ($delivery_status) {
            case 'Y':
              $disp_stat1 = '<div class="badge badge-success" style="width:130px;">DELIVERED</div><input type="hidden" class="form-control" name="response_status[]" value="DELIVERED" />';
              break;

            default:
              $disp_stat1 = '<div class="badge badge-danger" style="width:130px;">NOT DELIVERED</div><input type="hidden" class="form-control" name="response_status[]" value="NOT DELIVERED" />';
              break;
          }

          $disp_stat2 = '';
          switch ($read_status) {
            case 'Y':
              $disp_stat2 = '<div class="badge badge-success" style="width:130px;">READ</div><input type="hidden" class="form-control" name="response_status[]" value="READ" />';
              break;

            default:
              $disp_stat2 = '<div class="badge badge-danger" style="width:130px;">NOT READ</div><input type="hidden" class="form-control" name="response_status[]" value="NOT READ" />';
              break;
          }
          ?>
            <tr>
              <td><?= $indicatori ?></td>
            <? if ($_SESSION['yjwatsp_user_master_id'] != 2) { ?>
                <td><?= strtoupper($user_name) ?></td>
            <? } ?>
              <td><?= $sms->report[$indicator]->campaign_name ?></td>
              <td>Total Mobile No : <?= $sms->report[$indicator]->total_mobile_no_count ?></td>
              <td class="text-left" style='width: 180px !important;'>
                <div>
                  <div style='float: left'>Sender : </div>
                  <div style='float: right; width:100px; margin-right: 15px;'><a href="#!"
                      class="btn btn-outline-primary btn-disabled"
                      style='width: 130px;'><?= $sms->report[$indicator]->sender_mobile_no ?></a></div>
                </div>
                <div style='clear: both;'>
                  <div style='float: left'>Receiver : </div>
                  <div style='float: right;  width:100px; margin-right: 15px;'><a href="#!"
                      class="btn btn-outline-success btn-disabled"
                      style='width: 130px;'><?= $sms->report[$indicator]->receiver_mobile_no ?></a></div>
                </div>
              </td>
              <td><?= $sms->report[$indicator]->cm_entry_date ?> </td>
              <td><?= $sms->report[$indicator]->response_date . "<br>" . $disp_stat ?></td>
              <td><?= $sms->report[$indicator]->delivery_date . "<br>" . $disp_stat1 ?></td>
              <!---<td><?= $sms->report[$indicator]->read_date . "<br>" . $disp_stat2 ?></td>--->
      <td><a href="#!"
          onclick="call_getsingletemplate(`<?= $sms->report[$indicator]->com_msg_content ?>`,'<?= $sms->report[$indicator]->com_cus_msg_media ?>','<?= $sms->report[$indicator]->message_type ?>')"><i
            class="fa fa-eye" style="font-size:28px;color:red"></i><br>View </a></td>
    </tr>
    <?
        }
      }
      ?>
  </tbody>
</table>

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
  var table = $('#table-1').DataTable({
    dom: 'PlBfrtip',
    searchPanes: {
      cascadePanes: true,
      initCollapsed: true
    },
    colReorder: true,
    buttons: [{
      extend: 'copyHtml5',
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6]
      },
      action: function (e, dt, button, config) {
        showLoader();
        // Use the built-in pdfHtml5 button action
        $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
        setTimeout(function () {
          hideLoader();
        }, 1000);
      }
    }, {
      extend: 'csvHtml5',
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6]
      },
      action: function (e, dt, button, config) {
        showLoader();
        // Use the built-in pdfHtml5 button action
        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
        setTimeout(function () {
          hideLoader();
        }, 1000);
      }
    }, {
      extend: 'pdfHtml5',
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6]
      },
      action: function (e, dt, button, config) {
        showLoader();
        // Use the built-in pdfHtml5 button action
        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
        setTimeout(function () {
          hideLoader();
        }, 1000);
      }
    }, 'colvis'],
    columnDefs: [{
      searchPanes: {
        show: true
      },
      targets: [1, 2, 3, 4, 6]
    }, {
      searchPanes: {
        show: false
      },
      targets: [0, 5, 7]
    }]
  });

  // Event listener for button click
  $('#table-1').on('buttons-processing', function (e, settings, processing) {
    // Show/hide loader based on the processing status
    if (processing) {
      $('.loading').show();
    } else {
      $('.loading').hide();
    }
  });
  function showLoader() {
    table.buttons().processing(true); // Show the DataTables Buttons processing indicator
    $(".loading").css('display', 'block');
    $('.loading').show();
  }

  function hideLoader() {
    $(".loading").css('display', 'none');
    $('.loading').hide();
    table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
  }
</script>

<?
}
// campaign_report_sms Page campaign_report_sms - End

// compose RCS summary report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_rcs_summary") {
  site_log_generate("compose_rcs_summary Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>

<table class="table table-striped" id="table-1">
  <thead>
    <tr>
      <th>#</th>
      <th title="Date"
        style="display: flexbox; justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Date (🗓️)</th>
      <th title="User"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        User (👤)</th>
      <th title="Campaign Name"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Campaign Name (🔈)</th>
      <th title="Total Pushed"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Total Pushed (📤)</th>
      <th title="Waiting"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Waiting (⌛) </th>
      <th title="In Processing"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        In Processing (⏳)</th>
      <th title="Success"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Success (✔️ )</th>
      <th title="Delivered"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Delivered (☑️)</th>
      <th title="Read"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Read (✅)</th>
      <th title="Failed"
        style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
        Failed (❌)</th>
    </tr>
  </thead>
  <tbody>
    <?
    if ($_REQUEST['dates']) {
      $date = $_REQUEST['dates'];
    }

    $td = explode('-', $date);
    $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
    $thismonth_today = date("Y/m/d", strtotime($td[1]));

    $replace_txt .= '{
            "user_product":"RCS",';

    if ($date) {
      $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
    }


    // To Send the request API 
    $replace_txt = rtrim($replace_txt, ",");
    $replace_txt .= '}';

    // Add bearer token
    $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
    // To Get Api URL
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $api_url . '/report/summary_report',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $replace_txt,
        CURLOPT_HTTPHEADER => array(
          $bearer_token,
          'Content-Type: application/json'
        ),
      )
    );

    // Send the data into API and execute  
    site_log_generate("compose_rcs_summary Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
    $response = curl_exec($curl);
    curl_close($curl);
    // After got response decode the JSON result
    $sms = json_decode($response);
    site_log_generate("compose_rcs_summary Report Page  : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
    // To get the one by one data
    $indicatori = 0;

    if ($response == '') { ?>
    <script>window.location = "logout"</script>
    <? } else if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
    <? }
    if ($sms->response_code == 1) {
      // If the response is success to execute this condition
      $data = $sms->report;
      for ($indicator = 0; $indicator < count($data); $indicator++) {
        $subArray = $data[$indicator];

        //Looping the indicator is less than the count of report.if the condition is true to continue the process.if the condition is false to stop the process
        $indicatori++;
        $entry_date = date('d-m-Y', strtotime($subArray->entry_date));
        $user_id = $subArray->user_id;
        $user_name = $subArray->user_name;
        $user_master_id = $subArray->user_master_id;
        $user_type = $subArray->user_type;
        $total_msg = $subArray->total_msg;
        $credits = $subArray->available_messages;
        $total_success = $subArray->total_success;
        $total_delivered = $subArray->total_delivered;
        $total_read = $subArray->total_read;
        $total_process = $subArray->total_process;
        $total_waiting = ($subArray->total_waiting) ? $subArray->total_waiting : 0;
        $total_failed = $subArray->total_failed;
        $campaign_name = $subArray->campaign_name;
        if ($total_msg == 0) {
        } else {
          if ($user_id != '') {
            $increment++; ?>
    <tr style="text-align: center !important">
      <td>
        <?= $increment ?>
      </td>
      <td>
        <?= $entry_date ?>
      </td>
      <td>
        <?= strtoupper($user_name) ?>
      </td>
      <td>
        <?= $campaign_name ?>
      </td>
      <td>
        <?= $total_msg ?>
      </td>
      <td>
        <?= $total_waiting ?>
      </td>

      <td>
        <?= $total_process ?>
      </td>

      <td>
        <?= $total_success ?>
      </td>
      <td>
        <?= $total_delivered ?>
      </td>
      <td>
        <?= $total_read ?>
      </td>
      <td>
        <?= $total_failed ?>
      </td>
    </tr>

    <?
          }
        }
      }
    } else if ($sms->response_status == 204) {
      site_log_generate("compose_rcs_summary Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
      $json = array("status" => 2, "msg" => $sms->response_msg);
    } else {
      site_log_generate("compose_rcs_summary Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
      $json = array("status" => 0, "msg" => $sms->response_msg);
    }
    ?>

  </tbody>
</table>
<!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 8, 9]
      }]
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// compose RCS summary report - end


// compose sms  detailed report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_sms_detailed") {
  site_log_generate("Campaign Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User</th>
        <th>Campaign Name</th>
        <th>Count</th>
        <th>Mobile No</th>
        <th>Status</th>
        <!-- <th>Delivery Status</th>
          <th>Read Status</th>--->
      <th>Message</th>
    </tr>
  </thead>
  <tbody>

    <?
    $user_name_srch = $_REQUEST['user_name_srch'];
    $srch_status = $_REQUEST['srch_status'];
    $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "user_product":"GSM SMS",';
    // To Send the request API 
  
    if ($user_name_srch != 'undefined' && empty($user_name_srch) == false) {
      $replace_txt .= '"user_filter" : "' . $user_name_srch . '",';
    }
    if ($srch_status) {
      switch ($srch_status) {
        case 'SENT':
          $replace_txt .= '"status_filter" : "' . $srch_status . '",';
          break;
        case 'YET TO SENT':
          $replace_txt .= '"status_filter" : "' . $srch_status . '",';
          break;
        case 'FAILED':
          $replace_txt .= '"status_filter" : "' . $srch_status . '",';
          break;
        case 'INVALID':
          $replace_txt .= '"status_filter" : "' . $srch_status . '",';
          break;

        case 'DELIVERED':
          $replace_txt .= '"delivery_filter" : "' . $srch_status . '",';
          break;
        case 'NOT DELIVERED':
          $replace_txt .= '"delivery_filter" : "' . $srch_status . '",';
          break;

        case 'READ':
          $replace_txt .= '"read_filter" : "' . $srch_status . '",';
          break;
        case 'NOT READ':
          $replace_txt .= '"read_filter" : "' . $srch_status . '",';
          break;
      }
    }
    if (($_REQUEST['dates'] != 'undefined') && ($_REQUEST['dates'] != '[object HTMLInputElement]') && ($_REQUEST['dates'] != '')) {
      $date = $_REQUEST['dates'];
      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));
      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }
    } else {
      $currentDate = date('Y/m/d');
      $thirtyDaysAgo = date('Y/m/d', strtotime('-7 days', strtotime($currentDate)));
      $date = $thirtyDaysAgo . "-" . $currentDate; // 01/28/2023 - 02/27/2023 
      $replace_txt .= '"date_filter" : "' . $thirtyDaysAgo . ' - ' . $currentDate . '",';
    }

    $replace_txt = rtrim($replace_txt, ",");
    $replace_txt .= '}';

    $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
    // $replace_txt = '';
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $api_url . '/report/detailed_report',
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
    site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
    $response = curl_exec($curl);
    curl_close($curl);
    if ($response == '') { ?>
    <script>window.location = "logout"</script>
    <? }
    $sms = json_decode($response, false);
    site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

    if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
    <? }

    // print_r($sms); exit;
    $indicatori = 0;

    if ($sms->response_status == 200) {

      for ($indicator = 0; $indicator < count($sms->report); $indicator++) {
        $indicatori++;
        $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->contact_mobile_entry_date));

        if ($sms->report[$indicator]->response_date != '') {
          $response_date = $sms->report[$indicator]->response_date;
        } else {
          $response_date = '';
        }

        if ($sms->report[$indicator]->delivery_date != '') {
          $delivery_date = $sms->report[$indicator]->delivery_date;
        } else {
          $delivery_date = '';
        }

        if ($sms->report[$indicator]->read_date != '') {
          $read_date = $sms->report[$indicator]->read_date;
        } else {
          $read_date = '';
        }
        $response_status = $sms->report[$indicator]->response_status;
        $response_message = $sms->report[$indicator]->response_message;

        $response_id = $sms->report[$indicator]->response_id;
        $delivery_status = $sms->report[$indicator]->delivery_status;
        $read_status = $sms->report[$indicator]->read_status;

        $disp_stat = '';
        switch ($response_status) {
          case 'Y':
            $disp_stat = '<div class="badge badge-success">SENT</div>';
            break;
          case 'F':
            $disp_stat = '<div class="badge badge-danger">FAILED</div>';
            break;
          case 'I':
            $disp_stat = '<div class="badge badge-warning">INVALID</div>';
            break;

          default:
            $disp_stat = '<div class="badge badge-info">YET TO SENT</div>';
            break;
        }

        $disp_stat1 = '';
        switch ($delivery_status) {
          case 'Y':
            $disp_stat1 = '<div class="badge badge-success">DELIVERED</div>';
            break;

          default:
            $disp_stat1 = '<div class="badge badge-danger">NOT DELIVERED</div>';
            break;
        }

        $disp_stat2 = '';
        switch ($read_status) {
          case 'Y':
            $disp_stat2 = '<div class="badge badge-success">READ</div>';
            break;

          default:
            $disp_stat2 = '<div class="badge badge-danger">NOT READ</div>';
            break;
        }
        ?>
    <tr>
      <td>
        <?= $indicatori ?>
      </td>
      <td>
        <?= strtoupper($sms->report[$indicator]->user_name) ?>
      </td>
      <td>
        <?= $sms->report[$indicator]->campaign_name ?>
      </td>
      <td>Total Mobile No :
        <?= $sms->report[$indicator]->total_mobile_no_count ?>
      <td class="text-left" style='width: 180px !important;'>
        <div>
          <div style='float: left'>Sender : </div>
          <div style='float: right; width:100px; margin-right: 15px;'><a href="#!"
              class="btn btn-outline-primary btn-disabled" style='width: 140px;'>
              <?= $sms->report[$indicator]->sender_mobile_no ?>
            </a></div>
        </div>
        <div style='clear: both;'>
          <div style='float: left'>Receiver : </div>
          <div style='float: right;  width:100px; margin-right: 15px;'><a href="#!"
              class="btn btn-outline-success btn-disabled" style='width: 140px;'>
              <?= $sms->report[$indicator]->receiver_mobile_no ?>
            </a></div>
        </div>
      </td>
      <td>
        <?= $sms->report[$indicator]->response_date . "<br>" . $disp_stat ?>
      </td>
      <td> <a href="#!"
          onclick="call_getsingletemplate(`<?= $sms->report[$indicator]->com_msg_content ?>`,'<?= $sms->report[$indicator]->media_url ?>','<?= $sms->report[$indicator]->message_type ?>')">View</a>
      </td>
    </tr>
    <?
      }
    }
    ?>
  </tbody>
</table>

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
<?
}
// compose sms  detailed report - End 

// compose RCS detailed report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_rcs_detailed") {
  site_log_generate("Campaign Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
<table class="table table-striped text-center" id="table-1">
  <thead>
    <tr class="text-center">
      <th>#</th>
      <? if ($_SESSION['yjwatsp_user_master_id'] != 2) { ?>
      <th>User</th>
      <? } ?>
      <th>Campaign Name</th>
      <th>Count</th>
      <th>Mobile No</th>
      <th>Entry Date</th>
      <th>Response Status</th>
      <th>Delivery Status</th>
      <th>Message</th>
    </tr>
  </thead>
  <tbody>

    <?
    $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "user_product":"RCS",';
    // To Send the request API 
    if (($_REQUEST['dates'] != 'undefined') && ($_REQUEST['dates'] != '[object HTMLInputElement]') && ($_REQUEST['dates'] != '')) {
      $date = $_REQUEST['dates'];
      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));
      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }
    } else {
      $currentDate = date('Y/m/d');
      $thirtyDaysAgo = date('Y/m/d', strtotime('-7 days', strtotime($currentDate)));
      $date = $thirtyDaysAgo . "-" . $currentDate; // 01/28/2023 - 02/27/2023 
      $replace_txt .= '"date_filter" : "' . $thirtyDaysAgo . ' - ' . $currentDate . '",';
    }

    $replace_txt = rtrim($replace_txt, ",");
    $replace_txt .= '}';
    $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $api_url . '/report/detailed_report',
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
    site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
    $response = curl_exec($curl);
    curl_close($curl);
    if ($response == '') { ?>
    <script>window.location = "logout"</script>
    <? }
    $sms = json_decode($response, false);
    site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');

    if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
    <? }
    $indicatori = 0;

    if ($sms->response_status == 200 && $sms->num_of_rows > 10000) { ?>
    <div><input type="hidden" class="form-control" name="num_of_rows" id="num_of_rows"
        value="<?= $sms->num_of_rows ?>" /></div>
    <a id="downloadLink" href="<?= $sms->file_location ?>" download class="text-danger" style="display:none;"
      title="Download App File :<?php echo $sms->file_location; ?>">
      <?= $sms->file_location ?>
    </a>
    <? } else if ($sms->response_status == 200 && $sms->num_of_rows <= 10000) {
      for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
        $indicatori++;
        $user_name = $sms->report[$indicator]->user_name; ?>
    <div> <input type="hidden" class="form-control" name='user_name_array[]' id='user_name_array'
        value='<?= $user_name ?>' /></div>
    <? $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->cm_entry_date));

        if ($sms->report[$indicator]->response_date != '') {
          $response_date = $sms->report[$indicator]->response_date;
        } else {
          $response_date = '';
        }

        if ($sms->report[$indicator]->delivery_date != '') {
          $delivery_date = $sms->report[$indicator]->delivery_date;
        } else {
          $delivery_date = '';
        }

        if ($sms->report[$indicator]->read_date != '') {
          $read_date = $sms->report[$indicator]->read_date;
        } else {
          $read_date = '';
        }
        $response_status = $sms->report[$indicator]->response_status;
        $response_message = $sms->report[$indicator]->response_message;

        $response_id = $sms->report[$indicator]->response_id;
        $delivery_status = $sms->report[$indicator]->delivery_status;
        $read_status = $sms->report[$indicator]->read_status;
        $response_message = $sms->report[$indicator]->response_message;
        $disp_stat = '';
        switch ($response_status) {
          case 'Y':
            $disp_stat = '<div class="badge badge-success" style="width:130px;">SENT</div><input type="hidden" class="form-control" name="response_status[]" value="SENT" />';
            break;
          case 'F':
            $disp_stat = '<div class="badge badge-danger" style="white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap; white-space: -o-pre-wrap;word-wrap: break-word;width:130px;">' . strtoupper($response_message) . '</div><input type="hidden" class="form-control" name="response_status[]" value="FAILED" />';
            break;
          case 'I':
            $disp_stat = '<div class="badge badge-warning" style="width:130px;">INVALID</div><input type="hidden" class="form-control" name="response_status[]" value="INVAILD" />';
            break;
          default:
            $disp_stat = '<div class="badge badge-info" style="width:130px;">YET TO SENT</div><input type="hidden" class="form-control" name="response_status[]" value="YET TO SENT" />';
            break;
        }

        $disp_stat1 = '';
        switch ($delivery_status) {
          case 'Y':
            $disp_stat1 = '<div class="badge badge-success" style="width:130px;">DELIVERED</div><input type="hidden" class="form-control" name="response_status[]" value="DELIVERED" />';
            break;

          default:
            $disp_stat1 = '<div class="badge badge-danger" style="width:130px;">NOT DELIVERED</div><input type="hidden" class="form-control" name="response_status[]" value="NOT DELIVERED" />';
            break;
        }

        $disp_stat2 = '';
        switch ($read_status) {
          case 'Y':
            $disp_stat2 = '<div class="badge badge-success" style="width:130px;">READ</div><input type="hidden" class="form-control" name="response_status[]" value="READ" />';
            break;

          default:
            $disp_stat2 = '<div class="badge badge-danger" style="width:130px;">NOT READ</div><input type="hidden" class="form-control" name="response_status[]" value="NOT READ" />';
            break;
        }
        ?>
    <tr>
      <td>
        <?= $indicatori ?>
      </td>
      <? if ($_SESSION['yjwatsp_user_master_id'] != 2) { ?>
      <td>
        <?= strtoupper($user_name) ?>
      </td>
      <? } ?>
      <td>
        <?= $sms->report[$indicator]->campaign_name ?>
      </td>
      <td>Total Mobile No :
        <?= $sms->report[$indicator]->total_mobile_no_count ?>
      </td>
      <td class="text-left" style='width: 180px !important;'>
        <div>
          <div style='float: left'>Sender : </div>
          <div style='float: right; width:100px; margin-right: 15px;'><a href="#!"
              class="btn btn-outline-primary btn-disabled" style='width: 130px;'>
              <?= $sms->report[$indicator]->sender_mobile_no ?>
            </a></div>
        </div>
        <div style='clear: both;'>
          <div style='float: left'>Receiver : </div>
          <div style='float: right;  width:100px; margin-right: 15px;'><a href="#!"
              class="btn btn-outline-success btn-disabled" style='width: 130px;'>
              <?= $sms->report[$indicator]->receiver_mobile_no ?>
            </a></div>
        </div>
      </td>
      <td>
        <?= $sms->report[$indicator]->cm_entry_date ?>
      </td>
      <td>
        <?= $sms->report[$indicator]->response_date . "<br>" . $disp_stat ?>
      </td>
      <td>
        <?= $sms->report[$indicator]->delivery_date . "<br>" . $disp_stat1 ?>
      </td>
      <td><a href="#!"
          onclick="call_getsingletemplate(`<?= $sms->report[$indicator]->com_msg_content ?>`,'<?= $sms->report[$indicator]->com_cus_msg_media ?>','<?= $sms->report[$indicator]->message_type ?>')"><i
            class="fa fa-eye" style="font-size:28px;color:red"></i><br>View </a></td>
    </tr>
    <?
      }
    }
    ?>
  </tbody>
</table>

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
  var table = $('#table-1').DataTable({
    dom: 'PlBfrtip',
    searchPanes: {
      cascadePanes: true,
      initCollapsed: true
    },
    colReorder: true,
    buttons: [{
      extend: 'copyHtml5',
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6, 7]
      },
      action: function (e, dt, button, config) {
        showLoader();
        // Use the built-in pdfHtml5 button action
        $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
        setTimeout(function () {
          hideLoader();
        }, 1000);
      }
    }, {
      extend: 'csvHtml5',
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6, 7]
      },
      action: function (e, dt, button, config) {
        showLoader();
        // Use the built-in pdfHtml5 button action
        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
        setTimeout(function () {
          hideLoader();
        }, 1000);
      }
    }, {
      extend: 'pdfHtml5',
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6, 7]
      },
      action: function (e, dt, button, config) {
        showLoader();
        // Use the built-in pdfHtml5 button action
        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
        setTimeout(function () {
          hideLoader();
        }, 1000);
      }
    }, 'colvis'],
    columnDefs: [{
      searchPanes: {
        show: true
      },
      targets: [1, 2, 3, 4, 6]
    }, {
      searchPanes: {
        show: false
      },
      targets: [0, 5, 7]
    }]
  });

  // Event listener for button click
  $('#table-1').on('buttons-processing', function (e, settings, processing) {
    // Show/hide loader based on the processing status
    if (processing) {
      $('.loading').show();
    } else {
      $('.loading').hide();
    }
  });
  function showLoader() {
    table.buttons().processing(true); // Show the DataTables Buttons processing indicator
    $(".loading").css('display', 'block');
    $('.loading').show();
  }

  function hideLoader() {
    $(".loading").css('display', 'none');
    $('.loading').hide();
    table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
  }
</script>

<?
}
// compose RCS detailed report - End

// manage_whatsappno_list Page manage_whatsappno_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "campaign_list_stop") {
  site_log_generate("Manage Whatsappno List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
<table class="table table-striped text-center" id="table-1">
  <thead>
    <tr class="text-center">
      <th>#</th>
      <th>User</th>
      <th>Campaign Name</th>
      <th>Total Mobile No Count</th>
      <th>Compose Status</th>
      <th>Entry Date</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?
    $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
    $replace_txt = '{
          "user_product": "WHATSAPP"
      }';
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $api_url . '/list/campaign_list_stop',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => $replace_txt,
        CURLOPT_HTTPHEADER => array(
          $bearer_token,
          'Content-Type: application/json'
        ),
      )
    );

    $response = curl_exec($curl);
    //echo $response;
    curl_close($curl);
    $sms = json_decode($response, false);
    if ($response == '') { ?>
    <script>window.location = "logout"</script>
    <? } else if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
    <? }
    $indicatori = 0;
    if ($sms->response_status == 200) {
      for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
        $indicatori++;
        $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->cm_entry_date));
        ?>
    <tr>
      <td>
        <?= $indicatori ?>
      </td>
      <td>
        <?= strtoupper($sms->campaign_list[$indicator]->user_name) ?>
      </td>
      <td>
        <?= $sms->campaign_list[$indicator]->campaign_name ?>
      </td>
      <td>
        <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
      </td>
      <td>
        <?
        if ($sms->campaign_list[$indicator]->cm_status == 'S') { ?><a href="#!"
          class="btn btn-outline-danger btn-disabled" style="width:100px; text-align:center">Stop Campaign</a>
        <? } elseif ($sms->campaign_list[$indicator]->cm_status == 'P') { ?><a href="#!"
          class="btn btn-outline-success btn-disabled" style="width:100px; text-align:center">Processing Campaign</a>
        <? } ?>
      </td>
      <td>
        <?= $entry_date ?>
      </td>

      <td id='id_approved_lineno_<?= $indicatori ?>'>
        <? if ($sms->campaign_list[$indicator]->cm_status == 'P') { ?>
        <button type="button" title="Stop"
          onclick="sender_id_popup('<?= $sms->campaign_list[$indicator]->campaign_name ?>', '<?= $indicatori ?>')"
          class="btn btn-icon btn-danger" style="width:75px;padding: 0.3rem 0.41rem !important;">Stop</button>
        <? } else { ?>
        <a href="#!" class="btn btn-outline-light btn-disabled"
          style="width:75px;padding: 0.3rem 0.41rem !important;cursor: not-allowed;">Stop</a>
        <? } ?>
        <? if ($sms->campaign_list[$indicator]->cm_status == 'S') { ?>
        <button type="button" title="Restart"
          onclick="start_sender_id_popup('<?= $sms->campaign_list[$indicator]->campaign_name ?>', '<?= $indicatori ?>', '<?= $sms->campaign_list[$indicator]->compose_message_id ?>')"
          class="btn btn-success" style="width:75px;padding: 0.3rem 0.41rem !important;">Restart</button>
        <? } else { ?>
        <a href="#!" class="btn btn-outline-light btn-disabled" style="width:75px;cursor: not-allowed;">Restart</a>
        <? } ?>
      </td>
    </tr>
    <?
      }
    }
    ?>
  </tbody>
</table>

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
  var table = $('#table-1').DataTable({
    dom: 'Bfrtip',
    colReorder: true,
    buttons: [
      {
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5],
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }

      },
      {
        extend: 'csvHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in csvHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      },
      {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      },
      {
        extend: 'searchPanes',
        config: {
          cascadePanes: true
        }
      },
      'colvis'
    ],
    columnDefs: [
      {
        searchPanes: {
          show: false
        },
        targets: [0]
      }
    ]
  });

  function showLoader() {
    table.buttons().processing(true); // Show the DataTables Buttons processing indicator
    $(".loading").css('display', 'block');
    $('.loading').show();
  }

  function hideLoader() {
    $(".loading").css('display', 'none');
    $('.loading').hide();
    table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
  }
</script>
<?
}
// manage_whatsappno_list Page manage_whatsappno_list - End


// stop_senderid_list Page stop_senderid_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "stop_senderid_list") {
  site_log_generate("Manage Whatsappno List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  $campaign_name = htmlspecialchars(strip_tags(isset($_GET["campaign_name"]) ? $conn->real_escape_string($_GET["campaign_name"]) : ""));

  $replace_txt = '{
          "campaign_name" :  "' . $campaign_name . '"
      }';
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/senderID_process_list',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  $response = curl_exec($curl);
  curl_close($curl);
  $sms = json_decode($response, false);
  if ($response == '') { ?>
<script>window.location = "logout"</script>
<? } else if ($sms->response_status == 403) { ?>
<script>window.location = "logout"</script>
<? } else if ($sms->response_status == 200) {
    $indicatori = 0; ?>
<table style="width: 100%;">
  <? $counter = 0;
      for ($indicator = 0; $indicator < count($sms->sender_id); $indicator++) {
        if ($counter % 2 == 0) { ?>
  <tr>
    <? } ?>
    <td>
      <input type="checkbox" class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="txt_whatsapp_mobno"
        tabindex="1" autofocus value="<?= $sms->sender_id[$indicator]->mobile_no ?>">
      <label class="form-label">
        <?= $sms->sender_id[$indicator]->mobile_no ?>
      </label>
    </td>
    <?
        if ($counter % 2 == 1) { ?>
  </tr>
  <? }
        $counter++;
      } ?>
</table>
<? } else if ($sms->response_status == 204 || $sms->response_status == 201) {
    echo $sms->response_status;
  }
}
// stop_senderid_list Page stop_senderid_list - End

// start_senderid_list Page start_senderid_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "start_senderid_list") {
  site_log_generate("start_senderid_list List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  $campaign_name = htmlspecialchars(strip_tags(isset($_GET["campaign_name"]) ? $conn->real_escape_string($_GET["campaign_name"]) : ""));

  $replace_txt = '{
          "campaign_name" :  "' . $campaign_name . '"
      }';
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/senderID_stop_list',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  $response = curl_exec($curl);

  curl_close($curl);
  $sms = json_decode($response, false);
  if ($response == '') { ?>
<script>window.location = "logout"</script>
<? } else if ($sms->response_status == 403) { ?>
<script>window.location = "logout"</script>
<? }
  $indicatori = 0;
  if ($sms->response_status == 200) { ?>
<table style="width: 100%;">
  <? $counter = 0;
      for ($indicator = 0; $indicator < count($sms->sender_id); $indicator++) {
        if ($counter % 2 == 0) { ?>
  <tr>
    <? } ?>
    <td>
      <input type="checkbox" class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="txt_whatsapp_mobno"
        tabindex="1" autofocus value="<?= $sms->sender_id[$indicator]->mobile_no ?>">
      <label class="form-label">
        <?= $sms->sender_id[$indicator]->mobile_no ?>
      </label>
    </td>
    <?
        if ($counter % 2 == 1) { ?>
  </tr>
  <? }
        $counter++;
      } ?>
</table>

<? } else if ($sms->response_status == 204) {
    echo $sms->response_status;
  }
}
// start_senderid_list Page start_senderid_list - End

// app_details_list Page app_details_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "app_details_list") {
  site_log_generate("app_details_list List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
<table class="table table-striped text-center" id="table-1">
  <thead>
    <tr class="text-center">
      <th>#</th>
      <th>App File Name</th>
      <th>App Version</th>
      <th>App Status</th>
      <th>Entry Date</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?
    $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
    $replace_txt = '';
    $curl = curl_init();
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => $api_url . '/app_update/app_list',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => $replace_txt,
        CURLOPT_HTTPHEADER => array(
          $bearer_token,
          'Content-Type: application/json'
        ),
      )
    );

    $response = curl_exec($curl);
    curl_close($curl);
    $sms = json_decode($response, false);
    if ($response == '') { ?>
    <script>window.location = "logout"</script>
    <? }
    if ($sms->response_status == 403) { ?>
    <script>window.location = "logout"</script>
    <? }
    $indicatori = 0;
    if ($sms->response_status == 200) {
      for ($indicator = 0; $indicator < count($sms->app_list); $indicator++) {
        $indicatori++;
        $entry_date = date('d-m-Y h:i:s A', strtotime($sms->app_list[$indicator]->app_update_entry_date));
        $app_update_id = $sms->app_list[$indicator]->app_update_id;
        $app_version_file = $sms->app_list[$indicator]->app_version_file;
        ?>
    <tr>
      <td>
        <?= $indicatori ?>
      </td>
      <td class="download-link-column"><a href="<?= $sms->app_list[$indicator]->app_version_file ?>" download
          class="text-danger" title="Download App File :<?php echo $sms->app_list[$indicator]->app_version_file; ?>">
          <?= $sms->app_list[$indicator]->app_version_file ?>
        </a></td>
      <td>
        <?= $sms->app_list[$indicator]->app_version ?>
      </td>
      <td>
        <?
        if ($sms->app_list[$indicator]->app_update_status == 'U') { ?><a href="#!"
          class="btn btn-outline-success btn-disabled" style="width:150px; text-align:center">Update App</a>
        <? } elseif ($sms->app_list[$indicator]->app_update_status == 'N') { ?><a href="#!"
          class="btn btn-outline-danger btn-disabled" style="width:150px; text-align:center">Not Updated App</a>
        <? } ?>
      </td>
      <td>
        <?= $entry_date ?>
      </td>
      <td id='id_approved_lineno_<?= $indicatori ?>'>
        <? if ($sms->app_list[$indicator]->app_update_status == 'N') { ?>
        <button type="button" title="Update app"
          onclick="sender_id_popup('<?= $app_update_id ?>', '<?= $indicatori ?>','<?= $app_version_file ?>' )"
          class="btn btn-icon btn-danger" style="padding: 0.3rem 0.41rem !important;">Update App</button>
        <? } else { ?>
        <a href="#!" class="btn btn-outline-light btn-disabled"
          style="padding: 0.3rem 0.41rem !important;cursor: not-allowed;">Update App</a>
        <? } ?>
      </td>
    </tr>
    <?
      }
    }
    ?>
  </tbody>
</table>

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
  var table = $('#table-1').DataTable({
    dom: 'Bfrtip',
    colReorder: true,
    buttons: [
      {
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4],
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }

      },
      {
        extend: 'csvHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4], // Exclude the third column (index 3)
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in csvHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      },
      {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4], // Exclude the third column (index 3)
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      },
      {
        extend: 'searchPanes',
        config: {
          cascadePanes: true
        }
      },
      'colvis'
    ],
    columnDefs: [
      {
        searchPanes: {
          show: false
        },
        targets: [0]
      }
    ]
  });

  function showLoader() {
    table.buttons().processing(true); // Show the DataTables Buttons processing indicator
    $(".loading").css('display', 'block');
    $('.loading').show();
  }

  function hideLoader() {
    $(".loading").css('display', 'none');
    $('.loading').hide();
    table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
  }
</script>
<?
}
// app_details_list Page app_details_list - End

// app_senderid_list Page app_senderid_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $tmpl_call_function == "app_senderid_list") {
  site_log_generate("app_senderid_list List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  $app_update_id = htmlspecialchars(strip_tags(isset($_GET["app_update_id"]) ? $conn->real_escape_string($_GET["app_update_id"]) : ""));

  $replace_txt = '{
          "app_update_id" :  "' . $app_update_id . '"
      }';
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';

  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/senderID_update_list',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  $response = curl_exec($curl);
  curl_close($curl);
  $data = json_decode($response, true);

  $updatedArray = $data['updated'];
  $notUpdatedArray = $data['notUpdated'];
  $processingArray = $data['process'];
  if ($response == '') { ?>
<script>window.location = "logout"</script>
<?php }
  if ($data['response_status'] == 403) { ?>
<script>window.location = "logout"</script>
<?php }
  if ($data['response_status'] == 200) {
    $indicatori = 0;
    ?>
<table style="width: 100%;">
  <?php
      for ($indicator = 0; $indicator < count($notUpdatedArray); $indicator++) {
        if ($indicator % 1 == 0) { ?>
  <tr>
    <? } ?>
    <td>
      <input type="checkbox" <?php if ($notUpdatedArray[$indicator]) { ?>
      <?php } ?>
      class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="txt_whatsapp_mobno" tabindex="1" autofocus
      value="<?= $notUpdatedArray[$indicator] ?>">
      <label class="form-label"><?= $notUpdatedArray[$indicator] . " <b><span style='color: red;'>[ Not Updated ] </span></b>" ?></label>
    </td>
    <?
        if ($indicator % 2 == 1) { ?>
  </tr>
  <? }
      }

      for ($indicator = 0; $indicator < count($processingArray); $indicator++) {
        if ($indicator % 1 == 0) { ?>
  <tr>
    <?php } ?>
    <td>
      <input type="checkbox" <?php if ($processingArray[$indicator]) { ?>disabled
      <?php } ?>
      class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>"
      name="txt_whatsapp_mobno" tabindex="1" autofocus value="<?= $processingArray[$indicator] ?>"><label class="form-label"><?= $processingArray[$indicator] . " <b><span style='color: green;'>[ Processing ] </span></b>" ?>
      </label>
    </td>
    <?
        if ($indicator % 2 == 1) { ?>
  </tr>
  <? }
      }
      for ($indicator = 0; $indicator < count($updatedArray); $indicator++) {
        if ($indicator % 1 == 0) { ?>
  <tr>
    <?php } ?>
    <td>
      <input type="checkbox" <?php if ($updatedArray[$indicator]) { ?>disabled
      <?php } ?>class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="txt_whatsapp_mobno" tabindex="1" autofocus
      value="<?= $updatedArray[$indicator] ?>"><label class="form-label"><?= $updatedArray[$indicator] . " <b><span style='color: #05b6ff'>[ Updated ] </span></b>" ?></label>
    </td>
    <?
        if ($indicator % 2 == 1) { ?>
  </tr>
  <? }
      }

      ?>
</table>
<?
  } else if ($sms->response_status == 204) {
    echo $sms->response_msg;
  }
}
// app_senderid_list Page app_senderid_list - End

// manage_users_list Page manage_users_list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "manage_users_list") {
  site_log_generate("Manage Users List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" name="picUploadForm">
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>Parent</th>
        <th>User</th>
        <th>Usertype</th>
        <th>Logo</th>
        <th>Contact Details</th>
        <th>Status</th>
        <? if ($_SESSION['yjwatsp_user_master_id'] == 1) { ?>
        <th>Action</th>
        <? } ?>
      </tr>
    </thead>
    <tbody>
      <?
      // To Send the request API 
      $replace_txt = '{
          "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '"
        }';
      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // It will call "manage_users" API to verify, can we can we allow to view the manage_users list
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
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );
      // Send the data into API and execute   
      $response = curl_exec($curl);
      site_log_generate("Manage Users List Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . date("Y-m-d H:i:s"), '../');
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("Manage Users List Page : " . $uname . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      // To get the one by one data
      $indicatori = 0;
      if ($sms->num_of_rows > 0) {  // If the response is success to execute this condition
        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          // Looping the indicator is less than the num_of_rows.if the condition is true to continue the process.if the condition is false to stop the process
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->user_entry_date));
          ?>
      <tr>
        <td>
          <?= $indicatori ?>
        </td>
        <td>
          <?= $sms->report[$indicator]->parent_name ?>
        </td>
        <td>
          <?= $sms->report[$indicator]->user_name ?>
        </td>
        <td>
          <?= $sms->report[$indicator]->user_type ?>
        </td>
        <td>
          <? if ($sms->report[$indicator]->user_type == 'Reseller' && !$sms->report[$indicator]->logo_media) { ?>
          <div> <input type="file" accept="image/*" class="form-control"
              onchange="logoimage(this,'<?= $sms->report[$indicator]->user_id ?>')" name="file_image_header"
              id="file_image_header" tabindex="1" title="Upload any Media file [JPG/JPEG/PNG] below 5 MB Size.">
            <? } else if ($sms->report[$indicator]->user_type == 'Reseller') { ?>
            <input type="file" accept="image/*" class="form-control"
              onchange="logoimage(this,'<?= $sms->report[$indicator]->user_id ?>')" name="file_image_header"
              id="file_image_header" tabindex="1" title="Upload any Media file [JPG/JPEG/PNG] below 5 MB Size."
              disabled>
            <? }
          ?>
          </div>
        </td>

        <td>Mobile :
          <?= $sms->report[$indicator]->user_mobile ?><br>Email :
          <?= $sms->report[$indicator]->user_email ?>
        </td>
        <td>
          <? if ($sms->report[$indicator]->user_status == 'Y') { ?>
          <div class="badge badge-success">Active</div>
          <? } elseif ($sms->report[$indicator]->user_status == 'R') { ?>
          <div class="badge badge-danger">Rejected</div>
          <? } elseif ($sms->report[$indicator]->user_status == 'N') { ?>
          <div class="badge badge-info">Waiting for Approval</div>
          <? } elseif ($sms->report[$indicator]->user_status == 'D') { ?>
          <div class="badge badge-info">Suspend</div>
          <? } ?>
          <br>
          <?= $entry_date ?>
        </td>
        <? if ($_SESSION['yjwatsp_user_master_id'] == 1) { ?>
        <td>
          <div class="dropdown-primary dropdown open">
            <button class="btn btn-primary dropdown-toggle waves-effect waves-light btn-sm f-w-700" type="button"
              id="dropdown-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Action</button>
            <div class="dropdown-menu" aria-labelledby="dropdown-2" data-dropdown-in="fadeIn"
              data-dropdown-out="fadeOut">
              <a href="view_onboarding?action=viewrep&usr=<?= $sms->report[$indicator]->user_id ?>"
                class="dropdown-item waves-effect waves-light">View Account</a>
              <? if ($sms->report[$indicator]->user_status != 'S') { ?>
              <a href="view_onboarding?action=suspend&usr=<?= $sms->report[$indicator]->user_id ?>"
                class="dropdown-item waves-effect waves-light">Suspend Account</a>
              <? }

              if ($sms->report[$indicator]->user_status != 'R') { ?>
              <a href="view_onboarding?action=reject&usr=<?= $sms->report[$indicator]->user_id ?>"
                class="dropdown-item waves-effect waves-light">Reject Account</a>
              <? }

              if ($sms->report[$indicator]->user_status != 'Y') { ?>
              <a href="view_onboarding?action=active&usr=<?= $sms->report[$indicator]->user_id ?>"
                class="dropdown-item waves-effect waves-light">Activate Account</a>
              <? } ?>
              <div class="dropdown-divider"></div>
              <? if ($sms->report[$indicator]->user_master_id != '2' && $sms->report[$indicator]->parent_id == '1') { ?>
              <a href="view_onboarding?action=makereseller&usr=<?= $sms->report[$indicator]->user_id ?>"
                class="dropdown-item waves-effect waves-light">Make Reseller</a>
              <? } ?>
              <? if (($_SESSION['yjwatsp_user_master_id'] == 1 || $_SESSION['yjwatsp_user_master_id'] == 2) && $sms->report[$indicator]->user_master_id == '2') { ?>
              <a href="#!"
                onclick="addusers_popup('<?= $sms->report[$indicator]->user_id ?>', '2', '<?= $indicatori ?>')"
                class="dropdown-item waves-effect waves-light">Add Users</a>
              <? } ?>
            </div>
          </div>
        </td>
        <? } ?>
      </tr>
      <?
        }
      } else if ($sms->response_status == 204) {
        site_log_generate("Manage Users List Page  : " . $user_name . "get the Service response [$sms->response_status] on " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("Manage Users List Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . date("Y-m-d H:i:s"), '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>
    </tbody>
  </table>
</form>
<!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter  using-->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// manage_users_list Page manage_users_list - End


// approve_campaign_list Page approve_campaign_list sms- Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_campaign_list_sms") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <th>Status</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
                    "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
                    "user_product": "GSM SMS"
                  }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
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
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->cm_entry_date));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          $is_same_media = $sms->campaign_list[$indicator]->is_same_media;

          ?>
          <tr>
            <td>
              <?= $indicatori ?>
            </td>
            <td>
              <?= strtoupper($sms->campaign_list[$indicator]->user_name) ?>
            </td>
            <td>
              <?= $sms->campaign_list[$indicator]->campaign_name ?>
            </td>
            <td style="text-align:center;" id='approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Total mobile numbers">
                <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
              </div>
              <div><button type="button" title="Total Mobile Numbers"
                  onclick="func_download_rc_no('<?= $encodedDatas = json_encode($sms->campaign_list[$indicator]->receiver_mobile_nos->data) ?>')"
                  class="btn btn-icon btn-success">Download</button>
              </div>
            </td>
            <td>
              <? if ($sms->campaign_list[$indicator]->cm_status == 'W') { ?><a href="#!"
                  class="btn btn-outline-info btn-disabled" style="text-align:center">Waiting</a>
              <? } ?>
            </td>
            <td>
              <?= $entry_date ?>
            </td>
            <td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Approve"
                  onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>',`<?= $sms->campaign_list[$indicator]->text_title ?>`,'<?= $sms->campaign_list[$indicator]->message_type ?>','<?= $sms->campaign_list[$indicator]->media_url ?>','<?= $is_same_media ?>')"
                  class="btn btn-icon btn-success">Approve campaign
                </button>
              </div>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Reject"
                  onclick="cancel_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>')"
                  class="btn btn-icon btn-danger">Reject</i></button>
              </div>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// approve_campaign_list Page approve_campaign_list sms - End


// compose obd call holding report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_obd_callholding") {
  site_log_generate("call_holding_report Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>

  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th title="Date"
          style="display: flexbox; justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Call Dates (🗓️)</th>
        <th title="User"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          User Name (👤)</th>
        <th title="Campaign Name"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Name (🔈)</th>
        <th title="Total Pushed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          1-5(in secs) (🕓)</th>
        <th title="Waiting"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          6-10(in secs) (🕓) </th>
        <th title="In Processing"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          11-15(in secs) (🕓)</th>
        <th title="Success"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          16-20(in secs) (🕓)</th>
        <th title="Delivered"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          21-25(in secs) (🕓)</th>
        <th title="Read"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          26-30(in secs) (🕓)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          31-45(in secs) (⏱️)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          46-60(in secs) (🕓)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Total Calls (📤)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Call Answered (✔️)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Call Not Answered (❌)</th>
      </tr>
    </thead>
    <tbody>
      <?


      if ($_REQUEST['dates']) {
        $date = $_REQUEST['dates'];
      }

      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));

      $replace_txt .= '{';

      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }


      // To Send the request API 
      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';
      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // To Get Api URL
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/call_holding_report',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Send the data into API and execute  
      site_log_generate("call_holding_report Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . $current_date, '../');
      $response = curl_exec($curl);
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("call_holding_report Report Page  : " . $uname . " get the Service response [$response] on " . $current_date, '../');
      // To get the one by one data
      $indicatori = 0;

      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }
      if ($sms->response_code == 1) {
        // If the response is success to execute this condition
        for ($indicator = 0; $indicator < count($sms->report); $indicator++) {
          //Looping the indicator is less than the count of report.if the condition is true to continue the process.if the condition is false to stop the process
          $indicatori++;

          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->call_holding_reprtdt));
          $user_id = $sms->report[$indicator]->user_id;
          $user_name = $sms->report[$indicator]->user_name;
          $user_master_id = $sms->report[$indicator]->user_master_id;
          $user_type = $sms->report[$indicator]->user_type;
          $campaign_name = $sms->report[$indicator]->campaign_name;
          ${'1_5_secs'} = $sms->report[$indicator]->{'1_5_secs'};
          ${'6_10_secs'} = $sms->report[$indicator]->{'6_10_secs'};
          ${'11_15_secs'} = $sms->report[$indicator]->{'11_15_secs'};
          ${'16_20_secs'} = $sms->report[$indicator]->{'16_20_secs'};
          ${'21_25_secs'} = $sms->report[$indicator]->{'21_25_secs'};
          ${'26_30_secs'} = $sms->report[$indicator]->{'26_30_secs'};
          ${'31_45_secs'} = $sms->report[$indicator]->{'31_45_secs'};
          ${'46_60_secs'} = $sms->report[$indicator]->{'46_60_secs'};
          $total_calls = $sms->report[$indicator]->total_calls;
          $call_answered = $sms->report[$indicator]->call_answered;
          $call_not_answered = $sms->report[$indicator]->call_not_answered;
          $increment++; ?>
          <tr style="text-align: center !important">
            <td>
              <?= $increment ?>
            </td>
            <td>
              <?= $entry_date ?>
            </td>
            <td>
              <?= strtoupper($user_name) ?>
            </td>
            <td>
              <?= $campaign_name ?>
            </td>
            <td>
              <?php echo ${'1_5_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'6_10_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'11_15_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'16_20_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'21_25_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'26_30_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'31_45_secs'} ?? "0"; ?>
            </td>
            <td>
              <?php echo ${'46_60_secs'} ?? "0"; ?>
            </td>
            <td>
              <?= $total_calls ?>
            </td>
            <td>
              <?php echo $call_answered ?? "0"; ?>
            </td>
            <td>
              <?php echo $call_not_answered ?? "0"; ?>
            </td>
          </tr>

        <? }

      } else if ($sms->response_status == 204) {
        site_log_generate("call_holding_report Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . $current_date, '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("call_holding_report Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . $current_date, '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>

    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 8, 9]
      }]
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// compose obd call holding report - Start

// compose compose_obd_summary report - Start

if ($_SERVER['REQUEST_METHOD'] == "POST" && $call_function == "compose_obd_summary") {
  site_log_generate("compose_obd_summary Report Page: User: " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  ?>

  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th title="Date">Call Dates (🗓️)</th>
        <th title="User">User Name (👤)</th>
        <th title="Campaign Name">Campaign Name (🔈)</th>
        <th title="Total Pushed">Total Dialled (📤)</th>
        <th title="Total Success">Total Success (✔️)</th>
        <th title="First Attempt">First Attempt (🟢)</th>
        <th title="Retry 1">Retry 1 (🔁1️⃣)</th>
        <th title="Retry 2">Retry 2 (🔁2️⃣)</th>
        <th title="Busy">Busy (📞🚫)</th>
        <th title="No Answer">No Answer (📞❌)</th>
        <th title="Total Failed">Total Failed (Busy + No Answer) (❌)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if (isset($_REQUEST['dates'])) {
        $date = $_REQUEST['dates'];
      }

      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));

      $replace_txt = '{
            "user_product":"OBD CALL SIP",';

      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }

      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'];

      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';

      // Initialize cURL session
      $curl = curl_init();

      // Set cURL options
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/summary_report',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Execute cURL request
      $response = curl_exec($curl);
      // Close cURL session
      curl_close($curl);
      // Process API response
      $sms = json_decode($response);

      if (empty($response)) {
        echo '<script>window.location = "logout"</script>';
      } elseif ($sms->response_status == 403) {
        echo '<script>window.location = "logout"</script>';
      }

      if ($sms->response_code == 1) {
        $increment = 0;
        // Loop through each report entry
        foreach ($sms->report as $report) {
          $increment++;
          $entry_date = date('d-m-Y', strtotime($report->entry_date));
          $total_dialled = $report->total_dialled;
          $total_success = $report->total_success;
          $first_attempt = $report->first_attempt;
          $retry_1 = $report->retry_1;
          $retry_2 = $report->retry_2;
          $total_busy = $report->total_busy;
          $total_no_answer = $report->total_no_answer;
          $total_failed = $total_busy + $total_no_answer;
          ?>
          <tr style="text-align: center;">
            <td><?= $increment ?></td>
            <td><?= $entry_date ?></td>
            <td><?= strtoupper($report->user_name) ?></td>
            <td><?= $report->campaign_name ?></td>
            <td><?= !empty($total_dialled) ? $total_dialled : '0' ?></td>
            <td><?= !empty($total_success) ? $total_success : '0' ?></td>
            <td><?= !empty($first_attempt) ? $first_attempt : '0' ?></td>
            <td><?= !empty($retry_1) ? $retry_1 : '0' ?></td>
            <td><?= !empty($retry_2) ? $retry_2 : '0' ?></td>
            <td><?= !empty($total_busy) ? $total_busy : '0' ?></td>
            <td><?= !empty($total_no_answer) ? $total_no_answer : '0' ?></td>
            <td><?= !empty($total_failed) ? $total_failed : '0' ?></td>

          </tr>
          <?php
        }
      } elseif ($sms->response_status == 204) {
        site_log_generate("compose_obd_summary Report Page: " . $_SESSION['yjwatsp_user_name'] . " Service response [$sms->response_status] on " . $current_date, '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("compose_obd_summary Report Page: " . $_SESSION['yjwatsp_user_name'] . " Service response [$sms->response_msg] on " . $current_date, '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>
    </tbody>
  </table>

  <!-- DataTables and Export Scripts -->
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
    $(document).ready(function () {
      var table = $('#table-1').DataTable({
        dom: 'PlBfrtip',
        searchPanes: {
          cascadePanes: true,
          initCollapsed: true
        },
        buttons: [{
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, ':visible']
          }
        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: ':visible'
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: ':visible'
          }
        },
          'colvis'
        ],
        columnDefs: [{
          searchPanes: {
            show: true
          },
          targets: [1, 2, 3, 4, 6]
        },
        {
          searchPanes: {
            show: false
          },
          targets: [0, 5, 7, 8, 9, 10, 11]
        }
        ]
      });
    });
  </script>

  <?php
}
// compose obd summary report - Start

// compose obd detail report - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_obd_detailed") {
  site_log_generate("Campaign Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <? if ($_SESSION['yjwatsp_user_master_id'] != 3) { ?>
          <th>User</th>
        <? } ?>
        <th>Entry Date</th>
        <th>Campaign Name</th>
        <th>Context</th>
        <th>Total Calls</th>
        <th>Success Calls</th>
        <th>Failure Calls</th>
        <th>Download</th>
      </tr>
    </thead>
    <tbody>

      <?
      $replace_txt = '{
            "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",
            "user_product":"OBD CALL SIP",';
      // To Send the request API 
      if (($_REQUEST['dates'] != 'undefined') && ($_REQUEST['dates'] != '[object HTMLInputElement]') && ($_REQUEST['dates'] != '')) {
        $date = $_REQUEST['dates'];
        $td = explode('-', $date);
        $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
        $thismonth_today = date("Y/m/d", strtotime($td[1]));
        if ($date) {
          $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
        }
      } else {
        $currentDate = date('Y/m/d');
        $thirtyDaysAgo = date('Y/m/d', strtotime('-7 days', strtotime($currentDate)));
        $date = $thirtyDaysAgo . "-" . $currentDate; // 01/28/2023 - 02/27/2023 
        $replace_txt .= '"date_filter" : "' . $thirtyDaysAgo . ' - ' . $currentDate . '",';
      }

      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/report/obd_detailed_report',
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
      site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
      $response = curl_exec($curl);
      curl_close($curl);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      $sms = json_decode($response, false);
      site_log_generate("Campaign Report Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . $current_date, '../');

      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;

      if ($sms->response_status == 200) {

        for ($indicator = 0; $indicator < $sms->num_of_rows; $indicator++) {
          $indicatori++;
          $user_name = $sms->report[$indicator]->user_name;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->report[$indicator]->report_entry_time));
          $context = $sms->report[$indicator]->context;
          $total_dialled = $sms->report[$indicator]->total_dialled;
          $total_success = $sms->report[$indicator]->total_success;
          $total_failed = $sms->report[$indicator]->total_failed;
          $total_busy = $sms->report[$indicator]->total_busy;
          $total_no_answer = $sms->report[$indicator]->total_no_answer;
          $download_url = $sms->report[$indicator]->download_url;
          $report_status = $sms->report[$indicator]->report_status;

          ?>
          <tr>
            <td>
              <?= $indicatori ?>
            </td>

            <? if ($_SESSION['yjwatsp_user_master_id'] != 3) { ?>
              <td>
                <?= strtoupper($user_name) ?>
              </td>
            <? } ?>
            <td>
              <?= $entry_date ?>
            </td>
            <td>
              <?= $sms->report[$indicator]->campaign_name ?>
            </td>
            <td>
              <?= $context ?>
            </td>
            <td><?= !empty($total_dialled) ? $total_dialled : '0' ?></td>
            <td><?= !empty($total_success) ? $total_success : '0' ?></td>
            <td><?= !empty($total_failed) ? $total_failed : '0' ?></td>
            <td>
              <?php if ($report_status == 'Y'): ?>
                <a title="Download the report" href="uploads/obd_call_report_csv/<?php echo basename($download_url); ?>"
                  style="width: 50px; height: 40px; color: #000;" download>
                  <i class="fas fa-download"></i>
                </a>
                <?/*<a href="javascript:void(0)" title="Download the report"
                              onclick="download_server_file('<?php echo $sms->report[$indicator]->campaign_name; ?>')">
                              <i class="fas fa-download"></i></a>*/ ?>
              <?php else: ?>
                <span style="color:red;">CDR generation in progress</span>
              <?php endif; ?>
            </td>
            <?
        }
      }
      ?>
    </tbody>
  </table>

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
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7]
        },
        action: function (e, dt, button, config) {
          showLoader();
          // Use the built-in pdfHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 8]
      }]
    });

    // Event listener for button click
    $('#table-1').on('buttons-processing', function (e, settings, processing) {
      // Show/hide loader based on the processing status
      if (processing) {
        $('.loading').show();
      } else {
        $('.loading').hide();
      }
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>

  <?
}
// compose obd detail report - Start


// compose obd campaign list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_obd_campaign_list") {
  site_log_generate("compose_rcs_summary Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>

  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th title="Date"
          style="display: flexbox; justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Call Dates (🗓️)</th>
        <th title="User"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          User Name (👤)</th>
        <th title="Campaign Name"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Name (🔈)</th>
        <th title="Total Pushed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Context (📖)</th>
        <th title="Waiting"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Total Calls (📤) </th>
        <th title="In Processing"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Total Success (✔️)</th>
        <th title="Success"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Total Failure (❌)</th>
        <th title="Delivered"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Status (📊)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Action (🔄)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Remarks (📝)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Created Date (🗓️)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Start Date (🚀)</th>
        <th title="Failed"
          style="justify-content: space-evenly;align-items: stretch;align-content: stretch;flex-direction: row-reverse;flex-wrap: nowrap;">
          Campaign Completed Date (✅)</th>
      </tr>
    </thead>
    <tbody>
      <?
      if ($_REQUEST['dates']) {
        $date = $_REQUEST['dates'];
      }

      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));

      $replace_txt .= '{
            "user_product":"OBD CALL SIP",
              "user_id" : "' . $_SESSION['yjwatsp_user_id'] . '",';

      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }
      // To Send the request API 
      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';

      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // To Get Api URL
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/obd_call/obd_campaign_list',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Send the data into API and execute  
      site_log_generate("compose_rcs_summary Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . $current_date, '../');
      $response = curl_exec($curl);
      curl_close($curl);
      // echo $response;
      // After got response decode the JSON result
      $sms = json_decode($response, true);
      site_log_generate("compose_rcs_summary Report Page  : " . $uname . " get the Service response [$response] on " . $current_date, '../');
      // To get the one by one data
      $indicatori = 0;
      // Check if the response is empty or unauthorized
      if ($response == '' || $sms['response_status'] == 403) {
        echo '<script>window.location = "logout"</script>';
        exit;
      }
      // Check if the response code indicates success
      if ($sms['response_code'] == 1) {
        // Iterate over the campaign list and display the data in a table
        $campaign_list = $sms['campaign_list'];
        $increment = 0;

        foreach ($campaign_list as $campaign) {
          $increment++;
          $cm_entry_date = date('d-m-Y h:i:s A', strtotime($campaign['cm_entry_date']));
          $user_name = strtoupper($campaign['user_name']);
          $campaign_name = $campaign['campaign_name'];
          $context = $campaign['context'];
          $total_msg = $campaign['total_mobile_no_count'];
          $total_success = $campaign['total_success'];
          $cm_status = $campaign['cm_status'];
          $total_failed = $campaign['total_failed'];
          $remarks = $campaign['rejected_reason'];
          $call_start_date = !empty($campaign['call_start_date']) ? date('d-m-Y h:i:s A', strtotime($campaign['call_start_date'])) : '';
          $call_end_date = !empty($campaign['call_end_date']) ? date('d-m-Y h:i:s A', strtotime($campaign['call_end_date'])) : '';
          ?>
          <tr style='text-align: center !important'>
            <td><?= $increment ?></td>
            <td><?= $cm_entry_date ?></td>
            <td><?= $user_name ?></td>
            <td><?= $campaign_name ?></td>
            <td><?= $context ?></td>
            <td><?= $total_msg ?></td>
            <td>
              <?php echo $total_success = $total_success ? $total_success : '0'; ?>
            </td>
            <td>
              <?php echo $total_failed = $total_failed ? $total_failed : '0'; ?>
            </td>
            <td>
              <?php
              if ($cm_status == 'P') { ?>
                <a href="#!" class="btn btn-outline-primary btn-disabled" style="width:100px; text-align:center">Processing</a>
                <?php
              } elseif ($cm_status == 'S') { ?>
                <a href="#!" class="btn btn-outline-dark btn-disabled" style="width:100px; text-align:center">Stop</a>
                <?php
              } elseif ($cm_status == 'W') { ?>
                <a href="#!" class="btn btn-outline-warning btn-disabled" style="width:100px; text-align:center">Waiting</a>
                <?php
              } elseif ($cm_status == 'R') { ?>
                <a href="#!" class="btn btn-outline-danger btn-disabled" style="width:100px; text-align:center">Rejected</a>
                <?php
              } elseif ($cm_status == 'Y') { ?>
                <a href="#!" class="btn btn-outline-success btn-disabled" style="width:100px; text-align:center">Completed</a>
                <?php
              }
              ?>
            </td>
            <td><?= '-' ?></td>
            <td><?= $remarks ?></td>
            <td><?= $cm_entry_date ?></td>
            <td><?php echo $call_start_date = $call_start_date ? $call_start_date : '-'; ?></td>
            <td><?php echo $call_end_date = $call_end_date ? $call_end_date : '-'; ?></td>
          </tr>

        <? }
      } else if ($sms->response_status == 204) {
        site_log_generate("compose_rcs_summary Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . $current_date, '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("compose_rcs_summary Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . $current_date, '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>

    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [{
        extend: 'copyHtml5',
        exportOptions: {
          columns: [0, ':visible']
        },
        action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'csvHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, {
        extend: 'pdfHtml5',
        exportOptions: {
          columns: ':visible'
        }, action: function (e, dt, button, config) {
          showLoader(); // Display loader before export
          // Use the built-in copyHtml5 button action
          $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
          setTimeout(function () {
            hideLoader();
          }, 1000);
        }
      }, 'colvis'],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 6]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 8, 9]
      }]
    });
    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// compose obd campaign list - Start


// compose obd prompt list - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "compose_obd_prompt_list") {
  site_log_generate("compose_obd_prompt_list Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>

  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th>User Name</th>
        <th>Type</th>
        <th> Context</th>
        <th> Remarks</th>
        <th> Prompt Name</th>
        <th>Prompt Status</th>
        <th> Entry Time </th>
        <th> Approve Date </th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      if ($_REQUEST['dates']) {
        $date = $_REQUEST['dates'];
      }

      $td = explode('-', $date);
      $thismonth_startdate = date("Y/m/d", strtotime($td[0]));
      $thismonth_today = date("Y/m/d", strtotime($td[1]));

      $replace_txt .= '{';

      if ($date) {
        $replace_txt .= '"date_filter" : "' . $thismonth_startdate . ' - ' . $thismonth_today . '",';
      }


      // To Send the request API 
      $replace_txt = rtrim($replace_txt, ",");
      $replace_txt .= '}';

      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // To Get Api URL
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/list/prompt_list',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Send the data into API and execute  
      site_log_generate("compose_obd_prompt_list Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . $current_date, '../');
      $response = curl_exec($curl);
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("compose_obd_prompt_list Report Page  : " . $uname . " get the Service response [$response] on " . $current_date, '../');
      // To get the one by one data
      $indicatori = 0;

      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }

      if ($sms->response_code == 1) {
        // If the response is success to execute this condition
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->prompt_entry_time));
          $approve_date = $sms->campaign_list[$indicator]->approve_date;
          $formatted_approve_date = $approve_date === null ? '-' : date('d-m-Y h:i:s A', strtotime($approve_date));
          //$approve_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->approve_date));
          $campaign_type = $sms->campaign_list[$indicator]->campaign_type;
          $campaign = '';

          if ($campaign_type == 'G') {
            $campaign = 'Generic';
          } else if ($campaign_type == 'C') {
            $campaign = 'Customized';
          } else {
            $campaign = 'Personalized';
          }
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->user_name;
          $prompt_id = $sms->campaign_list[$indicator]->prompt_id;
          $company_name = $sms->campaign_list[$indicator]->company_name;
          $context = $sms->campaign_list[$indicator]->context;
          $states_id = $sms->campaign_list[$indicator]->states_id;
          $language_id = $sms->campaign_list[$indicator]->language_id;
          $type = $sms->campaign_list[$indicator]->type;
          $created_at = $sms->campaign_list[$indicator]->created_at;
          $prompt_path = $sms->campaign_list[$indicator]->prompt_path;
          $prompt_name = basename($prompt_path);
          $remarks = $sms->campaign_list[$indicator]->remarks;
          $increment++;
          $prompt_indicator++; ?>

          <tr style="text-align: center !important">
            <td>
              <?= $increment ?>
            </td>
            <td>
              <?= strtoupper($user_name) ?>
            </td>
            <td>
              <?= $campaign ?>
            </td>
            <td>
              <?= $context ?>
            </td>
            <td>
              <?= $remarks ?>
            </td>
            <td>
              <?php echo $prompt_name = $prompt_name ? $prompt_name : '-'; ?>
            </td>
            <td>
              <? if ($sms->campaign_list[$indicator]->prompt_status == 'Y') { ?><a href="#!"
                  class="btn btn-outline-success btn-disabled"
                  style="width:100px; text-align:center">Active</a><? } elseif ($sms->campaign_list[$indicator]->prompt_status == 'N') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Inactive</a><? } elseif ($sms->campaign_list[$indicator]->prompt_status == 'R') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Rejected</a><? } ?>
            </td>
            <td>
              <?= $entry_date ?>
            </td>
            <td>
              <?= $formatted_approve_date ?>
            </td>
            <td>
              <? if ($campaign_type != 'C') { ?>
                <div class="btn-group">
                  <a href="javascript:void(0)" class="play-pause" title="Play/Stop the Audio"
                    style="width: 40px; height: 40px; color: #000;" id='id_prompt_<?php echo $prompt_indicator; ?>'
                    onclick="toggleAudio('<?php echo $prompt_path; ?>', 'id_prompt_<?php echo $prompt_indicator; ?>')">
                    <i class="fas fa-play"></i></a>
                  <a title="Download the Audio" href="<?php echo $prompt_path; ?>"
                    style="width: 40px; height: 40px; color: #000;" download="">
                    <i class="fas fa-download"></i></a>
                </div>
              <? } else { ?>
                -
              <? } ?>
            </td>
          </tr>

        <? }
      } else if ($sms->response_status == 204) {
        site_log_generate("compose_obd_prompt_list Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . $current_date, '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("compose_obd_prompt_list Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . $current_date, '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>

    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'PlBfrtip',
      searchPanes: {
        cascadePanes: true,
        initCollapsed: true
      },
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [{
        searchPanes: {
          show: true
        },
        targets: [1, 2, 3, 4, 5]
      }, {
        searchPanes: {
          show: false
        },
        targets: [0, 5, 7, 9]
      }]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// compose obd prompt list - Start



// approve prompt obd - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_prompt_obd") {
  site_log_generate("approve_prompt_obd Report Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  // Here we can Copy, Export CSV, Excel, PDF, Search, Column visibility the Table 
  ?>

  <table class="table table-striped" id="table-1">
    <thead>
      <tr>
        <th>#</th>
        <th>User Name</th>
        <th>Type</th>
        <th> Context</th>
        <th> Remarks</th>
        <th> Prompt Name</th>
        <th>Prompt Status</th>
        <th> Entry Time </th>
        <th>Action</th>
        <th>Approve</th>
      </tr>
    </thead>
    <tbody>
      <?

      // Add bearer token
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      // To Get Api URL
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/list/approve_prompt_list',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      // Send the data into API and execute  
      site_log_generate("approve_prompt_obd Report Page : " . $uname . " Execute the service [$replace_txt,$bearer_token] on " . $current_date, '../');
      $response = curl_exec($curl);
      curl_close($curl);
      // After got response decode the JSON result
      $sms = json_decode($response, false);
      site_log_generate("approve_prompt_obd Report Page  : " . $uname . " get the Service response [$response] on " . $current_date, '../');
      // To get the one by one data
      $indicatori = 0;

      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }

      if ($sms->response_code == 1) {
        // If the response is success to execute this condition
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->prompt_entry_time));
          $campaign_type = $sms->campaign_list[$indicator]->campaign_type;
          $campaign = '';

          if ($campaign_type == 'G') {
            $campaign = 'Generic';
          } else if ($campaign_type == 'C') {
            $campaign = 'Customized';
          } else {
            $campaign = 'Personalized';
          }
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->user_name;
          $prompt_id = $sms->campaign_list[$indicator]->prompt_id;
          $company_name = $sms->campaign_list[$indicator]->company_name;
          $context = $sms->campaign_list[$indicator]->context;
          $states_id = $sms->campaign_list[$indicator]->states_id;
          $language_id = $sms->campaign_list[$indicator]->language_id;
          $type = $sms->campaign_list[$indicator]->type;
          $created_at = $sms->campaign_list[$indicator]->created_at;
          $prompt_path = $sms->campaign_list[$indicator]->prompt_path;
          $prompt_name = basename($prompt_path);
          $remarks = $sms->campaign_list[$indicator]->remarks;
          $increment++;
          $prompt_indicator++; ?>

          <tr style="text-align: center !important">
            <td>
              <?= $increment ?>
            </td>
            <td>
              <?= strtoupper($user_name) ?>
            </td>
            <td>
              <?= $campaign ?>
            </td>
            <td>
              <?= $context ?>
            </td>
            <td>
              <?= $remarks ?>
            </td>
            <td>
              <?php echo $prompt_name = $prompt_name ? $prompt_name : '-'; ?>
            </td>
            <td>
              <? if ($sms->campaign_list[$indicator]->prompt_status == 'Y') { ?><a href="#!"
                  class="btn btn-outline-success btn-disabled"
                  style="width:100px; text-align:center">Active</a><? } elseif ($sms->campaign_list[$indicator]->prompt_status == 'N') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Inactive</a><? } elseif ($sms->campaign_list[$indicator]->prompt_status == 'R') { ?><a
                  href="#!" class="btn btn-outline-danger btn-disabled"
                  style="width:100px; text-align:center">Rejected</a><? } ?>
            </td>
            <td>
              <?= $entry_date ?>
            </td>
            <td>
              <? if ($campaign_type != 'C') { ?>
                <div class="btn-group">
                  <a href="javascript:void(0)" class="play-pause" title="Play/Stop the Audio"
                    style="width: 40px; height: 40px; color: #000;" id='prompt_<?php echo $prompt_indicator; ?>'
                    onclick="toggleAudio('<?php echo $prompt_path; ?>', 'prompt_<?php echo $prompt_indicator; ?>')">
                    <i class="fas fa-play"></i></a>
                  <a title="Download the Audio" href="<?php echo $prompt_path; ?>"
                    style="width: 40px; height: 40px; color: #000;" download="">
                    <i class="fas fa-download"></i></a>
                </div>
              <? } else { ?>
                -
              <? } ?>
            </td>

            <td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Approve"
                  onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->prompt_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->context ?>')"
                  class="btn btn-icon btn-success">Approve Prompt

                </button>
              </div>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Reject"
                  onclick="cancel_popup('<?= $sms->campaign_list[$indicator]->prompt_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->context ?>')"
                  class="btn btn-icon btn-danger">Decline</i></button>
              </div>
            </td>
          </tr>

        <? }
      } else if ($sms->response_status == 204) {
        site_log_generate("approve_prompt_obd Report Page  : " . $user_name . "get the Service response [$sms->response_status] on " . $current_date, '../');
        $json = array("status" => 2, "msg" => $sms->response_msg);
      } else {
        site_log_generate("approve_prompt_obd Report Page  : " . $user_name . " get the Service response [$sms->response_msg] on  " . $current_date, '../');
        $json = array("status" => 0, "msg" => $sms->response_msg);
      }
      ?>

    </tbody>
  </table>
  <!-- General JS Scripts -->
  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.buttons.min.js"></script>
  <script src="assets/js/dataTables.searchPanes.min.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <script src="assets/js/jszip.min.js"></script>
  <script src="assets/js/pdfmake.min.js"></script>
  <script src="assets/js/vfs_fonts.js"></script>
  <script src="assets/js/buttons.html5.min.js"></script>
  <script src="assets/js/buttons.colVis.min.js"></script>
  <!-- filter using -->
  <script>
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// approve prompt obd - Start


// approve obd campaign- Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "approve_campaign_list_obd") {
  site_log_generate("Approve Campaign List Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . $current_date, '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User Name</th>
        <th>Campaign Name</th>
        <th>Total Mobile Number Count</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
        "user_id":"' . $_SESSION['yjwatsp_user_id'] . '",
        "user_product": "OBD CALL SIP"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/approve_user/campaign_lt',
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
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Manage Whatsappno List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . $current_date, '../');
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? }
      if ($sms->response_status == 403) { ?>
        <script>window.location = "logout"</script>
      <? }

      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->whatspp_config_entdate));
          $compose_message_id = $sms->campaign_list[$indicator]->compose_message_id;
          $user_id = $sms->campaign_list[$indicator]->user_id;
          $user_name = $sms->campaign_list[$indicator]->campaign_name;
          $array_buffer = $sms->campaign_list[$indicator]->receiver_mobile_nos->data;
          ?>
          <tr>
            <td><?= $indicatori ?></td>
            <td><?= strtoupper($sms->campaign_list[$indicator]->user_name) ?></td>
            <td><?= $sms->campaign_list[$indicator]->campaign_name ?></td>
            <td style="text-align:center;" id='approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
              </div>
              <div>
                <button type="button" title="Total Mobile Numbers"
                  onclick="func_download_rc_no('<?= $sms->campaign_list[$indicator]->receiver_nos_path ?>')"
                  class="btn btn-icon btn-success">Download</button>
              </div>
            </td>
            <td><?= $sms->campaign_list[$indicator]->cm_entry_date ?></td>
            <td style="text-align:center;" id='id_approved_lineno_<?= $indicatori ?>'>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Approve"
                  onclick="func_save_phbabt_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->campaign_name ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>','<?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>','<?= $sms->campaign_list[$indicator]->user_name ?>','<?= $sms->campaign_list[$indicator]->context ?>')"
                  class="btn btn-icon btn-success">Approve campaign

                </button>
              </div>
              <div class="btn-group mb-3" role="group" aria-label="Basic example">
                <button type="button" title="Reject"
                  onclick="cancel_popup('<?= $sms->campaign_list[$indicator]->compose_message_id ?>','<?= $indicatori ?>','<?= $user_id = $sms->campaign_list[$indicator]->user_id ?>','<?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>','<?= $sms->campaign_list[$indicator]->user_name ?>','<?= $sms->campaign_list[$indicator]->context ?>','<?= $sms->campaign_list[$indicator]->campaign_name ?>')"
                  class="btn btn-icon btn-danger">Decline</i></button>
              </div>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, ':visible']
          }
        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: ':visible'
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: ':visible', // Exclude the third column (index 3)
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

  </script>
  <?
}
// approve obd campaign - Start

// campaign_list_restart_sip Page campaign_list_restart_sip - Start
if ($_SERVER['REQUEST_METHOD'] == "POST" and $call_function == "campaign_list_process_sip") {
  site_log_generate("campaign_list_restart_sip Page : User : " . $_SESSION['yjwatsp_user_name'] . " Preview on " . date("Y-m-d H:i:s"), '../');
  ?>
  <table class="table table-striped text-center" id="table-1">
    <thead>
      <tr class="text-center">
        <th>#</th>
        <th>User</th>
        <th>Campaign Name</th>
        <th>Total Mobile No Count</th>
        <th>Compose Status</th>
        <th>Entry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?
      $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . '';
      $replace_txt = '{
          "user_product": "OBD CALL SIP"
      }';
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $api_url . '/list/campaign_list_stop',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_POSTFIELDS => $replace_txt,
          CURLOPT_HTTPHEADER => array(
            $bearer_token,
            'Content-Type: application/json'
          ),
        )
      );

      $response = curl_exec($curl);
      //echo $response;
      curl_close($curl);
      $sms = json_decode($response, false);
      if ($response == '') { ?>
        <script>window.location = "logout"</script>
      <? } else if ($sms->response_status == 403) { ?>
          <script>window.location = "logout"</script>
      <? }
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->campaign_list); $indicator++) {
          $indicatori++;
          $entry_date = date('d-m-Y h:i:s A', strtotime($sms->campaign_list[$indicator]->cm_entry_date));
          ?>
          <tr>
            <td>
              <?= $indicatori ?>
            </td>
            <td>
              <?= strtoupper($sms->campaign_list[$indicator]->user_name) ?>
            </td>
            <td>
              <?= $sms->campaign_list[$indicator]->campaign_name ?>
            </td>
            <td>
              <?= $sms->campaign_list[$indicator]->total_mobile_no_count ?>
            </td>
            <td>
              <?
              if ($sms->campaign_list[$indicator]->cm_status == 'S') { ?><a href="#!"
                  class="btn btn-outline-danger btn-disabled" style="width:100px; text-align:center">Stop Campaign</a>
              <? } elseif ($sms->campaign_list[$indicator]->cm_status == 'P') { ?><a href="#!"
                  class="btn btn-outline-success btn-disabled" style="width:100px; text-align:center">Processing Campaign</a>
              <? } ?>
            </td>
            <td>
              <?= $entry_date ?>
            </td>

            <td id='id_approved_lineno_<?= $indicatori ?>'>
              <? if ($sms->campaign_list[$indicator]->cm_status == 'P') { ?>
                <button type="button" title="Stop"
                  onclick="sender_id_popup('<?= $sms->campaign_list[$indicator]->campaign_name ?>', '<?= $indicatori ?>','<?= $sms->campaign_list[$indicator]->compose_message_id ?>', '<?= $sms->campaign_list[$indicator]->user_id ?>','<?= $sms->campaign_list[$indicator]->context_id ?>')"
                  class="btn btn-icon btn-danger" style="width:75px;padding: 0.3rem 0.41rem !important;">Stop</button>
              <? } else { ?>
                <a href="#!" class="btn btn-outline-light btn-disabled"
                  style="width:75px;padding: 0.3rem 0.41rem !important;cursor: not-allowed;">Stop</a>
              <? } ?>
              <? if ($sms->campaign_list[$indicator]->cm_status == 'S') { ?>
                <button type="button" title="Restart"
                  onclick="restart_sender_id_popup('<?= $sms->campaign_list[$indicator]->campaign_name ?>', '<?= $indicatori ?>', '<?= $sms->campaign_list[$indicator]->compose_message_id ?>', '<?= $sms->campaign_list[$indicator]->user_id ?>')"
                  class="btn btn-success" style="width:75px;padding: 0.3rem 0.41rem !important;">Restart</button>
              <? } else { ?>
                <a href="#!" class="btn btn-outline-light btn-disabled" style="width:75px;cursor: not-allowed;">Restart</a>
              <? } ?>
            </td>
          </tr>
          <?
        }
      }
      ?>
    </tbody>
  </table>

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
    var table = $('#table-1').DataTable({
      dom: 'Bfrtip',
      colReorder: true,
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5],
          },
          action: function (e, dt, button, config) {
            showLoader(); // Display loader before export
            // Use the built-in copyHtml5 button action
            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }

        },
        {
          extend: 'csvHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in csvHtml5 button action
            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude the third column (index 3)
          },
          action: function (e, dt, button, config) {
            showLoader();
            // Use the built-in pdfHtml5 button action
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
            setTimeout(function () {
              hideLoader();
            }, 1000);
          }
        },
        {
          extend: 'searchPanes',
          config: {
            cascadePanes: true
          }
        },
        'colvis'
      ],
      columnDefs: [
        {
          searchPanes: {
            show: false
          },
          targets: [0]
        }
      ]
    });

    function showLoader() {
      table.buttons().processing(true); // Show the DataTables Buttons processing indicator
      $(".loading").css('display', 'block');
      $('.loading').show();
    }

    function hideLoader() {
      $(".loading").css('display', 'none');
      $('.loading').hide();
      table.buttons().processing(false); // Hide the DataTables Buttons processing indicator
    }
  </script>
  <?
}
// campaign_list_restart_sip Page campaign_list_restart_sip - End

// Channel List Page process_channel_list - Start
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_GET['call_function'] == "process_channel_list") {

  $campaign_id = htmlspecialchars(strip_tags(isset($_REQUEST["campaign_id"]) ? $conn->real_escape_string($_REQUEST["campaign_id"]) : ""));
  $selected_user_id = htmlspecialchars(strip_tags(isset($_REQUEST["user_id"]) ? $conn->real_escape_string($_REQUEST["user_id"]) : ""));

  $replace_txt = '{
    "campaign_id" : "' . $campaign_id . '",
    "selected_user_id" : "' . $selected_user_id . '"

  }';
  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/process_server',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  // Send the data into API and execute                          
  site_log_generate("Compose OBD Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  $sms = json_decode($response, false);

  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <?php } else if ($sms->response_status == 403) { ?>
      <script>window.location = "logout"</script>
  <?php } else if ($sms->response_status == 200) {
    $indicatori = 0; ?>
        <table style="width: 100%;">
        <?php
        $counter = 0;
        for ($indicator = 0; $indicator < count($sms->reports); $indicator++) {
          if ($sms->reports[$indicator]->sip_status == 'P') {
            if ($counter % 2 == 0) { ?>
                <tr>
            <?php } ?>
                <td>
                  <input type="checkbox" class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="server_names"
                    tabindex="1" autofocus value="<?= $sms->reports[$indicator]->sip_id ?>">
                  <label class="form-label"><?= $sms->reports[$indicator]->server_name ?></label>
                </td>
              <?php
              if ($counter % 2 == 1) { ?>
                </tr>
          <?php }
              $counter++;
          }
        } ?>
        </table>
  <?php } else if ($sms->response_status == 204 || $sms->response_status == 201) {
    echo $sms->response_status;
  }
}

// Channel List Page process_channel_list - Start
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_GET['call_function'] == "restart_channel_list") {
  $campaign_id = htmlspecialchars(strip_tags(isset($_REQUEST["campaign_id"]) ? $conn->real_escape_string($_REQUEST["campaign_id"]) : ""));
  $selected_user_id = htmlspecialchars(strip_tags(isset($_REQUEST["user_id"]) ? $conn->real_escape_string($_REQUEST["user_id"]) : ""));

  $replace_txt = '{
    "campaign_id" : "' . $campaign_id . '",
    "selected_user_id" : "' . $selected_user_id . '"
  }';

  $bearer_token = 'Authorization: ' . $_SESSION['yjwatsp_bearer_token'] . ''; // Add Bearer Token
  $curl = curl_init();
  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => $api_url . '/list/process_server',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $replace_txt,
      CURLOPT_HTTPHEADER => array(
        $bearer_token,
        'Content-Type: application/json'
      ),
    )
  );

  // Send the data into API and execute                          
  site_log_generate("Compose OBD Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . $current_date, '../');
  $response = curl_exec($curl);
  curl_close($curl);
  $sms = json_decode($response, false);

  if ($response == '') { ?>
    <script>window.location = "logout"</script>
  <?php } else if ($sms->response_status == 403) { ?>
      <script>window.location = "logout"</script>
  <?php } else if ($sms->response_status == 200) {
    $indicatori = 0; ?>
        <table style="width: 100%;">
        <?php
        $counter = 0;
        for ($indicator = 0; $indicator < count($sms->reports); $indicator++) {
          if ($sms->reports[$indicator]->sip_status == 'Y' || $sms->reports[$indicator]->sip_status == 'T') {
            if ($counter % 2 == 0) { ?>
                <tr>
            <?php } ?>
                <td>
                  <input type="checkbox" class="cls_checkbox1" id="txt_whatsapp_mobno_<?= $indicator ?>" name="server_names"
                    tabindex="1" autofocus value="<?= $sms->reports[$indicator]->sip_id ?>">
                  <label class="form-label"><?= $sms->reports[$indicator]->server_name ?></label>
                </td>
              <?php
              if ($counter % 2 == 1) { ?>
                </tr>
          <?php }
              $counter++;
          }
        } ?>
        </table>
  <?php } else if ($sms->response_status == 204 || $sms->response_status == 201) {
    echo $sms->response_status;
  }
}

// Finally Close all Opened Mysql DB Connection
$conn->close();

// Output header with HTML Response
header('Content-type: text/html');
echo $result_value;
