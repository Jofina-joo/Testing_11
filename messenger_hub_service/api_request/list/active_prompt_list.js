/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in Active_Prompt_List report functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
// Start Function - Active_Prompt_List
async function Active_Prompt_List(req) {
    // Destructure loggers from main
    const { logger_all, logger } = main;

    try {
        logger_all.info(" [Active_Prompt_List report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));
        // get the request values 
        const { user_id } = req.body;

        // Execute the prompt list query.
        const get_prompt_master_result = await db.query(`SELECT pm.*,um.user_name FROM obd_prompt_masters pm LEFT JOIN user_management um ON pm.user_id = um.user_id where (um.user_id = '${user_id}' OR um.parent_id IN (${user_id})) order by prompt_id desc`);

        // Check Active_Prompt_List result length is zero execute the this condition. otherwise else condition is execute.
        if (get_prompt_master_result.length === 0) {
            return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
        } else {
            return { response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: get_prompt_master_result.length, campaign_list: get_prompt_master_result };
        }

    } catch (err) {
        // send error response as 'Error occurred'
        logger_all.info("[Active_Prompt_List report] Failed - " + err);
        send_response = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }
        return send_response;
    }
}

// End Function - Active_Prompt_List
module.exports = {
    Active_Prompt_List
};