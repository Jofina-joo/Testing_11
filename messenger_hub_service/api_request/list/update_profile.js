/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update profile functions which is used to update profile for signup page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const md5 = require("md5")
// UpdateProfileDetails- start
async function UpdateProfileDetails(req) {
    var logger_all = main.logger_all
    var logger = main.logger
    try {
        // get all the req data
        var user_name = req.body.user_name;
        var user_email = req.body.user_email;
        var user_mobile = req.body.user_mobile;
        var login_id = req.body.login_id;
        var user_id = req.body.user_id;

        var update_query_value_1;
        var update_query_value1 = '';
        var update_profile_details1;

        // query parameters
        if (user_name) {
            update_query_value1 += `user_name = '${user_name}', login_id = '${login_id}',`;
        }
        if (user_email) {
            update_query_value1 += `user_email = '${user_email}',`;
        }
        if (user_id != 1) {
            update_query_value1 += `user_status = 'N',`;
        }
        if (user_mobile) {
            update_query_value1 += `user_mobile = '${user_mobile}',`;
        }

        logger_all.info("[UpdateProfileDetails query parameters] : " + JSON.stringify(req.body));
        if (update_query_value1) {
            // UpdateProfileDetails to execute this query
            var update_user_man = `UPDATE user_management SET ${update_query_value1}`;
            logger_all.info(update_user_man);

            update_query_value_1 = update_user_man.substring(0, update_user_man.length - 1);
            logger_all.info(update_query_value_1);
            logger_all.info("[Update query request - User details1] : " + `${update_query_value_1} WHERE user_id = ${user_id}`);
            update_profile_details1 = await db.query(`${update_query_value_1} WHERE user_id = ${user_id}`);
            logger_all.info("[Update query request - User details1] : " + JSON.stringify(update_profile_details1));
            // if the get_available_message length is not available to send the no available data.otherwise it will be return the get_available_message details.
        }

        // if the get_available_message length is not available to send the no available data.otherwise it will be return the get_available_message details.
        if (update_profile_details1) {
            return { response_code: 1, response_status: 200, num_of_rows: 1, response_msg: 'Success' };

        } else {
            return { response_code: 1, response_status: 204, response_msg: 'No data available' };
        }

    }
    catch (e) {// any error occurres send error response to client
        logger_all.info("[UpdateProfileDetails failed response] : " + e)
        return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
    }
}
// UpdateProfileDetails - end

// using for module exporting
module.exports = {
    UpdateProfileDetails,
}