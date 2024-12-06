/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to list user data for dashboard page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require('dotenv').config()
const main = require('../../logger');
const env = process.env
const DB_NAME = env.DB_NAME;
// Destructure loggers from main
const { logger_all, logger } = main;
//Start Function - Dashboard page
async function Dash_Board(req) {
    try {
        // Using for which request is coming to write the log file
        logger_all.info(" [Dashboard list] - " + req.body);
        logger.info("[API REQUEST - dashboard] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

        // get current Date and time
        const day = new Date();
        const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();
        // declare the variables
        let newdb, getsummary;
        // declare the array
        let array_list_user_id = [], total_rights_name = [], total_user_id = [], total_user_master_id = [], total_user_name = [], total_available_credits = [], total_rights_id = [], total_response = [];

        //Call Stored procedure - Dashboard_Query1
        const [results] = await db.query(`CALL Dashboard_Query1('${req.body.user_id}')`);

        // loop all the get the user id to push the total_available_messages, the total_user_id,total_user_master_id,total_user_name array
        if (results.length > 0) {
            for (var i = 0; i < results.length; i++) {
                total_user_id.push(results[i].user_id);
                total_rights_name.push(results[i].rights_name);
                total_rights_id.push(results[i].rights_id);
                total_user_master_id.push(results[i].user_master_id);
                total_user_name.push(results[i].user_name);
                array_list_user_id.push(results[i].user_id);
                total_available_credits.push(results[i].available_credits);
            }
        }
        // loop for array_list_user_id length get all counts
        for (var i = 0; i < array_list_user_id.length; i++) {
            newdb = DB_NAME + "_" + array_list_user_id[i];
            //Call Stored procedure - Dashboard_Query2
            const [getsummary] = await db.query(`CALL Dashboard_Query2('${array_list_user_id[i]}','${today_date}','${total_rights_id[i]}')`);
            // if the getsummary length is not available to push the my obj datas.otherwise it will be return the push the getsummary details.
            if (getsummary.length == 0) {
                var newObj = {
                    "user_id": total_user_id[i],
                    "rights_name": total_rights_name[i],
                    "user_name": total_user_name[i],
                    "available_credits": total_available_credits[i],
                    "total_msg": 0,
                    "total_success": 0,
                    "total_failed": 0,
                    "total_invalid": 0,
                    "total_waiting": 0,
                    "total_process": 0,
                }
                total_response.push([newObj]);
            } else {
                getsummary[0].available_credits = total_available_credits[i];
                total_response.push([getsummary[0]]);
            }
        }

        // getsummary length is '0'.to send the Success message and to send the total_response datas.
        if (getsummary == 0) {
            return {
                response_code: 1,
                response_status: 200,
                response_msg: 'Success',
                report: total_response
            };
        } else { //otherwise to send the success message and get summarydetails
            return {
                response_code: 1,
                response_status: 200,
                response_msg: 'Success',
                report: total_response
            };
        }
    } catch (e) { // any error occurres send error response to client
        logger_all.info("[dashboard page - error] : " + e)
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error occured'
        };
    }
}
//End Function - Dashboard page

// using for module exporting
module.exports = {
    Dash_Board,
};