/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in Prompt_List report functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
// Start Function - Prompt_List
async function Prompt_List(req) {
    // Destructure loggers from main
    const { logger_all, logger } = main;

    try {
        logger_all.info(" [Prompt_List report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));
        // get the request values 
        const { user_id, date_filter } = req.body;

        // get current Date and time
        const day = new Date();
        const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();

        let get_prompt_master = '';

        if (date_filter) {
            // date function for looping in one by one date
            const filter_date_1 = date_filter.split("-");
            get_prompt_master = `SELECT pm.*,um.user_name FROM obd_prompt_masters pm LEFT JOIN user_management um ON pm.user_id = um.user_id where (um.user_id = '${user_id}' OR um.parent_id IN (${user_id})) AND (DATE(pm.prompt_entry_time) BETWEEN '${filter_date_1[0]}' AND '${filter_date_1[1]}')order by prompt_id desc`;
        } else {
            get_prompt_master = `SELECT pm.*,um.user_name FROM obd_prompt_masters pm LEFT JOIN user_management um ON pm.user_id = um.user_id where (um.user_id = '${user_id}' OR um.parent_id IN (${user_id})) AND (DATE(pm.prompt_entry_time) BETWEEN '${today_date}' AND '${today_date}')order by prompt_id desc`;
        }
        // Execute the prompt list query.
        const get_prompt_master_result = await db.query(get_prompt_master);

        // Check Prompt_List result length is zero execute the this condition. otherwise else condition is execute.
        if (get_prompt_master_result.length === 0) {
            return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
        } else {
            return { response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: get_prompt_master_result.length, campaign_list: get_prompt_master_result };
        }

    } catch (err) {
        // send error response as 'Error occurred'
        logger_all.info("[Prompt_List report] Failed - " + err);
        send_response = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }
        return send_response;
    }
}

// End Function - Prompt_List
module.exports = {
    Prompt_List
};