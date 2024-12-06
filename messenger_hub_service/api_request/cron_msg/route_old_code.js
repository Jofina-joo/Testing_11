/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to list user data for dashboard page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and libraries
const main = require('../../logger');
const db = require("../../db_connect/connect");

var logger_all = main.logger_all
var logger = main.logger
const axios = require('axios');
const crypto = require('crypto');
const env = process.env
const DB_NAME = env.DB_NAME;
const Message_Url = env.Message_url;
const fetch = require('node-fetch');

// Define the function containing the logic you want to run periodically
async function cron_send_msg() {
    try {
        cron_send_msg_1();
        var user_id = 8;
        var duration = 7;
        var message = 'Dear Customer, 24*7 support and cashless claims service. Best Insurance Quotes now. Click here: https://bit.ly/3yJBU6S Policybazaar Insurance Brokers. T&C.';

        const select_cdr_1 = `SELECT dst,id FROM obd_cdr_1 WHERE user_id = '${user_id}' and sms_status = 'N' and disposition = 'ANSWERED' and billsec >= ${duration}`;
        logger_all.info("[select query select_cdr_1 request] : " + select_cdr_1);
        const select_cdr_1_result = await db.query(select_cdr_1);
        logger_all.info("[select query select_cdr_1_result response] : " + JSON.stringify(select_cdr_1_result));

        const receiver_nos = select_cdr_1_result.map(obd_cdr_1 => obd_cdr_1.dst);
        const receiver_ids = select_cdr_1_result.map(obd_cdr_1 => obd_cdr_1.id);

        console.log(receiver_nos);
        if (receiver_ids.length != 0) {
            const update_cdr_1 = `UPDATE obd_cdr_1 SET sms_status = 'W' WHERE id in (${receiver_ids}) and user_id = '${user_id}' and sms_status = 'N'`;

            logger_all.info("[update query update_cdr_1 request] : " + update_cdr_1);
            await db.query(update_cdr_1);
            //  const message = get_compose_tbl_result[i].message;
            //  const compose_message_id = get_compose_tbl_result[i].compose_message_id;
            send_msg(user_id, receiver_nos, receiver_ids, message);
        }
    } catch (error) {
        logger_all.error("Error in cron task:", error);
    }
}

