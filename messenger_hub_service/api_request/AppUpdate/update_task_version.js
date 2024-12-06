/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update task version functions which is used to update the app version.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../../logger')
require("dotenv").config();
const env = process.env
const DB_NAME = env.DB_NAME;
//Start Function - Update task version
async function update_task_version(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  var app_update_id = req.body.app_update_id;
  var version_file = req.body.version_file;
  var update_sts = req.body.update_sts;
  var sender_numbers = req.body.sender_numbers;
  var request_id = req.body.request_id;

  try {
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Query to get active sender ID
    var senderID_active = `SELECT * from sender_id_master WHERE sender_id_status = 'P' AND is_qr_code ='N' AND mobile_no = '${sender_numbers}'`
    logger_all.info("[Select query request - Active sender ID] : " + senderID_active);
    var select_sender_id_active = await db.query(senderID_active);
    logger_all.info("[Select query response - Active sender ID] : " + JSON.stringify(select_sender_id_active))

    //check if active sender numbers length is zero, send error response 'No sender ID available'
    if (select_sender_id_active.length == 0) {
      logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'No Sender ID Available' }))
      return { response_code: 0, response_status: 201, response_msg: 'No Sender ID Available', request_id: req.body.request_id };
    }

    //Otherwise continue process
    var select_app_update_id = `SELECT app_update_id from app_version_update order by app_update_id desc limit 1`
    logger_all.info("[select query request_senderid] : " + select_app_update_id);
    const select_app_update_id_res = await db.query(select_app_update_id);
    logger_all.info("[select query response] : " + JSON.stringify(select_app_update_id_res));
    latest_app_update_id = select_app_update_id_res[0].app_update_id;

    //Check if selected data is equal to zero, send failure response 'No Data Available'
    if (select_app_update_id_res.length == 0) {

      logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 204, response_msg: 'No Data Available.' }))
      return { response_code: 0, response_status: 204, response_msg: 'No Data Available.' };
    }
    logger_all.info("update_status: " + update_sts)

    //Check if app version is latest version, update 'U' for latest version
    if (latest_app_update_id == app_update_id && update_sts == 1) {
      var update_version_sts = `UPDATE ${DB_NAME}.sender_id_master SET app_update_status = 'U', app_update_id = '${app_update_id}' WHERE mobile_no = '${select_sender_id_active[0].mobile_no}'`;
      logger_all.info("[insert query request] : " + update_version_sts);
      var update_version_sts_res = await db.query(update_version_sts);
      logger_all.info("[insert query response] : " + JSON.stringify(update_version_sts_res))

    }

    //Otherwise update failure response
    else if (update_sts == 0) {
      var update_version_sts = `UPDATE ${DB_NAME}.sender_id_master SET app_update_status = 'F', app_update_id = '${app_update_id}' WHERE mobile_no = '${select_sender_id_active[0].mobile_no}'`;
      logger_all.info("[insert query request] : " + update_version_sts);
      var update_version_sts_res = await db.query(update_version_sts);
      logger_all.info("[insert query response] : " + JSON.stringify(update_version_sts_res))
    }

    //Otherwise update app id only
    else {
      var update_version_sts = `UPDATE ${DB_NAME}.sender_id_master SET  app_update_id = '${app_update_id}' WHERE mobile_no = '${select_sender_id_active[0].mobile_no}'`;
      logger_all.info("[insert query request] : " + update_version_sts);
      var update_version_sts_res = await db.query(update_version_sts);
      logger_all.info("[insert query response] : " + JSON.stringify(update_version_sts_res))
    }

    //Check sender number have latest app version
    var select_sender_ID = `SELECT mobile_no from sender_id_master where is_qr_code = 'N' and sender_id_status IN ('N', 'P', 'Y')`;
    logger_all.info("[select query request_senderid] : " + select_sender_ID);
    const select_sender_ID_res = await db.query(select_sender_ID);
    logger_all.info("[select query response] : " + JSON.stringify(select_sender_ID_res));
    logger_all.info("select_sender_ID_res.length : " + select_sender_ID_res.length)
    var select_app_ID = `SELECT mobile_no from sender_id_master where is_qr_code = 'N' and sender_id_status IN ('N', 'P', 'Y') and app_update_id = '${app_update_id}'`;
    logger_all.info("[select query request_senderid] : " + select_app_ID);
    const select_app_ID_res = await db.query(select_app_ID);
    logger_all.info("[select query response] : " + JSON.stringify(select_app_ID_res));
    logger_all.info("select_app_ID_res.length : " + select_app_ID_res.length)

    //Check if selected data is equal to zero, send failure response 'No Data Available'
    if (select_sender_ID_res.length == 0 && select_app_ID_res.length == 0) {
      logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 204, response_msg: 'No Data Available.' }))
      return { response_code: 0, response_status: 204, response_msg: 'No Data Available.' };
    }

    //Check if selected is equal, update app status 'U'
    if (select_sender_ID_res.length == select_app_ID_res.length) {
      var update_app_sts = `UPDATE ${DB_NAME}.app_version_update SET app_update_status = 'U' WHERE app_update_id = '${app_update_id}' AND app_version_file = '${version_file}'`;
      logger_all.info("[insert query request] : " + update_app_sts);
      var update_app_sts_res = await db.query(update_app_sts);
      logger_all.info("[insert query response] : " + JSON.stringify(update_app_sts_res))
    }

    //After complete all process update sender ID status as 'Y'
    var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${select_sender_id_active[0].mobile_no}'`
    logger_all.info("[update query request] : " + update_sender_sts);
    const update_sender_sts_result = await db.query(update_sender_sts);
    logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))

    //Send success response
    return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };
  }
  catch (err) {
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id }))

    //Send failure response
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  update_task_version,
};
//End Function - Update task version