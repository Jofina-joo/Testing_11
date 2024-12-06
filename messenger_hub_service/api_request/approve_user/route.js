/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the approve user page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const express = require("express");
const router = express.Router();
require("dotenv").config();
const main = require('../../logger');
const db = require("../../db_connect/connect");
var axios = require('axios');
const fs = require('fs');
const csv = require('csv-parser');
const env = process.env
const DB_NAME = env.DB_NAME;
const fcm_key = env.NOTIFICATION_SERVER_KEY;
const moment = require('moment');
const uuid = require('uuid');
const path = require('path');
const CampaignList = require("./campaign_lt");
const RejectCampaign = require("./reject_campaign");
var admin = require("firebase-admin");
var serviceAccount = require('../../watsp-app-firebase-adminsdk-nhc22-5a94b7667c.json');

// Validation file using 
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
const CampaignListValidation = require("../../validation/campaign_list_validation");
const ApproveUserValidation = require("../../validation/approve_user_validation");
const ApproveWtspValidation = require("../../validation/approve_wtsp_validation");
const RejectCampaignValidation = require("../../validation/reject_campaign_validation");
const ApproveOBD_SIPValidation = require("../../validation/approve_obd_sip_validation");


//Start Route - Campaign list
router.get(
  "/campaign_lt",
  validator.body(CampaignListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await CampaignList.campaign_lt(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Campaign list

// Start Route - Approve RCS
router.post(
  "/approve_rcs",
  validator.body(ApproveUserValidation),
  valid_user_reqID,
  async function (req, res, next) {
    var logger_all = main.logger_all
    var logger = main.logger
    try {

      //Get all request data
      var compose_message_id = req.body.compose_message_id;
      var sender_numbers = req.body.sender_numbers;
      var user_id = req.body.selected_user_id;
      var request_id = req.body.request_id;
      var rec_count = 0;
      var total_count = 0;
      var insert_count = 1;
      var sender_numbers_active = [];
      var sender_numbers_inactive = [];
      var sender_id_active = [];
      var sender_devicetoken_active = [];
      var variable_value = [];
      let isFirstRow = true;
      is_check = false;
      const variable_values = [];
      const valid_mobile_numbers = [];
      const invalid_mobile_numbers = [];
      var media_url_csv;
      var media_url = [];
      const duplicateMobileNumbers = new Set();
      user_id_check = req.body.user_id;

      //Check if user is admin
      if (user_id_check == 1) {

        //Query to get data based on compose id
        var get_product = `SELECT * FROM ${DB_NAME}_${user_id}.compose_message_${user_id} where user_id = '${user_id}' AND compose_message_id = '${compose_message_id}'`;
        logger_all.info("[select query request] : " + get_product);
        const get_user_det = await db.query(get_product);
        logger_all.info("[select query response] : " + JSON.stringify(get_user_det));
        var get_compose_data = `SELECT * FROM ${DB_NAME}_${user_id}.compose_msg_media_${user_id} where compose_message_id = '${compose_message_id}'`;
        logger_all.info("[select query request] : " + get_compose_data);
        const get_compose_data_result = await db.query(get_compose_data);
        logger_all.info("[select query response] : " + JSON.stringify(get_compose_data_result));

        //Check if selected data is equal to zero, send failuer response 'Compose ID Not Available'
        if (get_user_det.length == 0 || get_compose_data_result == 0) {
          const update_api = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Compose ID Not Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
          logger_all.info("[update query request - compose ID not available] : " + update_api);
          const update_api_log = await db.query(update_api);
          logger_all.info("[update query response - compose ID not available] : " + JSON.stringify(update_api_log))
          const composeid_msg = { response_code: 0, response_status: 201, response_msg: 'Compose ID Not Available', request_id: req.body.request_id }
          logger.info("[API RESPONSE] " + JSON.stringify(composeid_msg))
          logger_all.info("[compose ID not available] : " + JSON.stringify(composeid_msg))
          return res.json(composeid_msg);
        }
        mobile_no_cnt = get_user_det[0].total_mobile_no_count;

        //Check if sender numbers length is greater than total mobile number count, send failure response 'Sender Numbers should be less than Receiver Numbers'
        if (sender_numbers.length > mobile_no_cnt) {
          const failure_msg = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender Numbers should be less than Receiver Numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`;
          logger_all.info("[update query request - Sender Numbers should be less than Receiver Numbers] : " + failure_msg);
          const update_api_log = await db.query(failure_msg);
          logger_all.info("[update query response -Sender Numbers should be less than Receiver Numbers] : " + JSON.stringify(update_api_log))
          const sender_msg = { response_code: 0, response_status: 201, response_msg: 'Sender Numbers should be less than Receiver Numbers' };
          logger.info("[API RESPONSE] " + JSON.stringify(sender_msg))
          return res.json(sender_msg);
        }

        //Get Required data from query
        receiver_nos_path = get_user_det[0].receiver_nos_path;
        product_id_one = get_user_det[0].product_id;
        message_type = get_user_det[0].campaign_type;
        variable_count = get_user_det[0].variable_count;
        is_same_msg = get_user_det[0].is_same_msg;
        is_same_media_flag = get_user_det[0].is_same_media;
        messages = get_compose_data_result[0].text_title;

        if (is_same_media_flag == "true") {
          media_url = get_compose_data_result[0].media_url;
        }
        logger.info("media_url " + media_url)
        logger.info("is_same_media_flag " + is_same_media_flag)
        logger.info("is_same_msg " + is_same_msg)
        logger.info("message_type " + message_type)

        if (receiver_nos_path != '-') {

          // Fetch the CSV file
          fs.createReadStream(receiver_nos_path)

            // Read the CSV file from the stream
            .pipe(csv({
              headers: false
            }))

            // Set headers to false since there are no column headers
            .on('data', (row) => {
              if (Object.values(row).every(value => value === '')) {
                return;
              }

              const firstColumnValue = row[0].trim();

              // Validate mobile number format
              const isValidFormat = /^\d{12}$/.test(firstColumnValue) && firstColumnValue.startsWith('91') && /^[6-9]/.test(firstColumnValue.substring(2, 3));

              // Check for duplicates
              if (duplicateMobileNumbers.has(firstColumnValue)) {
                invalid_mobile_numbers.push(firstColumnValue);
              } else {
                duplicateMobileNumbers.add(firstColumnValue);
                if (isValidFormat) {
                  valid_mobile_numbers.push(firstColumnValue);

                  // Create a new array for each row
                  const otherColumnsArray = [];
                  let secondColumnValue;
                  for (let i = 1; i < Object.keys(row).length; i++) {

                    // Skip processing if the mobile number is invalid
                    if (!isValidFormat) {
                      break;
                    }
                    logger_all.info(Object.keys(row).length);

                    if ((is_same_media_flag === "false") && (is_same_msg === "false") && (message_type === 'VIDEO' || message_type === 'IMAGE') && i == 1) {
                      // Check if the second column value is not empty and equal to the current column value
                      //                      if (row[1].trim() && row[1].trim() === row[i].trim()) {
                      logger_all.info('Values in the second column and column ' + (i + 1) + ' are equal');
                      // Process the second column value if it hasn't been processed already
                      logger_all.info('Processing second column value...' + row[1].trim() + "$$$$$$$$$$");
                      const secondColumnValue = row[1].trim();
                      media_url.push(secondColumnValue);
                      continue;
                    }

                    if (row[i]) {
                      otherColumnsArray.push(row[i].trim());
                    } else {

                      // Handle the case where row[i] is undefined
                      console.error('row[i] is undefined at index ' + i);
                    }
                    if ((is_same_media_flag === "true" && (Object.keys(row).length == 2))) {
                      otherColumnsArray.push(row[i].trim());
                    }
                  }

                  // Only push the otherColumnsArray if the mobile number is still valid
                  if (isValidFormat) {
                    variable_values.push(otherColumnsArray);
                  }
                } else {
                  invalid_mobile_numbers.push(firstColumnValue);
                }
              }
            })
            .on('error', (error) => {
              console.error('Error:', error.message);
            })
            .on('end', async () => {

              if (is_same_msg == "false" && is_same_media_flag == "false") {
                media_url_csv = media_url;

              }
              //Continue Process only if valid mobile numbers not equal to zero
              if (valid_mobile_numbers.length != 0) {
                logger_all.info("[variable_values] : " + variable_values)
                rec_no = valid_mobile_numbers;
                const rec_no_string = rec_no.toString(); // Convert to a string

                // Split the string by comma to create an array
                const valuesArray = rec_no_string.split(',');
                var get_product = `SELECT * FROM rights_master where rights_name = 'RCS' AND rights_status = 'Y' `;
                logger_all.info("[select query request] : " + get_product);
                const get_product_id = await db.query(get_product);
                logger_all.info("[select query response] : " + JSON.stringify(get_product_id));
                product_id_two = get_product_id[0].rights_id;

                //Check if product is 'RCS'
                if (product_id_one == product_id_two) {
                  for (var j = 0; j < sender_numbers.length; j++) {

                    //Query to get active sender numbers
                    var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_numbers[j]}' AND sender_id_status = 'Y' AND is_qr_code ='N'`
                    logger_all.info("[Select query request] : " + senderID_active);
                    var select_sender_id_active = await db.query(senderID_active);
                    logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id_active))

                    //Get sender numbers data if selected data length is not equal to zero
                    if (select_sender_id_active.length != 0) {
                      sender_numbers_active.push(select_sender_id_active[0].mobile_no)
                      sender_id_active.push(select_sender_id_active[0].sender_id)
                      sender_devicetoken_active.push(select_sender_id_active[0].device_token)
                      logger_all.info("[ sender_numbers_active] : " + sender_numbers_active)
                      logger_all.info("[sender_id_active] : " + sender_id_active)
                      logger_all.info("[sender_devicetoken_active] : " + sender_devicetoken_active)
                    }
                    else {

                      //Otherwise store as inactive numbers
                      sender_numbers_inactive.push(sender_numbers[j])
                    }
                  }

                  //Check if active sender numbers length is equal to zero, send failure response 'No Sender ID available'
                  if (sender_numbers_active.length == 0) {
                    const update_failure = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                    logger_all.info("[update query request - No sender ID available] : " + update_failure);
                    const update_api_log = await db.query(update_failure);
                    logger_all.info("[update query response - No sender ID available] : " + JSON.stringify(update_api_log))
                    const no_senerid_msg = { response_code: 0, response_status: 201, response_msg: 'No Sender ID available' }
                    logger.info("[API RESPONSE] " + JSON.stringify(no_senerid_msg))
                    return res.json(no_senerid_msg);
                  }

                  //Update campaign status as 'P'
                  var update_sender_wtsp = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET sender_mobile_nos = '${sender_numbers_active}', cm_status = 'P' WHERE cm_status = 'W' AND compose_message_id = '${compose_message_id}'`
                  logger_all.info("[insert query request] : " + update_sender_wtsp)
                  const update_sender_wtsp_res = await db.query(update_sender_wtsp);
                  logger_all.info("[insert query response] : " + JSON.stringify(update_sender_wtsp_res))

                  //Insert compose details to compose_msg_status table
                  var insert_numbers = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`;

                  //Loop for receiver numbers
                  for (var k = 0; k < valuesArray.length; k) {
                    for (var i = 0; i < sender_numbers_active.length; i) {
                      var media = '-';
                      var cus_message = messages;
                      if (k == valuesArray.length) {
                        break;
                      }
                      logger_all.info("is_same_msg" + is_same_msg)
                      if (is_same_msg == "false") {
                        for (var j = 1; j <= variable_count; j++) {
                          logger_all.info(j)
                          logger_all.info("variable_values[k][j-1]" + variable_values[k][j - 1])

                          // cus_message = cus_message.replace(`{{${j}}}`,variable_values[k][j-1]);
                          cus_message = cus_message.replace(/{{(\w+)}}/, variable_values[k][j - 1]);
                        }
                      }
                      //Get media URL for same media
                      if (is_same_media_flag != '-') {
                        logger_all.info("[is_same_media_flag_test] : " + is_same_media_flag)
                        logger_all.info("kk.." + k)
                        media = is_same_media_flag == "true" ? media_url : media_url[k];
                        logger_all.info("[media_test] : " + media)
                      }
                      //Insert compose data
                      insert_numbers = insert_numbers + "" + `(NULL,${compose_message_id},'${sender_numbers_active[i]}','${valuesArray[k]}',"${cus_message}",'${media}','Y',CURRENT_TIMESTAMP,NULL,NULL,NULL,NULL,NULL,NULL,NULL),`;

                      //check if insert_count is 1000, insert 1000 splits data
                      if (insert_count == 1000) {
                        insert_numbers = insert_numbers.substring(0, insert_numbers.length - 1)
                        logger_all.info("[insert query request - insert numbers] : " + insert_numbers);
                        var insert_numbers_result = await db.query(insert_numbers);
                        logger_all.info("[insert query response - insert numbers] : " + JSON.stringify(insert_numbers_result))
                        insert_count = 0;
                        insert_numbers = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`;
                      }
                      insert_count = insert_count + 1;
                      i++;
                      k++;
                    }
                  }

                  //After the loops complete, this if statement checks if any pending insert queries are left to be executed. If so, it executes
                  if (insert_numbers !== `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`) {
                    insert_numbers = insert_numbers.substring(0, insert_numbers.length - 1); // Remove the trailing comma
                    logger_all.info("[insert query request - insert numbers] : " + insert_numbers);
                    var insert_numbers_result = await db.query(insert_numbers);
                    logger_all.info("[insert query response - insert numbers] : " + JSON.stringify(insert_numbers_result));
                  }

                  //Loop through sender numbers and update sender ID status 'P' while approve campaign
                  for (var j = 0; j < sender_numbers_active.length; j++) {
                    var update_senderID_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[j]}'`;
                    logger_all.info("[insert query request] : " + update_senderID_sts);
                    var update_senderID_sts_res = await db.query(update_senderID_sts);
                    logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
                  }

                // Check if Firebase is already initialized
                  if (!admin.apps.length) {
                     admin.initializeApp({
                     credential: admin.credential.cert(serviceAccount),
                       });
                         }
                 for (let i = 0; i < sender_devicetoken_active.length; i++) {

                    const message = {
                      data: {
                        "selected_user_id": user_id,
                        "product_id": product_id_two.toString(), // Fixed the method name
                        "title": compose_message_id,
                        "bodyText": "RCS_MSG"
                      },
                      token: sender_devicetoken_active[i]
                    };

                    logger_all.info(JSON.stringify(message));

                    admin.messaging().send(message)
                      .then((response) => {
                        logger_all.info('Notification sent:', response);
                      })
                      .catch((error) => {
                        logger_all.info('Error sending notification:', error);
                      });
                  }

                  //Update total waiting,total process in summary report
                  var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_waiting = 0,total_process = total_process+${valid_mobile_numbers.length},sum_start_date = CURRENT_TIMESTAMP WHERE com_msg_id = '${compose_message_id}'`
                  logger_all.info("[update_summary_report] : " + update_summary_report);
                  var update_summary_report_res = await db.query(update_summary_report);
                  logger_all.info("[update_summary_report response] : " + JSON.stringify(update_summary_report_res))

                  //Send success response
                  const update_msg = `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                  logger_all.info("[update query request - success] : " + update_msg);
                  const update_api_log = await db.query(update_msg);
                  logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
                  const success_msg = { response_code: 1, response_status: 200, response_msg: 'Success.' }
                  logger.info("[API RESPONSE] " + JSON.stringify(success_msg))
                  return res.json(success_msg)
                }
                else {

                  //Otherwise send failure response 'Campaign Not Found'
                  const update_campaign = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                  logger_all.info("[update query request - campaign not found] : " + update_campaign);
                  const update_api_log = await db.query(update_campaign);
                  logger_all.info("[update query response -  campaign not found] : " + JSON.stringify(update_api_log))
                  const failure_msg = { response_code: 0, response_status: 201, response_msg: 'Campaign Not Found' }
                  logger_all.info("[campaign not found] : " + JSON.stringify(failure_msg))
                  return res.json(failure_msg);
                }
              }
            })
        }
      }
      else {

        //Otherwise send failure response 'Invalid User'
        const inactive_user = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
        logger_all.info("[update query request - Invalid User] : " + inactive_user);
        const update_api_log = await db.query(inactive_user);
        logger_all.info("[update query response -  Invalid User] : " + JSON.stringify(update_api_log))

        const failure_msg_1 = { response_code: 0, response_status: 201, response_msg: 'Invalid User' }
        logger_all.info("[Invalid User] : " + JSON.stringify(failure_msg_1))
        return res.json(failure_msg_1);
      }
    }
    catch (err) {
      //If error occurs, send failure response
      const error_api = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
      logger_all.info("[update query request - Error Occurred] : " + error_api);
      const update_api_log = await db.query(error_api);
      logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
      logger_all.info(": [check] Failed - " + err);
      const error_msg = { response_code: 0, response_status: 201, response_msg: "Error Occurred." };
      logger.info("[Failed response - Error occured] : " + JSON.stringify(error_msg))
      return res.json(error_msg);
    }
  }
);
// End Route - Approve RCS


