/*
This api has chat API functions which is used to connect the mobile chat.
This page is act as a Backend page which is connect with Node JS API and PHP Frontend.
It will collect the form details and send it to API.
After get the response from API, send it back to Frontend.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 05-Jul-2023
*/
// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
// MCReceiverUser - start
async function MCReceiverUser(req) {
    var logger_all = main.logger_all
    var logger = main.logger
    try {
        logger_all.info(" [MCReceiverUser] - " + req.body);
        logger.info("[API REQUEST - MCReceiverUser] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
        //  Get all the req header data
        var get_mc_receiver_user = '';
        var receiver_user = `SELECT user_id, user_name, user_email, user_mobile, api_key FROM user_management where user_status = 'Y' and user_master_id != '1'`;
        logger_all.info("[select query request] : " + receiver_user);
        get_mc_receiver_user = await db.query(receiver_user);
        logger_all.info("[select query response] : " + JSON.stringify(get_mc_receiver_user))

        logger_all.info("[select query response] : " + JSON.stringify(get_mc_receiver_user))
        // if the get_mc_receiver_user length is '0' to get the no available data.otherwise it will be return the get_mc_receiver_user details.get_mc_receiver_user is empty is to get the no available data.
        if (get_mc_receiver_user.length > 0) {
            return {
                response_code: 1,
                response_status: 200,
                num_of_rows: get_mc_receiver_user.length,
                response_msg: 'Success',
                report: get_mc_receiver_user
            };

        } else {
            return {
                response_code: 1,
                response_status: 204,
                response_msg: 'No data available'
            };

        }

    } catch (e) { // any error occurres send error response to client
        logger_all.info("[MCReceiverUser failed response] : " + e)
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error occured'
        };
    }
}
// MCReceiverUser - end


// using for module exporting
module.exports = {
    MCReceiverUser
}
