/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in Process Channel functions which is used to Process Channel for user.

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
 // Destructure loggers from main
 const { logger_all, logger } = main;
// Start function to Process Channel
async function process_servers(req) {

    try {
        logger_all.info(" [Process Servers report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));
        // get Parameters
          const { campaign_id, selected_user_id } = req.body;

        // Get campaign id on server ids
        const campaign_list_results = await db.query(`SELECT compose_message_id,campaign_name,sender_mobile_nos,total_mobile_no_count,user_id,DATE_FORMAT(cm_entry_date, '%d-%m-%Y %H:%i:%s') as cm_entry_date FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} WHERE compose_message_id = '${campaign_id}'`);

        if (campaign_list_results.length === 0) {
            return { response_code: 0, response_status: 204, response_msg: 'Campaign ID is not available' };
        }

        const server_ids_blob = campaign_list_results[0].sender_mobile_nos;
        const server_ids = server_ids_blob.toString().split(',');

        // get_process_servers this condition is true.process will be continued. otherwise process are stoped.
        const process_servers_results = await db.query(`SELECT * FROM sip_servers where sip_status in ('P','Y','T') and sip_id in (${server_ids.join(',')})`);
        // if the get_process_servers length is not available to send the Invalid Existing Password. Kindly try again!.otherwise the process was continued
        if (process_servers_results.length == 0) {
            const failed_msg = {
                response_code: 0,
                response_status: 201,
                response_msg: 'No Data Available.'
            }
            logger_all.info(JSON.stringify(failed_msg))
            return failed_msg;
        } else {
            const success_msg = { // to return the success message
                response_code: 1,
                response_status: 200,
                response_msg: 'Success',
                no_of_rows: process_servers_results.length,
                reports: process_servers_results

            }
            logger_all.info(JSON.stringify(success_msg))
            return success_msg;
        }

    } catch (e) {// any error occurres send error response to client
        logger_all.info("[ChangePassword failed response] : " + e)
        const error_msg = {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error occured'
        }
        return error_msg;
    }
}
// End function to Process Channel

// using for module exporting
module.exports = {
    process_servers
}
