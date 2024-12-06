const https = require('https');  // For creating an HTTPS server
const Logger = require('./../logger');
const { connectToDatabase } = require('./../db_connect/db');  // Import a function to connect to the database
const generate_call = require('./generate_call');
const axios = require('axios');
require('dotenv').config();

const env = process.env;
const DB_NAME = env.DB_NAME;
const SIP_ID = env.SIP_ID;
const Report_generation = env.Report_generation;
const SIP_TABLE = env.SIP_TABLE;

// Utility functions
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
const deductCredits = promptSecond => Math.ceil(promptSecond / 30);

async function Restart_Calls(req, res, next) {
	Logger.info("Restart API started");

	try {

		const { campaignId, user_id, message_type, retry_count_value, retry_in_millisec, context_id } = req.body;

		const db = await connectToDatabase();

		const get_campaign = `SELECT total_mobile_no_count FROM ${DB_NAME}_${user_id}.compose_message_${user_id} WHERE user_id = ${user_id} AND compose_message_id = ${campaignId} and cm_status = 'S'`;
		const [get_campaign_result] = await db.query(get_campaign);

		if (get_campaign_result.length === 0) {
			console.log("caled.......")
			const api_log = `UPDATE api_log SET response_status = 'F', response_date = CURRENT_TIMESTAMP, response_comments = 'Campaign ID is not processing.' WHERE response_status = 'N'`;
			var test = await db.query(api_log);
			const send_response = { response_code: 0, response_status: 201, response_msg: 'Campaign ID is not available.' };
			console.log(send_response)
			return send_response;
		}

		const total_mobile_no_count = get_campaign_result[0].total_mobile_no_count;
                 const cmpretry_count = get_campaign_result[0].retry_count;
		const get_context = `SELECT audio_duration FROM ${DB_NAME}.obd_prompt_masters WHERE prompt_id = ? AND prompt_status = 'Y'`;
		const [get_context_result] = await db.query(get_context, [context_id]);

		if (get_context_result.length === 0) {
			throw new Error("Context ID not found or inactive.");
		}

		let promptSecond = get_context_result[0].audio_duration;
		let creditsToDeduct = deductCredits(promptSecond);

		if (!res.headersSent) {
			res.status(200).json({ response_code: 1, response_status: 200, response_msg: 'Restart call initiated' });
		}

		for (let retry_count_index = 0; retry_count_index <= retry_count_value; retry_count_index++) {
			Logger.info("Retry count loop");

			const status = retry_count_index === 0 ? "='I'" : "NOT IN ('Y', 'B')";
			if (retry_count_index !== 0) {
				await move_cdrtocdrs_table(campaignId, user_id, db);
			}

			const return_status = await generate_call(campaignId, user_id, status, retry_count_index, message_type, promptSecond);

			if (return_status === 0) {
				break;
			}

			//await sleep(retry_in_millisec);
			await (retry_count_index == retry_count_value ? sleep(1000) : sleep(retry_in_millisec));
		}

		await sleep(promptSecond * 8000); // Delay before running queries
		await runQueries(total_mobile_no_count, creditsToDeduct, campaignId, user_id, db,cmpretry_count);
	} catch (error) {
		console.log("error..")
		Logger.error('Error in Restart_Calls:', error);
		if (!res.headersSent) {
			return next(error); // Call the error handler middleware if headers haven't been sent
		}
	}
}


// The other functions remain unchanged...

