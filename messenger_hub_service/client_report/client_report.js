/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in report functions which is used to get summary and detailed report data from Whatsapp and SMS.
This page also have report generation for Whatsapp, SMS and RCS

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../db_connect/connect");
require("dotenv").config();
const main = require('../logger')
const json2csv = require('json2csv');
var axios = require('axios');
const fs = require('fs');
const valid_user_reqID = require("../validation/valid_user_middleware_reqID");
//Load Environment variables
const env = process.env
const DB_NAME = env.DB_NAME;
const fcm_key = env.NOTIFICATION_SERVER_KEY;
const media_storage = env.MEDIA_STORAGE;

// Start function to create a campaign report
async function cl_campaign_report(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  logger_all.info(" [campaign report] - " + req.body);
  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

  var day = new Date();
  var today_date = day.getFullYear() + '' + (day.getMonth() + 1) + '' + day.getDate();
  var today_time = day.getHours() + "" + day.getMinutes() + "" + day.getSeconds();
  var current_date = today_date + '_' + today_time;
  var campaign_id = req.body.campaign_id;
  try {

    // get all the req data
    let date_filter = req.body.date_filter;
    var filter_date_1 = [];
    if (date_filter) {
      filter_date_1 = date_filter.split("-");
    }
    else {
      const today = new Date();

      const lastWeekStart = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);

      const year1 = lastWeekStart.getFullYear();
      const month1 = String(lastWeekStart.getMonth() + 1).padStart(2, '0');
      const day1 = String(lastWeekStart.getDate()).padStart(2, '0');

      filter_date_1.push(`${year1}/${month1}/${day1}`);
      const year2 = today.getFullYear();
      const month2 = String(today.getMonth() + 1).padStart(2, '0');
      const day2 = String(today.getDate()).padStart(2, '0');

      filter_date_1.push(`${year2}/${month2}/${day2}`);

    }
    const report_query = `CALL testing_bluk('${req.body.user_id}', 'whatsapp','${filter_date_1[0]}','${filter_date_1[1]}','${campaign_id}')`;
    logger_all.info("[Select query request] : " + report_query);
    var select_campaign = await db.query(report_query);
    logger_all.info("[select query response - det_report_query] : " + JSON.stringify(select_campaign))

    if (select_campaign[0].length > 10000) {
      function response_status_label(status) {
        switch (status) {
          case 'Y':
            return 'Sent';
          case 'F':
            return 'Failed';
          default:
            return 'Yet to Send';
        }
      }
      function delivery_status_label(status) {
        switch (status) {
          case 'Y':
            return 'Delivered';
          default:
            return 'Not Delivered';
        }
      }
      function read_status_label(status) {
        switch (status) {
          case 'Y':
            return 'Read';
          default:
            return 'Not Read';
        }
      }
      // Modify your select_campaign data to include labels
      const modifiedData = select_campaign[0].map(item => ({
        ...item,
        response_status: response_status_label(item.response_status),
        delivery_status: delivery_status_label(item.delivery_status),
        read_status: read_status_label(item.read_status),
      }));
      const fields = [
        'campaign_name',
        'user_name',
        'cm_entry_date',
        'message_type',
        'total_mobile_no_count',
        'receiver_mobile_no',
        'sender_mobile_no',
        'com_msg_content',
        'media_url',
        'response_status',
        'response_date',
        'delivery_status',
        'delivery_date',
        'read_date',
        'read_status',
        'com_cus_msg_media',
      ];

      const opts = { fields };
      const csv = json2csv.parse(modifiedData, opts);
      fs.writeFileSync(`${media_storage}/uploads/detailed_report_csv/detailed_report_${current_date}.csv`, csv);
      logger.info("[SUCCESS API RESPONSE] " + 'CSV file has been saved successfully.');
      logger.info("[SUCCESS API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success ', file_location: `uploads/detailed_report_csv/detailed_report_${current_date}.csv` }))
      //send success response
      return { response_code: 200, response_status: 'Success', response_msg: 'Success ', num_of_rows: select_campaign[0].length, file_location: `uploads/detailed_report_csv/detailed_report_${current_date}.csv` };

    } else {
      //check if select campaign length is equal to zero, send error response as 'No data available'
      if (select_campaign[0].length === 0) {
        logger.info("[Failed response - No data available] : " + JSON.stringify({
          response_code: 0,
          response_status: 204,
          response_msg: 'No data available.'
        }))
        return {
          response_code: 204,
          response_status: 'Failure',
          response_msg: 'No data available.'
        };
      } else {
        //Otherwise send success response with campaign report data
        logger.info("[API SUCCESS RESPONSE] : " + JSON.stringify({
          response_code: 1,
          response_status: 200,
          response_msg: 'Success',
          num_of_rows: select_campaign[0].length,
          report: select_campaign[0]
        }))
        return {
          response_code: 200,
          response_status: 'Success',
          response_msg: 'Success',
          num_of_rows: select_campaign[0].length,
          report: select_campaign[0]
        };
      }
    }
  } catch (err) {
    logger_all.info("[campaign report] Failed - " + err);
    logger.info("[Failed response - Error Occurred] : " + JSON.stringify({
      response_code: 0,
      response_status: 201,
      response_msg: 'Error Occurred.'
    }))
    return {
      response_code: 201,
      response_status: 'Failure',
      response_msg: 'Error Occurred.'
    };
  }
}
// End function to create a campaign report