async function send_msg(user_id, receiver_nos, receiver_ids, message) {
    //cron_send_msg_1();
    logger_all.info("SEND MESSAGE FUNCTION CALLING");
    // Split receiver_nos into chunks of 15
    const chunkSize = 15;
    const receiverChunks = [];
    const receiverChunksId = [];

    for (let i = 0; i < receiver_nos.length; i += chunkSize) {
        receiverChunks.push(receiver_nos.slice(i, i + chunkSize));
        receiverChunksId.push(receiver_ids.slice(i, i + chunkSize));
    }
    // Process each chunk
    for (const chunk of receiverChunks) {
        // Construct URL with URL encoding
        const url = `${Message_Url}&number=${chunk.join(',')}&message=${encodeURIComponent(message)}`;
        const config = {
            method: 'post',
            url: url,
            headers: {
                'Cookie': 'PHPSESSID=qnp4liifilloh0c75lt2v194f0'
            }
        };
        logger_all.info("Message send request" + config);
        try {
            // Send POST request
            const response = await axios(config);
            // Log the response
            logger_all.info(JSON.stringify(response.data));
            // Extract status_code and status_msg from the response
            const { status_code, status_msg } = response.data;
            // Construct and execute the update query based on status_code
            let msg_status = 'S';
            if (status_code !== 200) {
                msg_status = 'F'
            }
            var update_cdr_1 = `UPDATE obd_cdr_1 SET sms_status = '${msg_status}' WHERE id in (${receiverChunksId}) and sms_status = 'W'`;

            // Logging the update query before execution
            logger_all.info("[update query request - obd_cdr_1] : " + update_cdr_1);
            // Execute the update query
            const update_user_credits_res = await db.query(update_cdr_1);
            // Logging the response after query execution
            logger_all.info("[update query response - obd_cdr_1] : " + JSON.stringify(update_user_credits_res));
        } catch (error) {
            // Handle any errors
            console.error(`Error with URL ${url}:`, error.message);
            logger_all.error("Error occurred:", error);
        }
    }
}
//cron_send_msg_1();
async function cron_send_msg_1() {
    try {
        var user_id = 8;
        var duration = 7;
        var message = 'Dear Customer, 24*7 support and cashless claims service. Best Insurance Quotes now. Click here: https://bit.ly/3yJBU6S Policybazaar Insurance Brokers. T&C.';

        const select_cdr_1 = `SELECT dst,id FROM obd_cdr_2 WHERE user_id = '${user_id}' and sms_status = 'N' and disposition = 'ANSWERED' and billsec >= ${duration}`;
        logger_all.info("[select query select_cdr_1 request] : " + select_cdr_1);
        const select_cdr_1_result = await db.query(select_cdr_1);
        logger_all.info("[select query select_cdr_1_result response] : " + JSON.stringify(select_cdr_1_result));

        const receiver_nos = select_cdr_1_result.map(obd_cdr_2 => obd_cdr_2.dst);
        const receiver_ids = select_cdr_1_result.map(obd_cdr_2 => obd_cdr_2.id);

        console.log(receiver_nos);
        if (receiver_ids.length != 0) {
            const update_cdr_1 = `UPDATE obd_cdr_2 SET sms_status = 'W' WHERE id in (${receiver_ids}) and user_id = '${user_id}' and sms_status = 'N'`;

            logger_all.info("[update query update_cdr_1 request] : " + update_cdr_1);
            await db.query(update_cdr_1);
            //  const message = get_compose_tbl_result[i].message;
            //  const compose_message_id = get_compose_tbl_result[i].compose_message_id;
            send_msg_1(user_id, receiver_nos, receiver_ids, message);
        }
    } catch (error) {
        logger_all.error("Error in cron task:", error);
    }
}

async function send_msg_1(user_id, receiver_nos, receiver_ids, message) {
    logger_all.info("SEND MESSAGE FUNCTION CALLING");
    // Split receiver_nos into chunks of 15
    const chunkSize = 15;
    const receiverChunks = [];
    const receiverChunksId = [];

    for (let i = 0; i < receiver_nos.length; i += chunkSize) {
        receiverChunks.push(receiver_nos.slice(i, i + chunkSize));
        receiverChunksId.push(receiver_ids.slice(i, i + chunkSize));
    }
    // Process each chunk
    for (const chunk of receiverChunks) {
        // Construct URL with URL encoding
        const url = `${Message_Url}&number=${chunk.join(',')}&message=${encodeURIComponent(message)}`;
        const config = {
            method: 'post',
            url: url,
            headers: {
                'Cookie': 'PHPSESSID=qnp4liifilloh0c75lt2v194f0'
            }
        };
        logger_all.info("Message send request" + config);
        try {
            // Send POST request
            const response = await axios(config);
            // Log the response
            logger_all.info(JSON.stringify(response.data));
            // Extract status_code and status_msg from the response
            const { status_code, status_msg } = response.data;
            // Construct and execute the update query based on status_code
            let msg_status = 'S';
            if (status_code !== 200) {
                msg_status = 'F'
            }
            var update_cdr_1 = `UPDATE obd_cdr_2 SET sms_status = '${msg_status}' WHERE id in (${receiverChunksId}) and sms_status = 'W'`;

            // Logging the update query before execution
            logger_all.info("[update query request - obd_cdr_2] : " + update_cdr_1);
            // Execute the update query
            const update_user_credits_res = await db.query(update_cdr_1);
            // Logging the response after query execution
            logger_all.info("[update query response - obd_cdr_2] : " + JSON.stringify(update_user_credits_res));
        } catch (error) {
            // Handle any errors
            console.error(`Error with URL ${url}:`, error.message);
            logger_all.error("Error occurred:", error);
        }
    }
}

module.exports = cron_send_msg;