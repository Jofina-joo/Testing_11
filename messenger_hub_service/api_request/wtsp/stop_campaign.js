/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in stop campaign functions which is used to stop campaign

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

//Start function to stop running campaign
async function stop_campaign(req, res, next) {
  var logger_all = main.logger_all
  var logger = main.logger

  //Get all request data
  var sender_numbers = req.body.sender_numbers;
  var user_product = req.body.user_product;
  var campaign_name = req.body.campaign_name;
  var request_id = req.body.request_id;

  var sender_numbers_process = [];
  var sender_numbers_inactive = [];
  var sender_id_active = [];
  var sender_devicetoken_active = [];

  try {
    const header_token = req.headers['authorization'];
    //get user id
    user_id_check = req.body.user_id;

    //check if user is admin, then only continue process
    if (user_id_check == 1) {
      //check if product is whatsapp
      if (user_product == 'WHATSAPP') {

        //Loop for get active sender numbers
        for (var i = 0; i < sender_numbers.length; i++) {
          //Query to get sender numbers
          var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_numbers[i]}' AND sender_id_status = 'P' AND is_qr_code ='N'`
          logger_all.info("[Select query request] : " + senderID_active);
          var select_sender_id_active = await db.query(senderID_active);
          logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id_active))
          //check if sender numbers length is not equal to zero, get sender ID data
          if (select_sender_id_active.length != 0) {
            sender_numbers_process.push(select_sender_id_active[0].mobile_no)
            sender_id_active.push(select_sender_id_active[0].sender_id)
            sender_devicetoken_active.push(select_sender_id_active[0].device_token)

            logger_all.info("[ sender_numbers_process] : " + sender_numbers_process)
            logger_all.info("[sender_id_active] : " + sender_id_active)
            logger_all.info("[sender_devicetoken_active] : " + sender_devicetoken_active)
          }
          //Otherwise get inactive sender ID data
          else {
            sender_numbers_inactive.push('${sender_numbers[i]}')
          }
        }

        //check if active sender numbers length is zero, send error response 'No sender ID available'
        if (sender_numbers_process.length == 0) {
          logger_all.info("[update query request - No sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
          logger_all.info("[update query response - No sender ID available] : " + JSON.stringify(update_api_log))
          logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'No Sender ID available' }))
          return { response_code: 0, response_status: 201, response_msg: 'No Sender ID available', request_id: req.body.request_id };
        }


        //To send push notification
        var data = JSON.stringify({
          "registration_ids": sender_devicetoken_active,
          // "notification": {
          //   "body": "test",
          //   "content-available": true,
          //   "priority": "high",

          // },

          "data": {
            "title": "stop_campaign",
            "selected_user_id": user_id_check,
            "priority": "high",
            "content-available": true,
            "bodyText": "WTSP_STOP_CAMPAIGN"
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

        for (var i = 0; i < sender_numbers_process.length; i++) {
          var update_senderID_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no = '${sender_numbers_process[i]}'`;
          logger_all.info("[insert query request] : " + update_senderID_sts);
          var update_senderID_sts_res = await db.query(update_senderID_sts);
          logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
        }
        //update cm_status
        var update_campaign_sts = `UPDATE ${DB_NAME}_${user_id_check}.compose_message_${user_id_check} SET cm_status = 'S' WHERE campaign_name = '${campaign_name}'`;
        logger_all.info("[insert query request] : " + update_campaign_sts);
        var update_campaign_sts_res = await db.query(update_campaign_sts);
        logger_all.info("[insert query response] : " + JSON.stringify(update_campaign_sts_res))

        logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
        logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))

        //send success response
        return { response_code: 1, response_status: 200, response_msg: 'Success.', request_id: req.body.request_id }
      }

      //Otherwise send failure response 'campaign not found'
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
//End Function to stop running campaign
module.exports = {
  stop_campaign,
};