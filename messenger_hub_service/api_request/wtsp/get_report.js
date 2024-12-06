/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in get report functions which is used to get whatsapp report.

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
//Start Function - Get Whatsapp Report
async function get_report(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  try {
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Get all request data
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_whatsapp_id;
    var receiver_number = req.body.receiver_number;
    var user_id = req.body.selected_user_id;
    var request_id = req.body.request_id;
    var number_array = [];
    var message_array = [];
    var sender_number_active = [];

    //Query to get message and receiver number 
    var select_data = `SELECT com_msg_content,receiver_mobile_no from ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}' AND response_status ='Y' AND read_status is NULL`
    if (receiver_number) {
      select_data = select_data + ` AND receiver_mobile_no = '${receiver_number}'`
    }
    logger_all.info("[select query request] : " + select_data);
    const select_data_result = await db.query(select_data);

    //Check if selected data is equal to zero, update sender ID status as 'Y'
    if (select_data_result.length === 0) {
      var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`
      logger_all.info("[update query request] : " + update_sender_sts);
      const update_sender_sts_result = await db.query(update_sender_sts);
      logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));

    }

    for (var i = 0; i < select_data_result.length; i++) {
      number_array.push(select_data_result[i].receiver_mobile_no);
      message_array.push(select_data_result[i].com_msg_content)

    }

    var response_json = { response_code: 1, response_status: 200, response_msg: 'Success', numbers: number_array, messages: message_array, request_id: req.body.request_id }
    logger_all.info("[API RESPONSE] : " + JSON.stringify(response_json));

    return (response_json);

  }

  catch (err) {
    logger_all.info(": [get report] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}

module.exports = {
  get_report,
};
//End Function - Get Whatsapp Report