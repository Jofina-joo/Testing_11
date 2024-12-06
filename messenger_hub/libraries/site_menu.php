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

site_log_generate("Site Menu Page : " . $_SESSION['yjwatsp_user_name'] . " Access on " . date("Y-m-d H:i:s"), '../');
 site_log_generate("Site Menu Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');

      $bearer_token = 'Authorization: '.$_SESSION['yjwatsp_bearer_token'].''; 
      $replace_txt = '{
      "user_id" : "'.$_SESSION['yjwatsp_user_id'].'"    }';
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url . '/site_menu/product_menu',
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
     // site_log_generate("Site Menu Page : " . $_SESSION['yjwatsp_user_name'] . " Execute the service [$replace_txt] on " . date("Y-m-d H:i:s"), '../');
      $response = curl_exec($curl);
      curl_close($curl);
      $sms = json_decode($response, false);
      site_log_generate("Site Menu Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
 if ($response == '') { ?>
                                    <script>window.location="logout"</script>
                  <? } else if ($sms->response_status == 403) { ?>
        <script>window.location="logout"</script>
      <? } 
      // print_r($sms); exit;
      $indicatori = 0;
      if ($sms->response_status == 200) {
        for ($indicator = 0; $indicator < count($sms->menu_list); $indicator++) {
          $indicatori++;
       $user_master_id = $sms->menu_list[$indicator]->user_master_id;
       $rights_name[] = $sms->menu_list[$indicator]->rights_name;    
 $rights_id[] = $sms->menu_list[$indicator]->rights_id;
 $logo_media = $sms->menu_list[$indicator]->logo_media;
}

}


$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $api_url . '/list/waiting_approvals',
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
  ),) );
$response = curl_exec($curl);
curl_close($curl);
$sms = json_decode($response, false);
site_log_generate("Site Menu Page : " . $_SESSION['yjwatsp_user_name'] . " get the Service response [$response] on " . date("Y-m-d H:i:s"), '../');
if ($response == '') { ?>
<script>
window.location = "logout"
</script>
<? } else if ($sms->response_status == 403) { ?>
<script>
window.location = "logout"
</script>
<? } 
$indicatori = 0;
if ($sms->response_status == 200) {
  for ($indicator = 0; $indicator < count($sms->result); $indicator++) {
    $indicatori++;
 $WaitingCounts[] = $sms->result[$indicator]->WaitingCounts;
}
}

?>

