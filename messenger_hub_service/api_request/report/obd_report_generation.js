/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in OBD_Report_Generation functions which is used to OBD_Report_Generation for user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const createCsvWriter = require('csv-writer').createObjectCsvWriter;
const archiver = require('archiver');
const { exec } = require('child_process');
const json2csv = require('json2csv');
const axios = require('axios');
const fs = require('fs');
const env = process.env
const media_storage = env.MEDIA_STORAGE;
const moment = require('moment');
const util = require('util');
const exec_wait = util.promisify(require('child_process').exec);

async function OBD_Report_Generation(req, res) {
    const logger_all = main.logger_all;
    const logger = main.logger;
    try {
        logger_all.info(" [Call_Holding report] - " + req.body);
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));

        // declare the variables
        let report_query = '';

        // Query parameters
        logger_all.info("[OBD_Report_Generation query parameters] : " + JSON.stringify(req.body));

        // get all the req data
        report_query = `CALL cdr_generation('${req.body.selected_user_id}','${req.body.campaign_id}')`;
        logger_all.info("[Select query request] : " + report_query);
        const getsummary = await db.query(report_query);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(getsummary));

        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(getsummary[0]));

        // getsummary length is '0'. To send the Success message and to send the total_response data.
        logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(getsummary));
        if (getsummary[0].length === 0) {
            const data_send = { response_code: 0, response_status: 204, response_msg: "No Data Available" }
            logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(data_send));
            return data_send;
        } else { // otherwise to send the success message and get summary details
            const campaignName = getsummary[0][0].cdr_campaign_name;
            const com_sms_status = getsummary[0][0].com_sms_status;
            console.log(campaignName + " campaignName");
            const csvFileName = `${campaignName}.csv`;
            const zipFileName = `${campaignName}.zip`;

            // Modify your select_campaign data to include labels
            /*const modifiedData = getsummary[0].map((item, index) => ({
                'No': index + 1,
                'Campaign Name': item.cdr_campaign_name,
                'Receiver Mobile No': item.dst,
                'Sender Mobile No': item.src,
                'Call Status': item.disposition,
                'Retry Count': item.retry_count,
                'Call Duration (In Secs)': item.billsec,
                'Context': item.cdr_context,
                'Call Time': moment(item.calldate).format('YYYY-MM-DD HH:mm:ss'), // Format Call Time
                'Answered Time': moment(item.answerdate).format('YYYY-MM-DD HH:mm:ss'), // Format Answered Time
                'End Time': moment(item.hangupdate).format('YYYY-MM-DD HH:mm:ss') // Format End Time
            }));*/

// Determine if 'SMS' should be included based on data
const includeSmsColumn = getsummary[0].some(item => item.sms_status && com_sms_status === 'C');

const csvHeaders = [
    'No',
    'Campaign Name',
    'Receiver Mobile No',
    'Sender Mobile No',
    'Call Status',
    'Retry Count',
    'Call Duration (In Secs)',
    'Context',
    'Call Time',
    'Answered Time',
    'End Time',
    ...(includeSmsColumn ? ['SMS'] : []) // Conditionally add 'SMS' header
];

const modifiedData = getsummary[0].map((item, index) => {
    let smsStatus = '';
    let smsField = {}; // Default to an empty object

    if (com_sms_status === 'C') {
        smsStatus = item.sms_status === 'S' ? 'Sent' : 'Failed';
        smsField = { 'SMS': smsStatus }; // Include SMS status
    }

    return {
        'No': index + 1,
        'Campaign Name': item.cdr_campaign_name,
        'Receiver Mobile No': item.dst,
        'Sender Mobile No': item.src,
        'Call Status': item.disposition,
        'Retry Count': item.retry_count,
        'Call Duration (In Secs)': item.billsec,
        'Context': item.cdr_context,
        'Call Time': moment(item.calldate).format('YYYY-MM-DD HH:mm:ss'), // Format Call Time
        'Answered Time': moment(item.answerdate).format('YYYY-MM-DD HH:mm:ss'), // Format Answered Time
        'End Time': moment(item.hangupdate).format('YYYY-MM-DD HH:mm:ss'), // Format End Time
        ...(includeSmsColumn ? smsField : {}) // Conditionally include SMS field
    };
});

            const opts = { fields: csvHeaders };
            const csv = json2csv.parse(modifiedData, opts);
            const csvFilePath = `${media_storage}/uploads/obd_call_report_csv/${csvFileName}`;
            fs.writeFileSync(csvFilePath, csv);
            logger.info("[SUCCESS API RESPONSE] CSV file has been saved successfully at " + csvFilePath);

            // Create a zip file
            const zipFilePath = `${media_storage}/uploads/obd_call_report_csv/${zipFileName}`;
            const output = fs.createWriteStream(zipFilePath);
            const archive = archiver('zip', {
                zlib: { level: 9 } // Sets the compression level.
            });

            output.on('close', function () {
                logger.info("[SUCCESS API RESPONSE] ZIP file has been saved successfully at " + zipFilePath);
                logger.info("[SUCCESS API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success', file_location: `/uploads/obd_call_report_csv/${zipFileName}` }));
            });

            archive.on('error', function (err) {
                logger.error("[ERROR] ZIP file creation error: " + err.message);
                throw err;
            });

            archive.pipe(output);

            // Append files
            archive.file(csvFilePath, { name: csvFileName });
            archive.finalize();

            // Log the SCP command for debugging
           // const scpCommand = `sudo scp ${zipFilePath} root@yourpostman.in:${media_storage}/uploads/obd_call_report_csv/`;
           //logger_all.info(`First File moving to server - ${scpCommand}`);

            try {
                /*var { firststdout, stderr } = await exec_wait(scpCommand);
                var firststderrLines = stderr.split('\n');

                // Filter out non-error messages
                var firsterrorLines = firststderrLines.filter(line => line.toLowerCase().includes(' error'));
                if (firsterrorLines.length > 0) {
                    logger.error("[API RESPONSE] SCP failed: " + firsterrorLines.join('\n'));
                    return {
                        response_code: 0,
                        response_status: 201,
                        response_msg: 'scp failed',
                        request_id: req.body.request_id
                    };
                }*/

                logger.info("[SUCCESS] SCP command executed successfully.");

                // update zip file name 
                const update_report = `UPDATE obd_cdr_reports SET download_url = '${zipFileName}',report_status = "Y" WHERE campaign_id = '${req.body.campaign_id}' and user_id = '${req.body.selected_user_id}'`;
                logger_all.info("[Select query request] : " + update_report);
                const update_report_result = await db.query(update_report);
                logger_all.info("[select query response - update_report_result] : " + JSON.stringify(update_report_result));

                // update report status
                const update_report_status = `UPDATE messenger_hub_${req.body.selected_user_id}.compose_message_${req.body.selected_user_id} SET campaign_report_status = "Y" WHERE compose_message_id = '${req.body.campaign_id}'`;
                logger_all.info("[Select query request] : " + update_report_status);
                const update_report_status_rsult = await db.query(update_report_status);
                logger_all.info("[select query response - update_report_result] : " + JSON.stringify(update_report_status_rsult));

            } catch (err) {
                logger.error("[ERROR] SCP command failed: " + err.message);
                return {
                    response_code: 0,
                    response_status: 201,
                    response_msg: 'Error occurred',
                    request_id: req.body.request_id
                };
            }

            const succ_msg = { response_code: 1, response_status: 200, response_msg: 'Success' }
            return succ_msg;
        }
    } catch (e) { // any error occurs send error response to client
        logger_all.info("[OBD_Report_Generation - error] : " + e);
        const failed_msg = { response_code: 0, response_status: 201, response_msg: 'Error occurred', request_id: req.body.request_id }
        logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(failed_msg));
        return failed_msg;
    }
}

// using for module exporting
module.exports = {
    OBD_Report_Generation
}
