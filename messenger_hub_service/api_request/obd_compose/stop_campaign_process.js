/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for stop_process.

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

//Start Function - StopCampaignProcess
async function StopCampaignProcess(req) {
    // Destructure loggers from main
    const { logger_all, logger } = main;
    let send_response = {};

    try {
        logger_all.info(" [Stop Process] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
        // get call request 
        const { campaign_id, selected_user_id, sip_id, context_id, user_master_id } = req.body;
        let server_urls = [], send_reponse = {}, server_names = [];
        let sip_status = user_master_id === '4' ? 'T' : 'Y';

        // Get compose message table using on stop campaign
        const get_campaign_result = await db.query(`SELECT * FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} where compose_message_id = '${campaign_id}' and cm_status = 'P'`);

        //Check if selected data length is equal to zero, send failure response 'Compose ID Not Available'
        if (get_campaign_result.length == 0) {
            await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Campaign not available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            return { response_code: 0, response_status: 201, response_msg: 'Campaign not available.' };;
        }

        // Destructure the required properties from get_campaign_result[0]
        const { sender_mobile_nos } = get_campaign_result[0];

        const formattedSipId = sip_id.map(id => `'${id}'`).join(", ");

        const get_sip_servers_results = await db.query(`SELECT * FROM sip_servers WHERE sip_id in (${formattedSipId}) and sip_status = 'P'`);

        if (get_sip_servers_results.length == 0) {
            send_reponse = { response_code: 0, response_status: 204, response_msg: 'No processing sip servers.' };
            return send_reponse;
        }
        get_sip_servers_results.forEach(result => {
            server_names.push(result.server_name)
            server_urls.push(result.server_url);
        });

        //Update channel status as 'P' while approve
        await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = '${sip_status}' WHERE sip_status = 'P' and sip_id in (${formattedSipId})`);

        const get_active_sips = await db.query(`SELECT * FROM sip_servers WHERE sip_id in (${sender_mobile_nos}) and sip_status in ('Y','T')`);

        let payload = {
            campaign_id: String(campaign_id),
            user_id: String(selected_user_id),
            context_id: context_id
        };
        // send the request on Asterisk
        logger_all.info("Send Request From Astersik " + payload)

        let allPromises = server_urls.map(url => {
            logger_all.info(`Sending request to ${url}/stop_call with payload`, payload);
            return axios.post(url + "/stop_call", payload)
                .then(response => {
                    logger_all.info(`Response from ${url}/stop_call:`, response.data);
                    return 1; // Assuming return 1 on success
                })
                .catch(error => {
                    console.error(`Error with URL ${url}/stop_call: ${error.message}`);
                    return 0; // Assuming return 0 on failure
                });
        });

        let results = await Promise.all(allPromises);
        // let return_status = results.every(status => status === 1) ? 1 : 0;
        const success_case = server_names.filter((name, index) => results[index] === 1);
        const failure_case = server_names.filter((name, index) => results[index] === 0);

        logger_all.info(success_case + "success_case")
        logger_all.info(failure_case + "failure_case")
        let response_comments = '', response_status = 'S';

        if (failure_case.length > 0) {
            response_status = 'F';
            response_comments = `Failed to call these servers ${failure_case.join(', ')}`;
        }
        if (success_case.length > 0) {
            response_status = 'S';
            response_comments += ` Calls initiated on ${success_case.join(', ')}`;
        }

        if (failure_case.length == server_names.length) {
            logger_all.info("LOG 1");
            // If error occurs, send failure response
            await db.query(`UPDATE api_log SET response_status = 'F', response_date = CURRENT_TIMESTAMP, response_comments = 'Failed to stop the call.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'P' WHERE sip_status IN ('Y','T') AND sip_id IN (${sender_mobile_nos})`);
            await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'P' WHERE compose_message_id = '${campaign_id}'`);
            send_response = { response_code: 0, response_status: 201, response_msg: response_comments };

        } else if (success_case.length == server_names.length) {
            logger_all.info("LOG 2");
            // Send success response
            await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'S' WHERE compose_message_id = '${campaign_id}'`);
            await db.query(`UPDATE api_log SET response_status = 'S', response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };

        } else {
            logger_all.info("LOG 3");
            // Filter to get channel IDs where results are '0'
            const sipidsWithzero = channel_ids.filter((sipid, index) => results[index] === 0);
            // Format the filtered IDs with quotes
            const sipidsWithZerosFormatted = sipidsWithzero.map(id => `'${id}'`).join(',');
            // Construct the SQL query using the correct variable 'sipidsWithZerosFormatted'
            await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'P' WHERE sip_status IN ('Y','T') AND sip_id IN (${sipidsWithZerosFormatted})`);
            await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'P' WHERE compose_message_id = '${campaign_id}'`);
            // Send success response
            await db.query(`UPDATE api_log SET response_status = '${response_status}', response_date = CURRENT_TIMESTAMP, response_comments = 'Success.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 1, response_status: 200, response_msg: response_comments, request_id: req.body.request_id };
        }

        logger_all.info(JSON.stringify(send_response));
        return send_response;


    } catch (err) {
        logger_all.info("[Stop Campaign Process] Failed - " + err);
        send_response = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
        return send_response;
    }
}
module.exports = {
    StopCampaignProcess
};
//End Function - StopCampaignProcess
