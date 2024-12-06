/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update version functions which is used to update app's latest version

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
const env = process.env
const fcm_key = env.NOTIFICATION_SERVER_KEY;
var admin = require("firebase-admin");
var serviceAccount = require('../../watsp-app-firebase-adminsdk-nhc22-5a94b7667c.json');

//Start Function - Update latest version
async function update_version(req, res, next) {
  var logger_all = main.logger_all
  var logger = main.logger

  //Get all request data
  var app_update_id = req.body.app_update_id;
  var sender_numbers = req.body.sender_numbers;
  var request_id = req.body.request_id;
  var version_file;
  var sender_numbers_active = [];
  var sender_numbers_inactive = [];
  var sender_id_active = [];
  var sender_devicetoken_active = [];
  try {
    const header_token = req.headers['authorization'];
    user_id_check = req.body.user_id;
    if (user_id_check) {

      //Loop for get active sender numbers
      for (var i = 0; i < sender_numbers.length; i++) {

        //Query to get active sender numbers
        var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_numbers[i]}' AND sender_id_status = 'Y' AND is_qr_code ='N'`
        logger_all.info("[Select query request] : " + senderID_active);
        var select_sender_id_active = await db.query(senderID_active);
        logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id_active))

        //check if sender numbers length is not equal to zero, get sender ID data
        if (select_sender_id_active.length != 0) {
          sender_numbers_active.push(select_sender_id_active[0].mobile_no)
          sender_id_active.push(select_sender_id_active[0].sender_id)
          sender_devicetoken_active.push(select_sender_id_active[0].device_token)
        }

        //Otherwise get inactive sender ID data
        else {
          sender_numbers_inactive.push('${sender_numbers[i]}')
        }
      }

      //check if active sender numbers length is zero, send error response 'No sender ID available'
      if (sender_numbers_active.length == 0) {
        logger_all.info("[update query request - No sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - No sender ID available] : " + JSON.stringify(update_api_log))
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'No Sender ID available' }))
        return { response_code: 0, response_status: 201, response_msg: 'No Sender ID Available', request_id: req.body.request_id };
      }

      //Query to Get version_file
      var select_version_file = `SELECT app_version_file from app_version_update WHERE app_update_id = '${app_update_id}'`
      logger_all.info("[select query request_senderid] : " + select_version_file);
      const select_version_file_res = await db.query(select_version_file);
      logger_all.info("[select query response] : " + JSON.stringify(select_version_file_res));
      version_file = select_version_file_res[0].app_version_file;

      //Check if selected data is equal to zero, send failure response 'No Data Available'
      if (select_version_file_res.length == 0) {
        logger_all.info("[update query request - No Data Available.] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Data Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Data Available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - No Data Available.] : " + JSON.stringify(update_api_log))
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 204, response_msg: 'No Data Available.' }))
        return { response_code: 0, response_status: 204, response_msg: 'No Data Available.' };
      }

      //To send push notification to respective sender ID
      /*var data = JSON.stringify({
        "registration_ids": sender_devicetoken_active,
        "priority": "high",
        // "notification": {
        //   "body": "test",
        //   "content-available": true,
        //   "priority": "high",
        // },
        "data": {
          "title": version_file,
          "sender_numbers_active": sender_numbers_active,
          "app_update_id": app_update_id,
          "selected_user_id": user_id_check,
          "priority": "high",
          "content-available": true,
          "bodyText": "APP_VERSION"
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
        });*/

 // Check if Firebase is already initialized
                  if (!admin.apps.length) {
                     admin.initializeApp({
                     credential: admin.credential.cert(serviceAccount),
                       });
                         }
                 for (let i = 0; i < sender_devicetoken_active.length; i++) {

const message = {
  data: {
    "title": String(version_file),
    "sender_numbers_active": String(sender_numbers_active[i]),
    "selected_user_id": String(user_id_check),
    "app_update_id": String(app_update_id),
    "bodyText": "APP_VERSION"
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


      //update sender id status 'P'
      for (var i = 0; i < sender_numbers_active.length; i++) {
        var update_senderID_sts = `UPDATE sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[i]}'`;
        logger_all.info("[insert query request] : " + update_senderID_sts);
        var update_senderID_sts_res = await db.query(update_senderID_sts);
        logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
      }
      //Update app status sender ID master table
      logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
      logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))

      //send success response
      return { response_code: 1, response_status: 200, response_msg: 'Success.', request_id: req.body.request_id }
    }
    //Otherwise send failure response 'Invalid User'
    else {
      logger_all.info("[update query request - Invalid User] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response -  Invalid User] : " + JSON.stringify(update_api_log))
      return { response_code: 0, response_status: 201, response_msg: 'Invalid User' };
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
module.exports = {
  update_version,
};
//End Function - Update latest version
