/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/


const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const env = process.env
const DB_NAME = env.DB_NAME;
async function OBDcampaign_list(req) {
    const logger_all = main.logger_all
    const logger = main.logger

    try {

        const user_product = req.body.user_product;
        const selected_user_id = req.body.selected_user_id;

        // to get the product id from select query
        const get_product = `SELECT * FROM rights_master where rights_name = '${user_product}' AND rights_status = 'Y' `;

        logger_all.info("[select query request] : " + get_product);
        const get_product_id = await db.query(get_product);
        logger_all.info("[select query response] : " + JSON.stringify(get_product_id));

        const user_product_id = get_product_id[0].rights_id;

        // Loop for array_list_user_id length (not provided in your snippet, assuming it's outside this block)
        const campaign_list = `SELECT compose_message_id, campaign_name, total_mobile_no_count, user_id, DATE_FORMAT(cm_entry_date, '%d-%m-%Y %H:%i:%s') AS cm_entry_date FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} WHERE cm_status IN ('Y') AND product_id='${user_product_id}' AND campaign_report_status = 'N' ORDER BY cm_entry_date DESC`;

        // Log the constructed query for debugging
        logger_all.info("[Select query request - campaign list] : " + campaign_list);

        // Execute the query
        const campaign_list_result = await db.query(campaign_list);
        logger_all.info("[Select query response - campaign list] : " + JSON.stringify(campaign_list_result));
        // Handle campaign_list_result as needed

        if (campaign_list_result.length == 0) {
            return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
        }
        else {
            return { response_code: 1, response_status: 200, response_msg: 'Success', campaign_list: campaign_list_result };
        }
    }

    catch (err) {
        logger_all.info("[campaign list report] Failed - " + err);

        return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
    }
}
module.exports = {
    OBDcampaign_list
};
