/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in reject campaign functions which is used to reject campaign.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import required modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const { count } = require("sms-length");
const env = process.env
const DB_NAME = env.DB_NAME;

// Start Function - Reject Campaign
async function reject_campaign(req) {
    var logger_all = main.logger_all
    var logger = main.logger
    try {

        //Get all request data
        var user_id = req.body.user_id;
        var selected_user_id = req.body.selected_user_id;
        var compose_message_id = req.body.compose_message_id;
        var product_name = req.body.product_name;
        var reason = req.body.reason;
        var total_credit_count, reject_camp;

        // Check if the user is admin then only continue process
        if (user_id == 1) {
            var get_campaign = `SELECT * FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} WHERE cm_status = 'W' AND compose_message_id='${compose_message_id}'`;
            logger_all.info("[select query request - get user except test user] : " + get_campaign);
            const get_campaign_result = await db.query(get_campaign);
            logger_all.info("[select query response - get user except test user] : " + JSON.stringify(get_campaign_result));

            //Check if product is 'Whatsapp' to update credits
            if (product_name == 'WHATSAPP') {
                total_credit_count = get_campaign_result[0].total_mobile_no_count;

                //Call Stored procedure - Reject Campaign
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_credit_count}','${total_credit_count}','1')`;
                logger.info(" REJECT CAMPAIGN " + `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_credit_count}','${total_credit_count}','1')`);
            }

            //Otherwise check if product is 'GSM SMS' to update credits
            else if (product_name == 'GSM SMS') {
                var select_media = `SELECT text_title from ${DB_NAME}_${selected_user_id}.compose_msg_media_${selected_user_id} WHERE compose_message_id = '${compose_message_id}' AND cmm_status = 'Y'`
                logger_all.info("[select query request] : " + select_media);
                const select_media_result = await db.query(select_media);
                logger_all.info("[select query response] : " + JSON.stringify(select_media_result));
                message_content = select_media_result[0].text_title;

                //SMS Credits Calculation
                var data = count(message_content);
                logger_all.info(JSON.stringify(data) + "SMS Calculation");
                txt_sms_count = data.messages;
                logger_all.info(txt_sms_count + " SMS count based");
                total_mobile_no_count = get_campaign_result[0].total_mobile_no_count;
                total_credit_count = total_mobile_no_count * txt_sms_count;

                //Call Stored procedure - Reject Campaign
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_mobile_no_count}','${total_credit_count}','2')`;
                logger.info(" REJECT CAMPAIGN " + `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_mobile_no_count}','${total_credit_count}','2')`);
                logger.info(" REJECT CAMPAIGN " + reject_camp);
            }
            // Otherwise, check if the product is 'GSM SMS' to update credits
            else if (product_name == 'RCS') {
                const select_media = `SELECT text_title from ${DB_NAME}_${selected_user_id}.compose_msg_media_${selected_user_id} WHERE compose_message_id = '${compose_message_id}' AND cmm_status = 'Y'`
                logger_all.info("[select query request] : " + select_media);
                const select_media_result = await db.query(select_media);
                logger_all.info("[select query response] : " + JSON.stringify(select_media_result));
                const message_content = select_media_result[0].text_title;

                // SMS Credits Calculation
                const data = count(message_content);
                logger_all.info(JSON.stringify(data) + "SMS Calculation");
                const txt_sms_count = data.messages;
                logger_all.info(txt_sms_count + " SMS count based");
                const total_mobile_no_count = get_campaign_result[0].total_mobile_no_count;
                total_credit_count = total_mobile_no_count * txt_sms_count;

                // Call Stored procedure - Reject Campaign
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_mobile_no_count}','${total_credit_count}','3')`;
                logger.info(" REJECT CAMPAIGN " + `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_mobile_no_count}','${total_credit_count}','3')`);
                logger.info(" REJECT CAMPAIGN " + reject_camp);
            }
            else if (product_name == 'OBD CALL SIP') {
                const context_id = get_campaign_result[0].context_id;

                const get_context = `SELECT audio_duration from ${DB_NAME}.obd_prompt_masters WHERE prompt_id = '${context_id}' AND prompt_status = 'Y'`
                logger_all.info("[select query request] : " + get_context);
                const get_context_result = await db.query(get_context);
                logger_all.info("[select query response] : " + JSON.stringify(get_context_result));
                const promptSecond = get_context_result[0].audio_duration;
                logger_all.info(promptSecond + "Prompt second");
                // Prompt Second Calculation
                const creditsToDeduct = deductCredits(promptSecond);

                logger_all.info(creditsToDeduct + "Duration count based");
                const total_mobile_no_count = get_campaign_result[0].total_mobile_no_count;
                total_credit_count = total_mobile_no_count * creditsToDeduct;

                // Call Stored procedure - Reject Campaign
                reject_camp = `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_credit_count}','${total_credit_count}','4')`;
                logger.info(" REJECT CAMPAIGN " + `CALL RejectCampaign('${selected_user_id}','${compose_message_id}','${reason}','${total_credit_count}','${total_credit_count}','4')`);
            }
            //Check if selected data length is equal to zero, send failure response 'Campaign not found'
            if (get_campaign_result.length == 0) {
                const invalid_msg_1 = { response_code: 0, response_status: 201, response_msg: 'Campaign not found' };
                logger.info("[Failed response - Invalid User] : " + JSON.stringify(invalid_msg_1))
                return invalid_msg_1;
            }
            logger.info(" REJECT CAMPAIGN " + reject_camp);
            const get_reject_campaign = await db.query(reject_camp);
            logger.info("[Failed response - Invalid User] : " + JSON.stringify(get_reject_campaign));
            //Send Success response
            const success_msg = { response_code: 1, response_status: 200, response_msg: 'Success' };
            logger.info("[success_msg] : " + JSON.stringify(success_msg));
            return success_msg;
        }
        else {
            //Otherwise send error response as 'Invalid User'
            const invalid_msg = { response_code: 0, response_status: 201, response_msg: "Only admin can reject" };
            logger.info("[Failed response - Invalid User] : " + JSON.stringify(invalid_msg))
            return invalid_msg;
        }
    }
    catch (err) {
        logger_all.info("[reject campaign] - error :  " + err);
        const error_msg = { response_code: 0, response_status: 201, response_msg: "Error Occurred." };
        logger.info("[Failed response - Error occured] : " + JSON.stringify(error_msg))
        return error_msg;
    }
}

function deductCredits(promptSecond) {
    // Calculate credits as the ceiling of promptSecond divided by 30
    const credits = Math.ceil(promptSecond / 30);
    return credits;
}

module.exports = {
    reject_campaign
};
//End Function - Reject Campaign