/*
This api has chat API functions which is used to connect the mobile chat.
This page is act as a Backend page which is connect with Node JS API and PHP Frontend.
It will collect the form details and send it to API.
After get the response from API, send it back to Frontend.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 05-Jul-2023
*/
// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');

// AddMessageCredit Function - start
async function AddMessageCredit(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {

		// get current Date and time
		var day = new Date();
		var today_time = day.getHours() + ":" + day.getMinutes() + ":" + day.getSeconds();
		var nextyear_dt = (day.getFullYear() + 1) + '-' + (day.getMonth() + 1) + '-' + (day.getDate());
		var next_year_date = nextyear_dt + ' ' + today_time;

		// get all the req data
		var user_id = req.body.user_id;
		var parent_user = req.body.parent_user;
		var product_id = req.body.product_id;
		var receiver_user = req.body.receiver_user;
		var message_count = req.body.message_count;
		var credit_raise_id = req.body.credit_raise_id;
		// declare variable
		logger_all.info(" [AddMessageCredit] - " + req.body);
		logger.info("[API REQUEST - AddMessageCredit] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

		if (typeof parent_user !== 'undefined') {
			exp1 = parent_user.split("~~");
		}

		var exp2 = receiver_user.split("~~");

		/*var get_credit = `SELECT available_credits from user_credits WHERE user_id = '${user_id}' AND uc_status = 'Y' and rights_id= '${product_id}'`;
		logger_all.info("[select query request] : " + get_credit)
		const select_credit = await db.query(get_credit);
		logger_all.info("[select query response] : " + JSON.stringify(select_credit))
		// To check the available_credits
		if (select_credit[0].available_credits < message_count) {
			return {
				response_code: 0,
				response_status: 201,
				response_msg: 'Insufficient credits.'
			};
		}
		logger_all.info("Testing");*/
		// insert the message_credit_log to request data
		var insert_credit = `INSERT INTO message_credit_log VALUES (NULL, '${user_id}', '${exp2[0]}', '${product_id}','${message_count}', '${message_count} Messages allocated to ${exp2[1]} by admin', 'Y', CURRENT_TIMESTAMP)`;
		logger_all.info("[insert query request] : " + insert_credit)
		const insert_template = await db.query(insert_credit);
		logger_all.info("[insert query response] : " + JSON.stringify(insert_template))

		// upadte the message_limit to request data
		var update_credit = `UPDATE user_credits SET available_credits = available_credits + '${message_count}', total_credits = total_credits + '${message_count}', expiry_date = '${next_year_date}' WHERE user_id = ${exp2[0]} and rights_id = '${product_id}'`;
		logger_all.info("[update query request] : " + update_credit)
		const update_succ = await db.query(update_credit);
		logger_all.info("[update query response] : " + JSON.stringify(update_succ))

		if (exp2[0] != 1) {  // upadte the message_limit for expect the primaryadmin using the condition
			/*var update_credits2 = `UPDATE user_credits SET available_credits = available_credits - '${message_count}' WHERE user_id = ${user_id} and rights_id = '${product_id}'`;
			logger_all.info("[update query request] : " + update_credits2)
			const update_succ2 = await db.query(update_credits2);
			logger_all.info("[update query response] : " + JSON.stringify(update_succ2))*/

			logger_all.info("credit_raise_id====" + credit_raise_id);
			if (typeof credit_raise_id !== 'undefined') {
				// Purchase Credit Raised and Approved
				var update_credit_raiseid = `UPDATE user_credit_raise SET usr_credit_status = 'U' WHERE usr_credit_id = ${credit_raise_id} and usr_credit_status = 'A'`;
				logger_all.info("[update query request - user_sms_credit_raise] : " + update_credit_raiseid)
				const update_succ3 = await db.query(update_credit_raiseid);
				logger_all.info("[update query response - user_credit_raise] : " + JSON.stringify(update_succ3))
			}

			// update_succ2 to get the response message through theMessage Credit updated. 
			if (update_succ.affectedRows > 0) {
				return {
					response_code: 1,
					response_status: 200,
					num_of_rows: 1,
					response_msg: 'Message Credit updated.'
				};

			} else {// otherwise send the No data available
				return {
					response_code: 1,
					response_status: 204,
					response_msg: 'No data available'
				};
			}
		}
		if (update_succ.affectedRows > 0) {
			return {
				response_code: 1,
				response_status: 200,
				num_of_rows: 1,
				response_msg: 'Message Credit updated.'
			};

		} else {// otherwise send the No data available
			return {
				response_code: 1,
				response_status: 204,
				response_msg: 'No data available'
			};
		}

	} catch (e) { // any error occurres send error response to client
		logger_all.info("[AddMessageCredit failed response] : " + e)
		return {
			response_code: 0,
			response_status: 201,
			response_msg: 'Error occured'
		};
	}
}
// AddMessageCredit Function - end
// using for module exporting
module.exports = {
	AddMessageCredit
}
