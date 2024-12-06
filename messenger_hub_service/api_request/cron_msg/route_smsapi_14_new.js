/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be used to list user data for the dashboard page.

Version : 1.0
Author : Sabena Yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const main = require('../../logger'); //Include logger files
const db = require("../../db_connect/connect"); //Conncet the database
const axios = require('axios'); // Using Request send for another api.
const { DB_NAME, Message_url } = process.env; // env file varibale using
const logger_all = main.logger_all; //logger file declare.

// cron_send_msg - function starting
async function cron_send_msg() {
    try {
        // Get User ids for active and obd sms user.
        const get_userids_result = await db.query(`SELECT user_id FROM user_management WHERE user_master_id = '4' AND user_status = 'Y'`);
        // if userid length is zero execute the condition 
        if (get_userids_result.length === 0) {
            logger_all.info("[SMS OBD Users Not Available]");
            return;
        }

        // Get Compose tables detailes on processing campaign and sms_status is active.Union query join on multiple databases.
        const compose_tables = get_userids_result.map(user =>
            `SELECT compose_message_id, message, sms_duration FROM ${DB_NAME}_${user.user_id}.compose_message_${user.user_id} WHERE cm_status = 'P' AND sms_status = 'Y'`
        ).join(' UNION ');

        // exeute the query in compose tables
        const compose_tables_values = await db.query(compose_tables);
        // if Compose tables detailes length is zero execute the condition 
        if (compose_tables_values.length === 0) {
            logger_all.info("[No Processing Campaigns]");
            return;
        }
        // Using loop condition get the compose table all values 
        for (const compose of compose_tables_values) {
            // Get Sip servers table names 
            const get_tablenames = await db.query(`SELECT table_name FROM sip_servers`);
            // Using loop condition get all sip table names
            for (const table of get_tablenames) {
                const { table_name } = table;
                // Get the billsec greater than equal and cdr status is active and sms_status is 'N' in sip server tables
                const select_cdr_1_result = await db.query(`SELECT dst, id FROM ${table_name} WHERE campaign_id = '${compose.compose_message_id}' AND sms_status = 'N' AND cdrs_status = 'Y' AND billsec >= ${compose.sms_duration}`);
                // get all dst and id using map all values
                const receiver_nos = select_cdr_1_result.map(record => record.dst);
                const receiver_ids = select_cdr_1_result.map(record => record.id);
                // if Compose tables detailes length is not equal zero execute the condition 
                if (receiver_ids.length != 0) {
                    // update the sip tables on sms_status is 'W' this particular campaigns.
                    await db.query(`UPDATE ${table_name} SET sms_status = 'W' WHERE id IN (${receiver_ids}) AND campaign_id = '${compose.compose_message_id}' AND sms_status = 'N'`);

                    // Split receiver_nos into Batch of 15
                    const chunkSize = 15;
                    const receiverChunks = [];
                    const receiverChunksId = [];
                    // This loop is using on 15 receiver nos,and ids
                    for (let i = 0; i < receiver_nos.length; i += chunkSize) {
                        receiverChunks.push(receiver_nos.slice(i, i + chunkSize));
                        receiverChunksId.push(receiver_ids.slice(i, i + chunkSize));
                    }

                    // This loop is uing Process each chunk send the message and get the reponse 
                    for (let i = 0; i < receiverChunks.length; i++) {
                        const chunk = receiverChunks[i];
                        const idsChunk = receiverChunksId[i];
                        // SMS send Url
                        const url = `${Message_url}&number=${chunk.join(',')}&message=${encodeURIComponent(compose.message)}`;
                        const config = {
                            method: 'post',
                            url: url,
                            headers: {
                                'Cookie': 'PHPSESSID=qnp4liifilloh0c75lt2v194f0'
                            }
                        };
                        logger_all.info("Message send request" + JSON.stringify(config));

                        try {
                            // Send the request for send message
                            const response = await axios(config);
                            // Get the response for send message
                            logger_all.info(JSON.stringify(response.data));
                              // Get the response for send message in status code
                            const { status_code } = response.data;
                            // status_code = 200;
                            // if msg_status is 200 send the 'S' status otherwise 'F' in update the sip tables.
                            const msg_status = status_code === 200 ? 'S' : 'F';
                            await db.query(`UPDATE ${table_name} SET sms_status = '${msg_status}' WHERE id IN (${idsChunk}) AND sms_status = 'W'`);
                        } catch (error) {
                            //Using For error handiling 
                            logger_all.error("Error with URL " + url + ":", error.message);
                        }
                    }
                }
            }
        }
    } catch (error) {
        //Using For error handiling 
        logger_all.error("Error in cron task:", error);
    }
}

module.exports = cron_send_msg;