/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in report functions which is used to get summary and detailed report data from Whatsapp and SMS.
This page also have report generation for Whatsapp, SMS and RCS

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const json2csv = require('json2csv');
const axios = require('axios');
const fs = require('fs');
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
//Load Environment variables
const env = process.env
const DB_NAME = env.DB_NAME;
const fcm_key = env.NOTIFICATION_SERVER_KEY;
const media_storage = env.MEDIA_STORAGE;
var admin = require("firebase-admin");
var serviceAccount = require('../../watsp-app-firebase-adminsdk-nhc22-5a94b7667c.json');

// Start function to create a campaign report
async function campaign_report(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;
    logger_all.info(" [campaign report] - " + req.body);
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));

    const day = new Date();
    const today_date = day.getFullYear() + '' + (day.getMonth() + 1) + '' + day.getDate();
    const today_time = day.getHours() + "" + day.getMinutes() + "" + day.getSeconds();
    const current_date = today_date + '_' + today_time;

    try {
        // get all the req data
        const date_filter = req.body.date_filter;
        const filter_date_1 = date_filter.split("-");

        const report_query = `CALL testing_bluk('${req.body.user_id}', '${req.body.user_product}','${filter_date_1[0]}','${filter_date_1[1]}', '${req.body.campaign_id}')`;
        logger_all.info("[Select query request] : " + report_query);
        const select_campaign = await db.query(report_query);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(select_campaign));

        if (select_campaign[0].length > 10000) {
            function response_status_label(status) {
                switch (status) {
                    case 'Y':
                        return 'Sent';
                    case 'F':
                        return 'Failed';
                    default:
                        return 'Yet to Send';
                }
            }
            function delivery_status_label(status) {
                switch (status) {
                    case 'Y':
                        return 'Delivered';
                    default:
                        return 'Not Delivered';
                }
            }
            function read_status_label(status) {
                switch (status) {
                    case 'Y':
                        return 'Read';
                    default:
                        return 'Not Read';
                }
            }
            // Modify your select_campaign data to include labels
            const modifiedData = select_campaign[0].map(item => ({
                ...item,
                response_status: response_status_label(item.response_status),
                delivery_status: delivery_status_label(item.delivery_status),
                read_status: read_status_label(item.read_status),
            }));
            const fields = [
                'campaign_name',
                'user_name',
                'cm_entry_date',
                'message_type',
                'total_mobile_no_count',
                'receiver_mobile_no',
                'sender_mobile_no',
                'com_msg_content',
                'media_url',
                'response_status',
                'response_date',
                'delivery_status',
                'delivery_date',
                'read_date',
                'read_status',
                'com_cus_msg_media',
            ];

            const opts = { fields };
            const csv = json2csv.parse(modifiedData, opts);
            fs.writeFileSync(`${media_storage}/uploads/detailed_report_csv/detailed_report_${current_date}.csv`, csv);
            logger.info("[SUCCESS API RESPONSE] " + 'CSV file has been saved successfully.');
            logger.info("[SUCCESS API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success ', file_location: `uploads/detailed_report_csv/detailed_report_${current_date}.csv` }));
            //send success response
            return { response_code: 1, response_status: 200, response_msg: 'Success ', num_of_rows: select_campaign[0].length, file_location: `uploads/detailed_report_csv/detailed_report_${current_date}.csv` };

        } else {
            //check if select campaign length is equal to zero, send error response as 'No data available'
            if (select_campaign[0].length === 0) {
             const failed_msg = { response_code: 0,response_status: 204,response_msg: "No Data Available" }
            logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(failed_msg));
            return failed_msg;
            } else {
                      const success_msg = { response_code: 1,response_status: 200,response_msg: 'Success', num_of_rows: select_campaign[0].length,report: select_campaign[0] }
            logger.info("[API SUCCESS RESPONSE - get summary] : " + JSON.stringify(success_msg));
            return success_msg;
            }
        }
    } catch (err) {
        logger_all.info("[campaign list report] Failed - " + err);
        const error_msg = { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
        logger_all.info("[error_msg response] : " + JSON.stringify(error_msg));
        return error_msg;
    }
}

// End function to create a campaign report


