// Import required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const axios = require('axios');

const DB_NAME = process.env.DB_NAME;
const deductCredits = promptSecond => Math.ceil(promptSecond / 30);

// Start Function - RestartCampaignProcess
async function RestartCampaignProcess(req) {
    const { logger_all, logger } = main;
    const { campaign_id, selected_user_id, sip_id, user_master_id, request_id } = req.body;
    let send_response = {};
    logger_all.info(`[Restart Process] - ${JSON.stringify(req.body)}`);
    logger.info(`[API REQUEST] ${req.originalUrl} - ${JSON.stringify(req.body)} - ${JSON.stringify(req.headers)}`);

    try {

        const formattedSipId = sip_id.map(id => `'${id}'`).join(", ");

        // Check if campaign is stopped
        const [campaign] = await db.query(`SELECT * FROM ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} WHERE compose_message_id = ? AND cm_status = 'S'`, [campaign_id]);

        if (!campaign) {
            await db.query(`UPDATE api_log SET response_status = 'F', response_date = CURRENT_TIMESTAMP, response_comments = 'Campaign not available.' WHERE request_id = ? AND response_status = 'N'`, [request_id]);
            return { response_code: 0, response_status: 201, response_msg: 'Campaign not available.' };
        }

        // Get the required campaign details
        const { sender_mobile_nos, campaign_type, context_id, retry_time_interval: retry_time, retry_count } = campaign;

        const get_total = await db.query(`SELECT COUNT(*) AS Total_count FROM ${DB_NAME}_${selected_user_id}.obd_cdrs_${selected_user_id} WHERE disposition is NULL AND campaignId = ${campaign_id}`);
        const Total_counts = get_total[0].Total_count;

        const context = await db.query(`SELECT audio_duration FROM ${DB_NAME}.obd_prompt_masters WHERE prompt_id = ${context_id} AND prompt_status = 'Y'`);

        let promptSecond = context[0].audio_duration || 0;
        let creditsToDeduct = deductCredits(promptSecond);
        const total_count = creditsToDeduct * Total_counts;
        if(user_master_id != '1'){
        await db.query(`UPDATE user_credits SET used_credits = used_credits - ${total_count}, available_credits = available_credits - ${total_count} WHERE user_id = ${selected_user_id} AND rights_id = '4'`);
        }
        const retry_in_millisec = retry_time * 1000;

        // Fetch active SIP servers
        const sipServers = await db.query(`SELECT * FROM sip_servers WHERE sip_id IN (${formattedSipId}) AND sip_status IN ('Y','T')`);

        if (sipServers.length === 0) return { response_code: 0, response_status: 204, response_msg: 'No active sip servers.' };

        const server_urls = sipServers.map(server => server.server_url);
        const server_names = sipServers.map(server => server.server_name);

        // Update SIP server status to 'P'
        await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'P' WHERE sip_id IN (${formattedSipId}) AND sip_status IN ('Y','T')`);

        const payload = {
            campaignId: String(campaign_id),
            user_id: String(selected_user_id),
            message_type: String(campaign_type),
            retry_count_value: String(retry_count),
            retry_in_millisec: String(retry_in_millisec),
            context_id: String(context_id)
        };

        logger_all.info(`Sending requests to SIP servers with payload: ${JSON.stringify(payload)}`);

        const results = await Promise.all(
            server_urls.map(url => axios.post(`${url}/restart_call`, payload)
                .then(response => {
                    logger_all.info(`Response from ${url}/restart_call: ${JSON.stringify(response.data)}`);
                    return 1;
                })
                .catch(error => {
                    logger_all.error(`Error with URL ${url}/restart_call: ${error.message}`);
                    return 0;
                })
            )
        );
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
            await db.query(`UPDATE api_log SET response_status = 'F', response_date = CURRENT_TIMESTAMP, response_comments = 'Failed to generate the call.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'Y' WHERE sip_status IN ('P') AND sip_id IN (${sender_mobile_nos})`);
            await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'W', call_start_date = NULL, sender_mobile_nos = '${channel_ids}' WHERE cm_status = 'P' AND compose_message_id = '${campaign_id}'`);
            send_response = { response_code: 0, response_status: 201, response_msg: response_comments };

        } else if (success_case.length == server_names.length) {
              await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'P' WHERE compose_message_id = '${campaign_id}'`);
            logger_all.info("LOG 2");
            // Send success response
            await db.query(`UPDATE api_log SET response_status = 'S', response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 1, response_status: 200, response_msg: 'Call initiated.', request_id: req.body.request_id };

        } else {
            logger_all.info("LOG 3");
            // Filter to get channel IDs where results are '0'
            const sipidsWithzero = channel_ids.filter((sipid, index) => results[index] === 0);
            // Format the filtered IDs with quotes
            const sipidsWithZerosFormatted = sipidsWithzero.map(id => `'${id}'`).join(',');
            // Construct the SQL query using the correct variable 'sipidsWithZerosFormatted'
            await db.query(`UPDATE ${DB_NAME}.sip_servers SET sip_status = 'Y' WHERE sip_status IN ('P') AND sip_id IN (${sipidsWithZerosFormatted})`);
            await db.query(`UPDATE ${DB_NAME}_${selected_user_id}.compose_message_${selected_user_id} SET cm_status = 'S' WHERE cm_status = 'P' AND compose_message_id = '${campaign_id}'`);
            // Send success response
            await db.query(`UPDATE api_log SET response_status = '${response_status}', response_date = CURRENT_TIMESTAMP, response_comments = 'Success.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            send_response = { response_code: 1, response_status: 200, response_msg: response_comments, request_id: req.body.request_id };
        }

        logger_all.info(JSON.stringify(send_response));
        return send_response;
    } catch (error) {
        logger_all.error(`[Restart Campaign Process] Failed - ${error}`);
        send_response = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }
        return send_response;
    }
}

// Export the restart campaign process
module.exports = { RestartCampaignProcess };
