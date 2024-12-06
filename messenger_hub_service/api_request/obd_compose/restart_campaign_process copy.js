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
const util = require('util');
const exec_wait = util.promisify(require('child_process').exec);

//Start Function - RestartCampaignProcess
async function RestartCampaignProcess(req) {
    const logger_all = main.logger_all
    const logger = main.logger

    try {

        // get call request 
        const compose_message_id = req.body.campaign_id;
        const selected_user_id = req.body.selected_user_id;
        const sip_id = req.body.sip_id;
        const server_urls = [];
         const formattedSipId = sip_id.map(id => `'${id}'`).join(", ");
        const get_sip_servers = `SELECT * FROM sip_servers WHERE sip_id in (${formattedSipId}) and sip_status in ('Y','T')`;
        logger_all.info("[Update query request] : " + get_sip_servers);
        const get_sip_servers_results = await db.query(get_sip_servers);
        logger_all.info("[Update query request] : " + JSON.stringify(get_sip_servers_results));

        if (get_sip_servers_results.length > 0) {
            //server_urls.push(get_sip_servers_results[0].server_url)
              get_sip_servers_results.forEach(result => {
        server_urls.push(result.server_url);
           });
        } else {
            const failed_msg = { response_code: 0, response_status: 204, response_msg: 'No active sip servers.' };
            logger_all.info("Sip server select query Response" + failed_msg)
            return failed_msg;
        }
        //Update channel status as 'P' while Restart_process
        const campaign_id = String(compose_message_id);
        const userid = String(selected_user_id);
        let payload = '';

        if (!compose_message_id || !selected_user_id) {
            console.error("Required fields are missing or not defined.");
        } else {
            payload = {
                campaign_id: campaign_id,
                user_id: userid
            };
        }
        // send the request on Asterisk
        logger_all.info("Send Request From Astersik " + payload)

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
            const failed_msg = { response_code: 0, response_status: 201, response_msg: 'Failed' }
            logger_all.info(failed_msg);
            return failed_msg;

        } else {
            //Otherwise send success response
            const success_msg = { response_code: 1, response_status: 200, response_msg: 'Success' }
            logger_all.info(success_msg);
            return success_msg;
        }

    } catch (err) {
        logger_all.info("[Restart Campaign Process] Failed - " + err);
        return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
    }
}
module.exports = {
    RestartCampaignProcess
};
//End Function - RestartCampaignProcess
