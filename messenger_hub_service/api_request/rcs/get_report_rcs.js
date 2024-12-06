/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in get report rcs functions which is used to get delivery report from RCS messages.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../../logger')
require("dotenv").config();
const env = process.env
const DB_NAME = env.DB_NAME;
//Start function to get RCS report
async function get_report_rcs(req) {
  const logger_all = main.logger_all
  const logger = main.logger
  logger_all.info(" [get report rcs] - " + req.body);
  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

  try {

    // Extract request parameters
    const mobile_number = req.body.mobile_number;
    const compose_id = req.body.compose_message_id;
    const receiver_number = req.body.receiver_number;
    const user_id = req.body.selected_user_id;

    const number_array = [];
    const message_array = [];

    //Query to retrieve RCS report data
    const select_data = `SELECT com_msg_content,receiver_mobile_no from ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}' AND response_status ='Y' AND read_status is NULL`

    // Check if receiver_number is provided in the request and add it to the query if present
    if (receiver_number) {
      select_data = select_data + `AND receiver_mobile_no = '${receiver_number}'`
    }
    logger_all.info("[select query request] - get message and receiver number : " + select_data);
    const select_data_result = await db.query(select_data);

    // Loop for Extract numbers and messages from the query result
    for (let i = 0; i < select_data_result.length; i++) {
      number_array.push(select_data_result[i].receiver_mobile_no);
      message_array.push(select_data_result[i].com_msg_content)

    }
    //send success response with numbers and messages data
    const response_json = { response_code: 1, response_status: 200, response_msg: 'Success', numbers: number_array, messages: message_array, request_id: req.body.request_id }
    logger_all.info("[SUCCESS API RESPONSE] : " + JSON.stringify(response_json));
    return (response_json);

  }

  catch (err) {
    logger_all.info(": [get report rcs] Failed - " + err);
    logger.info("[Failed response - Error occurred] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id }))

    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }

}
//End function to get RCS report

module.exports = {
  get_report_rcs,
};
