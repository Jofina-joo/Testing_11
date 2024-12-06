/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in get task sms functions which is used to get sms message send details .

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
//Start Function - Get Task SMS
async function get_task_sms(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  try {
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Get all request data
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_message_id;
    var user_id = req.body.selected_user_id;
    var request_id = req.body.request_id;
    var number_array = [];
    var message_array = [];

    //Query to get message & receiver numbers from compose msg status table
    var select_data = `SELECT com_msg_content,receiver_mobile_no from ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id = '${compose_id}'  AND sender_mobile_no = '${mobile_number}' AND (response_status IS NULL OR response_status = 'T')`;
    logger_all.info("[select query request] : " + select_data);
    const select_data_result = await db.query(select_data);
    logger_all.info("[select query response] : " + JSON.stringify(select_data_result));

    //Check if selected data length is equal to zero, send failure response 'No data available.'
    if (select_data_result.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.', request_id: req.body.request_id };
    }

    //Loop through selected data and store numbers and messages array
    for (var i = 0; i < select_data_result.length; i++) {
      number_array.push(select_data_result[i].receiver_mobile_no);
      message_array.push(select_data_result[i].com_msg_content)
    }

    //Send Success response
    var response_json = { response_code: 1, response_status: 200, response_msg: 'Success', messages: message_array, numbers: number_array, request_id: req.body.request_id }
    return (response_json);
  }

  catch (err) {
    logger_all.info(": [get task sms] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }
}

module.exports = {
  get_task_sms,
};
//End Function - Get Task SMS