// Start function to create a summary report
async function SummaryReport(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;
    try {

        logger_all.info(" [summary report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));

        // get current Date and time
        const day = new Date();
        const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();

        // get all the req filter data
        const date_filter = req.body.date_filter;
        const user_product = req.body.user_product;
        const user_id = req.body.user_id;
        // declare the variables
        let report_query = '';
        let getsummary;
        let filter_date_1;

        // Query parameters
        logger_all.info("[Otp summary report query parameters] : " + JSON.stringify(req.body));

        const filter_condition = ` `;


        // To get the product_id
        const get_product_id = await db.query(`SELECT * FROM rights_master WHERE rights_name = '${user_product}' AND rights_status = 'Y'`);
        logger_all.info("[select query request - get product] : " + get_product_id);
        logger_all.info("[select query response - get product] : " + JSON.stringify(get_product_id));

        if (user_product == 'OBD CALL SIP') {
            console.log("OBD CALL SIP");
            // if the filter_date is empty and store_id_filter is empty to execute this condition
            if (!date_filter) {
                console.log("OBD CALL SIP 1");

                report_query = `SELECT wht.user_id, usr.user_name, ussr.user_type, wht.campaign_name, DATE_FORMAT(wht.summary_report_entdate, '%d-%m-%Y') AS entry_date, wht.total_dialled, wht.total_success, wht.total_failed,  wht.first_attempt, wht.retry_1 , wht.retry_2,wht.total_busy,wht.total_no_answer  FROM ${DB_NAME}.summary_reports_obd wht LEFT JOIN ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id LEFT JOIN ${DB_NAME}.user_master ussr ON usr.user_master_id = ussr.user_master_id WHERE (usr.user_id = '${user_id}' OR usr.parent_id IN (${user_id})) AND (DATE(wht.summary_report_entdate) BETWEEN '${today_date}' AND '${today_date}') GROUP BY campaign_name ORDER BY wht.summary_report_entdate DESC`;
            }

            if (date_filter) {
                // date function for looping in one by one date
                filter_date_1 = date_filter.split("-");
                report_query = ` SELECT wht.user_id, usr.user_name, ussr.user_type, wht.campaign_name, DATE_FORMAT(wht.summary_report_entdate, '%d-%m-%Y') AS entry_date, wht.total_dialled, wht.total_success, wht.total_failed,  wht.first_attempt, wht.retry_1 , wht.retry_2,wht.total_busy,wht.total_no_answer  FROM ${DB_NAME}.summary_reports_obd wht LEFT JOIN ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id LEFT JOIN ${DB_NAME}.user_master ussr ON usr.user_master_id = ussr.user_master_id WHERE (usr.user_id = '${user_id}' OR usr.parent_id IN (${user_id})) AND(DATE(wht.summary_report_entdate) BETWEEN '${filter_date_1[0]}' AND '${filter_date_1[1]}') GROUP BY campaign_name ORDER BY wht.summary_report_entdate DESC`;

            } // filter date
        } else {
            // if the filter_date is empty and store_id_filter is empty to execute this condition
            if (!date_filter) {
                report_query = `SELECT wht.user_id,wht.product_id, usr.user_name, ussr.user_type, wht.campaign_name, DATE_FORMAT(wht.com_entry_date, '%d-%m-%Y') AS entry_date, wht.total_msg, (CASE WHEN wht.report_status = 'N' THEN wht.total_waiting ELSE 0 END) AS total_waiting, (CASE WHEN wht.report_status = 'N' THEN wht.total_process ELSE 0 END) AS total_process, (CASE WHEN wht.report_status = 'N' THEN wht.total_success ELSE 0 END) AS total_success, (CASE WHEN wht.report_status = 'N' THEN wht.total_failed ELSE 0 END) AS total_failed, (CASE WHEN wht.report_status = 'N' THEN wht.total_delivered ELSE 0 END) AS total_delivered, (CASE WHEN wht.report_status = 'N' THEN wht.total_read ELSE 0 END) AS total_read FROM ${DB_NAME}.user_summary_report wht LEFT JOIN ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id LEFT JOIN ${DB_NAME}.user_master ussr ON usr.user_master_id = ussr.user_master_id WHERE (usr.user_id = '${user_id}' OR usr.parent_id IN (${user_id})) AND (DATE(wht.com_entry_date) BETWEEN '${today_date}' AND '${today_date}') AND wht.product_id = '${get_product_id[0].rights_id}' ${filter_condition} GROUP BY campaign_name ORDER BY wht.com_entry_date DESC`;
            }

            if (date_filter) {
                // date function for looping in one by one date
                filter_date_1 = date_filter.split("-");
                report_query = `SELECT wht.user_id, wht.product_id, usr.user_name, ussr.user_type, wht.campaign_name, DATE_FORMAT(wht.com_entry_date, '%d-%m-%Y') AS entry_date, wht.total_msg, (CASE WHEN wht.report_status = 'N' THEN wht.total_waiting ELSE 0 END) AS total_waiting, (CASE WHEN wht.report_status = 'N' THEN wht.total_process ELSE 0 END) AS total_process, (CASE WHEN wht.report_status = 'N' THEN wht.total_success ELSE 0 END) AS total_success, (CASE WHEN wht.report_status = 'N' THEN wht.total_failed ELSE 0 END) AS total_failed, (CASE WHEN wht.report_status = 'N' THEN wht.total_delivered ELSE 0 END) AS total_delivered, (CASE WHEN wht.report_status = 'N' THEN wht.total_read ELSE 0 END) AS total_read FROM ${DB_NAME}.user_summary_report wht LEFT JOIN ${DB_NAME}.user_management usr ON wht.user_id = usr.user_id LEFT JOIN ${DB_NAME}.user_master ussr ON usr.user_master_id = ussr.user_master_id WHERE (usr.user_id = '${user_id}' OR usr.parent_id IN (${user_id})) AND (DATE(wht.com_entry_date) BETWEEN '${filter_date_1[0]}' AND '${filter_date_1[1]}') AND wht.product_id = '${get_product_id[0].rights_id}' ${filter_condition} GROUP BY campaign_name ORDER BY wht.com_entry_date DESC`;

            } // filter date condition
        }

        logger_all.info('[select query request] : ' + report_query);
        getsummary = await db.query(report_query, null, `${DB_NAME}_${user_id}`);
        logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(getsummary));
        // total_response.push(getsummary);

        // getsummary length is '0'. To send the Success message and to send the total_response data.
        logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(getsummary));
 if (getsummary.length === 0) {
            const failed_msg = { response_code: 0,response_status: 204,response_msg: "No Data Available" }
            logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(failed_msg));
            return failed_msg;
        } else { // otherwise to send the success message and get summary details
            const success_msg = { response_code: 1,response_status: 200,response_msg: 'Success',report: getsummary }
            logger.info("[API SUCCESS RESPONSE - get summary] : " + JSON.stringify(success_msg));
            return success_msg;
        }
    } catch (e) { // any error occurs send error response to client
        logger_all.info("[summary report - error] : " + e);
        const error_msg = { response_code: 0, response_status: 201,response_msg: 'Error occurred' }
        logger.info("[Failed response - Error occurred] : " + JSON.stringify(error_msg));
        return error_msg;
    }
}
// End function to create a summary report


