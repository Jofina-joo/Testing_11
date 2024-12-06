/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to list user data for dashboard page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and libraries
const main = require('../../logger');
const db = require("../../db_connect/connect");

var logger_all = main.logger_all
var logger = main.logger
// Define the function containing the logic you want to run periodically
async function cronfolder() {
    try {
        // Query to retrieve active users
        var get_users = `SELECT * FROM user_management WHERE user_status = 'Y' AND user_master_id != '1'`;
        logger_all.info("[select query get_users request] : " + get_users);
        // Execute the query to get active users
        const get_all_users = await db.query(get_users);

        logger_all.info("[select query get_all_users response] : " + JSON.stringify(get_all_users));

        var total_user_id = [];

        // Extract user IDs from the query result
        if (get_all_users.length > 0) {
            for (var i = 0; i < get_all_users.length; i++) {
                total_user_id.push(get_all_users[i].user_id);
            }
        }

        logger_all.info("[select query request] : " + total_user_id);
        // Constructing the comma-separated list of user IDs
        var userIds = total_user_id.join("','");
        // Constructing the SQL update query with the IN clause
        //var update_user_credits = `UPDATE user_credits SET available_credits = '300', total_credits = '300', used_credits = '0' WHERE user_id IN ('${userIds}') and rights_id in (1,2)`;
        var update_user_credits = `UPDATE user_credits SET available_credits = CASE WHEN rights_id = 1 THEN 1000 WHEN rights_id = 2 THEN 300 ELSE available_credits END,total_credits = CASE WHEN rights_id = 1 THEN 1000 WHEN rights_id = 2 THEN 300 ELSE total_credits END WHERE user_id IN ('${userIds}')`;
        // Logging the update query before execution
        logger_all.info("[update query request - update user credits] : " + update_user_credits);
        // Executing the update query
        var update_user_credits_res = await db.query(update_user_credits);
        // Logging the response after query execution
        logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));

    } catch (error) {
        logger_all.info("Error in cron task:", error);
    }
}

// Export the function so that it can be called from outside
module.exports = cronfolder;