//Start Route - Approve SMS
router.post(
  "/approve_usr",
  validator.body(ApproveUserValidation),
  valid_user_reqID,
  async function (req, res, next) {
    var logger_all = main.logger_all
    var logger = main.logger
    try {

      //Get all request data
      var compose_message_id = req.body.compose_message_id;
      var sender_numbers = req.body.sender_numbers;
      var user_id = req.body.selected_user_id;
      var request_id = req.body.request_id;
      var rec_count = 0;
      var total_count = 0;
      var insert_count = 1;
      var sender_numbers_active = [];
      var sender_numbers_inactive = [];
      var sender_id_active = [];
      var sender_devicetoken_active = [];
      var variable_value = [];
      let isFirstRow = true;
      is_check = false;
      const variable_values = [];
      const valid_mobile_numbers = [];
      const invalid_mobile_numbers = [];
      var media_url = [];
      const duplicateMobileNumbers = new Set();
      user_id_check = req.body.user_id;

      //Check if user is admin
      if (user_id_check == 1) {

        //Query to get data based on compose id
        var get_product = `SELECT * FROM ${DB_NAME}_${user_id}.compose_message_${user_id} where user_id = '${user_id}' AND compose_message_id = '${compose_message_id}'`;
        logger_all.info("[select query request] : " + get_product);
        const get_user_det = await db.query(get_product);
        logger_all.info("[select query response] : " + JSON.stringify(get_user_det));
        var get_compose_data = `SELECT * FROM ${DB_NAME}_${user_id}.compose_msg_media_${user_id} where compose_message_id = '${compose_message_id}'`;
        logger_all.info("[select query request] : " + get_compose_data);
        const get_compose_data_result = await db.query(get_compose_data);
        logger_all.info("[select query response] : " + JSON.stringify(get_compose_data_result));

        //Check if selected data is equal to zero, send failuer response 'Compose ID Not Available'
        if (get_user_det.length == 0 || get_compose_data_result == 0) {
          const update_api = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Compose ID Not Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
          logger_all.info("[update query request - compose ID not available] : " + update_api);
          const update_api_log = await db.query(update_api);
          logger_all.info("[update query response - compose ID not available] : " + JSON.stringify(update_api_log))
          const composeid_msg = { response_code: 0, response_status: 201, response_msg: 'Compose ID Not Available', request_id: req.body.request_id }
          logger.info("[API RESPONSE] " + JSON.stringify(composeid_msg))
          logger_all.info("[compose ID not available] : " + JSON.stringify(composeid_msg))
          return res.json(composeid_msg);
        }
        mobile_no_cnt = get_user_det[0].total_mobile_no_count;

        //Check if sender numbers length is greater than total mobile number count, send failure response 'Sender Numbers should be less than Receiver Numbers'
        if (sender_numbers.length > mobile_no_cnt) {
          const failure_msg = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender Numbers should be less than Receiver Numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`;
          logger_all.info("[update query request - Sender Numbers should be less than Receiver Numbers] : " + failure_msg);
          const update_api_log = await db.query(failure_msg);
          logger_all.info("[update query response -Sender Numbers should be less than Receiver Numbers] : " + JSON.stringify(update_api_log))
          const sender_msg = { response_code: 0, response_status: 201, response_msg: 'Sender Numbers should be less than Receiver Numbers' };
          logger.info("[API RESPONSE] " + JSON.stringify(sender_msg))
          return res.json(sender_msg);
        }

        //Get Required data from query
        receiver_nos_path = get_user_det[0].receiver_nos_path;
        product_id_one = get_user_det[0].product_id;
        message_type = get_user_det[0].campaign_type;
        variable_count = get_user_det[0].variable_count;
        is_same_msg = get_user_det[0].is_same_msg;
        messages = get_compose_data_result[0].text_title;
        if (receiver_nos_path != '-') {

          // Fetch the CSV file
          fs.createReadStream(receiver_nos_path)

            // Read the CSV file from the stream
            .pipe(csv({
              headers: false
            }))

            // Set headers to false since there are no column headers
            .on('data', (row) => {
              if (Object.values(row).every(value => value === '')) {
                return;
              }

              // Skip the first row (header)
              /*   if (isFirstRow) {
                     isFirstRow = false;
                     return;
                 }*/
              const firstColumnValue = row[0].trim();

              // Validate mobile number format
              const isValidFormat = /^\d{12}$/.test(firstColumnValue) && firstColumnValue.startsWith('91') && /^[6-9]/.test(firstColumnValue.substring(2, 3));

              // Check for duplicates
              if (duplicateMobileNumbers.has(firstColumnValue)) {
                invalid_mobile_numbers.push(firstColumnValue);
              } else {
                duplicateMobileNumbers.add(firstColumnValue);
                if (isValidFormat) {
                  valid_mobile_numbers.push(firstColumnValue);

                  // Create a new array for each row
                  const otherColumnsArray = [];
                  let secondColumnValue;
                  for (let i = 1; i < Object.keys(row).length; i++) {

                    // Skip processing if the mobile number is invalid
                    if (!isValidFormat) {
                      break;
                    }
                    if (row[i]) {
                      otherColumnsArray.push(row[i].trim());
                    } else {

                      // Handle the case where row[i] is undefined
                      console.error('row[i] is undefined at index ' + i);
                    }
                  }

                  // Only push the otherColumnsArray if the mobile number is still valid
                  if (isValidFormat) {
                    variable_values.push(otherColumnsArray);
                  }
                } else {
                  invalid_mobile_numbers.push(firstColumnValue);
                }
              }
            })
            .on('error', (error) => {
              console.error('Error:', error.message);
            })
            .on('end', async () => {

              //Continue Process only if valid mobile numbers not equal to zero
              if (valid_mobile_numbers.length != 0) {
                logger_all.info("[variable_values] : " + variable_values)
                rec_no = valid_mobile_numbers;
                const rec_no_string = rec_no.toString(); // Convert to a string

                // Split the string by comma to create an array
                const valuesArray = rec_no_string.split(',');
                var get_product = `SELECT * FROM rights_master where rights_name = 'GSM SMS' AND rights_status = 'Y' `;
                logger_all.info("[select query request] : " + get_product);
                const get_product_id = await db.query(get_product);
                logger_all.info("[select query response] : " + JSON.stringify(get_product_id));
                product_id_two = get_product_id[0].rights_id;

                //Check if product is 'GSM SMS'
                if (product_id_one == product_id_two) {
                  for (var j = 0; j < sender_numbers.length; j++) {

                    //Query to get active sender numbers
                    var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_numbers[j]}' AND sender_id_status = 'Y' AND is_qr_code ='N'`
                    logger_all.info("[Select query request] : " + senderID_active);
                    var select_sender_id_active = await db.query(senderID_active);
                    logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id_active))

                    //Get sender numbers data if selected data length is not equal to zero
                    if (select_sender_id_active.length != 0) {
                      sender_numbers_active.push(select_sender_id_active[0].mobile_no)
                      sender_id_active.push(select_sender_id_active[0].sender_id)
                      sender_devicetoken_active.push(select_sender_id_active[0].device_token)
                      logger_all.info("[ sender_numbers_active] : " + sender_numbers_active)
                      logger_all.info("[sender_id_active] : " + sender_id_active)
                      logger_all.info("[sender_devicetoken_active] : " + sender_devicetoken_active)
                    }
                    else {

                      //Otherwise store as inactive numbers
                      sender_numbers_inactive.push(sender_numbers[j])
                    }
                  }

                  //Check if active sender numbers length is equal to zero, send failure response 'No Sender ID available'
                  if (sender_numbers_active.length == 0) {
                    const update_failure = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                    logger_all.info("[update query request - No sender ID available] : " + update_failure);
                    const update_api_log = await db.query(update_failure);
                    logger_all.info("[update query response - No sender ID available] : " + JSON.stringify(update_api_log))
                    const no_senerid_msg = { response_code: 0, response_status: 201, response_msg: 'No Sender ID available' }
                    logger.info("[API RESPONSE] " + JSON.stringify(no_senerid_msg))
                    return res.json(no_senerid_msg);
                  }

                  //Update campaign status as 'P'
                  var update_sender_wtsp = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET sender_mobile_nos = '${sender_numbers_active}', cm_status = 'P' WHERE cm_status = 'W' AND compose_message_id = '${compose_message_id}'`
                  logger_all.info("[insert query request] : " + update_sender_wtsp)
                  const update_sender_wtsp_res = await db.query(update_sender_wtsp);
                  logger_all.info("[insert query response] : " + JSON.stringify(update_sender_wtsp_res))

                  //Insert compose details to compose_msg_status table
                  var insert_numbers = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`;

                  //Loop for receiver numbers
                  for (var k = 0; k < valuesArray.length; k) {
                    for (var i = 0; i < sender_numbers_active.length; i) {
                      var cus_message = messages;
                      if (k == valuesArray.length) {
                        break;
                      }
                      logger_all.info("is_same_msg" + is_same_msg)
                      if (is_same_msg == "false") {
                        for (var j = 1; j <= variable_count; j++) {
                          logger_all.info(j)
                          logger_all.info("variable_values[k][j-1]" + variable_values[k][j - 1])

                          // cus_message = cus_message.replace(`{{${j}}}`,variable_values[k][j-1]);
                          cus_message = cus_message.replace(/{{(\w+)}}/, variable_values[k][j - 1]);
                        }
                      }

                      //Insert compose data
                      insert_numbers = insert_numbers + "" + `(NULL,${compose_message_id},'${sender_numbers_active[i]}','${valuesArray[k]}',"${cus_message}",'-','Y',CURRENT_TIMESTAMP,NULL,NULL,NULL,NULL,NULL,NULL,NULL),`;

                      //check if insert_count is 1000, insert 1000 splits data
                      if (insert_count == 1000) {
                        insert_numbers = insert_numbers.substring(0, insert_numbers.length - 1)
                        logger_all.info("[insert query request - insert numbers] : " + insert_numbers);
                        var insert_numbers_result = await db.query(insert_numbers);
                        logger_all.info("[insert query response - insert numbers] : " + JSON.stringify(insert_numbers_result))
                        insert_count = 0;
                        insert_numbers = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`;
                      }
                      insert_count = insert_count + 1;
                      i++;
                      k++;
                    }
                  }

                  //After the loops complete, this if statement checks if any pending insert queries are left to be executed. If so, it executes
                  if (insert_numbers !== `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`) {
                    insert_numbers = insert_numbers.substring(0, insert_numbers.length - 1); // Remove the trailing comma
                    logger_all.info("[insert query request - insert numbers] : " + insert_numbers);
                    var insert_numbers_result = await db.query(insert_numbers);
                    logger_all.info("[insert query response - insert numbers] : " + JSON.stringify(insert_numbers_result));
                  }

                  //Loop through sender numbers and update sender ID status 'P' while approve campaign
                  for (var j = 0; j < sender_numbers_active.length; j++) {
                    var update_senderID_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[j]}'`;
                    logger_all.info("[insert query request] : " + update_senderID_sts);
                    var update_senderID_sts_res = await db.query(update_senderID_sts);
                    logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
                  }
                       // Check if Firebase is already initialized
                  if (!admin.apps.length) {
                     admin.initializeApp({
                     credential: admin.credential.cert(serviceAccount),
                       });
                         }

                    for (let i = 0; i < sender_devicetoken_active.length; i++) {

                    const message = {
                      data: {
                        "selected_user_id": user_id,
                        "product_id": product_id_two.toString(), // Fixed the method name
                        "title": compose_message_id,
                        "bodyText": "SMS_MSG"
                      },
                      token: sender_devicetoken_active[i]
                    };

                    logger_all.info(JSON.stringify(message));

                    admin.messaging().send(message)
                      .then((response) => {
                        logger_all.info('Notification sent:', response);
                      })
                      .catch((error) => {
                        logger_all.info('Error sending notification:', error);
                      });
                  }

                  //Update total waiting,total process in summary report
                  var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_waiting = 0,total_process = total_process+${valid_mobile_numbers.length},sum_start_date = CURRENT_TIMESTAMP WHERE com_msg_id = '${compose_message_id}'`
                  logger_all.info("[update_summary_report] : " + update_summary_report);
                  var update_summary_report_res = await db.query(update_summary_report);
                  logger_all.info("[update_summary_report response] : " + JSON.stringify(update_summary_report_res))

                  //Send success response
                  const update_msg = `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                  logger_all.info("[update query request - success] : " + update_msg);
                  const update_api_log = await db.query(update_msg);
                  logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
                  const success_msg = { response_code: 1, response_status: 200, response_msg: 'Success.' }
                  logger.info("[API RESPONSE] " + JSON.stringify(success_msg))
                  return res.json(success_msg)
                }
                else {

                  //Otherwise send failure response 'Campaign Not Found'
                  const update_campaign = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                  logger_all.info("[update query request - campaign not found] : " + update_campaign);
                  const update_api_log = await db.query(update_campaign);
                  logger_all.info("[update query response -  campaign not found] : " + JSON.stringify(update_api_log))
                  const failure_msg = { response_code: 0, response_status: 201, response_msg: 'Campaign Not Found' }
                  logger_all.info("[campaign not found] : " + JSON.stringify(failure_msg))
                  return res.json(failure_msg);
                }
              }
            })
        }
      }
      else {

        //Otherwise send failure response 'Invalid User'
        const inactive_user = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
        logger_all.info("[update query request - Invalid User] : " + inactive_user);
        const update_api_log = await db.query(inactive_user);
        logger_all.info("[update query response -  Invalid User] : " + JSON.stringify(update_api_log))

        const failure_msg_1 = { response_code: 0, response_status: 201, response_msg: 'Invalid User' }
        logger_all.info("[Invalid User] : " + JSON.stringify(failure_msg_1))
        return res.json(failure_msg_1);
      }
    }
    catch (err) {
      //If error occurs, send failure response
      const error_api = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
      logger_all.info("[update query request - Error Occurred] : " + error_api);
      const update_api_log = await db.query(error_api);
      logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
      logger_all.info(": [check] Failed - " + err);
      const error_msg = { response_code: 0, response_status: 201, response_msg: "Error Occurred." };
      logger.info("[Failed response - Error occured] : " + JSON.stringify(error_msg))
      return res.json(error_msg);
    }
  }
);
//End Route - Approve SMS

