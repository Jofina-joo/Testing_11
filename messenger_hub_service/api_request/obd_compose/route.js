/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the sms compose.

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
const fs = require('fs');
const csv = require('csv-parser');
const moment = require('moment');
const date = moment();
const env = process.env
const DB_NAME = env.DB_NAME;
const path = require('path');
var axios = require('axios');
// Import the functions page
const Create_Prompt = require("./create_prompt");
const obdCampaignList = require("./obd_campaign_list");
const Stop_Campaign_Process = require("./stop_campaign_process");
const Restart_Campaign_Process = require("./restart_campaign_process.js");
const PromptComposeValidation = require("../../validation/prompt_compose_validation");
const OBDComposeValidation = require("../../validation/obd_compose_validation");
const ListCampaignValidation = require("../../validation/list_campaign_validation");
const main = require('../../logger');
const util = require('util');
const exec_wait = util.promisify(require('child_process').exec);

// CreatePrompt - start
router.post(
  "/create_prompt",
  validator.body(PromptComposeValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try { // access the PromptComposeValidation function
      const logger_all = main.logger_all
      const logger = main.logger

      const result = await Create_Prompt.CreatePrompt(req);
      result['request_id'] = req.body.request_id;
      //check if response code is equal to zero, update api log entry as response_status = 'F',response_comments = 'response msg'
      if (result.response_code == 0) {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      }
      else {  // Otherwise update api log entry	as response_status = 'S',response_comments = 'Success'
        await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      }

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// CreatePrompt - end

// obd_campaign_list - start
router.get(
  "/obd_campaign_list",
  validator.body(ListCampaignValidation),
  async function (req, res, next) {
    try { // access the PromptComposeValidation function
      // Destructure loggers from main
      const { logger_all, logger } = main;
      const result = await obdCampaignList.CampaignListOBD(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// obd_campaign_list - end

//Start Route - OBD CALL Compose
router.post(
  "/compose_obd",
  validator.body(OBDComposeValidation),
  valid_user_reqID,
  valid_user,
  async function (req, res, next) {
      // Destructure loggers from main
    const { logger_all, logger } = main;
    try {

      // Destructure required properties from req.body
      const { is_same_msg, call_retry_count, message_type, receiver_nos_path, retry_time, slt_context_id: context_id, user_id, sms_message, sms_duration, user_master_id } = req.body;
      const send_sms = req.body.send_sms || 'N'; let send_response = {};
      const bearerHeader = req.headers['authorization'];
      let valid_mobile_numbers = [];
      const requestId = `${user_id}_${date.format('YYYY')}${date.dayOfYear()}${date.format('HHmmss')}_${Math.floor(1000 + Math.random() * 9000)}`;
      /*const send_command = `scp ${env.Auth}@${env.Hostname}:${receiver_nos_path} ${env.Yeejai_STORAGE}`
       logger_all.info(`First File moving to server - `, send_command)
       var { firststdout, stderr } = await exec_wait(send_command)
       var firststderrLines = stderr.split('\n');
 
       // Filter out non-error messages
       var firsterrorLines = firststderrLines.filter(line => line.toLowerCase().includes(' error'));
       if (firsterrorLines.length > 0) {
         send_response = { response_code: 1, response_status: 201, response_msg: ' scp failed' }
         return res.json(send_response)
       }*/

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

      // Fetch the CSV file
      fs.createReadStream(receiver_nos_path)
        .pipe(csv({ headers: false })) // Set headers to false since there are no column headers
        .on('data', (row) => {
          // Push trimmed first column value
          valid_mobile_numbers.push(row[0]?.trim());
        })
        .on('error', async (error) => {
          logger_all.info('Error:', error.message);
        })
        .on('end', async () => {
          //Continue process only if valid mobile numbers length is not equal to zero
          if (valid_mobile_numbers.length == 0) {
            //Otherwise send failure response 'The valid mobile numbers count is zero; cannot create the campaign.'
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = "The valid mobile numbers count is zero; cannot create the campaign." WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: "The valid mobile numbers count is zero; cannot create the campaign.", request_id: req.body.request_id }
            return res.json(send_response);
          }
            // Tocheck Test user for only compose the below 10 numbers.
            if ( user_master_id == '4' && valid_mobile_numbers.length > 10) {
              //If check user_master_id is '4' and valid_mobile_numbers are greater than 10
              await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = ' Test User have below 10 numbers.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              send_response = { response_code: 0, response_status: 201, response_msg: ' Test User have below 10 numbers' }
              return res.json(send_response)
            }

          // get context,audio duration based on obd_prompt_masters tables.
          const get_context_result = await db.query(`SELECT audio_duration,context FROM obd_prompt_masters where prompt_id = '${context_id}' AND prompt_status = 'Y' `);
          //Continue process only if get_context_result length is not equal to zero
          if (get_context_result.length == 0) {
            //Otherwise send failure response 'Context ID is not available'
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Context ID is not available' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: 'Context ID is not available' }
            return res.json(send_response)
          }
          // get context values 
          const promptSecond = get_context_result[0].audio_duration;
          const context = get_context_result[0].context;

          //Query to get rights id for obd call sip product
          const get_product_id = await db.query(`SELECT rights_id FROM rights_master where rights_name = 'OBD CALL SIP' AND rights_status = 'Y'`);
          //Continue process only if get_product_id length is not equal to zero
          if (get_product_id.length == 0) {
            //Otherwise send failure response 'Inactive rights'
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Inactive rights' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: 'Inactive rights' }
            return res.json(send_response)
          }
          // get product values 
          const product_id = get_product_id[0].rights_id;

          //Query to get user credits in rights based.
          const get_used_credits_id = await db.query(`SELECT available_credits FROM user_credits where user_id = '${user_id}' AND uc_status = 'Y' AND rights_id = '${product_id}'`);
          //Continue process only if get_used_credits_id length is not equal to zero
          if (get_used_credits_id.length == 0 && user_master_id != '1') {
            //Otherwise send failure response 'Not used credits for this user'
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Not used credits for this user' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: 'Not used credits for this user' }
            return res.json(send_response)
          }
          const available_credits = get_used_credits_id[0].available_credits;
          //  Deduct the credit based on audio duration.
          const creditsToDeduct = deductCredits(promptSecond);
          // multiply the total mobile nos and total credits.
          const msg_mobile_credits = valid_mobile_numbers.length * creditsToDeduct;

          logger_all.info(msg_mobile_credits + "msg_mobile_credits")
          //Check if total mobile number length is greater than available credits, then send failure response 'Not enough credits.'
          if ((msg_mobile_credits > available_credits) && user_master_id != '1') {
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Not enough credits.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 0, response_status: 201, response_msg: 'Not enough credits.' }
            return res.json(send_response)
          }


          try {
            // generate the campaign name (context + current date&time + random numbers )
            const curdtm = moment().format('YYYYMMDDHHmmss');
            let randomNumbers = '';
            for (let i = 0; i < 3; i++) {
              randomNumbers += Math.floor(Math.random() * 10); // Generate a random number between 0 and 9
            }
            let compose_unique_name = context + curdtm + randomNumbers;
              // Insert the compose message tables
            const insert_compose_result = await db.query(`INSERT INTO ${DB_NAME}_${user_id}.compose_message_${user_id} VALUES(NULL, '${user_id}', '${product_id}', '-', '-', '${message_type}', '-', ${valid_mobile_numbers.length}, ${valid_mobile_numbers.length} , '${compose_unique_name}', 'W', CURRENT_TIMESTAMP, '${receiver_nos_path}', '-', '${is_same_msg}', '0', '-', 'N','${context_id}','${call_retry_count}','${retry_time}',NULL,NULL, ${sms_duration !== undefined ? `'${sms_duration}'` : 'NULL'}, ${sms_message !== undefined ? `'${sms_message}'` : 'NULL'},'${send_sms}')`);
            // Get the compose insert ID
            const compose_message_id = insert_compose_result.insertId;

            // Update credits based on prompt calculation
            await db.query(`UPDATE user_credits SET used_credits = used_credits + ${msg_mobile_credits}, available_credits = available_credits - ${msg_mobile_credits} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`);
            // If execute the Test user for this conditions. otherwise send success response.
            if (user_master_id == 4) {
              logger_all.info("Coming To the Test user")
              //get channel status as 'T' while sip servers
              const testuser_server_res = await db.query(`SELECT sip_id from sip_servers WHERE sip_status in ('T')`);
              //Continue process only if testuser_server_res length is not equal to zero
              if (testuser_server_res.length == 0) {
                //Otherwise send failure response 'Test server is busy.Please wait'
                await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Test server is busy.Please wait' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                send_response = { response_code: 0, response_status: 201, response_msg: 'Test server is busy.Please wait' }
                return res.json(send_response)
              }
              // get sip id for Test user
              const server_id = testuser_server_res[0].sip_id;

              const payload = {
                compose_message_id: String(compose_message_id),
                selected_user_id: String(user_id),
                channel_ids: [String(server_id)],
                channel_percentage: ["100"],
                request_id: String(requestId)
              };
              async function approve() {
                try {
                  // Add the Authorization header with the bearer token
                  const config = {
                    headers: {
                      Authorization: `${bearerHeader}` // Ensure user_bearer_token is defined and contains the token
                    }
                  };
                  logger_all.info(env.OBD_Approve_URL, payload, config); // Logging the payload itself
                 const response = await axios.post(env.OBD_Approve_URL, payload, config);
                  logger_all.info(`Response from ${env.OBD_Approve_URL}:`, response.data);
                  return 1; // Assuming return 1 on success
                } catch (error) {
                  console.error(`Error with URL ${env.OBD_Approve_URL}: ${error.message}`);
                  return 0; // Assuming return 0 on failure
                }
              }
              // Function to handle the approval process
              setTimeout(async () => {
                const result = await approve();
                logger_all.info('Approval result:', result);
                if (result === 1) {
                  logger_all.info('Approval successful processing');
                  // Additional actions if needed
                } else {
                  logger_all.error('Approval failed');
                }
              }, 1000);
            }
            // Send success response
            await db.query(`UPDATE api_log SET response_status = 'S', response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 1, response_status: 200, response_msg: 'Success.' }
            return res.json(send_response);
          } catch (e) {
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info(": [check] Failed - " + e);
            send_response = { response_code: 0, response_status: 201, response_msg: "Error Occurred." };
            return res.json(error_msg);
          }
        })
    }
    catch (err) {
      logger_all.info("[Restart Campaign Process] Failed - " + err);
      send_response = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }
      return send_response;
    }
  });

function deductCredits(promptSecond) {
  // Calculate credits as the ceiling of promptSecond divided by 30
  const credits = Math.ceil(promptSecond / 30);
  return credits;
}

// Sip Stop Campaign - start
router.post(
  "/stop_campaign",
  // validator.body(PromptComposeValidation),
  valid_user_reqID,
  valid_user,
  async function (req, res, next) {
    try { // access the PromptComposeValidation function
      const { logger_all, logger } = main;
      const result = await Stop_Campaign_Process.StopCampaignProcess(req);
      result['request_id'] = req.body.request_id;
      //check if response code is equal to zero, update api log entry as response_status = 'F',response_comments = 'response msg'
      if (result.response_code == 0) {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      }
      else {  // Otherwise update api log entry	as response_status = 'S',response_comments = 'Success'
        await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      }
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      logger_all.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// Sip Stop Campaign  - end


// Sip RESTART Campaign  - start
router.post(
  "/restart_campaign",
  // validator.body(PromptComposeValidation),
  valid_user_reqID,
  valid_user,
  async function (req, res, next) {
    try { // access the PromptComposeValidation function
      const { logger_all, logger } = main;
      const result = await Restart_Campaign_Process.RestartCampaignProcess(req);
      result['request_id'] = req.body.request_id;
      //check if response code is equal to zero, update api log entry as response_status = 'F',response_comments = 'response msg'
      if (result.response_code == 0) {
        await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      }
      else {  // Otherwise update api log entry	as response_status = 'S',response_comments = 'Success'
        await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      }
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      logger_all.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// Sip RESTART Campaign  - end

module.exports = router;
//End Function 
