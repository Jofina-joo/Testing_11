/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in sWaitingApprovals functions which is used to list processed Approval details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start function to retrieve a list of Approval process
async function WaitingApprovals(req) {
    var logger_all = main.logger_all;
    var logger = main.logger

    try {

        //Query to get Approval details based on campaign
        var get_approval = `CALL WaitingApprovals('${req.body.user_id}')`;
        logger_all.info("[select query request - Approval] : " + get_approval);
        var get_approval_result = await db.query(get_approval);

        logger_all.info("select query response - Approval" + JSON.stringify(get_approval_result));

        // Extract and flatten the nested arrays
        const convertedResult = get_approval_result
            .slice(0, -1) // Remove the last non-relevant object
            .flat();      // Flatten the nested arrays


        //check if array length is zero, send failure response
        if (get_approval_result) { //Otherwise send success response

            // Fetch detailed information for the unique Approval
            return {
                response_code: 1,
                response_status: 200,
                response_msg: 'Success',
                result: convertedResult
            };
        } else {
            return {
                response_code: 0,
                response_status: 204,
                response_msg: 'No Data Available.'
            };
        }
    } catch (err) {
        logger_all.info(": [sWaitingApprovals list ] Failed - " + err);
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error Occurred.'
        };
    }
}
module.exports = {
    WaitingApprovals
};