//Start Route - Approve Whatsapp
router.post(
  "/approve_wtsp",
  validator.body(ApproveWtspValidation),
  valid_user_reqID,
  async function (req, res, next) {
    var logger_all = main.logger_all
    var logger = main.logger

    //Get all request data
    var compose_message_id = req.body.compose_whatsapp_id;
    var sender_numbers = req.body.sender_numbers;
    var user_id = req.body.selected_user_id;
    var media_url_csv;
    var request_id = req.body.request_id;
    var rec_count = 0;
    var total_count = 0;
    var insert_count = 1;
    var sender_numbers_active = [];
    var sender_numbers_inactive = [];
    var sender_id_active = [];
    var sender_devicetoken_active = [];
    var variable_value = [];
    let isFirstRow = true;
    is_check = false;
    const variable_values = [];
    const valid_mobile_numbers = [];
    const invalid_mobile_numbers = [];
    var media_url = [];
    const duplicateMobileNumbers = new Set();
    try {
      user_id_check = req.body.user_id;

      //Check if user is admin
      if (user_id_check == 1) {

        //Query to get compose data     
        var get_product = `SELECT * FROM ${DB_NAME}_${user_id}.compose_message_${user_id} where user_id = '${user_id}' AND compose_message_id = '${compose_message_id}'`;
        logger_all.info("[select query request] : " + get_product);
        const get_user_det = await db.query(get_product);
        logger_all.info("[select query response] : " + JSON.stringify(get_user_det));

        var get_compose_data = `SELECT * FROM ${DB_NAME}_${user_id}.compose_msg_media_${user_id} where compose_message_id = '${compose_message_id}'`;
        logger_all.info("[select query request] : " + get_compose_data);
        const get_compose_data_result = await db.query(get_compose_data);
        logger_all.info("[select query response] : " + JSON.stringify(get_compose_data_result));

        //Check if selected data length is equal to zero, send failure response 'Compose ID Not Available'
        if (get_user_det.length == 0 || get_compose_data_result == 0) {
          const update_api = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Compose ID Not Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
          logger_all.info("[update query request - compose ID not available] : " + update_api);
          const update_api_log = await db.query(update_api);
          logger_all.info("[update query response - compose ID not available] : " + JSON.stringify(update_api_log))
          const composeid_msg = { response_code: 0, response_status: 201, response_msg: 'Compose ID Not Available', request_id: req.body.request_id }
          logger.info("[API RESPONSE] " + JSON.stringify(composeid_msg))
          logger_all.info("[compose ID not available] : " + JSON.stringify(composeid_msg))
          return res.json(composeid_msg);
        }
        mobile_no_cnt = get_user_det[0].total_mobile_no_count;

        //Check if sender numbers length is greater than total mobile number count, send failure response 'Sender Numbers should be less than Receiver Numbers'
        if (sender_numbers.length > mobile_no_cnt) {
          const failure_msg = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender Numbers should be less than Receiver Numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`;
          logger_all.info("[update query request - Sender Numbers should be less than Receiver Numbers] : " + failure_msg);
          const update_api_log = await db.query(failure_msg);
          logger_all.info("[update query response -Sender Numbers should be less than Receiver Numbers] : " + JSON.stringify(update_api_log))
          const sender_msg = { response_code: 0, response_status: 201, response_msg: 'Sender Numbers should be less than Receiver Numbers' };
          logger.info("[API RESPONSE] " + JSON.stringify(sender_msg))
          return res.json(sender_msg);
        }

        //Get required data from query
        receiver_nos_path = get_user_det[0].receiver_nos_path;
        product_id_one = get_user_det[0].product_id;
        message_type = get_user_det[0].campaign_type;
        //     mobile_no_cnt = get_user_det[0].total_mobile_no_count;
        is_same_media_flag = get_user_det[0].is_same_media;
        variable_count = get_user_det[0].variable_count;
        is_same_msg = get_user_det[0].is_same_msg;
        messages = get_compose_data_result[0].text_title;
        if (is_same_media_flag == "true") {
          media_url = get_compose_data_result[0].media_url;
        }
        logger.info("media_url " + media_url)
        logger.info("is_same_media_flag " + is_same_media_flag)
        logger.info("is_same_msg " + is_same_msg)
        logger.info("message_type " + message_type)

        if (receiver_nos_path != '-') {
          // Fetch the CSV file
          fs.createReadStream(receiver_nos_path)
            // Read the CSV file from the stream
            .pipe(csv({
              headers: false
            })) // Set headers to false since there are no column headers
            .on('data', (row) => {
              if (Object.values(row).every(value => value === '')) {
                return;
              }
              const firstColumnValue = row[0].trim();
              // Validate mobile number format
              const isValidFormat = /^\d{12}$/.test(firstColumnValue) && firstColumnValue.startsWith('91') && /^[6-9]/.test(firstColumnValue.substring(2, 3));
              // Check for duplicates
              if (duplicateMobileNumbers.has(firstColumnValue)) {
                invalid_mobile_numbers.push(firstColumnValue);
              } else {
                duplicateMobileNumbers.add(firstColumnValue);
                if (isValidFormat) {
                  valid_mobile_numbers.push(firstColumnValue);
                  // Create a new array for each row
                  const otherColumnsArray = [];
                  let secondColumnValue;

                  for (let i = 1; i < Object.keys(row).length; i++) {
                    // Skip processing if the mobile number is invalid

                    if (!isValidFormat) {
                      break;
                    }
                    logger_all.info(Object.keys(row).length);

                    if ((is_same_media_flag === "false") && (is_same_msg === "false") && (message_type === 'VIDEO' || message_type === 'IMAGE') && i == 1) {
                      // Check if the second column value is not empty and equal to the current column value
                      //                      if (row[1].trim() && row[1].trim() === row[i].trim()) {
                      logger_all.info('Values in the second column and column ' + (i + 1) + ' are equal');
                      // Process the second column value if it hasn't been processed already
                      //  if (i === 1) {
                      logger_all.info('Processing second column value...' + row[1].trim() + "$$$$$$$$$$");
                      const secondColumnValue = row[1].trim();
                      media_url.push(secondColumnValue);
                      continue;
                      //                      }
                      //}
                    }
                    logger_all.info("I VALUE " + i)
                    logger_all.info("I row VALUE " + row[i])

                    // Push other column values to otherColumnsArray
                    if (row[i]) {
                      otherColumnsArray.push(row[i].trim());
                    } else if (!row[i]) {
                      logger_all.info('row[i] is undefined at index ' + i);
                    }
                    if ((is_same_media_flag === "true" && (Object.keys(row).length == 2))) {
                      otherColumnsArray.push(row[i].trim());
                    }

                  }
                  // Only push the otherColumnsArray if the mobile number is still valid
                  if (isValidFormat) {
                    variable_values.push(otherColumnsArray);
                  }
                } else {
                  invalid_mobile_numbers.push(firstColumnValue);
                }
              }
            })
            .on('error', (error) => {
              console.error('Error:', error.message);
            })
            .on('end', async () => {
              if (is_same_msg == "false" && is_same_media_flag == "false") {
                media_url_csv = media_url;

              }
              //Do process only valid numbers not equal to zero            
              if (valid_mobile_numbers.length != 0) {
                logger_all.info("[variable_values] : " + variable_values)
                rec_no = valid_mobile_numbers;
                const rec_no_string = rec_no.toString(); // Convert to a string

                // Split the string by comma to create an array
                const valuesArray = rec_no_string.split(',');
                var get_product = `SELECT * FROM rights_master where rights_name = 'WHATSAPP' AND rights_status = 'Y' `;
                logger_all.info("[select query request] : " + get_product);
                const get_product_id = await db.query(get_product);
                logger_all.info("[select query response] : " + JSON.stringify(get_product_id));
                product_id_two = get_product_id[0].rights_id;

                //Check if product is 'WHATSAPP'
                if (product_id_one == product_id_two) {
                  for (var j = 0; j < sender_numbers.length; j++) {

                    //Query to get active sender numbers
                    var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_numbers[j]}' AND sender_id_status = 'Y' AND is_qr_code ='N'`
                    logger_all.info("[Select query request] : " + senderID_active);
                    var select_sender_id_active = await db.query(senderID_active);
                    logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id_active))
                    if (select_sender_id_active.length != 0) {
                      sender_numbers_active.push(select_sender_id_active[0].mobile_no)
                      sender_id_active.push(select_sender_id_active[0].sender_id)
                      sender_devicetoken_active.push(select_sender_id_active[0].device_token)
                    }
                    else {
                      //Otherwise store as inactive numbers
                      sender_numbers_inactive.push(sender_numbers[j])
                    }
                  }

                  //Check if sender numbers length is equal to zero, send failure response 'No Sender ID available'
                  if (sender_numbers_active.length == 0) {
                    const update_failure = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                    logger_all.info("[update query request - No sender ID available] : " + update_failure);
                    const update_api_log = await db.query(update_failure);
                    logger_all.info("[update query response - No sender ID available] : " + JSON.stringify(update_api_log))
                    const no_senerid_msg = { response_code: 0, response_status: 201, response_msg: 'No Sender ID available' }
                    logger.info("[API RESPONSE] " + JSON.stringify(no_senerid_msg))
                    return res.json(no_senerid_msg);
                  }

                  //Update campaign status as 'P' while approve
                  var update_sender_wtsp = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET sender_mobile_nos = '${sender_numbers_active}', cm_status = 'P' WHERE cm_status = 'W' AND compose_message_id = '${compose_message_id}'`
                  logger_all.info("[insert query request] : " + update_sender_wtsp)
                  const update_sender_wtsp_res = await db.query(update_sender_wtsp);
                  logger_all.info("[insert query response] : " + JSON.stringify(update_sender_wtsp_res))

                  //Insert compose data to compose msg status table
                  var insert_numbers = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`;

                  //Loop for receiver numbers
                  for (var k = 0; k < valuesArray.length; k) {
                    for (var i = 0; i < sender_numbers_active.length; i) {
                      var cus_message = messages;
                      var media = '-';
                      if (k == valuesArray.length) {
                        break;
                      }
                      logger_all.info("is_same_msg" + is_same_msg)

                      //Add variables for personalized message 
                      if (is_same_msg == "false") {
                        for (var j = 1; j <= variable_count; j++) {
                          logger_all.info("variable_values[k][j-1]" + variable_values[k][j - 1])

                          //cus_message = cus_message.replace(`{{${j}}}`,variable_values[k][j-1]);
                          cus_message = cus_message.replace(/{{(\w+)}}/, variable_values[k][j - 1]);
                        }
                      }

                      //Get media URL for same media
                      if (is_same_media_flag != '-') {
                        logger_all.info("[is_same_media_flag_test] : " + is_same_media_flag)
                        logger_all.info("kk.." + k)
                        media = is_same_media_flag == "true" ? media_url : media_url[k];
                        logger_all.info("[media_test] : " + media)
                      }

                      //Insert compose data
                      insert_numbers = insert_numbers + "" + `(NULL,${compose_message_id},'${sender_numbers_active[i]}','${valuesArray[k]}',"${cus_message}",'${media}','Y',CURRENT_TIMESTAMP,NULL,NULL,NULL,NULL,NULL,NULL,NULL),`;

                      //check if insert_count is 1000, insert 1000 splits data
                      if (insert_count == 1000) {
                        insert_numbers = insert_numbers.substring(0, insert_numbers.length - 1)
                        logger_all.info("[insert query request - insert numbers] : " + insert_numbers);
                        var insert_numbers_result = await db.query(insert_numbers);
                        logger_all.info("[insert query response - insert numbers] : " + JSON.stringify(insert_numbers_result))
                        insert_count = 0;
                        insert_numbers = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`;
                      }
                      insert_count = insert_count + 1;
                      i++;
                      k++;
                    }
                  }

                  if (insert_numbers !== `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_status_${user_id} VALUES`) {
                    insert_numbers = insert_numbers.substring(0, insert_numbers.length - 1); // Remove the trailing comma
                    logger_all.info("[insert query request - insert numbers] : " + insert_numbers);
                    var insert_numbers_result = await db.query(insert_numbers);
                    logger_all.info("[insert query response - insert numbers] : " + JSON.stringify(insert_numbers_result));
                  }


                  //Loop through sender numbers
                  for (var j = 0; j < sender_numbers_active.length; j++) {

                    //Update sender ID status as 'P' while approve
                    var update_senderID_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[j]}'`;
                    logger_all.info("[insert query request] : " + update_senderID_sts);
                    var update_senderID_sts_res = await db.query(update_senderID_sts);
                    logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
                  }

                       // Check if Firebase is already initialized
                  if (!admin.apps.length) {
                     admin.initializeApp({
                     credential: admin.credential.cert(serviceAccount),
                       });
                         }


                  for (let i = 0; i < sender_devicetoken_active.length; i++) {
                    const message = {
                      data: {
                        "selected_user_id": user_id,
                        "product_id": product_id_two.toString(), // Fixed the method name
                        "title": compose_message_id,
                        "bodyText": "WTSP_MSG"
                      },
                      token: sender_devicetoken_active[i]
                    };

                    logger_all.info(JSON.stringify(message));

                    admin.messaging().send(message)
                      .then((response) => {
                        logger_all.info('Notification sent:', response);
                      })
                      .catch((error) => {
                        logger_all.info('Error sending notification:', error);
                      });
                  }


                  //Update total process, total waiting in summary report
                  var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_waiting = 0,total_process = total_process+${valid_mobile_numbers.length},sum_start_date = CURRENT_TIMESTAMP WHERE com_msg_id = '${compose_message_id}'`
                  logger_all.info("[update_summary_report] : " + update_summary_report);
                  var update_summary_report_res = await db.query(update_summary_report);
                  logger_all.info("[update_summary_report response] : " + JSON.stringify(update_summary_report_res))

                  //Send success response
                  const update_msg = `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                  logger_all.info("[update query request - success] : " + update_msg);
                  const update_api_log = await db.query(update_msg);
                  logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
                  const success_msg = { response_code: 1, response_status: 200, response_msg: 'Success.' }
                  logger.info("[API RESPONSE] " + JSON.stringify(success_msg))
                  return res.json(success_msg)
                }
                else {

                  //Otherwise send failure response 'Campaign Not Found'
                  const update_campaign = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
                  logger_all.info("[update query request - campaign not found] : " + update_campaign);
                  const update_api_log = await db.query(update_campaign);
                  logger_all.info("[update query response -  campaign not found] : " + JSON.stringify(update_api_log))
                  const failure_msg = { response_code: 0, response_status: 201, response_msg: 'Campaign Not Found' }
                  logger_all.info("[campaign not found] : " + JSON.stringify(failure_msg))
                  return res.json(failure_msg);
                }
              }
            })
        }
      }
      else {

        //Otherwise send failure response 'Invalid User'
        const inactive_user = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
        logger_all.info("[update query request - Invalid User] : " + inactive_user);
        const update_api_log = await db.query(inactive_user);
        logger_all.info("[update query response -  Invalid User] : " + JSON.stringify(update_api_log))

        const failure_msg_1 = { response_code: 0, response_status: 201, response_msg: 'Invalid User' }
        logger_all.info("[Invalid User] : " + JSON.stringify(failure_msg_1))
        return res.json(failure_msg_1);
      }
    }
    catch (err) {
      //If error occurs, send failure response
      const error_api = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
      logger_all.info("[update query request - Error Occurred] : " + error_api);
      const update_api_log = await db.query(error_api);
      logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
      logger_all.info(": [check] Failed - " + err);
      const error_msg = { response_code: 0, response_status: 201, response_msg: "Error Occurred." };
      logger.info("[Failed response - Error occured] : " + JSON.stringify(error_msg))
      return res.json(error_msg);
    }
  }
);
//End Route - Approve Whatsapp

