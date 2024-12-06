/*
This kotlin file is used to get API URL from server

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/

package com.example.background_app

class ApiUrl {
  //  val LiveUrl = "http://yjtec.in:10022"; // yjtec server url
   // val LiveUrl = "http://yourpostman.in:10022"; // yourpostman server url
  val LiveUrl = "http://192.168.29.244:10023"; // yourpostman server url
//  val LiveUrl = "http://192.168.95.211:10023"; // yourpostman server url
  //   val LiveUrl = "http://yourpostman.in:10050"; // server url
   //val LiveUrl = "http://192.168.112.119:10022"; //local url
   // val LiveUrl = "http://yj360.in:10022";
/*------------- api with authorization--------------------- */
    val mobile_login = "/mobile_login";
    val get_task_sms = "/sms/get_task_sms";
    val get_task = "/wtsp/get_task";
    val get_task_rcs = "/rcs/get_task_rcs";
    val get_task_block = "/wtsp/get_task_block";
    val get_report = "/wtsp/get_report";
    val update_task_sms = "/sms/update_task_sms";
    val update_report = "/wtsp/update_report";
    val update_task = "/wtsp/update_task";
    val update_task_stop = "/wtsp/update_task_stop";
    val update_block_sts = "/wtsp/update_block_sts";
    val update_report_block = "/wtsp/update_report_block";
    val update_task_version = "/app_update/update_task_version";
    val update_task_rcs = "/rcs/update_task_rcs";
    val update_task_block = "/wtsp/update_task_block";
    val get_report_sms = "/sms/get_report_sms";
    val update_report_sms = "/sms/update_report_sms";
    val get_report_rcs = "/rcs/get_report_rcs";
    val update_report_rcs = "/rcs/update_report_rcs";
    val logout = "/logout/mobile_logout";
}