// Start function to create a summary report
async function cl_SummaryReport(req) {
  try {
    var logger_all = main.logger_all
    var logger = main.logger
    logger_all.info(" [summary report] - " + req.body);
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));

    // Get all the req header data
    const header_token = req.headers['authorization'];

    // get current Date and time
    var day = new Date();
    var today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();

    // get all the req filter data
    let date_filter = req.body.date_filter;
    let user_product = 'whatsapp';
    // declare the variables
    var user_id;
    var report_query = '';
    var newdb;
    var this_date = today_date;
    var getsummary;
    var get_summary_report;
    var filter_date_1;

    // declare the array
    var array_list_user_id = [];
    var total_response = [];
    var total_available_messages = [];
    var total_user_id = [];
    var total_user_master_id = [];
    var total_user_name = [];
    // Query parameters
    logger_all.info("[Otp summary report query parameters] : " + JSON.stringify(req.body));
    // To get the User_id
    var get_user = `SELECT * FROM user_management where user_bearer_token = '${header_token}' AND user_status = 'Y' `;
    if (req.body.user_id) {
      get_user = get_user + `and user_id = '${req.body.user_id}' `;
    }
    logger_all.info("[select query request - get user] : " + get_user);
    const get_user_id = await db.query(get_user);
    logger_all.info("[select query response - get user] : " + JSON.stringify(get_user_id));

    if (get_user_id.length == 0) { // If get_user not available send error response to client in ivalid token;
      logger_all.info("Invalid Token");
      logger.info("[Failed response - Invalid Token] : " + JSON.stringify({
        response_code: 0,
        response_status: 201,
        response_msg: 'Invalid Token'
      }))
      return {
        response_code: 201,
        response_status: 'Failure',
        response_msg: 'Invalid Token'
      };
    } else { // otherwise to get the user details
      user_id = get_user_id[0].user_id;
      user_master_id = get_user_id[0].user_master_id;
    }
    var filter_condition = ` `;
    var grp_campaign = '';

    // To initialize a variable with an empty string value
    get_summary_report = ``;

    // To get the product_id
    var get_product_id = await db.query(`SELECT * FROM rights_master where rights_name = '${user_product}' AND rights_status = 'Y'`);
    logger_all.info("[select query request - get product] : " + get_product_id);
    logger_all.info("[select query response - get product] : " + JSON.stringify(get_product_id));

    // if the filter_date is empty and store_id_filter is empty to execute the this condition
    if (!date_filter) {
      get_summary_report = `SELECT
 usr.user_name,
 wht.campaign_name,
 DATE_FORMAT(wht.com_entry_date, '%d-%m-%Y') AS entry_date,
 wht.total_msg,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_waiting ELSE 0 END) AS total_waiting,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_process ELSE 0 END) AS total_process,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_success ELSE 0 END) AS total_success,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_failed ELSE 0 END) AS total_failed,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_delivered ELSE 0 END) AS total_delivered,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_read ELSE 0 END) AS total_read
 FROM
 ${DB_NAME}.user_summary_report wht

 LEFT JOIN
 ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id
 LEFT JOIN
 ${DB_NAME}.user_master ussr ON usr.user_master_id = ussr.user_master_id
 WHERE
 (usr.user_id = '${user_id}' or usr.parent_id in (${user_id}))
 AND (DATE(wht.com_entry_date) BETWEEN '${this_date}' AND '${this_date}')
 AND wht.product_id = '${get_product_id[0].rights_id}' ${filter_condition}
 GROUP BY
 campaign_name
 ORDER BY
 wht.com_entry_date DESC`;
      logger_all.info('[select query request - get summary report]' + get_summary_report);
      getsummary = await db.query(get_summary_report, null, `${DB_NAME}_${user_id}`);
      // if the getsummary length is not available to push the my obj datas.otherwise it will be return the push the getsummary details.
      if (getsummary.length == 0) {

        var res_date = moment().format('DD-MM-YYYY');
        var myObj = [{
          "entry_date": res_date,
          "user_name": get_user_id[0].user_name,
          "total_msg": 0,
          "total_success": 0,
          "total_failed": 0,
          "total_invalid": 0,
          "total_waiting": 0,
          "total_delivered": 0,
          "total_read": 0
        }]
        total_response.push(myObj);
      } else {
        total_response.push(getsummary);
      }
      logger_all.info("[select query response - get summary report] : " + JSON.stringify(getsummary))
    }

    if (date_filter) {
      // date function for looping in one by one date
      filter_date_1 = date_filter.split("-");
      report_query = `SELECT
 usr.user_name,
 wht.campaign_name,
 DATE_FORMAT(wht.com_entry_date, '%d-%m-%Y') AS entry_date,
 wht.total_msg,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_waiting ELSE 0 END) AS total_waiting,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_process ELSE 0 END) AS total_process,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_success ELSE 0 END) AS total_success,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_failed ELSE 0 END) AS total_failed,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_delivered ELSE 0 END) AS total_delivered,
 (CASE WHEN wht.report_status = 'N' THEN wht.total_read ELSE 0 END) AS total_read
 FROM
 ${DB_NAME}.user_summary_report wht

 LEFT JOIN
 ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id
 LEFT JOIN
 ${DB_NAME}.user_master ussr ON usr.user_master_id = ussr.user_master_id
 WHERE
 (usr.user_id = '${user_id}' or usr.parent_id in (${user_id}))
 AND (DATE(wht.com_entry_date) BETWEEN '${filter_date_1[0]}' AND '${filter_date_1[1]}')
 AND wht.product_id = '${get_product_id[0].rights_id}' ${filter_condition} group by campaign_name order by wht.com_entry_date desc `;

      logger_all.info('[select query request] : ' + report_query);
      getsummary = await db.query(report_query, null, `${DB_NAME}_${user_id}`);
      logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(getsummary));
      total_response = getsummary;

    } //filter date condition

    // getsummary length is '0'.to send the Success message and to send the total_response datas.
    logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(total_response));
    if (getsummary == 0) {
      logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify({
        response_code: 1,
        response_status: 200,
        response_msg: 'Success',
        report: total_response
      }))
      return {
        response_code: 200,
        response_status: 'Success',
        response_msg: 'Success',
        report: total_response
      };
    } else { //otherwise to send the success message and get summarydetails
      logger.info("[API SUCCESS RESPONSE - get summary] : " + JSON.stringify({
        response_code: 1,
        response_status: 200,
        response_msg: 'Success',
        report: total_response

      }))
      return {
        response_code: 200,
        response_status: 'Success',
        response_msg: 'Success',
        report: total_response
      };
    }
  } catch (e) { // any error occurres send error response to client
    logger_all.info("[summary report - error] : " + e)

    logger.info("[Failed response - Error occured] : " + JSON.stringify({
      response_code: 0,
      response_status: 201,
      response_msg: 'Error occured'
    }))
    return {
      response_code: 201,
      response_status: 'Failure',
      response_msg: 'Error occured'
    };
  }
}
// End function to create a summary report


