/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in sender ID process functions which is used to list processed sender ID details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start function to retrieve a list of sender IDs process
async function senderID_process_list(req) {
    var logger_all = main.logger_all;
    var logger = main.logger

    try {

        //Query to get sender ID details based on campaign
        var get_sender_number = `CALL senderid_process_list('${req.body.user_id}', '${req.body.campaign_name}')`;
        logger_all.info("[select query request - sender ID] : " + get_sender_number);
        var get_sender_number_result = await db.query(get_sender_number);

        logger_all.info("select query response - sender ID" + JSON.stringify(get_sender_number_result[0]));
        logger_all.info("select query response - sender ID" + get_sender_number_result[0].length);
        //check if array length is zero, send failure response
        if (get_sender_number_result[0].length > 0) { //Otherwise send success response

            // Fetch detailed information for the unique sender IDs
            return {
                response_code: 1,
                response_status: 200,
                response_msg: 'Success',
                sender_id: get_sender_number_result[0]
            };
        } else {
            return {
                response_code: 0,
                response_status: 204,
                response_msg: 'No Data Available.'
            };
        }
    } catch (err) {
        logger_all.info(": [sender id process list ] Failed - " + err);
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error Occurred.'
        };
    }
}
module.exports = {
    senderID_process_list
};