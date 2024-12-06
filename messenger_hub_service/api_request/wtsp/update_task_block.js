/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update task block functions which is used to update block status

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
//Start function to update task for block
async function update_task_block(req) {
      var logger_all = main.logger_all
      var logger = main.logger

      try {
            logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
            logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

            //Get all request data
            var mobile_number = req.body.mobile_number;
            var compose_id = req.body.com_msg_block_id;
            var user_id = req.body.selected_user_id;
            var str = req.body.data;

            //Split and get data
            var list = str.split("˜")
            for (var i = 0; i < list.length; i++) {
                  var array = list[i].split("||")

                  //Query to update blocked data
                  var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_block_${user_id} SET msg_block_status = 'Y', response_message = '${array[2]}', response_date = '${array[1]}' WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}'`
                  logger_all.info("[update query request] : " + update_data);
                  const update_data_result = await db.query(update_data);
                  logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

                  //Query to update sender ID status 'Y'
                  var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`
                  logger_all.info("[update query request] : " + update_sender_sts);
                  const update_sender_sts_result = await db.query(update_sender_sts);
                  logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));

            }
            //send success response
            return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };

      }

      catch (err) {
            logger_all.info(": [update_task_block] Failed - " + err);
            logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id }))
            //send failure response 'Error occurred'
            return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
      }

}

module.exports = {
      update_task_block,
};
//End function to update task for block