// Start function to generate a whatsapp report
async function cl_report_generation(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  //Get request data
  var receiver_number = req.body.receiver_number;
  var compose_id = req.body.campaign_id;
  var user_id = req.body.user_id;
  var request_id = req.body.request_id;
  var sender_numbers_active = [];
  var sender_numbers_inactive = [];

  try {

    //Query to select sender mobile number
    var report_query = `SELECT DISTINCT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y'`

    //check if receiver number available
    if (receiver_number) {
      report_query = `SELECT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y' AND receiver_mobile_no = '${receiver_number}'`;
      compose_id = compose_id + "-" + receiver_number;
    }
    logger_all.info("[Select query request - sender number] : " + report_query);
    var select_campaign = await db.query(report_query);
    logger_all.info("[Select query response - sender number] : " + JSON.stringify(select_campaign))

    //check if select campaign is equal to zero, send failure response as 'No data available'
    if (select_campaign.length == 0) {
      //update_api_log
      logger_all.info("[update query request - No data available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response -No data available] : " + JSON.stringify(update_api_log))
      return {
        response_code: 204,
        response_status: 'Failure',
        response_msg: 'No data available.',
        request_id: req.body.request_id
      };
    }

    //Otherwise continue notification process

    //loop for get sender number based on compose id

    for (var i = 0; i < select_campaign.length; i++) {
      var sender_mobile_no_sms = select_campaign[i].sender_mobile_no;
      logger_all.info("sender_mobile_no_sms " + sender_mobile_no_sms)

      //Query to get active sender ID
      var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_mobile_no_sms}' AND sender_id_status = 'Y' AND is_qr_code ='N'`

      logger_all.info("[Select query request - Active sender ID] : " + senderID_active);
      var select_sender_id_active = await db.query(senderID_active);
      logger_all.info("[Select query response - Active sender ID] : " + JSON.stringify(select_sender_id_active))
      //check if select_sender_id_active length is not equal to zero, store active numbers to sender_numbers_active array

      if (select_sender_id_active.length != 0) {
        sender_numbers_active.push(select_sender_id_active[0].mobile_no)
      }

      //Otherwise store inactive numbers to sender_numbers_inactive array
      else {
        logger_all.info("sender_numbers_inactive_here " + sender_numbers_inactive)
        sender_numbers_inactive.push(select_campaign[i].sender_mobile_no)
      }

    }
    //check if sender_numbers_active lenght is equal to zero, send error response as 'No Sender ID available'

    if (sender_numbers_active.length == 0) {
      //update_api_log
      logger_all.info("[update query request - No Sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response - No Sender ID available] : " + JSON.stringify(update_api_log))
      logger.info("[Failure Response] " + JSON.stringify({
        response_code: 0,
        response_status: 201,
        response_msg: 'No Sender ID available',
        request_id: req.body.request_id
      }))
      return {
        response_code: 201,
        response_status: 'Failure',
        response_msg: 'No Sender ID available',
        request_id: req.body.request_id
      };
    }

    //Loop for active sender numbers

    for (var i = 0; i < sender_numbers_active.length; i++) {
      logger_all.info("sender_numbers_active" + sender_numbers_active[i])

      //Query to get device token to send push notification
      var device_query = `SELECT device_token FROM sender_id_master WHERE mobile_no='${sender_numbers_active[i]}' AND sender_id_status='Y'`
      logger_all.info("[Select query request - get device token] : " + device_query);
      var device_query_result = await db.query(device_query);
      logger_all.info("[Select query response - get device token] : " + JSON.stringify(device_query_result))
      logger_all.info("device token" + device_query_result[0].device_token)

      //send push notification

      var data = JSON.stringify({
        "to": device_query_result[0].device_token,
        "priority": "high",
        "data": {
          "title": compose_id,
          "selected_user_id": user_id,
          "priority": "high",
          "content-available": true,
          "bodyText": "WTSP_Report"
        }
      });

      var config = {
        method: 'post',
        url: 'https://fcm.googleapis.com/fcm/send',
        headers: {
          'Authorization': fcm_key,
          'Content-Type': 'application/json'
        },
        data: data
      };

      logger_all.info(JSON.stringify(config));
      await axios(config)
        .then(function (response) {
          logger_all.info(JSON.stringify(response.data));
        })
        .catch(function (error) {
          logger_all.info(error);
        });
    }


    for (var i = 0; i < sender_numbers_active.length; i++) {

      var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no='${sender_numbers_active[i]}'`

      logger_all.info("[update query request] : " + update_sender_sts);
      const update_sender_sts_result = await db.query(update_sender_sts);
      logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
    }

    //update_api_log
    logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
    return {
      response_code: 200,
      response_status: 'Success',
      response_msg: 'Success',
      request_id: req.body.request_id,
      Inactive_senderID: sender_numbers_inactive
    };

  } catch (err) {
    //Otherwise send failure response 'Error occurred'
    logger_all.info("[sms report generation] Failed - " + err);
    //update_api_log
    logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))
    return {
      response_code: 201,
      response_status: 'Failure',
      response_msg: 'Error Occurred.',
      request_id: req.body.request_id
    };
  }
}
// End function to generate a whatsapp report

module.exports = {
  cl_campaign_report,
  cl_SummaryReport,
  cl_report_generation
};