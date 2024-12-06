/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in reject campaign functions which is used to reject campaign.

Version : 1.0
Author : Sabena Yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import required modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const { count } = require("sms-length");
const env = process.env;
const DB_NAME = env.DB_NAME;

// Start Function - Reject Campaign
async function reject_campaign(req) {
    // Destructure loggers from main
    const { logger_all, logger } = main;
    try {
        // Destructure required properties from req.body
        const { user_id, selected_user_id, compose_message_id, product_name, reason } = req.body;

        let total_credit_count, reject_camp, send_response = {};

        // Check if the user is admin then only continue process
        if (user_id == 1) {
            const get_campaign_result = await db.query(`SELECT * FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} WHERE cm_status = 'W' AND compose_message_id='${compose_message_id}'`);

            // Check if product is 'Whatsapp' to update credits
            if (product_name == 'WHATSAPP') {
                total_credit_count = get_campaign_result[0].total_mobile_no_count;

                // Call Stored procedure - Reject Campaign
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_credit_count}','${total_credit_count}','1')`;
            }
            // Check if product is 'GSM SMS' to update credits
            else if (product_name == 'GSM SMS' || product_name == 'RCS') {
                const select_media_result = await db.query(`SELECT text_title from ${DB_NAME}_${selected_user_id}.compose_msg_media_${selected_user_id} WHERE compose_message_id = '${compose_message_id}' AND cmm_status = 'Y'`);
                var message_content = select_media_result[0].text_title;

                // SMS Credits Calculation
                var data = count(message_content);
                logger_all.info(JSON.stringify(data) + " SMS Calculation");
                var txt_sms_count = data.messages;
                logger_all.info(txt_sms_count + " SMS count based");
                var total_mobile_no_count = get_campaign_result[0].total_mobile_no_count;
                total_credit_count = total_mobile_no_count * txt_sms_count;

                // Call Stored procedure - Reject Campaign
                var product_code = product_name == 'GSM SMS' ? '2' : '3';
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_mobile_no_count}','${total_credit_count}','${product_code}')`;
            }
            // Check if product is 'OBD CALL SIP' to update credits
            else if (product_name == 'OBD CALL SIP') {
                var context_id = get_campaign_result[0].context_id;
                const get_context_result = await db.query(`SELECT audio_duration from ${DB_NAME}.obd_prompt_masters WHERE prompt_id = '${context_id}' AND prompt_status = 'Y'`);
                var promptSecond = get_context_result[0].audio_duration;
                logger_all.info(promptSecond + " Prompt second");

                // Prompt Second Calculation
                var creditsToDeduct = deductCredits(promptSecond);
                logger_all.info(creditsToDeduct + " Duration count based");
                var total_mobile_no_count = get_campaign_result[0].total_mobile_no_count;
                total_credit_count = total_mobile_no_count * creditsToDeduct;

                // Call Stored procedure - Reject Campaign
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_credit_count}','${total_credit_count}','4')`;
            }

            // Check if selected data length is equal to zero, send failure response 'Campaign not found'
            if (get_campaign_result.length == 0) {
                send_response = { response_code: 0, response_status: 201, response_msg: 'Campaign not found' };
                return send_response;
            }

            await db.query(reject_camp);

            // Send Success response
            send_response = { response_code: 1, response_status: 200, response_msg: 'Success' };
            return send_response;
        } else {
            // Otherwise send error response as 'Invalid User'
            send_response = { response_code: 0, response_status: 201, response_msg: "Only admin can reject" };
            return send_response;
        }
    } catch (err) {
        logger_all.error("[reject campaign] - error : " + err);
        const error_msg = { response_code: 0, response_status: 500, response_msg: "Error Occurred." };
        return error_msg;
    }
}

// Helper function to calculate credits
function deductCredits(promptSecond) {
    // Calculate credits as the ceiling of promptSecond divided by 30
    const credits = Math.ceil(promptSecond / 30);
    return credits;
}

module.exports = {
    reject_campaign
};
// End Function - Reject Campaign