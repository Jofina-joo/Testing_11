/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in get task block functions which is used to block whatsapp number.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../../logger')
require("dotenv").config();
const env = process.env
const DB_NAME = env.DB_NAME;
//Start function to get task for whatsapp block
async function get_task_block(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  logger_all.info(" [get task block] - " + req.body);
  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

  try {

    // Extract request parameters
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.com_msg_block_id;
    var user_id = req.body.selected_user_id;
    var number_array = [];

    //Query to get receiver number
    var select_data = `SELECT receiver_mobile_no from ${DB_NAME}_${user_id}.compose_msg_block_${user_id} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}'`
    logger_all.info("[select query request - get receiver number] : " + select_data);
    const select_data_result = await db.query(select_data);
    logger_all.info("[select query response  - get receiver number] : " + JSON.stringify(select_data_result));

    //check if select data result length is equal to zero, send error response as 'No data available'
    if (select_data_result.length == 0) {

      logger.info("[Failed response - No data available] : " + JSON.stringify({ response_code: 0, response_status: 204, response_msg: 'No data available.', request_id: req.body.request_id }))
      return { response_code: 0, response_status: 204, response_msg: 'No data available.', request_id: req.body.request_id };
    }

    // Otherwise Populate number_array with query results
    for (var i = 0; i < select_data_result.length; i++) {
      number_array.push(select_data_result[i].receiver_mobile_no);
    }

    //send success response with numbers array data

    var response_json = { response_code: 1, response_status: 200, response_msg: 'Success', numbers: number_array, request_id: req.body.request_id }
    logger_all.info("[API SUCCESS RESPONSE] : " + JSON.stringify(response_json));

    return (response_json);

  }

  catch (err) {
    logger_all.info("[get task block] Failed - " + err);
    logger.info("[Failure response - Error Occurred] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))

    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }

}

//End function to get task for whatsapp block

module.exports = {
  get_task_block,
};