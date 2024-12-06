<?php
/*
This page has some functions which is access from Frontend.
This page is act as a Backend page which is connect with Node JS API and PHP Frontend.
It will collect the form details and send it to API.
After get the response from API, send it back to Frontend.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 01-Jul-2023
*/
session_start();
error_reporting(0);
// Include configuration.php
include_once('../api/configuration.php');
extract($_REQUEST);

$current_date = date("Y-m-d H:i:s"); 

if($_SESSION["yjwatsp_user_status"] == 'N' or $_SESSION["yjwatsp_user_status"] == 'R') {
  if($site_page_name != 'user_profiles' and $site_page_name != 'dashboard') {
    ?>
    <script>window.location = "user_profiles";</script>
    <?
  }
} ?>

<nav class="navbar navbar-expand-lg main-navbar">
  <form class="form-inline mr-auto">
    <ul class="navbar-nav mr-3">
      <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
    </ul>

   <?  
 site_log_generate("Site Header Page : " . $_SESSION['yjwatsp_user_name'] . " Access on MADHU " . date("Y-m-d H:i:s"), '../');

 $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].''; 
      $replace_txt = '{
     "user_id" : "'.$_SESSION['yjwatsp_user_id'].'"    }';
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url . '/site_menu/product_header',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30, // Set timeout to 20 seconds
        CURLOPT_CONNECTTIMEOUT => 30, // Set timeout for the connection phase to 5 seconds
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
      site_log_generate("Site Header Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);

// Check for cURL errors and timeouts
if (curl_errno($curl) == CURLE_OPERATION_TIMEDOUT) {
header("Location: index");
exit;
} elseif (curl_errno($curl)) {
header("Location: index");
exit;
}
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Site Header List Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
          if ($response == '') { ?>
                                    <script>window.location="logout"</script>
                  <? }
     else if ($sms->response_status == 403) { ?>
        <script>window.location="logout"</script>
      <? } 
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->report); $indicator++) {
          $indicatori++;
       $user_master_id = $sms->report[$indicator]->user_master_id;
       $available_credits[] = $sms->report[$indicator]->available_credits;    
 $total_credits = $sms->report[$indicator]->total_credits;
 $user_name = $sms->report[$indicator]->user_name;    
 $used_credits = $sms->report[$indicator]->used_credits;
 $rights_name[] = $sms->report[$indicator]->rights_name;
}
      }
?>
  </form>


  <div class="search-element">
<div>
<? if($_SESSION['yjwatsp_user_master_id'] != '1'){ ?>
    <span class="badge badge-secondary" style="color:#FFF; font-size:18px; font-weight: bold; text-align: right;"><? echo $rights_name[0] .":". $available_credits[0] ?></span>
    <span class="badge badge-secondary" style="color:#FFF; font-size:18px; font-weight: bold; text-align: right;"><? echo $rights_name[1] .":". $available_credits[1] ?></span>
   <span class="badge badge-secondary" style="color:#FFF; font-size:18px; font-weight: bold; text-align: right;"><? echo $rights_name[2] .":". $available_credits[2] ?></span> 
<? }
 if ($_SESSION['yjwatsp_user_master_id'] != '5' && $_SESSION['yjwatsp_user_master_id'] != '1'){ ?>
   <span class="badge badge-secondary" style="color:#FFF; font-size:18px; font-weight: bold; text-align: right;"><? echo $rights_name[3] .":". $available_credits[3] ?></span>
   <? } ?>
</div>
  </div>

  <ul class="navbar-nav navbar-right">
    <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
        <img alt="image" src="assets/img/avatar/avatar-1.png" class="rounded-circle mr-1">
        <div class="d-sm-none d-lg-inline-block">Hi, <?= strtoupper($_SESSION['yjwatsp_user_name']) ?>
        </div>
      </a>

    <div class="dropdown-menu dropdown-menu-right">
          <a href="user_profiles" class="dropdown-item has-icon">
            <i class="fas fa-user"></i> On Boarding
          </a> 
        <a href="change_password" class="dropdown-item has-icon">
          <i class="fas fa-bolt"></i> Change Password
        </a>
        <div class="dropdown-divider"></div>
        <a href="logout" class="dropdown-item has-icon text-danger">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </li>
  </ul>
</nav>

