/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the whatsapp messages.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const express = require("express");
const router = express.Router();
require("dotenv").config();
const Update_rep = require("./update_report");
const Update_task = require("./update_task");
const Update_task_Block = require("./update_task_block");
const Get_rep = require("./get_report");
const Get_task = require("./get_task");
const Get_task_Block = require("./get_task_block");
const Block_wtsp = require("./block_contact");
const Stop_campaign = require("./stop_campaign");
const Update_task_stop = require("./update_task_stop");
const Update_rep_block = require("./update_report_block");
const update_block_sts = require("./update_block_sts");

const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware")
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
const update_report_validation = require("../../validation/update_report_validation");
const update_task_validation = require("../../validation/update_task_validation");
const get_report_validation = require("../../validation/get_report_validation");
const get_task_validation = require("../../validation/get_task_validation");
const get_task_block_validation = require("../../validation/get_task_block_validation");
const update_task_block_validation = require("../../validation/update_task_block_validation");
const qr_code_validation = require("../../validation/get_qrcode_validation");
const CreateCsvValidation = require("../../validation/create_csv_validation");
const ResendMSGValidation = require("../../validation/resend_validation");
const stop_campaign_validation = require("../../validation/stop_campaign_validation");
const update_block_sts_validation = require("../../validation/update_block_sts_validation");
const db = require("../../db_connect/connect");
const main = require('../../logger');
const jwt = require("jsonwebtoken");
const md5 = require("md5")
var cors = require("cors");
var axios = require('axios');
const { Client, LocalAuth, Buttons, MessageMedia, Location, List } = require('whatsapp-web.js');

const fse = require('fs-extra');
const csv = require("csv-stringify");
const moment = require("moment")
var client_data;
const env = process.env
const chrome_path = env.GOOGLE_CHROME;
const waiting_time = env.WAITING_TIME;
const media_storage = env.MEDIA_STORAGE;
const fcm_key = env.NOTIFICATION_SERVER_KEY;
const bodyParser = require('body-parser');
const fs = require('fs');

