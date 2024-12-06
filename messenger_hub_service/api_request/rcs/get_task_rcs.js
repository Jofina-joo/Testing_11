/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in get task rcs functions which is used to send RCS message.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
const main = require('../../logger')
require("dotenv").config();
const env = process.env
const DB_NAME = env.DB_NAME;
//Start function to get task for RCS
async function get_task_rcs(req) {
  const logger_all = main.logger_all
  const logger = main.logger
  logger_all.info(" [get task] - " + req.body);
  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

  try {

    // Extract request parameters
    const mobile_number = req.body.mobile_number;
    const compose_id = req.body.compose_message_id;
    const user_id = req.body.selected_user_id;
    let number_array = [];
    let message_array = [];
    let media_array = [];

    //Query to fetch message data with response_status as NULL
    const select_data = `SELECT com_msg_content,receiver_mobile_no,com_cus_msg_media from ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}' AND response_status IS NULL`
    logger_all.info("[select query request - get message and receiver number] : " + select_data);
    const select_data_result = await db.query(select_data);
    logger_all.info("[select query response  - get message and receiver number] : " + JSON.stringify(select_data_result));

    //check if select data result length is equal to zero, send error response as 'No data available'
    if (select_data_result.length == 0) {

      logger.info("[Failed response - No data available] : " + JSON.stringify({ response_code: 0, response_status: 204, response_msg: 'No data available.', request_id: req.body.request_id }))
      return { response_code: 0, response_status: 204, response_msg: 'No data available.', request_id: req.body.request_id };
    }

    // Otherwise Populate number_array and message_array with query results
    for (let i = 0; i < select_data_result.length; i++) {
      number_array.push(select_data_result[i].receiver_mobile_no);
      message_array.push(select_data_result[i].com_msg_content);
      media_array.push(select_data_result[i].com_cus_msg_media)
    }

    let media_msg_url = []
    let media_message_type = "TEXT"
    let is_same_media;

    //const Query to fetch media URL with cmm_status as 'Y'
    const select_media = `SELECT is_same_media,campaign_type from ${DB_NAME}_${user_id}.compose_message_${user_id} WHERE compose_message_id = '${compose_id}' AND cm_status = 'P'`
    logger_all.info("[select query request - get media] : " + select_media);
    const select_media_result = await db.query(select_media);
    logger_all.info("[select query response - get media] : " + JSON.stringify(select_media_result));

    // Update media_msg_url if media URL is available
    if (select_media_result.length != 0) {
      if (select_media_result[0].is_same_media != '-') {
        is_same_media = select_media_result[0].is_same_media

        if (is_same_media == 'true') {
          media_msg_url.push(media_array[0])
        }
        else {
          media_msg_url = media_array;
        }

        media_message_type = select_media_result[0].campaign_type
      }
    }

    //send success response

    const response_json = { response_code: 1, response_status: 200, response_msg: 'Success', messages: message_array, numbers: number_array, media_url: media_msg_url, message_type: media_message_type, request_id: req.body.request_id }
    logger_all.info("[API SUCCESS RESPONSE] : " + JSON.stringify(response_json));

    return (response_json);

  }

  catch (err) {
    logger_all.info("[get task rcs] Failed - " + err);
    logger.info("[Failure response - Error Occurred] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))

    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }

}

//End function to get task for RCS

module.exports = {
  get_task_rcs,
};
