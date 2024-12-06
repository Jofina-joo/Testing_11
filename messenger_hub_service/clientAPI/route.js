/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to send whatsapp message for client purpose

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 27-Apr-2024
*/

//import the required packages and files
const express = require("express");
const router = express.Router();
require("dotenv").config();
const validator = require('../validation/middleware')
const valid_user = require("../validation/cli_valid_user_middleware");
const valid_user_reqID = require("../validation/cli_valid_user_middleware_reqID");
const db = require("../db_connect/connect");
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
const ComposeCliValidation = require("../validation/compose_cli_validation");
const main = require('../logger');
const util = require("util")
const multer = require('multer');
//Start Route - Whatsapp send message
// Set up multer storage configuration
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, process.env.FILE_STORAGE); // Specify the destination directory for uploaded files
  },
  filename: function (req, file, cb) {
    var filename = `${Math.floor(Date.now() / 1000)}_${Math.floor(100 + Math.random() * 900)}_${file.originalname.replaceAll(" ", "_")}`
    cb(null, filename); // Specify the file name for uploaded files
  }
});

// Initialize multer with the storage configuration
const upload = multer({ storage: storage });

router.post(
  "/",
  (req, res, next) => {
    upload.single('receiver_no')(req, res, async (err) => {
      if (err) {
        logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Receiver Number file required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Receiver Number file required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Receiver Number file required', request_id: req.body.request_id }))
        return res.json({ response_code: 201, response_status: "Failure", response_msg: 'Receiver Number file required', request_id: req.body.request_id });
      }
      next();
    });
  },
  validator.body(ComposeCliValidation),
  valid_user_reqID,
 valid_user,
  async function (req, res, next) {
    try {
      var logger_all = main.logger_all
      var logger = main.logger
      // logger_all.info(" [compose] - " + util.inspect(req));
      logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

      if (!req.file) {
        logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Receiver Number file required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Receiver Number  file required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Receiver Number  file required', request_id: req.body.request_id }))
        return res.json({ response_code: 201, response_status: "Failure", response_msg: 'Receiver Number  file required', request_id: req.body.request_id });

      }
      //Get all request data for compose data
      var media_url = [];
      var media_url_csv;
      var is_same_msg = req.body.is_same_msg;
      var messages = req.body.messages;
      var message_type = req.body.message_type;
      var receiver_nos_path = req.file.path;
      var insert_count = 1;
      var is_same_media = req.body.is_same_media;
      var variable_count = req.body.variable_count;
      var user_id = req.body.user_id;
      var is_media = false;

    //get user_master_id
 var get_usermaster_id = `SELECT user_master_id FROM user_management where user_id = '${user_id}'`
 logger_all.info("[Select query request] : " + get_usermaster_id);
 var get_usermaster_result = await db.query(get_usermaster_id);
 logger_all.info("[Select query response] : " + JSON.stringify(get_usermaster_result))

      if (req.body.media_url) {
        //media_url.push(req.body.media_url);
        console.log(is_same_media)
        console.log("......")
        if (!is_same_media) {
          logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Is same media flag required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Is same media flag required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
          logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Is same media flag required', request_id: req.body.request_id }))
          return res.json({ response_code: 201, response_status: "Failure", response_msg: 'Is same media flag required', request_id: req.body.request_id });
        }
      }

      if (!req.body.media_url && is_same_media == 'true') {
        logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Media URL required', request_id: req.body.request_id }))
        return res.json({ response_code: 201, response_status: "Failure", response_msg: 'Media URL required', request_id: req.body.request_id });
      }

      if (is_same_media == 'true') {
        media_url.push(req.body.media_url);
      }

      if (is_same_msg == 'false') {
        var regex = /{{[a-zA-Z0-9]+}}/g;

        // Use match() to find all matches of the pattern in the text
        var matches = messages.match(regex);

        // Count the number of matches
        var count = matches ? matches.length : 0;

        if (count != variable_count) {
          logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable count not matching with the message.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable count not matching with the message.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
          logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Variable count not matching with the message.', request_id: req.body.request_id }))
          return res.json({ response_code: 0, response_status: 201, response_msg: 'Variable count not matching with the message.', request_id: req.body.request_id });

        }
      }
      const variable_values = [];
      const valid_mobile_numbers = [];
      const invalid_mobile_numbers = [];
      const maxSizeBytes = 3 * 1024 * 1024; // 3 MB
      const maxDurationSeconds = 60;
      const duplicateMobileNumbers = new Set();
      var is_same_media_flag = is_same_media != undefined && is_same_media != 'undefined' ? is_same_media : '-';

      console.log(is_same_media_flag)
      console.log("******************")
      let isFirstRow = true;
      var totalColumnCount;
      var totalColumnCount2;
      var media_count = 0;
      var MobileNo_count = 1;
      var media_type;
      var invalid_count = 0;
      var invalid_cnt = 0;
      var request_id = req.body.request_id;
      const header_token = req.headers['authorization'];
      var sender_numbers_active = [];
      var sender_numbers_inactive = [];
      var sender_id_active = [];
      var sender_devicetoken_active = [];
      const rowsToRemove = [];
      if ((is_same_media == 'false') && (is_same_msg == 'false')) {
        media_count = 1
      }
      //Get Request for send message process
      var media_url_csv_send;
      var rec_count = 0;
      var total_count = 0;
      var insert_count = 1;
      is_check = false;
      const variable_values_send = [];
      const valid_mobile_numbers_send = [];
      const invalid_mobile_numbers_send = [];
      var media_url_send = [];
      const duplicateMobileNumbers_send = new Set();
      try {
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
                if (is_same_msg == 'false') {
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
                  if ((is_same_media == 'false') && (is_same_msg == 'false' || is_same_msg == 'true') && (message_type.toUpperCase() == 'VIDEO' || message_type.toUpperCase() == 'IMAGE') && (row[i] == row[1])) {
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

            var limit_receiver = process.env.LIMIT_RECEIVER
            console.log(limit_receiver)
            console.log(valid_mobile_numbers.length)
            if (valid_mobile_numbers.length > limit_receiver) {
              logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'You cannot send more than ${limit_receiver}.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'You cannot send more than ${limit_receiver}.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
              logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: `You cannot send more than ${limit_receiver}.`, request_id: req.body.request_id }))
              return res.json({ response_code: 201, response_status: 'Failure', response_msg: `You cannot send more than ${limit_receiver}.`, request_id: req.body.request_id });

            }
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
            if ((is_same_msg == 'true' || is_same_msg == 'false') && is_same_media == 'false') {
              media_url_send = media_url;
            }
            invalid_count = invalid_mobile_numbers.length + invalid_count
            //Check if same message
            if (is_same_msg == 'false') {
              //Check if personalized message and variable count is equal to zero, send failure response 'Variable count should not be zero cause it is a customized message.'
              if (variable_count == 0) {
                //update_api_log
                logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable count should not be zero cause it is a customized message.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable count should not be zero cause it is a customized message.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Variable count should not be zero cause it is a customized message.', request_id: req.body.request_id }))
                return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Variable count should not be zero cause it is a customized message.', request_id: req.body.request_id });
              }
              //Otherwise variable values equal to zero, send failure response 'Variable values required'
              else if (!variable_values || variable_values.length == 0) {

                logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable values required.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Variable values required.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Variable values required.', request_id: req.body.request_id }))
                return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Variable values required.', request_id: req.body.request_id });
              }
            }
            if (is_same_media_flag != '-') {
              //Check if same or personalized media but media url is null then send failure response 'Media URL required'
              if (!media_url) {
                logger_all.info("[update query request - count mismatch] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Media URL required' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - count mismatch] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Media URL required', request_id: req.body.request_id }))
                return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Media URL required', request_id: req.body.request_id });
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
              if ((valid_mobile_numbers.length > available_credits) && (get_usermaster_result[0].user_mater_id != '1')) {
                logger_all.info("[update query request - Not enough credits] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Not enough credits.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Not enough credits.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - Not enough credits] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 201, response_msg: 'Not enough credits.' }))
                return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Not enough credits.', request_id: req.body.request_id })
              }

              //Check if same media and continue same media process
              if ((message_type.toUpperCase() == "VIDEO" || message_type.toUpperCase() == 'IMAGE') && is_same_media == 'true') {
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
                        logger_all.info("[update query request - Size exceeds limit] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video size should be less than 3 MB.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video size should be less than 3 MB.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        logger_all.info("[update query response - Size exceeds limit] : " + JSON.stringify(update_api_log))
                        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Video size should be less than 3 MB.', request_id: req.body.request_id }))
                        return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Video size should be less than 3 MB.', request_id: req.body.request_id });

                      }


                      // Check if duration exceeds limit, send failure response 'Video duration should be less than 30 seconds.'
                      // else if (duration.seconds > maxDurationSeconds) {
                      // console.error('Error: Duration exceeds limit.');
                      // logger_all.info("[update query request - Duration exceeds limit ] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video duration should be less than 30 seconds.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                      // const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Video duration should be less than 30 seconds.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                      // logger_all.info("[update query response - Duration exceeds limit] : " + JSON.stringify(update_api_log))
                      // logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Video duration should be less than 30 seconds.', request_id: req.body.request_id }))
                      // return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Video duration should be less than 30 seconds.', request_id: req.body.request_id });

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
                        return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Image size should be less than 5 MB.', request_id: req.body.request_id });

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
                  return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Media URL Not Found.' })
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
                var compose_message_id = 1
                if (select_compose_id.length == 0) {
                  compose_unique_name = `ca_${user_id}_${new Date().julianDate()}_1`;
                }

                else { // Otherwise to get the select_compose_id using
                  compose_unique_name = `ca_${user_id}_${new Date().julianDate()}_${select_compose_id[0].compose_message_id + 1}`;
                  compose_message_id = select_compose_id[0].compose_message_id + 1
                }

                if (is_same_media_flag == "-") {
                  media_url_check = 'NULL'
                }
                else if (is_same_media_flag == true || is_same_media_flag == 'true') {
                  media_url_check = media_url[0]
                  logger.info("[media_url_check] " + media_url_check)
                }
                else {
                  media_url_check = '-'
                }

                //Call Stored Procedure ComposeProcedure
                const get_compose_procedure = await db.query(
                  `CALL ComposeProcedure('${user_id}','${product_id}','${valid_mobile_numbers}','${message_type}','${is_same_media_flag}','${messages}','${media_url_check}','${compose_unique_name}','${is_same_msg}','${variable_count}','-','${receiver_nos_path}','${valid_mobile_numbers.length}')`
                );

                //Update invalid mobile number count
                if (invalid_count > 0) {
                  invalid_cnt = invalid_count
                }
                //Send success response
                logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.', invalid_count: invalid_cnt }))
                try {
                  //Query to get compose data
                  var compose_message_id = select_compose_id[0].compose_message_id + 1;
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
                    logger_all.info("[update query request - compose ID not available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Compose ID Not Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Compose ID Not Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    logger_all.info("[update query response - compose ID not available] : " + JSON.stringify(update_api_log))
                    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Compose ID Not Available' }))
                    return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Compose ID Not Available', request_id: req.body.request_id });
                  }
                  mobile_no_cnt = get_user_det[0].total_mobile_no_count;

                  //Query to select sender mobile number
                  var active_senderID = `SELECT DISTINCT mobile_no FROM ${DB_NAME}.sender_id_master WHERE sender_id_status ='Y' and is_qr_code = 'N'`
                  logger_all.info("[Select query request - sender number] : " + active_senderID);
                  var active_senderID_result = await db.query(active_senderID);
                  logger_all.info("[Select query response - sender number] : " + JSON.stringify(active_senderID_result))

                  //check if sender_numbers_active lenght is equal to zero, send error response as 'No Sender ID available'
                  if (active_senderID_result.length == 0) {
                    //update_api_log
                    logger_all.info("[update query request - No Sender ID Available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    logger_all.info("[update query response -No Sender ID Available] : " + JSON.stringify(update_api_log))
                    return res.json({
                      response_code: 204,
                      response_status: 'Failure',
                      response_msg: 'No Sender ID Available.',
                      request_id: req.body.request_id
                    });
                  }


                  for (var i = 0; i < active_senderID_result.length; i++) {
                    var senderID_active_result = active_senderID_result[i].mobile_no;
                    logger_all.info("senderID_active_result " + senderID_active_result)

                    //Query to get active sender ID
                    var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${senderID_active_result}' AND sender_id_status = 'Y' AND is_qr_code ='N'`

                    logger_all.info("[Select query request - Active sender ID] : " + senderID_active);
                    var select_sender_id_active = await db.query(senderID_active);
                    logger_all.info("[Select query response - Active sender ID] : " + JSON.stringify(select_sender_id_active))
                    //check if select_sender_id_active length is not equal to zero, store active numbers to sender_numbers_active array

                    if (select_sender_id_active.length != 0) {
                      sender_numbers_active.push(select_sender_id_active[0].mobile_no)
                      sender_devicetoken_active.push(select_sender_id_active[0].device_token)
                    }

                    //Otherwise store inactive numbers to sender_numbers_inactive array
                    else {
                      sender_numbers_inactive.push(select_sender_id_active[i].sender_mobile_no)
                    }

                  }

                  //Check if sender numbers length is greater than total mobile number count, send failure response 'Sender Numbers should be less than Receiver Numbers'
                  if (sender_numbers_active.length > mobile_no_cnt) {
                    logger_all.info("[update query request - Sender Numbers should be less than Receiver Numbers] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender Numbers should be less than Receiver Numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    /*const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender Numbers should be less than Receiver Numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    logger_all.info("[update query response -Sender Numbers should be less than Receiver Numbers] : " + JSON.stringify(update_api_log))
                    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Sender Numbers should be less than Receiver Numbers' }))
                    return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Sender Numbers should be less than Receiver Numbers', request_id: req.body.request_id });*/

                    var sender_numbers_active1 = [];
                    for (var s = 0; s < mobile_no_cnt; s++) {
                      sender_numbers_active1.push(sender_numbers_active[s])
                    }

                    sender_numbers_active = sender_numbers_active1
                  }

                  //Get required data from query
                  receiver_nos_path = get_user_det[0].receiver_nos_path;
                  product_id_one = get_user_det[0].product_id;
                  message_type = get_user_det[0].campaign_type;
                  // mobile_no_cnt = get_user_det[0].total_mobile_no_count;
                  is_same_media_flag = get_user_det[0].is_same_media;
                  variable_count = get_user_det[0].variable_count;
                  is_same_msg = get_user_det[0].is_same_msg;
                  messages = get_compose_data_result[0].text_title;
                  if (is_same_media_flag == "true") {
                    media_url_send = get_compose_data_result[0].media_url;
                  }
                  logger.info("media_url " + media_url_send)
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
                        if (duplicateMobileNumbers_send.has(firstColumnValue)) {
                          invalid_mobile_numbers_send.push(firstColumnValue);
                        } else {
                          duplicateMobileNumbers_send.add(firstColumnValue);
                          if (isValidFormat) {
                            valid_mobile_numbers_send.push(firstColumnValue);
                            // Create a new array for each row
                            const otherColumnsArray = [];
                            let secondColumnValue;
                            for (let i = 1; i < Object.keys(row).length; i++) {
                              // Skip processing if the mobile number is invalid
                              if (!isValidFormat) {
                                break;
                              }
                              logger_all.info(Object.keys(row).length);
                              if ((is_same_media_flag === "false") && (is_same_msg === "false") && (message_type.toUpperCase() === 'VIDEO' || message_type.toUpperCase() === 'IMAGE') && i == 1) {
                                // Check if the second column value is not empty and equal to the current column value
                                // if (row[1].trim() && row[1].trim() === row[i].trim()) {
                                logger_all.info('Values in the second column and column ' + (i + 1) + ' are equal');
                                // Process the second column value if it hasn't been processed already
                                // if (i === 1) {
                                logger_all.info('Processing second column value...' + row[1].trim() + "$$$$$$$$$$");
                                const secondColumnValue = row[1].trim();
                                media_url_send.push(secondColumnValue);
                                continue;
                                // }
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
                              variable_values_send.push(otherColumnsArray);
                            }
                          } else {
                            invalid_mobile_numbers_send.push(firstColumnValue);
                          }
                        }
                      })
                      .on('error', (error) => {
                        console.error('Error:', error.message);
                      })
                      .on('end', async () => {
                        if (is_same_msg == "false" && is_same_media_flag == "false") {
                          media_url_csv_send = media_url_send;

                        }
                        //Do process only valid numbers not equal to zero
                        if (valid_mobile_numbers_send.length != 0) {
                          logger_all.info("[variable_values_send] : " + variable_values_send)
                          rec_no = valid_mobile_numbers_send;
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
                                    logger_all.info("variable_values[k][j-1]" + variable_values_send[k][j - 1])

                                    //cus_message = cus_message.replace(`{{${j}}}`,variable_values[k][j-1]);
                                    cus_message = cus_message.replace(/{{(\w+)}}/, variable_values_send[k][j - 1]);
                                  }
                                }

                                //Get media URL for same media
                                if (is_same_media_flag != '-') {
                                  logger_all.info("[is_same_media_flag_test] : " + is_same_media_flag)
                                  logger_all.info("kk.." + k)
                                  media = is_same_media_flag == "true" ? media_url_send : media_url_send[k];
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
                            for (var i = 0; i < sender_numbers_active.length; i++) {

                              //Update sender ID status as 'P' while approve
                              var update_senderID_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[i]}'`;
                              logger_all.info("[insert query request] : " + update_senderID_sts);
                              var update_senderID_sts_res = await db.query(update_senderID_sts);
                              logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
                            }

                            //Send push notification
                            var data = JSON.stringify({
                              "registration_ids": sender_devicetoken_active,
                              "priority": "high",
                              // "notification": {
                              // "body": "test",
                              // "content-available": true,
                              // "priority": "high",

                              // },
                              "data": {
                                "title": compose_message_id,
                                "selected_user_id": user_id,
                                "product_id": product_id_two,
                                "priority": "high",
                                "content-available": true,
                                "bodyText": "WTSP_MSG"
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

                            //Update total process, total waiting in summary report
                            var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_waiting = 0,total_process = total_process+${valid_mobile_numbers_send.length},sum_start_date = CURRENT_TIMESTAMP WHERE com_msg_id = '${compose_message_id}'`
                            logger_all.info("[update_summary_report] : " + update_summary_report);
                            var update_summary_report_res = await db.query(update_summary_report);
                            logger_all.info("[update_summary_report response] : " + JSON.stringify(update_summary_report_res))

                            //Send success response
                            logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                            logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
                            logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))
                            return res.json({ response_code: 200, response_status: "Success", response_msg: 'Success.', invalid_count: invalid_cnt, campaign_id: compose_message_id, campaign_name: compose_unique_name, request_id: req.body.request_id })

                          }
                          else {

                            //Otherwise send failure response 'Campaign Not Found'
                            logger_all.info("[update query request - campaign not found] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                            logger_all.info("[update query response - campaign not found] : " + JSON.stringify(update_api_log))
                            return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Campaign Not Found' });
                          }
                        }
                      })
                  }
                }
                catch (err) {

                  logger_all.info("[update query request - Error Occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                  const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                  logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
                  logger_all.info(": [check] Failed - " + err);
                  logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
                  return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Error Occurred.', request_id: req.body.request_id });
                }

              }
              catch (e) {
                logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))

                logger.info("[API RESPONSEE] " + e)
                logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error occurred.' }))
                return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Error occurred.' })
              }

            }
            else {
              //Otherwise send failure response 'Campaign Failed'
              logger_all.info("[update query request - Campaign Failed] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Failed' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Failed' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              logger_all.info("[update query response - Campaign Failed] : " + JSON.stringify(update_api_log))
              logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Campaign Failed', request_id: req.body.request_id }))
              return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Campaign Failed', request_id: req.body.request_id });
            }

          })
      }

      catch (err) {
        logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))

        logger_all.info("err" + err);
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))

        return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Error Occurred.' });
      }


    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

module.exports = router;
//End Route - Whatsapp send message
