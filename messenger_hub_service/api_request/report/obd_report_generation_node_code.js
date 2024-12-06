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
const DB_NAME = env.DB_NAME;
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
        const user_id = req.body.selected_user_id;
        const campaign_id = req.body.campaign_id;

        // Query parameters
        logger_all.info("[OBD_Report_Generation query parameters] : " + JSON.stringify(req.body));

        // get all the req data
        // report_query = `CALL cdr_generation('${req.body.selected_user_id}','${req.body.campaign_id}')`;
        const report_query = `SELECT cm_entry_date,campaign_name FROM ${DB_NAME}_${user_id}.compose_message_${user_id} WHERE user_id = "${user_id}" AND compose_message_id = "${campaign_id}"`;
        logger_all.info("[Select query request] : " + report_query);
        const getsummary = await db.query(report_query);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(getsummary));
        const campaign_date = getsummary[0].cm_entry_date;
        const campaign_name = getsummary[0].campaign_name;

        const update_query = `UPDATE ${DB_NAME}_${user_id}.obd_cdrs_${user_id} SET report_status = "Y" WHERE campaignId = "${campaign_id}"`;
        logger_all.info("[Select query request] : " + update_query);
        const update_query_result = await db.query(update_query);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(update_query_result));
        // select summary report query
        const select_summary_query = `SELECT 
            DATE_FORMAT(cdr.calldate, "%Y-%m-%d") AS calldatess,
            COUNT(*) AS total_dialled,
            SUM(CASE WHEN cdr.disposition = "answered" THEN 1 ELSE 0 END) AS total_success,
            SUM(CASE WHEN cdr.retry_count = 0 THEN 1 ELSE 0 END) AS first_attempt,
            SUM(CASE WHEN cdr.retry_count = 1 THEN 1 ELSE 0 END) AS retry_1,
            SUM(CASE WHEN cdr.retry_count = 2 THEN 1 ELSE 0 END) AS retry_2,
            SUM(CASE WHEN cdr.disposition = "busy" THEN 1 ELSE 0 END) AS total_busy,
            SUM(CASE WHEN cdr.disposition = "no answer" THEN 1 ELSE 0 END) AS total_no_answer,
            SUM(CASE WHEN cdr.disposition IN ("no answer", "busy", "failed") THEN 1 ELSE 0 END) AS total_failure,
            ROUND((SUM(CASE WHEN cdr.disposition = "answered" THEN 1 ELSE 0 END) * 100.0 / 
                   NULLIF(SUM(CASE WHEN cdr.disposition = "answered" THEN 1 ELSE 0 END) + 
                   SUM(CASE WHEN cdr.disposition IN ("no answer", "busy", "failed") THEN 1 ELSE 0 END), 0)), 2) AS success_percentage
        FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} AS cdr
        WHERE campaignId = '${campaign_id}'
        GROUP BY DATE_FORMAT(cdr.calldate, "%Y-%m-%d")`;
        logger_all.info("[Select query request] : " + select_summary_query);
        const select_summary_query_res = await db.query(select_summary_query);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(select_summary_query_res));
        // get all values on summary report
        const calldatess = select_summary_query_res[0].calldatess;
        const total_dialled = select_summary_query_res[0].total_dialled;
        const total_success = select_summary_query_res[0].total_success;
        const first_attempt = select_summary_query_res[0].first_attempt;
        const retry_1 = select_summary_query_res[0].retry_1;
        const retry_2 = select_summary_query_res[0].retry_2;
        const total_busy = select_summary_query_res[0].total_busy;
        const total_no_answer = select_summary_query_res[0].total_no_answer;
        const total_failure = select_summary_query_res[0].total_failure;
        const success_percentage = select_summary_query_res[0].success_percentage;
        // update summary report query
        const summary_report_update = `
                            UPDATE summary_reports_obd
                            SET 
                                campaign_date = '${calldatess}',
                                total_dialled = '${total_dialled}',
                                total_success = '${total_success}',
                                total_failed = '${total_failure}',
                                total_busy = '${total_busy}',
                                total_no_answer = '${total_no_answer}',
                                first_attempt = '${first_attempt}',
                                retry_1 = '${retry_1}',
                                retry_2 = '${retry_2}',
                                success_percentage = ${success_percentage},
                                summary_report_status = "Y"
                            WHERE campaign_id = '${campaign_id}'`;
        logger_all.info("[Select query request] : " + summary_report_update);
        const summary_report_update_result = await db.query(summary_report_update);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(summary_report_update_result));

        // select select_call_holding query
        const select_call_holding = `
        SELECT 
            DATE_FORMAT(cdr.calldate, "%Y-%m-%d") AS calldate,
            SUM(CASE WHEN cdr.billsec > 0 AND cdr.billsec < 6 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_1_5,
            SUM(CASE WHEN cdr.billsec > 5 AND cdr.billsec < 11 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_6_10,
            SUM(CASE WHEN cdr.billsec > 10 AND cdr.billsec < 16 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_11_15,
            SUM(CASE WHEN cdr.billsec > 15 AND cdr.billsec < 21 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_16_20,
            SUM(CASE WHEN cdr.billsec > 20 AND cdr.billsec < 26 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_21_25,
            SUM(CASE WHEN cdr.billsec > 25 AND cdr.billsec < 31 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_26_30,
            SUM(CASE WHEN cdr.billsec > 30 AND cdr.billsec < 46 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_31_45,
            SUM(CASE WHEN cdr.billsec > 45 AND cdr.billsec < 61 AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_46_60,
            ${DB_NAME}_${user_id}.compose_message_${user_id}.total_mobile_no_count AS total_mobile_no_count,
            SUM(CASE WHEN cdr.cdrs_status = "Y" AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_answered,
            SUM(CASE WHEN cdr.cdrs_status != "Y" AND cdr.report_status = "Y" THEN 1 ELSE 0 END) AS call_not_answered
        FROM ${DB_NAME}_${user_id}.compose_message_${user_id}
        LEFT JOIN ${DB_NAME}_${user_id}.obd_cdrs_${user_id} AS cdr ON ${DB_NAME}_${user_id}.compose_message_${user_id}.compose_message_id = cdr.campaignId
        WHERE ${DB_NAME}_${user_id}.compose_message_${user_id}.compose_message_id = '${campaign_id}'
        GROUP BY DATE_FORMAT(cdr.calldate, "%Y-%m-%d")
        LIMIT 1;`;
        logger_all.info("[Select query request] : " + select_call_holding);
        const select_call_holding_result = await db.query(select_call_holding);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(select_call_holding_result));
        // get all values on call holding reports
        const calldate = select_call_holding_result[0].calldate;
        const call_1_5 = select_call_holding_result[0].call_1_5;
        const call_6_10 = select_call_holding_result[0].call_6_10;
        const call_11_15 = select_call_holding_result[0].call_11_15;
        const call_16_20 = select_call_holding_result[0].call_16_20;
        const call_21_25 = select_call_holding_result[0].call_21_25;
        const call_26_30 = select_call_holding_result[0].call_26_30;
        const call_31_45 = select_call_holding_result[0].call_31_45;
        const call_46_60 = select_call_holding_result[0].call_46_60;
       const total_mobile_no_count = select_call_holding_result[0].total_mobile_no_count;
        const call_answered = select_call_holding_result[0].call_answered;
        const call_not_answered = select_call_holding_result[0].call_not_answered;
        // update call_holding_report query
        const call_holding_report = `UPDATE call_holding_reports_obd 
            SET 
                campaign_date = '${calldate}', 
                1_5_secs = '${call_1_5}', 
                6_10_secs = '${call_6_10}',
                11_15_secs = '${call_11_15}',
                16_20_secs = '${call_16_20}',
                21_25_secs = '${call_21_25}',
                26_30_secs = '${call_26_30}',
                31_45_secs = '${call_31_45}',
                46_60_secs = '${call_46_60}',
                total_calls = '${total_mobile_no_count}',
                call_answered = '${call_answered}',
                call_not_answered = '${call_not_answered}',
                call_holding_reprtstat = "Y"
            WHERE campaign_id = '${campaign_id}'`;
        logger_all.info("[Select query request] : " + call_holding_report);
        const call_holding_report_res = await db.query(call_holding_report);
        logger_all.info("[select query response - det_report_query] : " + JSON.stringify(call_holding_report_res));

        const zip_file_report = ` SELECT "success: CDR Generated Successfully" AS response_msg,cls.campaign_name AS cdr_campaign_name,pro.context AS cdr_context,cdr.* FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} cdr JOIN ${DB_NAME}_${user_id}.compose_message_${user_id} cls ON cls.compose_message_id = cdr.campaignId JOIN ${DB_NAME}.obd_prompt_masters pro ON cls.context_id = pro.prompt_id WHERE cdr.campaignId = "${campaign_id}"`;
        logger_all.info("[Select query request] : " + zip_file_report);
        const zip_file_report_res = await db.query(zip_file_report);
        logger_all.info("[select query response - zip_file_report] : " + JSON.stringify(zip_file_report_res));

        // getsummary length is '0'. To send the Success message and to send the total_response data.
        logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(zip_file_report_res));
        if (zip_file_report_res.length === 0) {
            logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify({
                response_code: 0,
                response_status: 204,
                response_msg: "No Data Available"
            }));
            return {
                response_code: 0,
                response_status: 204,
                response_msg: "No Data Available"
            };
        } else { // otherwise to send the success message and get summary details
            const campaignName = zip_file_report_res[0].cdr_campaign_name;
            console.log(campaignName + " campaignName");
            const csvFileName = `${campaignName}.csv`;
            const zipFileName = `${campaignName}.zip`;

            // Modify your select_campaign data to include labels
            const modifiedData = zip_file_report_res.map((item, index) => ({
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
            }));
            // Header fileds
            const csvHeaders = ['No', 'Campaign Name', 'Receiver Mobile No', 'Sender Mobile No', 'Call Status', 'Retry Count', 'Call Duration (In Secs)', 'Context', 'Call Time', 'Answered Time', 'End Time'];

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
            const scpCommand = `sudo scp ${zipFilePath} root@yourpostman.in:${media_storage}/uploads/obd_call_report_csv/`;
            logger_all.info(`First File moving to server - ${scpCommand}`);

            try {
                var { firststdout, stderr } = await exec_wait(scpCommand);
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
                }

                logger.info("[SUCCESS] SCP command executed successfully.");

                // update zip file name 
                const update_report = `UPDATE obd_cdr_reports SET download_url = '${zipFileName}',report_status = "Y" WHERE campaign_id = '${campaign_id}'`;
                logger_all.info("[Select query request] : " + update_report);
                const update_report_result = await db.query(update_report);
                logger_all.info("[select query response - update_report_result] : " + JSON.stringify(update_report_result));

                // update report status
                const update_report_status = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET campaign_report_status = "Y" WHERE compose_message_id = '${campaign_id}'`;
                logger_all.info("[Select query request] : " + update_report_status);
                const update_report_status_rsult = await db.query(update_report_status);
                logger_all.info("[select query response - update_report_result] : " + JSON.stringify(update_report_status_rsult));

            } catch (err) {
                logger.error("[ERROR] SCP command failed: " + err);
                const failed_msg = { response_code: 0, response_status: 201, response_msg: 'Error occurred', request_id: req.body.request_id }
                logger.info("[API SUCCESS RESPONSE - Total response] : " + JSON.stringify(failed_msg));
                return failed_msg;
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
