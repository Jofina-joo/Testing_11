/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in Channel Masters functions which is used to Channel Masters for user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');

// Start function to Channel Masters
async function channel_masters(req) {
    // Destructure loggers from main
    const { logger_all, logger } = main;
    try {
        logger_all.info(" [channel_masters report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));
        const channel_masters_results = await db.query(`SELECT * FROM sip_servers where sip_status = 'Y'`);
        // if the get_channel_masters length is not available to send the No data available.otherwise the process was continued.
        if (channel_masters_results.length == 0) {
            return {
                response_code: 0,
                response_status: 201,
                response_msg: 'No Data Available.'
            };
        } else {
            return { // to return the success message
                response_code: 1,
                response_status: 200,
                response_msg: 'Success',
                no_of_rows: channel_masters_results.length,
                reports: channel_masters_results

            };
        }

    } catch (e) {// any error occurres send error response to client
        logger_all.info("[channel_masters failed response] : " + e)
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error occured'
        };
    }
}
// End function to Channel Masters

// using for module exporting
module.exports = {
    channel_masters
}