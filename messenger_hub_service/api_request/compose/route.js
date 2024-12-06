/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in compose functions which is used to compose whatsapp message.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const express = require("express");
const router = express.Router();
require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
const db = require("../../db_connect/connect");
const { Client, LocalAuth, Buttons, MessageMedia, Location, List } = require('whatsapp-web.js');
var axios = require('axios');
const fse = require('fs-extra');
const fs = require('fs');
const csv = require('csv-parser');
const qrcode = require('qrcode-terminal');
const ffmpeg = require('fluent-ffmpeg');
const { videoDuration } = require("@numairawan/video-duration");
const env = process.env
const DB_NAME = env.DB_NAME;
const chrome_path = env.GOOGLE_CHROME;
const waiting_time = env.WAITING_TIME;
const fcm_key = env.NOTIFICATION_SERVER_KEY;
const ComposeValidation = require("../../validation/compose_validation");
const main = require('../../logger');
const util = require('util');
const exec_wait = util.promisify(require('child_process').exec);

//Start Route - Compose Whatsapp
router.post(
  "/",
  validator.body(ComposeValidation),
  valid_user_reqID,
  valid_user,
  async function (req, res, next) {
    try {
      var logger_all = main.logger_all
      var logger = main.logger
      logger_all.info(" [compose] - " + req.body);
      logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

      //Get all request data
      var media_url = [];
      var media_url_csv;
      var is_same_msg = req.body.is_same_msg;
      var messages = req.body.messages;
      var message_type = req.body.message_type;
      var receiver_nos_path = req.body.receiver_nos_path;
      var insert_count = 1;
      var is_same_media = req.body.is_same_media;
      var variable_count = req.body.variable_count;
      var user_id = req.body.user_id;
      var is_media = false;
      /*logger_all.info(`First File moving to server - sudo scp ${env.Auth}@${env.Hostname}:${receiver_nos_path} ${env.FILE_STORAGE}`)
      var { firststdout, stderr } = await exec_wait(`sudo scp ${env.Auth}@${env.Hostname}:${receiver_nos_path} ${env.FILE_STORAGE}`)
      var firststderrLines = stderr.split('\n');

      // Filter out non-error messages
      var firsterrorLines = firststderrLines.filter(line => line.toLowerCase().includes(' error'));
      if (firsterrorLines.length > 0) {
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 201, response_msg: ' scp failed' }))
        return res.json({ response_code: 1, response_status: 201, response_msg: 'scp failed', request_id: req.body.request_id })
      }*/

      if (is_same_media == true) {
        media_url = req.body.media_url;
      }
      const variable_values = [];
      const valid_mobile_numbers = [];
      const invalid_mobile_numbers = [];
      const maxSizeBytes = 3 * 1024 * 1024; // 3 MB
      const maxDurationSeconds = 60;
      const duplicateMobileNumbers = new Set();
      var is_same_media_flag = is_same_media != undefined && is_same_media != 'undefined' ? is_same_media : '-';
      let isFirstRow = true; // Flag to skip the first row
      var totalColumnCount;
      var totalColumnCount2;
      var media_count = 0;
      var MobileNo_count = 1;
      var media_type;
      var invalid_count = 0;
      var invalid_cnt = 0;
      var request_id = req.body.request_id;
      var user_master_id = req.body.user_master_id;
      const header_token = req.headers['authorization'];
      var sender_numbers_active = [];
      var sender_numbers_inactive = [];
      var sender_id_active = [];
      var sender_devicetoken_active = [];
      const rowsToRemove = [];

      if ((is_same_media == false) && (is_same_msg == false)) {
        media_count = 1
      }
      try {
        user_id = req.body.user_id;
        // Fetch the CSV file
        fs.createReadStream(receiver_nos_path)
          // Read the CSV file from the stream
          .pipe(csv({
            headers: false
          })) // Set headers to false since there are no column headers
          .on('data', (row) => {
            const nonEmptyValues = Object.values(row).filter(value => value.trim() !== ''); // Filter out empty values
            if (nonEmptyValues.length === 0) {
              return; // Skip row if all values are empty
            }
            const firstColumnValue = row[0].trim();
            // Validate mobile number format
            const isValidFormat = /^\d{12}$/.test(firstColumnValue) && firstColumnValue.startsWith('91') && /^[6-9]/.test(firstColumnValue.substring(2, 3));

            // Check for duplicates
            if (duplicateMobileNumbers.has(firstColumnValue)) {
              invalid_mobile_numbers.push(firstColumnValue);
              rowsToRemove.push(row);
            } else {
              duplicateMobileNumbers.add(firstColumnValue);
              if (isValidFormat) {
                if (is_same_msg == false) {
                  total_row_count = MobileNo_count + media_count + parseInt(variable_count);
                  const rowCount = nonEmptyValues.length;
                  if (total_row_count > rowCount) {
                    console.log(JSON.stringify(row) + "row");
                    rowsToRemove.push(row);
                    invalid_count++;
                    return;
                  }
                }
                valid_mobile_numbers.push(firstColumnValue);
                // Create a new array for each row
                const otherColumnsArray = [];
                let secondColumnValue;
                for (let i = 1; i < Object.keys(row).length; i++) {
                  // Skip processing if the mobile number is invalid
                  if (!isValidFormat) {
                    break;
                  }
                  // console.log('media_url_row:', row[1]);
                  if ((is_same_media == false) && (is_same_msg == false) && (message_type == 'VIDEO' || message_type == 'IMAGE') && (row[i] == row[1])) {
                    secondColumnValue = row[1].trim();
                    media_url.push(secondColumnValue);
                    i++;
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
                rowsToRemove.push(row);
                invalid_mobile_numbers.push(firstColumnValue);
              }
            }
          })
          .on('error', (error) => {
            console.error('Error:', error.message);
          })
          .on('end', async () => {

            // Read the entire file
            const fileData = fs.readFileSync(receiver_nos_path, 'utf8').split('\n');

            // Remove the rows that need to be removed
            rowsToRemove.forEach(row => {
              const rowString = Object.values(row).join(',');
              const index = fileData.indexOf(rowString);
              if (index !== -1) {
                fileData.splice(index, 1);
              }
            });

            // Write the modified data back to the file
            fs.writeFileSync(receiver_nos_path, fileData.join('\n'));
            if (is_same_msg == false && is_same_media == false) {
              media_url_csv = media_url;
            }

            invalid_count = invalid_mobile_numbers.length + invalid_count

            //Check if same message
            if (!is_same_msg) {
              //Check if personalized message and variable count is equal to zero, send failure response 'Variable count should not be zero cause it is a customized message.'
              if (variable_count == 0) {
                //update_api_log
                logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable count should not be zero cause it is a customized message.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable count should not be zero cause it is a customized message.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Variable count should not be zero cause it is a customized message.', request_id: req.body.request_id }))
                return res.json({ response_code: 0, response_status: 201, response_msg: 'Variable count should not be zero cause it is a customized message.', request_id: req.body.request_id });
              }

              //Otherwise variable values equal to zero, send failure response 'Variable values required'
              else if (!variable_values || variable_values.length == 0) {
                //update_api_log
                logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable values required.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable values required.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Variable values required.', request_id: req.body.request_id }))
                return res.json({ response_code: 0, response_status: 201, response_msg: 'Variable values required.', request_id: req.body.request_id });
              }
            }
            if (is_same_media_flag != '-') {
              //Check if same or personalized media but media url is null then send failure response 'Media URL required'
              if (!media_url) {
                logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Media URL required', request_id: req.body.request_id }))
                return res.json({ response_code: 0, response_status: 201, response_msg: 'Media URL required', request_id: req.body.request_id });
              }
            }


            //Continue process only if valid mobile numbers not equal to empty
            if (valid_mobile_numbers.length != 0) {

              //Query to get product 
              var get_product = `SELECT * FROM rights_master where rights_name = 'WHATSAPP' AND rights_status = 'Y' `;
              logger_all.info("[select query request] : " + get_product);
              const get_product_id = await db.query(get_product);
              logger_all.info("[select query response] : " + JSON.stringify(get_product_id));
              product_id = get_product_id[0].rights_id;
              logger_all.info("[select query request] : " + product_id);

              //Query to get available credits
              var get_used_credits = `SELECT * FROM user_credits where user_id = '${user_id}' AND uc_status = 'Y' AND rights_id = '${product_id}' `;
              logger_all.info("[select query request] : " + get_used_credits);
              const get_used_credits_id = await db.query(get_used_credits);
              logger_all.info("[select query response] : " + JSON.stringify(get_used_credits_id));
              available_credits = get_used_credits_id[0].available_credits;
              logger_all.info("[total_credits] : " + available_credits);

              //Check if total valid numbers length greater than available credits, then send failure response 'Not Enough Credits'
              if ((valid_mobile_numbers.length > available_credits) && user_master_id != '1' ) {
                logger_all.info("[update query request - Not enough credits] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Not enough credits.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Not enough credits.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - Not enough credits] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 201, response_msg: 'Not enough credits.' }))
                return res.json({ response_code: 1, response_status: 201, response_msg: 'Not enough credits.', request_id: req.body.request_id })
              }


              //Check if same media and continue same media process
              if ((message_type == "VIDEO" || message_type == 'IMAGE') && is_same_media == true) {
                //setTimeout(async () => {
                logger_all.info("Coming IF Condition" + media_url)
                try {
                  logger_all.info("Coming try Condition" + media_url)
                  logger_all.info("Coming start" + media_url)

                  // Use string manipulation to get the file extension
                  const parts = media_url.toString().split('.');
                  const fileExtension = parts[parts.length - 1];

                  const response = await axios.head(media_url);
                  const contentLength = response.headers['content-length'];

                  logger_all.info("File Extension:", fileExtension);


                  //Video Extension
                  if (['mp4', 'avi', 'mkv', 'mov', 'wmv'].includes(fileExtension.toLowerCase())) {
                    logger_all.info("It's an video file!")
                    logger_all.info("contentLength" + contentLength)

                    //Calculate media size 
                    if (contentLength) {
                      const sizeInBytes = parseInt(contentLength, 10);
                      const sizeInKilobytes = sizeInBytes / 1024;
                      const sizeInMegabytes = sizeInKilobytes / 1024;
                      logger_all.info("sizeInBytes" + sizeInBytes)
                      const duration = await videoDuration(media_url.toString());
                      console.log(`Duration: ${duration.seconds} seconds`);
                      logger_all.info("Coming IF Condition" + media_url)

                      //Check if media size exceeds 5 MB size, send failure response 'Video size should be less than 5 MB.'
                      if (sizeInBytes > maxSizeBytes) {
                        console.error('Error: Size exceeds limit.');
                        logger_all.info("[update query request - Size exceeds limit] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video size should be less than 5 MB.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video size should be less than 5 MB.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        logger_all.info("[update query response - Size exceeds limit] : " + JSON.stringify(update_api_log))
                        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Video size should be less than 5 MB.', request_id: req.body.request_id }))
                        return res.json({ response_code: 0, response_status: 201, response_msg: 'Video size should be less than 5 MB.', request_id: req.body.request_id });

                      }
                      //       Check if duration exceeds limit, send failure response 'Video duration should be less than 30 seconds.'
                      //      else if (duration.seconds > maxDurationSeconds) {
                      //      console.error('Error: Duration exceeds limit.');
                      //                logger_all.info("[update query request - Duration exceeds limit ] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video duration should be less than 30 seconds.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                      //              const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video duration should be less than 30 seconds.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                      //            logger_all.info("[update query response - Duration exceeds limit] : " + JSON.stringify(update_api_log))
                      //          logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Video duration should be less than 30 seconds.', request_id: req.body.request_id }))
                      //        return res.json({ response_code: 0, response_status: 201, response_msg: 'Video duration should be less than 30 seconds.', request_id: req.body.request_id });
                      // }

                    }
                    else {
                      logger_all.info('Content-Length header not found. Unable to determine video size.');
                    }
                  }

                  //Image Extension
                  else if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(fileExtension.toLowerCase())) {
                    if (contentLength) {
                      const sizeInBytes = parseInt(contentLength, 10);
                      const sizeInKilobytes = sizeInBytes / 1024;
                      const sizeInMegabytes = sizeInKilobytes / 1024;
                      logger_all.info("sizeInBytes" + sizeInBytes)

                      //Check media size exceeds 5 MB size, then send failure response 'Image size should be less than 5 MB.'
                      if (sizeInBytes > maxSizeBytes) {
                        logger_all.info('Error: Size exceeds limit.');
                        logger_all.info("[update query request - Size exceeds limit] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Image size should be less than 5 MB.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Image size should be less than 5 MB.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        logger_all.info("[update query response - Size exceeds limit] : " + JSON.stringify(update_api_log))
                        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Image size should be less than 5 MB.', request_id: req.body.request_id }))
                        return res.json({ response_code: 0, response_status: 201, response_msg: 'Image size should be less than 5 MB.', request_id: req.body.request_id });

                      }

                    } else {
                      logger_all.info("Content-Length header not found. Unable to determine image size.")
                    }
                  }
                }
                catch (e) {

                  //If error occurs, send failure response 'Media URL Not Found.'
                  logger_all.info("[update query request - Media URL Not Found] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                  const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                  logger_all.info("[update query response - Media URL Not Found] : " + JSON.stringify(update_api_log))

                  logger_all.info("[API RESPONSEE] " + e)
                  logger_all.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Media URL Not Found.' }))
                  return res.json({ response_code: 0, response_status: 201, response_msg: 'Media URL Not Found.' })
                }
                //}, 3000); // 3 seconds delay
              }


              try {
                var compose_unique_name;
                Date.prototype.julianDate = function () {
                  var j = parseInt((this.getTime() - new Date('Dec 30,' + (this.getFullYear() - 1) + ' 23:00:00').getTime()) / 86400000).toString(),
                    i = 3 - j.length;
                  while (i-- > 0) j = 0 + j;
                  return j
                };

                logger_all.info("[select query request] : " + `SELECT compose_message_id from ${DB_NAME}_${user_id}.compose_message_${user_id} ORDER BY compose_message_id desc limit 1`)
                const select_compose_id = await db.query(`SELECT compose_message_id from ${DB_NAME}_${user_id}.compose_message_${user_id} ORDER BY compose_message_id desc limit 1`);
                logger_all.info("[select query response] : " + JSON.stringify(select_compose_id))
                // To select the select_compose_id length is '0' to create the compose unique name
                if (select_compose_id.length == 0) {
                  compose_unique_name = `ca_${user_id}_${new Date().julianDate()}_1`;
                }

                else { // Otherwise to get the select_compose_id using
                  compose_unique_name = `ca_${user_id}_${new Date().julianDate()}_${select_compose_id[0].compose_message_id + 1}`;

                }

                if (is_same_media_flag == "-") {
                  media_url_check = 'NULL'
                }
                else if (is_same_media_flag == true) {
                  media_url_check = media_url[0]
                  logger.info("[media_url_check] " + media_url_check)
                }
                else {
                  media_url_check = '-'
                }

                var insert_compose = `INSERT INTO ${DB_NAME}_${user_id}.compose_message_${user_id} VALUES(NULL,'${user_id}','${product_id}','-','${valid_mobile_numbers}','${message_type}','${is_same_media_flag}',${valid_mobile_numbers.length},0,'${compose_unique_name}','N',CURRENT_TIMESTAMP,'${receiver_nos_path}','-','${is_same_msg}','${variable_count}','-','N',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL)`;
                logger_all.info("[insert query request] : " + insert_compose)
                const insert_compose_result = await db.query(insert_compose);
                logger_all.info("[insert query response] : " + JSON.stringify(insert_compose_result))
                // To get the compose insert id.
                compose_message_id = insert_compose_result.insertId;
                // Insert media (e.g., image) for the compose message
                var insert_media = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_media_${user_id} VALUES(NULL,'${compose_message_id}',"${messages}",NULL,NULL,NULL,NULL,'${media_url_check}','${message_type}','Y',CURRENT_TIMESTAMP)`
                logger_all.info("[insert query request - insert media] : " + insert_media)
                const insert_media_result = await db.query(insert_media);
                logger_all.info("[insert query response - insert media] : " + JSON.stringify(insert_media_result))

                   if(user_master_id != '1'){
                //Update credits base on SMS calculation
                var update_user_credits = `UPDATE user_credits SET used_credits = used_credits+ ${valid_mobile_numbers.length},available_credits = available_credits - ${valid_mobile_numbers.length} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
                logger_all.info("[update query request - update user credits] : " + update_user_credits);
                var update_user_credits_res = await db.query(update_user_credits);
                logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
                   }

                //Update campaign status as waiting while compose
                var update_campaign_sts = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'W' WHERE compose_message_id = '${compose_message_id}'`;
                logger_all.info("[insert query request] : " + update_campaign_sts);
                var update_campaign_sts_res = await db.query(update_campaign_sts);
                logger_all.info("[insert query response] : " + JSON.stringify(update_campaign_sts_res))

                //Update data in summary report
                var insert_summary_report = `INSERT INTO ${DB_NAME}.user_summary_report VALUES(NULL,'${user_id}','${product_id}','${compose_message_id}','${compose_unique_name}','${valid_mobile_numbers.length}','${valid_mobile_numbers.length}',0,0,0,0,0,'N',CURRENT_TIMESTAMP,NULL,NULL)`;
                logger_all.info("[insert query request] : " + insert_summary_report);
                var insert_summary_report_res = await db.query(insert_summary_report);
                logger_all.info("[insert query response] : " + JSON.stringify(insert_summary_report_res))

                //Update invalid mobile number count
                if (invalid_count > 0) {
                  invalid_cnt = invalid_count
                }
                //Send success response
                logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.', invalid_count: invalid_cnt }))
                return res.json({ response_code: 1, response_status: 200, response_msg: 'Success.', invalid_count: invalid_cnt })
              }
              catch (e) {
                logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))

                logger.info("[API RESPONSEE] " + e)
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error occurred.' }))
                return res.json({ response_code: 0, response_status: 201, response_msg: 'Error occurred.' })
              }

            }
            else {
              //Otherwise send failure response 'Campaign Failed'
              logger_all.info("[update query request - Campaign Failed] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Failed' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Failed' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              logger_all.info("[update query response - Campaign Failed] : " + JSON.stringify(update_api_log))
              logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Campaign Failed', request_id: req.body.request_id }))
              return res.json({ response_code: 0, response_status: 201, response_msg: 'Campaign Failed', request_id: req.body.request_id });
            }

          })
      }

      catch (err) {
        logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))

        logger_all.info("err" + err);
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))

        return res.json({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' });
      }


    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

module.exports = router;
//End Route - Compose Whatsapp