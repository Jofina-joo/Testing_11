/*
This API has chat API functions which are used to connect the mobile chat.
This page acts as a backend page which connects with Node JS API and PHP frontend.
It collects the form details and sends them to the API.
After getting the response from the API, it sends it back to the frontend.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 05-Jul-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const moment = require('moment');

// Create Prompt Function - start
async function CreatePrompt(req) {
  // Destructure loggers from main
const { logger_all, logger } = main;

    try {

        logger_all.info(" [Create Prompt] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

        // Get all request data
        const { call_type, language_code, location, type, context, company_name, prompt_remarks, prompt_second, user_id } = req.body;
        const upload_prompt = req.body.upload_prompt ? req.body.upload_prompt : '-';

        const curdtm = moment().format('HHmmss');
        // Decalure the variable values
        const prompt_status = (call_type === 'C') ? 'Y' : 'N';
        const prompt_second_value = (call_type === 'C' && prompt_second) ? prompt_second : 0;

        // Execute the obd_prompt_masters values insert in database.
        const insert_prompt_result = await db.query(`INSERT INTO obd_prompt_masters VALUES(NULL, '${user_id}','${call_type}','${company_name}','${location}', '${language_code}', '${type}','${upload_prompt}', '${context}_${curdtm}', '${prompt_remarks}', '${prompt_status}',CURRENT_TIMESTAMP,NULL,'${prompt_second_value}')`);

        // if insert_prompt_result is greaterthan zero exeute the success message.
        if (insert_prompt_result.affectedRows > 0) {
            return { response_code: 1, response_status: 200, response_msg: 'Success.' };
        } else { // Otherwise execute the No available data.
            return { response_code: 0, response_status: 201, response_msg: 'No data available' };
        }

    } catch (e) { // Any error occurs send error response to client
        logger_all.info("[Create Prompt failed response] : " + e);
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error occurred'
        };
    }
}
// Create Prompt Function - end

// Using for module exporting
module.exports = {
    CreatePrompt
};