// Start function to generate a whatsapp report
async function report_generation(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;

    // Get request data
    const receiver_number = req.body.receiver_number;
    const compose_id = req.body.compose_whatsapp_id;
    const user_id = req.body.selected_user_id;
    const request_id = req.body.request_id;
    const sender_numbers_active = [];
    const sender_numbers_inactive = [];

    try {
        // Query to select sender mobile number
        let report_query = `SELECT DISTINCT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y'`;

        // Check if receiver number available
        if (receiver_number) {
            report_query = `SELECT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y' AND receiver_mobile_no = '${receiver_number}'`;
            compose_id = compose_id + "-" + receiver_number;
        }
        logger_all.info("[Select query request - sender number] : " + report_query);
        const select_campaign = await db.query(report_query);
        logger_all.info("[Select query response - sender number] : " + JSON.stringify(select_campaign));

        // Check if select campaign is equal to zero, send failure response as 'No data available'
        if (select_campaign.length === 0) {
            // Update api_log
            logger_all.info("[update query request - No data available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response -No data available] : " + JSON.stringify(update_api_log));
            return {
                response_code: 0,
                response_status: 204,
                response_msg: 'No data available.',
                request_id: req.body.request_id
            };
        }

        // Otherwise continue notification process

        // Loop to get sender number based on compose id
        for (let i = 0; i < select_campaign.length; i++) {
            const sender_mobile_no_sms = select_campaign[i].sender_mobile_no;
            logger_all.info("sender_mobile_no_sms " + sender_mobile_no_sms);

            // Query to get active sender ID              
            const senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_mobile_no_sms}' AND sender_id_status = 'Y' AND is_qr_code ='N'`;

            logger_all.info("[Select query request - Active sender ID] : " + senderID_active);
            const select_sender_id_active = await db.query(senderID_active);
            logger_all.info("[Select query response - Active sender ID] : " + JSON.stringify(select_sender_id_active));

            // Check if select_sender_id_active length is not equal to zero, store active numbers to sender_numbers_active array 
            if (select_sender_id_active.length !== 0) {
                sender_numbers_active.push(select_sender_id_active[0].mobile_no);
            } else {
                // Otherwise store inactive numbers to sender_numbers_inactive array
                sender_numbers_inactive.push(select_campaign[i].sender_mobile_no);
            }
        }

        // Check if sender_numbers_active length is equal to zero, send error response as 'No Sender ID available'
        if (sender_numbers_active.length === 0) {
            // Update api_log
            logger_all.info("[update query request - No Sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response - No Sender ID available] : " + JSON.stringify(update_api_log));
            logger.info("[Failure Response] " + JSON.stringify({
                response_code: 0,
                response_status: 201,
                response_msg: 'No Sender ID available',
                request_id: req.body.request_id
            }));
            return {
                response_code: 0,
                response_status: 201,
                response_msg: 'No Sender ID available',
                request_id: req.body.request_id
            };
        }

// Check if Firebase is already initialized
if (!admin.apps.length) {
    admin.initializeApp({
        credential: admin.credential.cert(serviceAccount),
    });
}

        // Loop for active sender numbers
        for (let i = 0; i < sender_numbers_active.length; i++) {
            logger_all.info("sender_numbers_active" + sender_numbers_active[i]);

            // Query to get device token to send push notification
            const device_query = `SELECT device_token FROM sender_id_master WHERE mobile_no='${sender_numbers_active[i]}' AND sender_id_status='Y'`;
            logger_all.info("[Select query request - get device token] : " + device_query);
            const device_query_result = await db.query(device_query);
            logger_all.info("[Select query response - get device token] : " + JSON.stringify(device_query_result));
            logger_all.info("device token" + device_query_result[0].device_token);

            // Send push notification
            /*const data = JSON.stringify({
                "to": device_query_result[0].device_token,
                "priority": "high",
                "data": {
                    "title": compose_id,
                    "selected_user_id": user_id,
                    "priority": "high",
                    "content-available": true,
                    "bodyText": "WTSP_Report"
                }
            });

            const config = {
                method: 'post',
                url: 'https://fcm.googleapis.com/fcm/send',
                headers: {
                    'Authorization': fcm_key,
                    'Content-Type': 'application/json'
                },
                data: data
            };

            logger_all.info(JSON.stringify(config));
            await axios(config)
                .then(function (response) {
                    logger_all.info(JSON.stringify(response.data));
                })
                .catch(function (error) {
                    logger_all.info(error);
                });*/

//for (let i = 0; i < sender_devicetoken_active.length; i++) {
    const message = {
        data: {
            "selected_user_id": user_id,
            "title": compose_id,
            "bodyText": "WTSP_Report"
        },
        token: device_query_result[0].device_token
    };

    logger_all.info(JSON.stringify(message));

    admin.messaging().send(message)
        .then((response) => {
            logger_all.info('Notification sent:', response);
        })
        .catch((error) => {
            logger_all.info('Error sending notification:', error);
        });
//}


        }

        for (let i = 0; i < sender_numbers_active.length; i++) {
            const update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no='${sender_numbers_active[i]}'`;

            logger_all.info("[update query request] : " + update_sender_sts);
            const update_sender_sts_result = await db.query(update_sender_sts);
            logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
        }

        // Update api_log
        logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log));

        return {
            response_code: 1,
            response_status: 200,
            response_msg: 'Success',
            request_id: req.body.request_id,
            Inactive_senderID: sender_numbers_inactive
        };

    } catch (err) {
        // Otherwise send failure response 'Error occurred'
        logger_all.info("[sms report generation] Failed - " + err);
        // Update api_log
        const error_msg = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
        logger_all.info("[update query request - Error occurred] : " + error_msg);
        const update_api_log = await db.query(error_msg);
        logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log));

        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error Occurred.',
            request_id: req.body.request_id
        };
    }
}

// End function to generate a whatsapp report


// Start function to generate a sms report
async function sms_report_generation(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;

    // Get request data
    const receiver_number = req.body.receiver_number;
    let compose_id = req.body.compose_message_id;
    const user_id = req.body.selected_user_id;
    const request_id = req.body.request_id;
    const sender_numbers_active = [];
    const sender_numbers_inactive = [];

    try {
        // Query to select sender mobile number
        let report_query = `SELECT DISTINCT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y'`;

        // Check if receiver number available
        if (receiver_number) {
            report_query = `SELECT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y' AND receiver_mobile_no = '${receiver_number}'`;
            compose_id = compose_id + "-" + receiver_number;
        }
        logger_all.info("[Select query request - sender number] : " + report_query);
        const select_campaign = await db.query(report_query);
        logger_all.info("[Select query response - sender number] : " + JSON.stringify(select_campaign));

        // Check if select campaign is equal to zero, send failure response as 'No data available'
        if (select_campaign.length === 0) {
            // Update api_log
            logger_all.info("[update query request - No data available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response -No data available] : " + JSON.stringify(update_api_log));
            return {
                response_code: 0,
                response_status: 204,
                response_msg: 'No data available.',
                request_id: req.body.request_id
            };
        }

        // Otherwise continue notification process

        // Loop to get sender number based on compose id
        for (let i = 0; i < select_campaign.length; i++) {
            const sender_mobile_no_sms = select_campaign[i].sender_mobile_no;
            logger_all.info("sender_mobile_no_sms " + sender_mobile_no_sms);

            // Query to get active sender ID              
            const senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_mobile_no_sms}' AND sender_id_status = 'Y' AND is_qr_code ='N'`;

            logger_all.info("[Select query request - Active sender ID] : " + senderID_active);
            const select_sender_id_active = await db.query(senderID_active);
            logger_all.info("[Select query response - Active sender ID] : " + JSON.stringify(select_sender_id_active));

            // Check if select_sender_id_active length is not equal to zero, store active numbers to sender_numbers_active array 
            if (select_sender_id_active.length !== 0) {
                sender_numbers_active.push(select_sender_id_active[0].mobile_no);
            } else {
                // Otherwise store inactive numbers to sender_numbers_inactive array
                sender_numbers_inactive.push(select_campaign[i].sender_mobile_no);
            }
        }

        // Check if sender_numbers_active length is equal to zero, send error response as 'No Sender ID available'
        if (sender_numbers_active.length === 0) {
            // Update api_log
            logger_all.info("[update query request - No Sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response - No Sender ID available] : " + JSON.stringify(update_api_log));
            logger.info("[Failure Response] " + JSON.stringify({
                response_code: 0,
                response_status: 201,
                response_msg: 'No Sender ID available',
                request_id: req.body.request_id
            }));
            return {
                response_code: 0,
                response_status: 201,
                response_msg: 'No Sender ID available',
                request_id: req.body.request_id
            };
        }


// Check if Firebase is already initialized
if (!admin.apps.length) {
    admin.initializeApp({
        credential: admin.credential.cert(serviceAccount),
    });
}


        // Loop for active sender numbers
        for (let i = 0; i < sender_numbers_active.length; i++) {
            logger_all.info("sender_numbers_active" + sender_numbers_active[i]);

            // Query to get device token to send push notification
            const device_query = `SELECT device_token FROM sender_id_master WHERE mobile_no='${sender_numbers_active[i]}' AND sender_id_status='Y'`;
            logger_all.info("[Select query request - get device token] : " + device_query);
            const device_query_result = await db.query(device_query);
            logger_all.info("[Select query response - get device token] : " + JSON.stringify(device_query_result));
            logger_all.info("device token" + device_query_result[0].device_token);

            // Send push notification
            /*const data = JSON.stringify({
                "to": device_query_result[0].device_token,
                "priority": "high",
                "data": {
                    "title": compose_id,
                    "selected_user_id": user_id,
                    "priority": "high",
                    "content-available": true,
                    "bodyText": "SMS_Report"
                }
            });

            const config = {
                method: 'post',
                url: 'https://fcm.googleapis.com/fcm/send',
                headers: {
                    'Authorization': fcm_key,
                    'Content-Type': 'application/json'
                },
                data: data
            };

            logger_all.info(JSON.stringify(config));
            await axios(config)
                .then(function (response) {
                    logger_all.info(JSON.stringify(response.data));
                })
                .catch(function (error) {
                    logger_all.info(error);
                });*/
  const message = {
        data: {
            "selected_user_id": user_id,
            "title": compose_id,
            "bodyText": "SMS_Report"
        },
        token: device_query_result[0].device_token
    };

    logger_all.info(JSON.stringify(message));

    admin.messaging().send(message)
        .then((response) => {
            logger_all.info('Notification sent:', response);
        })
        .catch((error) => {
            logger_all.info('Error sending notification:', error);
        });


        }

        // Update sender status to 'P' for active sender numbers
        for (let i = 0; i < sender_numbers_active.length; i++) {
            const update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no='${sender_numbers_active[i]}'`;
            logger_all.info("[update query request] : " + update_sender_sts);
            const update_sender_sts_result = await db.query(update_sender_sts);
            logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
        }

        // Update api_log
        logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log));

        return {
            response_code: 1,
            response_status: 200,
            response_msg: 'Success',
            request_id: req.body.request_id,
            Inactive_senderID: sender_numbers_inactive
        };

    } catch (err) {
        // Otherwise send failure response 'Error occurred'
        logger_all.info("[sms report generation] Failed - " + err);
        // Update api_log
        // Update api_log
        const error_msg = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
        logger_all.info("[update query request - Error occurred] : " + error_msg);
        const update_api_log = await db.query(error_msg);
        logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log));

        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error Occurred.',
            request_id: req.body.request_id
        };
    }
}
// End function to generate a sms report

// Start function to generate a rcs report
async function rcs_report_generation(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;

    // Get request data
    const receiver_number = req.body.receiver_number;
    let compose_id = req.body.compose_message_id;
    const user_id = req.body.selected_user_id;
    const request_id = req.body.request_id;
    const sender_numbers_active = [];
    const sender_numbers_inactive = [];

    try {
        // Query to select sender mobile number
        let report_query = `SELECT DISTINCT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y'`;

        // Check if receiver number available
        if (receiver_number) {
            report_query = `SELECT sender_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} WHERE compose_message_id=${compose_id} AND com_msg_status='Y' AND receiver_mobile_no = '${receiver_number}'`;
            compose_id = compose_id + "-" + receiver_number;
        }
        logger_all.info("[Select query request - sender number] : " + report_query);
        const select_campaign = await db.query(report_query);
        logger_all.info("[Select query response - sender number] : " + JSON.stringify(select_campaign));

        // Check if select campaign is equal to zero, send failure response as 'No data available'
        if (select_campaign.length === 0) {
            // Update api_log
            logger_all.info("[update query request - No data available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No data available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response -No data available] : " + JSON.stringify(update_api_log));
            return {
                response_code: 0,
                response_status: 204,
                response_msg: 'No data available.',
                request_id: req.body.request_id
            };
        }

        // Otherwise continue notification process

        // Loop to get sender number based on compose id
        for (let i = 0; i < select_campaign.length; i++) {
            const sender_mobile_no_sms = select_campaign[i].sender_mobile_no;
            logger_all.info("sender_mobile_no_sms " + sender_mobile_no_sms);

            // Query to get active sender ID              
            const senderID_active = `SELECT * from sender_id_master WHERE mobile_no = '${sender_mobile_no_sms}' AND sender_id_status = 'Y' AND is_qr_code ='N'`;

            logger_all.info("[Select query request - Active sender ID] : " + senderID_active);
            const select_sender_id_active = await db.query(senderID_active);
            logger_all.info("[Select query response - Active sender ID] : " + JSON.stringify(select_sender_id_active));

            // Check if select_sender_id_active length is not equal to zero, store active numbers to sender_numbers_active array 
            if (select_sender_id_active.length !== 0) {
                sender_numbers_active.push(select_sender_id_active[0].mobile_no);
            } else {
                // Otherwise store inactive numbers to sender_numbers_inactive array
                sender_numbers_inactive.push(select_campaign[i].sender_mobile_no);
            }
        }

        // Check if sender_numbers_active length is equal to zero, send error response as 'No Sender ID available'
        if (sender_numbers_active.length === 0) {
            // Update api_log
            logger_all.info("[update query request - No Sender ID available] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'No Sender ID available.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response - No Sender ID available] : " + JSON.stringify(update_api_log));
            logger.info("[Failure Response] " + JSON.stringify({
                response_code: 0,
                response_status: 201,
                response_msg: 'No Sender ID available',
                request_id: req.body.request_id
            }));
            return {
                response_code: 0,
                response_status: 201,
                response_msg: 'No Sender ID available',
                request_id: req.body.request_id
            };
        }


// Check if Firebase is already initialized
if (!admin.apps.length) {
    admin.initializeApp({
        credential: admin.credential.cert(serviceAccount),
    });
}


        // Loop for active sender numbers
        for (let i = 0; i < sender_numbers_active.length; i++) {
            logger_all.info("sender_numbers_active" + sender_numbers_active[i]);

            // Query to get device token to send push notification
            const device_query = `SELECT device_token FROM sender_id_master WHERE mobile_no='${sender_numbers_active[i]}' AND sender_id_status='Y'`;
            logger_all.info("[Select query request - get device token] : " + device_query);
            const device_query_result = await db.query(device_query);
            logger_all.info("[Select query response - get device token] : " + JSON.stringify(device_query_result));
            logger_all.info("device token" + device_query_result[0].device_token);

  const message = {
        data: {
            "selected_user_id": user_id,
            "title": compose_id,
            "bodyText": "RCS_Report"
        },
	token: device_query_result[0].device_token
    };

    logger_all.info(JSON.stringify(message));

    admin.messaging().send(message)
        .then((response) => {
            logger_all.info('Notification sent:', response);
        })
	.catch((error) => {
            logger_all.info('Error sending notification:', error);
        });


            // Send push notification
             /*const data = JSON.stringify({
                "to": device_query_result[0].device_token,
                "priority": "high",
                "data": {
                    "title": compose_id,
                    "selected_user_id": user_id,
                    "priority": "high",
                    "content-available": true,
                    "bodyText": "RCS_Report"
                }
            });

            const config = {
                method: 'post',
                url: 'https://fcm.googleapis.com/fcm/send',
                headers: {
                    'Authorization': fcm_key,
                    'Content-Type': 'application/json'
                },
                data: data
            };

            logger_all.info(JSON.stringify(config));
            await axios(config)
                .then(function (response) {
                    logger_all.info(JSON.stringify(response.data));
                })
                .catch(function (error) {
                    logger_all.info(error);
                });*/
        }

        // Update sender status to 'P' for active sender numbers
        for (let i = 0; i < sender_numbers_active.length; i++) {
            const update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'P' WHERE mobile_no='${sender_numbers_active[i]}'`;
            logger_all.info("[update query request] : " + update_sender_sts);
            const update_sender_sts_result = await db.query(update_sender_sts);
            logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
        }

        // Update api_log
        logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log));

        return {
            response_code: 1,
            response_status: 200,
            response_msg: 'Success',
            request_id: req.body.request_id,
            Inactive_senderID: sender_numbers_inactive
        };
    } catch (err) {
        // Otherwise send failure response 'Error occurred'
        logger_all.info("[RCS report generation] Failed - " + err);
        // Update api_log
        const error_msg = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`
        logger_all.info("[update query request - Error occurred] : " + error_msg);
        const update_api_log = await db.query(error_msg);
        logger_all.info("[update query response - Error occurred] : " + JSON.stringify(update_api_log));

        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error Occurred.',
            request_id: req.body.request_id
        };
    }
}
// End function to generate a rcs report

module.exports = {
    campaign_report,
    SummaryReport,
    report_generation,
    sms_report_generation,
    rcs_report_generation
};