//Start Function - Update Whatsapp Report
router.post(
  "/update_report",
  validator.body(update_report_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Update_rep.update_report(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Update Whatsapp Report

//Start Function - Update task 
router.post(
  "/update_task",
  validator.body(update_task_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Update_task.update_task(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Update task

//Start Function - Get Whatsapp Report
router.post(
  "/get_report",
  validator.body(get_report_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Get_rep.get_report(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Get Whatsapp Report

//Start Function - Get Task
router.post(
  "/get_task",
  validator.body(get_task_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Get_task.get_task(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Get Task

//Start Function - Restart campaign
router.post(
  "/restart_campaign",
  validator.body(ResendMSGValidation),
  valid_user_reqID,
  async function (req, res, next) {
  var logger_all = main.logger_all
      var logger = main.logger

//Get all request data
  var sender_numbers = req.body.sender_numbers;
  var user_product = req.body.user_product;
   var compose_message_id = req.body.compose_whatsapp_id;
  var request_id = req.body.request_id;


var sender_numbers_active = [];
var sender_numbers_inactive = [];
var sender_id_active =[];
var sender_devicetoken_active = [];

  try {
 const header_token = req.headers['authorization'];
//get user id
  user_id_check = req.body.user_id;

//check if user is admin, then only continue process
          if(user_id_check == 1)
          {
//check if product is whatsapp
         if (user_product == 'WHATSAPP') {
          var get_product = `SELECT * FROM rights_master where rights_name = '${user_product}' AND rights_status = 'Y' `;

                                         logger_all.info("[select query request] : " + get_product);
                                         const get_product_id = await db.query(get_product);
                                         logger_all.info("[select query response] : " + JSON.stringify(get_product_id));

                                         product_id = get_product_id[0].rights_id;
//Loop for get active sender numbers
    for (var i = 0; i < sender_numbers.length; i++) {
//Query to get sender numbers
              var senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_numbers[i]}' AND sender_id_status = 'Y' AND is_qr_code ='N'`
                  logger_all.info("[Select query request] : " + senderID_active);
                  var select_sender_id_active = await db.query(senderID_active);
                  logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id_active))
 //check if sender numbers length is not equal to zero, get sender ID data
            if (select_sender_id_active.length != 0) {
                        sender_numbers_active.push(select_sender_id_active[0].mobile_no)
                        sender_id_active.push(select_sender_id_active[0].sender_id)
                         sender_devicetoken_active.push(select_sender_id_active[0].device_token)

                         logger_all.info("[ sender_numbers_active] : " + sender_numbers_active )
                           logger_all.info("[sender_id_active] : " + sender_id_active )
                             logger_all.info("[sender_devicetoken_active] : " + sender_devicetoken_active )
                             }
//Otherwise get inactive sender ID data
                             else
                             {
                               sender_numbers_inactive.push('${sender_numbers[i]}')
                             }
                             }

//check if active sender numbers length is zero, send error response 'No sender ID available'
                              if (sender_numbers_active.length == 0) {
                               logger_all.info("[update query request - No sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                               const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                                logger_all.info("[update query response - No sender ID available] : " + JSON.stringify(update_api_log))
                                     logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'No Sender ID available' }))
                                     return res.json( { response_code: 0, response_status: 201, response_msg: 'No Sender ID available', request_id: req.body.request_id });
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
                         "title": compose_message_id,
                          "selected_user_id":user_id_check,
                          "product_id" :product_id,
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

   for (var i = 0; i < sender_numbers_active.length; i++) {
                  var update_senderID_sts = `UPDATE messenger_hub.sender_id_master SET sender_id_status = 'P' WHERE mobile_no = '${sender_numbers_active[i]}'`;
                                          logger_all.info("[insert query request] : " + update_senderID_sts);
                                          var update_senderID_sts_res = await db.query(update_senderID_sts);
                                          logger_all.info("[insert query response] : " + JSON.stringify(update_senderID_sts_res))
                }

 //update cm_status
        var update_campaign_sts = `UPDATE messenger_hub_${user_id_check}.compose_message_${user_id_check} SET cm_status = 'P' WHERE cm_status = 'S' and compose_message_id = '${compose_message_id}'`;
        logger_all.info("[insert query request] : " + update_campaign_sts);
        var update_campaign_sts_res = await db.query(update_campaign_sts);
        logger_all.info("[insert query response] : " + JSON.stringify(update_campaign_sts_res))

   logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                     const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                     logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
            logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))

 //send success response
            return res.json({ response_code: 1, response_status: 200, response_msg: 'Success.', request_id: req.body.request_id })
          }

//Otherwise send failure response 'campaign not found'
                else
                     {
                                        logger_all.info("[update query request - campaign not found] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                                          const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign Not Found.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                                          logger_all.info("[update query response -  campaign not found] : " + JSON.stringify(update_api_log))
                      return res.json({ response_code: 1, response_status: 201, response_msg: 'Campaign Not Found'});
                     }
                     }
 //Otherwise send failure response 'Invalid User'
                       else{

                                                    logger_all.info("[update query request - Invalid User] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                                                      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Invalid User.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                                                      logger_all.info("[update query response -  Invalid User] : " + JSON.stringify(update_api_log))
                            return res.json( { response_code: 1, response_status: 201, response_msg: 'Invalid User'});
                           }



      logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                        logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
          return res.json({ response_code: 1, response_status: 200, response_msg: 'Success',request_id: req.body.request_id,Inactive_senderID:sender_numbers_inactive});



      }

      catch (err) {
    //Otherwise send failure response 'Error occurred'
        logger_all.info("[restart campaign] Failed - " + err);
        //update_api_log
                     logger_all.info("[update query request - Error occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                               const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
                               logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log))
       return res.json({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.',request_id: req.body.request_id});
      }
}
);
//End Function - Restart Campaign

router.post(
  "/block_contact",
   valid_user_reqID,
  //validator.body(get_task_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await Block_wtsp.block_contact(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.post(
  "/get_task_block",
  validator.body(get_task_block_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await Get_task_Block.get_task_block(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.post(
  "/update_task_block",
  validator.body(update_task_block_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await Update_task_Block.update_task_block(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

//Start Function - Stop Campaign
router.post(
  "/stop_campaign",
  validator.body(stop_campaign_validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Stop_campaign.stop_campaign(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Stop Campaign

//Start Function - Update Task Stop
router.post(
  "/update_task_stop",
  validator.body(update_task_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Update_task_stop.update_task_stop(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Update Task Stop

router.post(
  "/update_report_block",
  validator.body(update_report_validation),
  async function (req, res, next) {
    try {

      var logger = main.logger

      var result = await Update_rep_block.update_report_block(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);


router.post(
  "/update_block_sts",
  validator.body(update_block_sts_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await update_block_sts.update_block_sts(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);


module.exports = router;

