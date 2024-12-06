/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in Obd_Detailed_reports functions which is used to Obd_Detailed_reports for user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const env = process.env
const DB_NAME = env.DB_NAME;
// Start function to create a summary report
async function Obd_Detailed_reports(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;
    try {

        logger_all.info(" [Call_Holding report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));

        //  Get all the req header data
        const header_token = req.headers['authorization'];

        // get current Date and time
        const day = new Date();
        const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();

        // get all the req filter data
        const date_filter = req.body.date_filter;
        const user_id = req.body.user_id;
        // declare the variables
        let report_query = '';
        let getsummary;
        let filter_date_1;

        // Query parameters
        logger_all.info("[Obd_Detailed_reports query parameters] : " + JSON.stringify(req.body));

        // if the filter_date is empty and store_id_filter is empty to execute this condition
        if (!date_filter) {
            report_query = `SELECT wht.download_url,wht.context,wht.campaign_id,wht.report_entry_time,wht.report_status,wht.user_id, usr.user_name,sum.campaign_name,sum.total_dialled, sum.total_success,sum.total_failed , sum.total_busy ,sum.total_no_answer  FROM ${DB_NAME}.obd_cdr_reports wht LEFT JOIN ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id LEFT JOIN ${DB_NAME}.summary_reports_obd sum ON wht.report_id = sum.summary_report_id WHERE (usr.user_id = '${user_id}' OR usr.parent_id = '${user_id}') AND (DATE(wht.report_entry_time) BETWEEN '${today_date}' AND '${today_date}') ORDER BY wht.report_entry_time DESC`;
        }

        if (date_filter) {
            let filter_date_1 = date_filter.split("-");
            report_query = `SELECT wht.download_url,wht.context,wht.campaign_id,wht.report_entry_time,wht.report_status,wht.user_id,usr.user_name,sum.campaign_name,sum.total_dialled, sum.total_success,sum.total_failed , sum.total_busy ,sum.total_no_answer FROM ${DB_NAME}.obd_cdr_reports wht LEFT JOIN ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id LEFT JOIN ${DB_NAME}.summary_reports_obd sum ON wht.report_id = sum.summary_report_id  WHERE (usr.user_id = '${user_id}' OR usr.parent_id = '${user_id}') AND (DATE(wht.report_entry_time) BETWEEN '${filter_date_1[0]}' AND '${filter_date_1[1]}') ORDER BY wht.report_entry_time DESC`;
        }

        logger_all.info('[select query request] : ' + report_query);
        getsummary = await db.query(report_query);
        // logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(getsummary));

        // getsummary length is '0'. To send the Success message and to send the total_response data.
        if (getsummary.length === 0) {
            const failure_msg = { response_code: 0,response_status: 204,response_msg: "No Data Available"}
            logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(failure_msg));
            return failure_msg;
        } else { // otherwise to send the success message and get summary details
            const send_success = { response_code: 1,response_status: 200,response_msg: 'Success', report: getsummary ,num_of_rows:getsummary.length }
            logger.info("[API SUCCESS RESPONSE - get summary] : " + JSON.stringify(send_success));
            return send_success;
        }
    } catch (e) { // any error occurs send error response to client
        logger_all.info("[campaign list report] Failed - " + e);
        const error_msg = { response_code: 0,response_status: 201,response_msg: 'Error Occurred.'};
        logger_all.info("[error_msg response] : " + JSON.stringify(error_msg));
        return error_msg;
    }
}
// End function to create a summary report

// using for module exporting
module.exports = {
    Obd_Detailed_reports
}
