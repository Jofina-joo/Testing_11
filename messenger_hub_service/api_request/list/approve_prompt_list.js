/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in Approve_Prompt report functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

// Start Function - Approve_Prompt
async function Approve_Prompt(req) {
// Destructure loggers from main
const { logger_all, logger } = main;

    logger_all.info(" [Approve_Prompt report] - " + req.body);
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));
    try {

        // Retrieve the product ID for the specified user product
        const get_prompt_master_result = await db.query(`SELECT pm.*,um.user_name FROM obd_prompt_masters pm LEFT JOIN user_management um ON pm.user_id = um.user_id WHERE pm.prompt_status = 'N' order by prompt_id desc`);

        // Check Approve_Prompt result length
        if (get_prompt_master_result.length === 0) {
            return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
        } else {
            return { response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: get_prompt_master_result.length, campaign_list: get_prompt_master_result };
        }

    } catch (err) {
        logger_all.info("[Approve_Prompt report] Failed - " + err);
        // send error response as 'Error occurred'
        logger.info("[Failed response - Error Occurred] : " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }));
        return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
    }
}

// End Function - Approve_Prompt
module.exports = {
    Approve_Prompt
};