//Start Route - Reject Campaign
router.post(
  "/reject_campaign",
  validator.body(RejectCampaignValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var logger_all = main.logger_all
      var result = await RejectCampaign.reject_campaign(req);
      logger.info("[API RESPONSE - approve sms] " + JSON.stringify(result))
      // Send the response as JSON
      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

//Start Route - Approve Whatsapp
router.post(
  "/approve_obd",
  validator.body(ApproveOBD_SIPValidation),
  valid_user_reqID,
  async function (req, res, next) {
    // Destructure loggers from main
    const { logger_all, logger } = main;
    //Get all request data
    const { compose_message_id, selected_user_id: user_id, channel_ids, channel_percentage } = req.body;
    // You can use these variables directly now
    let start_channel_no = [], active_channel_id = [], server_urls = [], end_channel_no = [], valid_mobile_numbers = [], sender_numbers = [], server_ids = [], audio_url = [], server_names = [];
    let send_response = {};

    try {
      logger_all.info(" [Restart Process] - " + req.body);
      logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
      const get_campaign_result = await db.query(`SELECT * FROM ${DB_NAME}_${user_id}.compose_message_${user_id} where user_id = '${user_id}' AND compose_message_id = '${compose_message_id}'`);

      //Check if selected data length is equal to zero, send failure response 'Compose ID Not Available'
      if (get_campaign_result.length == 0) {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Compose ID Not Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        send_response = { response_code: 0, response_status: 201, response_msg: 'Compose ID Not Available' };
        return res.json(send_response);
      } else if (get_campaign_result[0].cm_status != 'W') {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign already approved' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        send_response = { response_code: 0, response_status: 201, response_msg: 'Campaign already approved' };
        return res.json(send_response);
      }


      // Destructure the required properties from get_campaign_result[0]
      const { receiver_nos_path, campaign_type: message_type, context_id, campaign_name, retry_time_interval: retry_time, retry_count, total_mobile_no_count } = get_campaign_result[0];

      // Define the file path
      const filePath = path.join(receiver_nos_path);
      // Check if file exists
      if (!fs.existsSync(filePath)) {
        logger_all.error(`File not found: ${filePath}`);
        // Update the database and send failure response
        await db.query(`UPDATE api_log SET response_status = 'F', response_date = CURRENT_TIMESTAMP, response_comments = 'File not found. Cannot read' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        send_response = { response_code: 0, response_status: 201, response_msg: 'File not found. Cannot read' };
        return res.json(send_response);
      }

      for (let i = 0; i < channel_ids.length; i++) {
        //Query to get active sender numbers
        const select_channel_id_active = await db.query(`SELECT * from sip_servers WHERE sip_id = '${channel_ids[i]}' AND sip_status in ('Y','T')`);
        if (select_channel_id_active.length != 0) {
          start_channel_no.push(select_channel_id_active[0].start_channel_no)
          active_channel_id.push(select_channel_id_active[0].sip_id)
          end_channel_no.push(select_channel_id_active[0].end_channel_no)
          server_names.push(select_channel_id_active[0].server_name)
          server_urls.push(select_channel_id_active[0].server_url)
        }
      }

      //Check if sender numbers length is equal to zero, send failure response 'No Channel ID available.'
      if (active_channel_id.length == 0) {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Channel ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        send_response = { response_code: 0, response_status: 201, response_msg: 'No Channel ID available' }
        return res.json(send_response);
      }
      const getPrompt = await db.query(`SELECT prompt_path,context FROM obd_prompt_masters WHERE prompt_id = '${context_id}'`);

      if (getPrompt.length == 0) {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Context Id is not available' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        send_response = { response_code: 0, response_status: 201, response_msg: "Context Id is not available" }
        return res.json(send_response);
      }
      // Assuming getPrompt[0].prompt_path is something like 'path/to/file/filename.ext'
      const promptPath = getPrompt[0].prompt_path.trim();
      const filename = promptPath.split('/').pop().replace(/\.[^/.]+$/, '');

      if (message_type == 'Generic') {
        audio_url.push(filename)
      }

      const context = getPrompt[0].context;

      // Fetch the CSV file
      fs.createReadStream(receiver_nos_path)
        // Read the CSV file from the stream
        .pipe(csv({
          headers: false
        })) // Set headers to false since there are no column headers
        .on('data', (row) => {
          // Push trimmed first column value
          valid_mobile_numbers.push(row[0]?.trim());
          // Trim and process the second column value
          const secondColumnValue = row[1]?.trim();
          if (message_type !== 'Generic' && secondColumnValue) {
            logger_all.info('Processing second column value: ' + secondColumnValue);
            audio_url.push(secondColumnValue);
          }
        })
        .on('error', (error) => {
          console.error('Error:', error.message);
        })
        .on('end', async () => {

          //Continue process only if valid mobile numbers length is not equal to zero
          if (valid_mobile_numbers.length == 0) {
            //Otherwise send failure response 'The valid mobile numbers count is zero; cannot create the campaign.'
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = "The valid mobile numbers count is zero; cannot create the campaign." WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: "The valid mobile numbers count is zero; cannot create the campaign.", request_id: req.body.request_id }
            return res.json(send_response);
          }

          // Get sender Numbers
          function loopWithFor(start, end, percentage, totalDesiredCount, ServerID) {
            let count = Math.round(totalDesiredCount * (percentage / 100)); // Calculate count based on percentage of totalDesiredCount
            while (count > 0 && sender_numbers.length < totalDesiredCount) {
              for (let j = start; j <= end && sender_numbers.length < totalDesiredCount; j++) {
                if (count > 0) {
                  sender_numbers.push(j);
                  server_ids.push(ServerID)
                  count--;
                }
              }
            }
          }
          // Iterate over each range defined by start_channel_no and end_channel_no
          for (let i = 0; i < channel_ids.length; i++) {
            loopWithFor(start_channel_no[i], end_channel_no[i], channel_percentage[i], total_mobile_no_count, channel_ids[i]);
            logger_all.info(start_channel_no[i], end_channel_no[i], channel_percentage[i], total_mobile_no_count, channel_ids[i]);
            if (sender_numbers.length >= total_mobile_no_count) {
              break;
            }
          }

          // Adjust the array length if it exceeds total_mobile_no_count
          if (sender_numbers.length > total_mobile_no_count) {
            sender_numbers = sender_numbers.slice(0, total_mobile_no_count);
          }
          // Verify lengths
          logger_all.info("Sender numbers length matches:", sender_numbers.length === total_mobile_no_count);
          logger_all.info("server_ids:", server_ids);
          //logger_all.info(sender_numbers); // Output the results
          logger_all.info("valid_mobile_numbers" + valid_mobile_numbers); // Output the results
          //  chcek the Sender Numbers should be less than Receiver Numbers.
          if (sender_numbers.length > valid_mobile_numbers.length) {
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender Numbers should be less than Receiver Numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: 'Sender Numbers should be less than Receiver Numbers' };
            return res.json(send_response);
          }

          //Update campaign status as 'P' while approve 
          await db.query(`UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'P',call_start_date = NOW(),sender_mobile_nos = '${channel_ids}' WHERE cm_status = 'W' AND compose_message_id = '${compose_message_id}'`);

          // Prepare the list of channel IDs
          const channelIdsString = channel_ids.map(id => `'${id}'`).join(',');
          // Construct the SQL query using the IN clause
          await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'P' WHERE sip_status IN ('Y', 'T') AND sip_id IN (${channelIdsString}) `);

          // Insert data in the summary report obd
          await db.query(`INSERT INTO ${DB_NAME}.summary_reports_obd VALUES(NULL, '${user_id}', CURDATE(), '${compose_message_id}','${campaign_name}', '${valid_mobile_numbers.length}', NULL, NULL, NULL,NULL, NULL, NULL, NULL, NULL, NULL, 'N', CURRENT_TIMESTAMP)`);

          // Insert data in the Call Holding report
          await db.query(`INSERT INTO ${DB_NAME}.call_holding_reports_obd VALUES(NULL, '${user_id}','${compose_message_id}','${campaign_name}', CURDATE(),NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL,'${valid_mobile_numbers.length}',NULL,NULL, 'N', CURRENT_TIMESTAMP)`);

          // Insert data in the Cdrs reports
          await db.query(`INSERT INTO ${DB_NAME}.obd_cdr_reports VALUES(NULL, '${user_id}','${compose_message_id}','${context}',NULL, 'N', CURRENT_TIMESTAMP)`);
          // invalid datatypes values trim the array
          audio_url = audio_url.filter(Boolean);

          // Insert the records on cdrs tables
          let insert_count = 0; // Initialize insert_count to 0
          let insert_query = `INSERT INTO ${DB_NAME}_${user_id}.obd_cdrs_${user_id} VALUES`;

          for (let idx = 0; idx < valid_mobile_numbers.length; idx++) {
            // Set audio_url_value based on message_type
            const audio_url_value = message_type === 'Personal' ? 'NULL' : `'${audio_url[message_type === 'Customiz' ? idx : 0]}'`;
            // Set name_value based on message_type
            const name_value = message_type === 'Personal' ? `'${valid_mobile_numbers[idx]}'` : 'NULL';
            const todayDate = moment().format('YYDDDHHMMSS');
            var retry_in_millisec = retry_time * 1000;
            const uniquestring = uuid.v4().slice(0, 4);
            // generate accountcode
            const accountcode = `${todayDate}${server_ids[idx]}${uniquestring}`; // Takes the first 8 characters of the UUID
            insert_query += `(NULL, '${compose_message_id}', '${accountcode}', '${sender_numbers[idx]}', '${valid_mobile_numbers[idx]}', ${audio_url_value}, ${name_value}, NULL, NULL, CURRENT_TIMESTAMP, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, CURRENT_TIMESTAMP, 'I', 'N', '${server_ids[idx]}','N','N'),`;
            insert_count++;
            // Execute batch insert when insert_count reaches a certain limit (e.g., 10000)
            if (insert_count % 10000 === 0) {
              // Remove the last comma from insert_query
              insert_query = insert_query.slice(0, -1);
              try {
                logger_all.info(insert_query);
                const insert_mobile_numbers = await db.query(insert_query);
                logger_all.info("[insert query response]: " + JSON.stringify(insert_mobile_numbers));
              } catch (error) {
                // Handle database query error
                logger_all.error("Error executing insert query:", error);
                // Decide if you need to break out of the loop or continue
              }
              // Reset insert_count and insert_query for next batch
              insert_count = 0;
              insert_query = `INSERT INTO ${DB_NAME}_${user_id}.obd_cdrs_${user_id} VALUES`;
            }
          }

          // Final batch insert for remaining records
          if (insert_count > 0) {
            // Remove the last comma from insert_query
            insert_query = insert_query.slice(0, -1);
            try {
              await db.query(insert_query);
            } catch (error) {
              // Handle database query error
              logger_all.error("Error executing final insert query:", error);
            }
          }
          // Retry Count set send Request on Campaign call on Sip servers
          const payload = {
            campaignId: compose_message_id,
            user_id: user_id,
            message_type: message_type,
            retry_count_value: retry_count,
            retry_in_millisec: retry_in_millisec,
            context_id: context_id
          };

          logger_all.info("Send Request From Astersik " + JSON.stringify(payload))
          let allPromises = server_urls.map(url => {
            logger_all.info(`Sending request to ${url}/campaign_request with payload`, payload)
            return axios.post(url + "/campaign_request", payload)
              .then(response => {
                logger_all.info(`Response from ${url}/campaign_request:`, response);
                return 1; // Assuming return 1 on success
              })
              .catch(error => {
                console.error(`Error with URL ${url}/campaign_request: ${error.message}`);
                return 0; // Assuming return 0 on failure
              });
          });

          let results = await Promise.all(allPromises);
          logger_all.info(results + "results")

          // let return_status = results.every(status => status === 1) ? 1 : 0;
          const success_case = server_names.filter((name, index) => results[index] === 1);
          const failure_case = server_names.filter((name, index) => results[index] === 0);

          logger_all.info(success_case + "success_case")
          logger_all.info(failure_case + "failure_case")
          let response_comments = '', response_status = 'S';

          if (failure_case.length > 0) {
            response_status = 'F';
            response_comments = `Failed to call these servers ${failure_case.join(', ')}`;
          }
          if (success_case.length > 0) {
            response_status = 'S';
            response_comments += ` Calls initiated on ${success_case.join(', ')}`;
          }

          if (failure_case.length == server_names.length) {
            logger_all.info("LOG 1");
            // If error occurs, send failure response
            await db.query(`UPDATE api_log SET response_status = 'F', response_date = CURRENT_TIMESTAMP, response_comments = 'Failed to generate the call.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'Y' WHERE sip_status IN ('P') AND sip_id IN (${channelIdsString})`);
            await db.query(`UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'W', call_start_date = NULL, sender_mobile_nos = '${channel_ids}' WHERE cm_status = 'P' AND compose_message_id = '${compose_message_id}'`);
            return res.json({ response_code: 0, response_status: 201, response_msg: response_comments });

          } else if (success_case.length == server_names.length) {
            logger_all.info("LOG 2");
            // Send success response
              await db.query(`UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'P' WHERE compose_message_id = '${compose_message_id}'`);
            await db.query(`UPDATE api_log SET response_status = 'S', response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            return res.json({ response_code: 1, response_status: 200, response_msg: 'Call initiated.', request_id: req.body.request_id });

          } else {
            logger_all.info("LOG 3");

            // Filter to get channel IDs where results are '0'
            const sipidsWithzero = channel_ids.filter((sipid, index) => results[index] === 0);
            // Format the filtered IDs with quotes
            const sipidsWithZerosFormatted = sipidsWithzero.map(id => `'${id}'`).join(',');
            // Construct the SQL query using the correct variable 'sipidsWithZerosFormatted'
            await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'Y' WHERE sip_status IN ('P') AND sip_id IN (${sipidsWithZerosFormatted})`);
            await db.query(`UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'S' WHERE cm_status = 'P' AND compose_message_id = '${compose_message_id}'`);
            // Send success response
            await db.query(`UPDATE api_log SET response_status = '${response_status}', response_date = CURRENT_TIMESTAMP, response_comments = 'Success.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            return res.json({ response_code: 1, response_status: 200, response_msg: response_comments, request_id: req.body.request_id });
          }
        })
    }

    catch (err) {
      //If error occurs, send failure response
      await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      send_response = { response_code: 0, response_status: 201, response_msg: "Error Occurred." };
      return res.json(send_response);
    }
  }
);
//End Route - Approve OBD

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

module.exports = router;
//End Route - Reject Campaign
