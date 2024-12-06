/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for Restart_process.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const env = process.env
const DB_NAME = env.DB_NAME;
const axios = require('axios');
const safeStringify = require('flatted').stringify; // Use Flatted to handle circular JSON

//Start Function - RestartCampaignProcess
async function RestartCampaignProcess(req) {
    // Destructure loggers from main
    const { logger_all, logger } = main;

    try {
        logger_all.info(" [Restart Process] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
        // get call request 
        const { campaign_id, selected_user_id, sip_id, user_master_id } = req.body;

        let server_urls = [], send_reponse = {};
        const formattedSipId = sip_id.map(id => `'${id}'`).join(", ");
        // Get compose message table using on stop campaign
        const get_campaign_result = await db.query(`SELECT * FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} where compose_message_id = '${campaign_id}' and cm_status = 'S'`);

        //Check if selected data length is equal to zero, send failure response 'Stoped campaign is not vailable for this campaignid.'
        if (get_campaign_result.length == 0) {
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign not available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            return { response_code: 0, response_status: 201, response_msg: 'Campaign not available.' };;
        }

        // Destructure the required properties from get_campaign_result[0]
        const { sender_mobile_nos, campaign_type: message_type, context_id, retry_time_interval: retry_time, retry_count } = get_campaign_result[0];

        var retry_in_millisec = retry_time * 1000;

        //Update channel status as 'P' while approve
        const get_sip_servers_results = await db.query(`SELECT * FROM sip_servers WHERE sip_id in (${formattedSipId}) and sip_status in ('Y','T')`);

        if (get_sip_servers_results.length == 0) {
            return { response_code: 0, response_status: 204, response_msg: 'No active sip servers.' };
        }

        get_sip_servers_results.forEach(result => {
            server_urls.push(result.server_url);
        });

        await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'P' WHERE sip_status in ('Y','T') and sip_id in (${formattedSipId})`);

        const get_process_ids = await db.query(`SELECT * FROM sip_servers WHERE sip_id in (${sender_mobile_nos}) and sip_status = 'P'`);

        // Send the request on Sip servers for restart process
        const payload = {
            campaignId: String(campaign_id),
            user_id: String(selected_user_id),
            message_type: String(message_type),
            retry_count_value: String(retry_count),
            retry_in_millisec: String(retry_in_millisec),
            context_id: String(context_id)
        };

        // Log payload using Flatted to handle circular JSON
        logger_all.info("Send Request From Asterisk with payload: " + safeStringify(payload));

        let allPromises = server_urls.map(url => {
            logger_all.info(`Sending request to ${url}/restart_call with payload`, payload);
            return axios.post(url + "/restart_call", payload)
                .then(response => {
                    logger_all.info(`Response from ${url}/restart_call:`, response.data);
                    return 1; // Assuming return 1 on success
                })
                .catch(error => {
                    console.error(`Error with URL ${url}/restart_call: ${error.message}`);
                    return 0; // Assuming return 0 on failure
                });
        });

        let results = await Promise.all(allPromises);
        let return_status = results.every(status => status === 1) ? 1 : 0;

        if (return_status === 0) {
            //Otherwise send success response
            return { response_code: 0, response_status: 201, response_msg: 'Failed to restart the campaign.Check the sip servers.' };

        } else {
            console.log(sender_mobile_nos.length )
            console.log(get_process_ids.length);
            if (sender_mobile_nos.length == get_process_ids.length) {
                console.log("coming if condition")
                // After inserting data into 'cdrs' table, update the 'call_status' in 'calls' table
                await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'P' WHERE compose_message_id = '${campaign_id}' and cm_status = 'S'`);
            }
            //Otherwise send success response
            send_reponse = { response_code: 1, response_status: 200, response_msg: 'Success' }
            logger_all.info(JSON.stringify(send_reponse));
        }
        console.log(JSON.stringify(send_reponse) + "send_reponse")
        return send_reponse;
    } catch (err) {
        logger_all.info("[Restart Campaign Process] Failed - " + err);
        send_reponse = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }
        return send_reponse;
    }
}
// export the restart campaign process
module.exports = {
    RestartCampaignProcess
};
//End Function - RestartCampaignProcess