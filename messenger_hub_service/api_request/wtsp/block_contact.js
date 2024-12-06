/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in block contact functions which is used to block whatsapp number

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

// Import the required packages and libraries
const express = require("express");
const router = express.Router();
require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");

const main = require('../../logger');
const db = require("../../db_connect/connect");

var axios = require('axios');
const { Client, LocalAuth, Buttons, MessageMedia, Location, List } = require('whatsapp-web.js');
var axios = require('axios');
const fse = require('fs-extra');
const fs = require('fs');
const qrcode = require('qrcode-terminal');
const env = process.env
const DB_NAME = env.DB_NAME;
const chrome_path = env.GOOGLE_CHROME;
const waiting_time = env.WAITING_TIME;
const fcm_key = env.NOTIFICATION_SERVER_KEY;

//Start function to block number
async function block_contact(req, res, next) {
  var logger_all = main.logger_all
  var logger = main.logger

  var compose_message_id = req.body.compose_whatsapp_id;
  var receiver_numbers = req.body.receiver_numbers;
  var product_name = req.body.product_name;
  var user_id = req.body.selected_user_id;
  var request_id = req.body.request_id;

  var sender_numbers_active = [];
  var sender_numbers_inactive = [];
  var sender_id_active = [];
  var sender_devicetoken_active = [];

  try {
    const header_token = req.headers['authorization'];

    //get user id
    user_id_check = req.body.user_id;

    //check if user is admin then only continue process
    if (user_id_check == 1) {

      //check if product is whatsapp
      if (product_name == 'WHATSAPP') {

        //Query to select sender mobile number
        var report_query = `SELECT DISTINCT mobile_no FROM ${DB_NAME}.sender_id_master WHERE sender_id_status = 'Y' and is_qr_code = 'N'`
        logger_all.info("[Select query request - sender number] : " + report_query);
        var select_campaign = await db.query(report_query);
        logger_all.info("[Select query response - sender number] : " + JSON.stringify(select_campaign))

        //check if select_campaign length is zero, send error response as No data available
        if (select_campaign.length == 0) {

          logger_all.info("[update query request - No data available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          logger_all.info("[update query response -No data available] : " + JSON.stringify(update_api_log))
          return { response_code: 0, response_status: 204, response_msg: 'No data available.', request_id: req.body.request_id };
        }

        //Loop for check sender number is in active
        for (var i = 0; i < select_campaign.length; i++) {
          var sender_mobile_no_sms = select_campaign[i].mobile_no;
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
            sender_numbers_inactive.push(select_campaign[i].mobile_no)
          }

        }
        //check if sender_numbers_active lenght is equal to zero, send error response as 'No Sender ID available'

        if (sender_numbers_active.length == 0) {
          //update_api_log
          logger_all.info("[update query request - No Sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          logger_all.info("[update query response - No Sender ID available] : " + JSON.stringify(update_api_log))
          logger.info("[Failure Response] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'No Sender ID available', request_id: req.body.request_id }))
          return { response_code: 0, response_status: 201, response_msg: 'No Sender ID available', request_id: req.body.request_id };
        }

        //Insert block numbers data to compose_block table
        var insert_block = `INSERT INTO ${DB_NAME}_${user_id}.compose_block_${user_id} VALUES(NULL,'${user_id}','1','${sender_numbers_active}','${receiver_numbers}',CURRENT_TIMESTAMP)`;
        logger_all.info("[insert query request] : " + insert_block);
        var insert_block_result = await db.query(insert_block);
        var compose_block_id = insert_block_result.insertId;
        logger_all.info("[insert query response] : " + JSON.stringify(insert_block_result))

        //Query to get active QR
        var select_qr_number = `SELECT * from sender_id_master WHERE sender_id_status = 'Y' AND is_qr_code ='Y' order by rand() limit 1`
        logger_all.info("[Select query request] : " + select_qr_number);
        var select_sender_id = await db.query(select_qr_number);
        logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id))
        qr_sender_number = select_sender_id[0].mobile_no;

        if (select_sender_id.length == 0) {
          logger_all.info("[update query request - QR sender number not available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'QR sender number not available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'QR sender number not available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          logger_all.info("[update query response - QR sender number not available] : " + JSON.stringify(update_api_log))
          logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'QR sender number not available' }))
          return { response_code: 0, response_status: 201, response_msg: 'QR sender number not available', request_id: req.body.request_id };
        }
        //client initialization
        var function_call = false;
        var client = new Client({
          restartOnAuthFail: true,
          puppeteer: {
            headless: true,
            args: [
              '--no-sandbox',
              '--disable-setuid-sandbox',
              '--disable-dev-shm-usage',
              '--disable-accelerated-2d-canvas',
              '--no-first-run',
              '--no-zygote',
              '--disable-gpu'
            ],
            executablePath: chrome_path,
          },
          authStrategy: new LocalAuth(
            { clientId: qr_sender_number }
          )
        }
        );

        logger_all.info('Client is - ' + client)
        client.initialize();
        client.on('ready', async (data) => {
          logger_all.info('Client is ready! - ' + qr_sender_number);
          create_grp()
        });

        setTimeout(async function () {
          if (function_call == false) {

            await client.destroy();
            logger_all.info(' rescan number - ' + qr_sender_number)
            if (fs.existsSync(`./.wwebjs_auth/session-${qr_sender_number}`)) {
              fs.rmdirSync(`./.wwebjs_auth/session-${qr_sender_number}`, { recursive: true })

            }
            if (fs.existsSync(`./session_copy/session-${qr_sender_number}`)) {
              try {
                fse.copySync(`./session_copy/session-${qr_sender_number}`, `./.wwebjs_auth/session-${qr_sender_number}`, { overwrite: true | false })
                logger_all.info('Folder copied successfully')

                client = new Client({
                  restartOnAuthFail: true,
                  puppeteer: {
                    headless: true,
                    args: [
                      '--no-sandbox',
                      '--disable-setuid-sandbox',
                      '--disable-dev-shm-usage',
                      '--disable-accelerated-2d-canvas',
                      '--no-first-run',
                      '--no-zygote',
                      '--disable-gpu'
                    ],
                    executablePath: chrome_path,
                  },
                  authStrategy: new LocalAuth(
                    { clientId: qr_sender_number }
                  )
                }
                );
                client.initialize();
                client.on('authenticated', async (data) => {
                  logger_all.info(" [Client is Log in] : " + JSON.stringify(data));
                });
                client.on('ready', async (data) => {
                  logger_all.info(" [Client is ready] : " + client.options.authStrategy.clientId);
                  create_grp()
                });
                setTimeout(async function () {
                  if (function_call == false) {

                    logger_all.info(" [update query request] : " + `UPDATE sender_id_master SET sender_id_status = 'X' WHERE mobile_no = '${qr_sender_number}' AND sender_id_status != 'D'`)
                    const update_inactive = await db.query(`UPDATE sender_id_master SET sender_id_status = 'X' WHERE mobile_no = '${qr_sender_number}' AND sender_id_status != 'D'`);
                    logger_all.info(" [update query response] : " + JSON.stringify(update_inactive))

                    logger_all.info("[update query request - Sender ID not ready] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender ID not ready.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Sender ID not ready.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                    logger_all.info("[update query response - Sender ID not ready] : " + JSON.stringify(update_api_log))

                    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Sender ID not ready' }))
                    return { response_code: 0, response_status: 201, response_msg: 'Sender ID not ready', request_id: req.body.request_id }

                  }
                }, waiting_time);
              } catch (err) {
                logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))
                logger_all.info(err)

                logger.info("[API RESPONSE] " + JSON.stringify({ request_id: req.body.request_id, response_code: 0, response_status: 201, response_msg: 'Error occurred.' }))
                return { response_code: 0, response_status: 201, response_msg: 'Error occurred.', request_id: req.body.request_id }
              }
            }

            else {
              logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
              logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))
              logger.info("[API RESPONSE] " + JSON.stringify({ request_id: req.body.request_id, response_code: 0, response_status: 201, response_msg: 'Error occurred.' }))
              return { response_code: 0, response_status: 201, response_msg: 'Error occurred.', request_id: req.body.request_id }
            }

          }
        }, waiting_time);

        async function create_grp() {
          function_call = true;
          try {
            logger_all.info("receiver_numbers.length" + receiver_numbers.length)
            for (var i = 0; i < sender_numbers_active.length; i++) {
              for (var k = 0; k < receiver_numbers.length; k++) {

                if (k == receiver_numbers.length) {
                  break;
                }

                logger_all.info("[select query request] : " + receiver_numbers[k]);
                const number = receiver_numbers[k];
                const sanitized_number = number.toString().replace(/[- )(]/g, "");
                const final_number = `${sanitized_number.substring(sanitized_number.length - 10)}`;

                const number_details = await client.getNumberId(final_number);

                if (number_details) {
                  var insert_block_details = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_block_${user_id} VALUES(NULL,'${compose_block_id}','${user_id}','1','${sender_numbers_active[i]}','${receiver_numbers[k]}','Mobile number available in WhatsApp',NULL,CURRENT_TIMESTAMP,'N')`;
                  logger_all.info("[insert query request] : " + insert_block_details);
                  var insert_block_details_result = await db.query(insert_block_details);
                  logger_all.info("[insert query response] : " + JSON.stringify(insert_block_details_result));
                } else {
                  var insert_block_details = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_block_${user_id} VALUES(NULL,'${compose_block_id}','${user_id}','1','${sender_numbers_active[i]}','${receiver_numbers[k]}','Mobile number not in WhatsApp',NULL,CURRENT_TIMESTAMP,'N')`;
                  logger_all.info("[insert query request] : " + insert_block_details);
                  var insert_block_details_result = await db.query(insert_block_details);
                  logger_all.info("[insert query response] : " + JSON.stringify(insert_block_details_result));
                }
              }
            }

            //            for (var k = 0; k < receiver_numbers.length; k) {
            //              for (var i = 0; i < sender_numbers_active.length; i) {
            //
            //                if (k == receiver_numbers.length) {
            //                  break;
            //                }
            //
            //                logger_all.info("[select query request] : " + receiver_numbers[k]);
            //                const number = receiver_numbers[k];
            //                const sanitized_number = number.toString().replace(/[- )(]/g, ""); // remove unnecessary chars from the number
            //                const final_number = `${sanitized_number.substring(sanitized_number.length - 10)}`; // add 91 before the number here 91 is country code of India
            //
            //                const number_details = await client.getNumberId(final_number); // get mobile number details
            //
            //                if (number_details) {
            //                  var insert_block_details = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_block_${user_id} VALUES(NULL,'${compose_block_id}','${user_id}','1','${sender_numbers_active[i]}','${receiver_numbers[k]}','Mobile number available in whastapp',NULL,CURRENT_TIMESTAMP,'N')`;
            //                  logger_all.info("[insert query request] : " + insert_block_details);
            //                  var insert_block_details_result = await db.query(insert_block_details);
            //                 //   var compose_msg_block_id = insert_block_details_result.insertId;
            //                  logger_all.info("[insert query response] : " + JSON.stringify(insert_block_details_result))
            //               i++;
            //
            //                }
            //                 else {
            //                                var insert_block_details = `INSERT INTO ${DB_NAME}_${user_id}.compose_msg_block_${user_id} VALUES(NULL,'${compose_block_id}','${user_id}','1','${sender_numbers_active[i]}','${receiver_numbers[k]}','Mobile number not in whastapp',NULL,CURRENT_TIMESTAMP,'N')`;
            //                                                logger_all.info("[insert query request] : " + insert_block_details);
            //                                                var insert_block_details_result = await db.query(insert_block_details);
            //                                             //   var compose_msg_block_id = insert_block_details_result.insertId;
            //                                                logger_all.info("[insert query response] : " + JSON.stringify(insert_block_details_result))
            //
            //
            //                              }
            //
            //                k++;
            //              }
            //            }

            //   for (var i = 0; i < sender_numbers_active.length; i++) {
            //    var update_senderID_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[i]}'`;
            //                            logger_all.info("[insert query request] : " + update_senderID_sts);
            //                            var update_senderID_sts_res = await db.query(update_senderID_sts);
            //                            logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
            //  }

            await client.destroy();
            logger_all.info(" Destroy client - " + qr_sender_number)

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
                // "notification": {
                //   "body": "test",
                //   "content-available": true,
                //   "priority": "high",

                // },
                "data": {
                  "title": compose_block_id,
                  "selected_user_id": user_id,
                  "priority": "high",
                  "content-available": true,
                  "bodyText": "WTSP_BLOCK"
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

            logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
            logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))

            //send success response
            return { response_code: 1, response_status: 200, response_msg: 'Success.', request_id: req.body.request_id }
          }
          catch (e) {
            logger_all.info(e);
            client.destroy();
            logger_all.info(" Destroy client - " + qr_sender_number)
            logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))
            logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error occurred.' }))
            return { response_code: 0, response_status: 201, response_msg: 'Error occurred.', request_id: req.body.request_id }
          }
        }

      }
      //Otherwise send failure response 'Campaign not found'
      else {
        logger_all.info("[update query request - campaign not found] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response -  campaign not found] : " + JSON.stringify(update_api_log))
        return { response_code: 1, response_status: 201, response_msg: 'Campaign Not Found' };
      }
    }

    //Otherwise send failure response 'Invalid User'
    else {

      logger_all.info("[update query request - Invalid User] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response -  Invalid User] : " + JSON.stringify(update_api_log))
      return { response_code: 1, response_status: 201, response_msg: 'Invalid User' };
    }
  }
  catch (err) {

    logger_all.info("[update query request - Error Occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
    logger_all.info(": [check] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }

}
//End function to block number
module.exports = {
  block_contact,
};