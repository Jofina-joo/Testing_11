// Import required modules
const express = require('express');
const router = express.Router();
const Logger = require('../logger');
const { connectToDatabase } = require('./../db_connect/db');
const { exec } = require('child_process');
require('dotenv').config();

const env = process.env;
const DB_NAME = env.DB_NAME;
const SIP_ID = env.SIP_ID;
const Report_generation = env.Report_generation;

const deductCredits = (promptSecond) => Math.ceil(promptSecond / 30);

async function stop_calls(req) {
    const db = await connectToDatabase();
    Logger.info("Stop campaign request received");

    const { campaign_id, user_id, context_id } = req.body;
    Logger.info("API request data: " + JSON.stringify(req.body));

    try {
        // Update on compose Table 
        const updateCallStatus = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'S', call_end_date = NOW() WHERE cm_status = 'P' AND compose_message_id = ?`;
        Logger.info(updateCallStatus);
        const [updateCallStatusResult] = await db.execute(updateCallStatus, [campaign_id]);
        Logger.info("Updated call status to 'S' in compose table successfully");

        // Update channel status as 'T' where it is 'P'
        const update_server_status = `UPDATE ${DB_NAME}.sip_servers SET sip_status = 'Y' WHERE sip_status = 'P' AND sip_id = ?`;
        Logger.info(update_server_status);
        const [update_server_status_response] = await db.execute(update_server_status, [SIP_ID]);
        Logger.info("Updated server status: " + JSON.stringify(update_server_status_response));

        // Get Audio duration
        const get_context = `SELECT audio_duration FROM ${DB_NAME}.obd_prompt_masters WHERE prompt_id = ? AND prompt_status = 'Y'`;
        Logger.info("Executing query: " + get_context);
        const [get_context_result] = await db.execute(get_context, [context_id]);
        Logger.info("Context query result: " + JSON.stringify(get_context_result));

        let promptSecond = 0;
        let creditsToDeduct = 1;

        if (get_context_result.length > 0 && !isNaN(get_context_result[0].audio_duration)) {
            promptSecond = get_context_result[0].audio_duration;
            Logger.info(`Prompt duration: ${promptSecond} seconds`);
            creditsToDeduct = deductCredits(promptSecond);
        } else {
            Logger.error('Invalid or missing prompt duration');
            return { status: 400, message: 'Invalid prompt duration' };
        }

        await stop_move_cdrtocdrs_table(campaign_id, user_id, db, creditsToDeduct);

        Logger.info({
            "response_code": 1,
            "response_status": 200,
            "response_msg": 'Campaign stopped and credits updated successfully',
        });

        return { status: 200, message: 'Operation completed successfully' };

    } catch (error) {
        Logger.error('Error in stop_calls function:', error);
        return { status: 500, message: 'Internal Server Error' };
    }
}

async function stop_move_cdrtocdrs_table(campaignId, user_id, db, creditsToDeduct) {

    Logger.info("Starting move_cdrtocdrs_table function");
    const tableName = env.SIP_TABLE;
    const cdrTable = `${DB_NAME}_${user_id}.obd_cdrs_${user_id}`;
    let SIP_ID = env.SIP_ID;
    const batchSize = 1000;
    let startRow = 0;

    try {
        const get_failure = `SELECT COUNT(*) AS failurecount FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} WHERE (disposition != 'ANSWERED' or disposition is NULL) AND channel_id = ${SIP_ID} AND campaignId = ${campaignId} AND credit = 'N'`;
        const [data] = await db.execute(get_failure);
        const failure_count = data[0].failurecount;
        Logger.info(failure_count + "failurecount + failurecount")
        const total_count = creditsToDeduct * failure_count;

        Logger.info(`Total credits to deduct: ${total_count}`);

        // Update the used credit for failure count
        const creditUpdate = `UPDATE user_credits SET used_credits = used_credits + ?, available_credits = available_credits + ? WHERE user_id = ? and rights_id = '4'`;
        Logger.info("Executing query: " + creditUpdate);
        const [creditUpdateResult] = await db.execute(creditUpdate, [total_count, total_count, user_id]);
        Logger.info("Credit update result: " + JSON.stringify(creditUpdateResult));

        // Step 1: Get total number of rows
        const countQuery = `SELECT COUNT(*) AS total_rows FROM ${cdrTable} WHERE campaignId = ? AND channel_id = ?`;
        const [rows] = await db.execute(countQuery, [campaignId, SIP_ID]);
        const totalRows = rows[0].total_rows;

        while (startRow < totalRows) {
            // Step 2: Create and execute dynamic update query
            const updateQuery = `UPDATE ${cdrTable} AS cdr JOIN (SELECT * FROM ${tableName} where campaign_id = '${campaignId}' ORDER BY id LIMIT ${startRow}, ${batchSize} ) AS cdrs ON cdr.dst = cdrs.dst SET cdr.disposition = cdrs.disposition, cdr.clid = cdrs.clid, cdr.channel = cdrs.channel,cdr.calldate = cdrs.calldate, cdr.answerdate = cdrs.answerdate, cdr.hangupdate = cdrs.hangupdate,cdr.billsec = cdrs.billsec, cdr.amaflags = cdrs.amaflags, cdr.sequence = cdrs.sequence,cdr.uniqueid = cdrs.uniqueid, cdr.cdrs_status = cdrs.cdrs_status,cdr.last_call_time = cdrs.calldate,cdr.credit = 'U' WHERE cdr.accountcode = cdrs.accountcode AND cdr.campaignId = ${campaignId}`;
            Logger.info("Executing query: " + updateQuery);
            const [updateResult] = await db.execute(updateQuery, [campaignId, startRow, batchSize, campaignId]);
            Logger.info(`Updated ${updateResult.affectedRows} rows`);

            // Increment startRow for the next batch
            startRow += batchSize;
        }
    } catch (error) {
        Logger.error('Error moving CDR to CDRS table:', error);
    }
}

module.exports = {
    stop_calls
};