<div class="main-sidebar sidebar-style-2">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="dashboard">
       <?php if($logo_media){ ?>
        <img src= "<?= $logo_media ?>" style="height:100%;margin-top:10px;" />
       <? }else{ ?>
        <img src="assets/img/cm-logo.png" style="height:100%" />
        <? } ?> 
     </a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
      <a href="dashboard"><img src="assets/img/cm.png" style="height:100%" /></a>
    </div>
    <ul class="sidebar-menu">
                  <!-- Dashboard Menu -->
      <li <? if ($site_page_name == 'dashboard') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="dashboard" class="nav-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
      </li>
                    <!-- ADD SENSERID MENU -->
      <? if($_SESSION['yjwatsp_user_master_id'] == '1'){?>                     
      <li <? if ($site_page_name == 'approve_whatsapp_no' or $site_page_name == 'manage_senderid_list' or $site_page_name == 'add_senderid') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i>
          <span>Sender ID</span></a>
        <ul class="dropdown-menu">
            <!---<li <? if ($site_page_name == 'add_senderid') { ?>class="active" <? } ?>><a class="nav-link" href="add_senderid">Add Sender ID</a></li>--->
            <li <? if ($site_page_name == 'manage_senderid_list') { ?>class="active" <? } ?>><a class="nav-link" href="manage_senderid_list">Sender ID List</a></li>
        </ul>
      </li>
      <? } ?>
      <? if($_SESSION['yjwatsp_user_master_id'] == '1' || $_SESSION['yjwatsp_user_master_id'] == '2'){?>
      <!-- Approve Payment MENU -->
      <li <? if ($site_page_name == 'approve_payment' or $site_page_name == 'message_credit_list' ) { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-crown"></i>
          <span>Admin</span></a>
        <ul class="dropdown-menu">
            <li <? if ($site_page_name == 'approve_payment') { ?>class="active" <? } ?>><a class="nav-link" href="approve_payment">Approve Payment</a></li>
            <li <? if ($site_page_name == 'message_credit_list') { ?>class="active" <? } ?>><a class="nav-link" href="message_credit_list">Message Credit List</a></li>
        </ul>
      </li>
    <? } ?>
     <? if($_SESSION['yjwatsp_user_master_id'] == '1'){?>
       <!-- App Details MENU -->
      <li <? if ($site_page_name == 'app_upload' or $site_page_name == 'app_details_list' ) { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-upload"></i>
          <span>App Details</span></a>
        <ul class="dropdown-menu">
            <li <? if ($site_page_name == 'app_upload') { ?>class="active" <? } ?>><a class="nav-link" href="app_upload">App Upload</a></li>
            <li <? if ($site_page_name == 'app_details_list') { ?>class="active" <? } ?>><a class="nav-link" href="app_details_list">App Details  List</a></li>
        </ul>
      </li>
       <!-- CAMPAIGN LIST MENU -->
   <?/* <li <? if ($site_page_name == 'approve_campaign_list_sms' || $site_page_name == 'approve_campaign_whatsapp' || $site_page_name == 'approve_campaign_rcs'  || $site_page_name == 'approve_campaign_obd' || $site_page_name == 'approve_prompt_obd' ) { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa-list-alt"></i>
          <span>Approve Campaign List</span></a>
        <ul class="dropdown-menu ">
      <li <? if ($site_page_name == 'approve_campaign_whatsapp') { ?>class="active" <? } ?>><a class="nav-link" href="approve_campaign_whatsapp"> Whatsapp Campaign List</a></li>
              <li <? if ($site_page_name == 'approve_campaign_list_sms') { ?>class="active" <? } ?>><a class="nav-link" href="approve_campaign_list_sms">SMS Campaign List</a></li>
              <li <? if ($site_page_name == 'approve_campaign_rcs') { ?>class="active" <? } ?>><a class="nav-link" href="approve_campaign_rcs"> RCS Campaign List</a></li>
                  <li <? if ($site_page_name == 'approve_campaign_obd') { ?>class="active" <? } ?>><a class="nav-link" href="approve_campaign_obd"> OBD Campaign List</a></li>
              <li <? if ($site_page_name == 'approve_prompt_obd') { ?>class="active" <? } ?>><a class="nav-link" href="approve_prompt_obd"> OBD Prompt List</a></li>
        </ul>
      </li>*/?>
     <!-- CAMPAIGN LIST MENU -->
            <li <? if ($site_page_name=='approve_campaign_list_sms' || $site_page_name=='approve_campaign_whatsapp' ||
                $site_page_name=='approve_campaign_rcs' || $site_page_name=='approve_campaign_obd' ||
                $site_page_name=='approve_prompt_obd' ) { ?>class="dropdown active"
                <? } else { ?>class="dropdown"
                <? } ?>>
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa-list-alt"></i>
                    <span>Approve Campaigns <?= (!empty($WaitingCounts) && $WaitingCounts[0] > 0) ? '<span class="badge badge-success" style="width: auto;">' . $WaitingCounts[0] + $WaitingCounts[1] + $WaitingCounts[2] + $WaitingCounts[3] + $WaitingCounts[4]. '</span>' : '' ?></span></a>
                <ul class="dropdown-menu ">
                    <li <? if ($site_page_name=='approve_campaign_whatsapp' ) { ?>class="active"
                        <? } ?>><a class="nav-link" href="approve_campaign_whatsapp"> Whatsapp List<?= (!empty($WaitingCounts) && $WaitingCounts[0] > 0) ? '<span class="badge badge-success" style="width: auto;">' . $WaitingCounts[0] . '</span>' : '' ?></a>
                      </li>
                    <li <? if ($site_page_name=='approve_campaign_list_sms' ) { ?>class="active"
                        <? } ?>><a class="nav-link" href="approve_campaign_list_sms">SMS List <?= (!empty($WaitingCounts) && $WaitingCounts[1] > 0) ? '<span class="badge badge-success" style="width: auto;">' . $WaitingCounts[1] . '</span>' : '' ?></a>
                    </li>
                    <li <? if ($site_page_name=='approve_campaign_rcs' ) { ?>class="active"
                        <? } ?>><a class="nav-link" href="approve_campaign_rcs"> RCS List<?= (!empty($WaitingCounts) && $WaitingCounts[2] > 0) ? '<span class="badge badge-success" style="width: auto;">' . $WaitingCounts[2] . '</span>' : '' ?></a>
                    </li>
                    <li <? if ($site_page_name=='approve_campaign_obd' ) { ?>class="active"
                        <? } ?>><a class="nav-link" href="approve_campaign_obd"> OBD List<?= (!empty($WaitingCounts) && $WaitingCounts[3] > 0) ? '<span class="badge badge-success" style="width: auto;">' . $WaitingCounts[3] . '</span>' : '' ?></a>
                    </li>
                    <li <? if ($site_page_name=='approve_prompt_obd' ) { ?>class="active"
                        <? } ?>><a class="nav-link" href="approve_prompt_obd"> OBD Prompt List<?= (!empty($WaitingCounts) && $WaitingCounts[4] > 0) ? '<span class="badge badge-success" style="width: auto;">' . $WaitingCounts[4] . '</span>' : '' ?></a>
                    </li>
                </ul>
            </li>

   <!--Process CAMPAIGN LIST MENU -->
     <li <? if ($site_page_name == 'campaign_list_process_sip') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-hourglass-half"></i>
          <span>Process Campaign List</span></a>
        <ul class="dropdown-menu ">
              <li <? if ($site_page_name == 'campaign_list_process_sip') { ?>class="active" <? } ?>><a class="nav-link" href="campaign_list_process_sip">OBD CALL SIP List</a></li>
        </ul>
      </li> 

<!-- Contacts MENU -->
      <?/* <li <? if ($site_page_name == 'contacts') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="contacts" class="nav-link"><i class="fas fa-user"></i><span>Contacts</span></a>
       </li>  */?>
<!-- Users List MENU -->
       <li <? if ($site_page_name == 'users_list') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="users_list" class="nav-link"><i class="fas fa-user-check"></i><span>Manage Users List</span></a>
       </li>
<? }?>

          <!-- Purchase Credits -->
<? if($_SESSION['yjwatsp_user_master_id'] == '2' || $_SESSION['yjwatsp_user_master_id'] == '3'){?>              
        <li <? if ($site_page_name == 'purchase_message_credit' || $site_page_name == 'purchase_message_list' ) { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
          <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa-list-alt"></i>
            <span>Purchase Credits</span></a>
          <ul class="dropdown-menu ">
            <li <? if ($site_page_name == 'purchase_message_credit') { ?>class="active" <? } ?>><a class="nav-link" href="purchase_message_credit">Purchase Message Credit</a></li>
            <li <? if ($site_page_name == 'purchase_message_list') { ?>class="active" <? } ?>><a class="nav-link" href="purchase_message_list">Purchase message List</a></li>
            </ul>
          </li> <? } ?>
<? if($_SESSION['yjwatsp_user_master_id'] != '5'){ ?>
             <!-- Whatsapp MENU -->
       <li <? if ($site_page_name == 'compose'  or $site_page_name == 'summary_report' or $site_page_name == 'report_generation' or $site_page_name == 'detailed_report' or $site_page_name == 'campaign_whatsapp_list') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fab fa-whatsapp"></i>
          <span><?=$rights_name[0] ?></span></a>
        <ul class="dropdown-menu ">
            <li <? if ($site_page_name == 'compose') { ?>class="active" <? } ?>><a class="nav-link" href="compose">Compose </a></li>
            <li <? if ($site_page_name == 'summary_report' or $site_page_name == 'detailed_report' or $site_page_name == 'report_generation' or $site_page_name == 'campaign_list_stop') { ?>class="dropdown active " <? } else { ?>class="dropdown" <? } ?>>
            <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-bar"></i> <span>Reports</span></a>
         <ul class="dropdown-menu">
            <li <? if ($site_page_name == 'summary_report') { ?>class="active" <? } ?>><a class="nav-link" href="summary_report">Summary Report</a></li>

            <li <? if ($site_page_name == 'detailed_report') { ?>class="active" <? } ?>><a class="nav-link" href="detailed_report">Detailed Report</a></li>
<? if($_SESSION['yjwatsp_user_master_id'] == '1'){?>
            <li <? if ($site_page_name == 'report_generation') { ?>class="active" <? } ?>><a class="nav-link" href="report_generation">Report Generate</a></li>
<? } ?>    
          </ul>     
         </li>
        </ul>
      </li>
            <!-- SMS MENU -->
      <li <? if ($site_page_name == 'compose_sms' or $site_page_name == 'compose_sms_summary_rp' or $site_page_name == 'compose_sms_detailed_rp' or $site_page_name == 'campaign_sms_list' or $site_page_name == 'report_generation_sms') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-comment"></i>
          <span><?=$rights_name[1] ?></span></a>
        <ul class="dropdown-menu">
            <li <? if ($site_page_name == 'compose_sms') { ?>class="active" <? } ?>><a class="nav-link" href="compose_sms">Compose </a></li>
    <li <? if ($site_page_name == 'compose_sms_summary_rp'  or $site_page_name == 'compose_sms_detailed_rp' or $site_page_name == 'report_generation_sms') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-area"></i> <span> Reports</span></a>
        <ul class="dropdown-menu">
        <li <? if ($site_page_name == 'compose_sms_summary_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_sms_summary_rp">Summary Report</a></li>
            <li <? if ($site_page_name == 'compose_sms_detailed_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_sms_detailed_rp">Detailed Report</a></li>
<?  if($_SESSION['yjwatsp_user_master_id'] == '1'){?>
            <li <? if ($site_page_name == 'report_generation_sms') { ?>class="active" <? } ?>><a class="nav-link" href="report_generation_sms">Report Generate</a></li>
<? } ?>
</ul>
      </li>    
        </ul>
      </li>
 <!-- RCS SMS MENU LIST  -->
      <li <? if ($site_page_name == 'compose_rcs' or $site_page_name == 'compose_rcs_summary_rp' or $site_page_name == 'compose_rcs_detailed_rp' or $site_page_name == 'campaign_sms_list' or $site_page_name == 'report_generation_rcs') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-comments"></i>
          <span><?=$rights_name[2] ?></span></a>
        <ul class="dropdown-menu">
            <li <? if ($site_page_name == 'compose_rcs') { ?>class="active" <? } ?>><a class="nav-link" href="compose_rcs">Compose </a></li>
    <li <? if ($site_page_name == 'compose_rcs_summary_rp'  or $site_page_name == 'compose_rcs_detailed_rp' or $site_page_name == 'report_generation_rcs') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-line" ></i> <span> Reports</span></a>
        <ul class="dropdown-menu">
        <li <? if ($site_page_name == 'compose_rcs_summary_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_rcs_summary_rp">Summary Report</a></li>
            <li <? if ($site_page_name == 'compose_rcs_detailed_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_rcs_detailed_rp">Detailed Report</a></li>
<? if($_SESSION['yjwatsp_user_master_id'] == '1'){?>
            <li <? if ($site_page_name == 'report_generation_rcs') { ?>class="active" <? } ?>><a class="nav-link" href="report_generation_rcs">Report Generate</a></li>
<? } ?>
</ul>
      </li>       
        </ul>
      </li>


<? } ?>
<!-- OBD CALL MENU LIST  -->
<li <? if ($site_page_name == 'compose_prompt' or $site_page_name == 'compose_obd_prompt_list' or  $site_page_name == 'compose_obd' or $site_page_name == 'compose_obd_campaign_list' or $site_page_name == 'compose_obd_callholding_rp' or $site_page_name == 'compose_obd_summary_rp' or $site_page_name == 'compose_obd_detailed_rp' or $site_page_name == 'campaign_sms_list' or $site_page_name == 'report_generation_obd') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa fa-phone"></i>
      <span><?=$rights_name[3] ?></span></a>
    <ul class="dropdown-menu">
        
        <li <? if ($site_page_name == 'compose_prompt' or $site_page_name == 'compose_obd_prompt_list'  or $site_page_name == 'approve_prompt_obd') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
            <a href="#" class="nav-link has-dropdown"><i class="fas fa-volume-up"></i> <span>Prompt</span></a>
            <ul class="dropdown-menu">
                <li <? if ($site_page_name == 'compose_prompt') { ?>class="active" <? } ?>><a class="nav-link" href="compose_prompt">Create Prompt</a></li>
                <li <? if ($site_page_name == 'compose_obd_prompt_list') { ?>class="active" <? } ?>><a class="nav-link" href="compose_obd_prompt_list">Prompt List</a></li>
            </ul>
        </li>
        
        <li <? if ($site_page_name == 'compose_obd' or $site_page_name == 'compose_obd_campaign_list') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
            <a href="#" class="nav-link has-dropdown"><i class="fas fa-bullhorn"></i> <span>Campaign</span></a>
            <ul class="dropdown-menu">
                <li <? if ($site_page_name == 'compose_obd') { ?>class="active" <? } ?>><a class="nav-link" href="compose_obd">Compose Campaign</a></li>
                <li <? if ($site_page_name == 'compose_obd_campaign_list') { ?>class="active" <? } ?>><a class="nav-link" href="compose_obd_campaign_list">Campaign List</a></li>
            </ul>
        </li>
        
        <li <? if ($site_page_name == 'compose_obd_callholding_rp' or $site_page_name == 'compose_obd_summary_rp'  or $site_page_name == 'compose_obd_detailed_rp' or $site_page_name == 'report_generation_obd') { ?>class="dropdown active" <? } else { ?>class="dropdown" <? } ?>>
            <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
            <ul class="dropdown-menu">
                <li <? if ($site_page_name == 'compose_obd_callholding_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_obd_callholding_rp">Call Holding Report</a></li>
                <li <? if ($site_page_name == 'compose_obd_summary_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_obd_summary_rp">Summary Report</a></li>
                <li <? if ($site_page_name == 'compose_obd_detailed_rp') { ?>class="active" <? } ?>><a class="nav-link" href="compose_obd_detailed_rp">Detail Report</a></li>
                <? if($_SESSION['yjwatsp_user_master_id'] == '1'){?>
                    <li <? if ($site_page_name == 'report_generation_obd') { ?>class="active" <? } ?>><a class="nav-link" href="report_generation_obd">CDR Report Generate</a></li>
                <? } ?>
            </ul>
        </li>
    </ul>
</li>

    </ul>
  </aside>
</div>