async function runQueries(total_mobile_no_count, creditsToDeduct, campaignId, user_id, db,TotalRetryCount) {
	try {

                  const get_failed_count = `SELECT COUNT(*) AS failurecount FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} WHERE (disposition != 'ANSWERED' or disposition is NULL) AND channel_id = ${SIP_ID} AND campaignId = ${campaignId} AND credit = 'N'`;
                        Logger.info(get_failed_count);
                        const [[{ failurecount }]] = await db.query(get_failed_count, [SIP_ID, campaignId]);
                         Logger.info(failurecount +"failurecount + failurecount")
                        const total_count = creditsToDeduct * failurecount;
                        const update_credit = `UPDATE user_credits SET used_credits = used_credits + ?, available_credits = available_credits + ? WHERE user_id = ? AND rights_id = '4'`;
                        Logger.info(update_credit);
                        await db.query(update_credit, [total_count, total_count, user_id]);


		await move_cdrtocdrs_table(campaignId, user_id, db);


		const get_total = `SELECT COUNT(*) AS total_update_count FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} WHERE disposition IS NOT NULL AND channel IS NOT NULL AND campaignId = ?`;
		Logger.info(get_total);
		const [[{ total_update_count }]] = await db.query(get_total, [campaignId]);

		const update_sips = `UPDATE ${DB_NAME}.sip_servers SET sip_status = 'Y' WHERE sip_status = 'P' AND sip_id = ?`;
		Logger.info(update_sips);
		await db.query(update_sips, [SIP_ID]);

		Logger.info(`${total_update_count} total_update_count`);
		Logger.info(`${total_mobile_no_count} total_mobile_no_count`);

              	const max_retrycount = `SELECT max(retry_count) as max_count FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} WHERE channel_id = ${SIP_ID} and campaignId = '${campaignId}'`;
		const [[{ max_count }]] = await db.query(max_retrycount);
		if ((total_update_count === total_mobile_no_count) || (TotalRetryCount != max_count)) {

			const update_campaign = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'Y', call_end_date = NOW() WHERE compose_message_id = ?`;
			Logger.info(update_campaign);
			const [{ affectedRows }] = await db.query(update_campaign, [campaignId]);

			Logger.info("Call status updated to 'Y' in calls table");
			return affectedRows > 0 ? 1 : 0;
		}


		return 1;
	} catch (error) {
		Logger.error("Error in runQueries:", error);
		return 0;
	}
}

async function move_cdrtocdrs_table(campaignId, user_id, db) {
	Logger.info("Entering move_cdrtocdrs_table function");
	const tableName = env.SIP_TABLE;
	const cdrTable = `${DB_NAME}_${user_id}.obd_cdrs_${user_id}`;
	const batchSize = 1000;
	let startRow = 0;
	let SIP_ID = env.SIP_ID;

	try {
		const [rows] = await db.query(`SELECT COUNT(*) AS total_rows FROM ${cdrTable} WHERE campaignId = ? AND channel_id = ?`, [campaignId, SIP_ID]);
		const totalRows = rows[0].total_rows;

		while (startRow < totalRows) {
			const [updateResult] = await db.query(`UPDATE ${cdrTable} AS cdr JOIN (SELECT * FROM ${tableName} WHERE campaign_id = ? ORDER BY id LIMIT ?, ?) AS cdrs ON cdr.dst = cdrs.dst SET cdr.disposition = cdrs.disposition, cdr.clid = cdrs.clid, cdr.channel = cdrs.channel, cdr.calldate = cdrs.calldate, cdr.answerdate = cdrs.answerdate, cdr.hangupdate = cdrs.hangupdate, cdr.billsec = cdrs.billsec, cdr.amaflags = cdrs.amaflags, cdr.sequence = cdrs.sequence, cdr.uniqueid = cdrs.uniqueid, cdr.cdrs_status = cdrs.cdrs_status, cdr.last_call_time = cdrs.calldate, cdr.sms_status = cdrs.sms_status,cdr.credit = 'U' WHERE cdr.accountcode = cdrs.accountcode AND cdr.campaignId = ?`, [campaignId, startRow, batchSize, campaignId]);
			Logger.info(`Updated ${updateResult.affectedRows} rows`);

			startRow += batchSize;
		}
	} catch (error) {
		Logger.error('Error moving CDR to CDRS table:', error);
	}
}

// Export the Restart_Calls function
module.exports = {
	Restart_Calls
}
