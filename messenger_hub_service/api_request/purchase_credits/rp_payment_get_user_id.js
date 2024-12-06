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
// AvailableCreditsList- start
async function Rppayment_User_id(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {

		logger_all.info(" [Rppayment_User_id] - " + req.body);
		logger.info("[API REQUEST - Rppayment_User_id] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

		// get all the req data
		var user_id = req.body.user_id;
		var max_credit_raise = ``;
		// query parameters
		logger_all.info("[Rppayment_User_id query parameters] : " + JSON.stringify(req.body));
		// get_available_message to execute this query
		max_credit_raise = `SELECT max(usr_credit_id) usr_credit_id, raise_credits, amount FROM user_credit_raise where user_id = '${user_id}' order by usr_credit_id desc`;
		logger_all.info("[select query request] : " + max_credit_raise);
		const get_rppayment_user_id = await db.query(max_credit_raise);
		logger_all.info("[select query response] : " + JSON.stringify(get_rppayment_user_id));

		// if the get_available_message length is not available to send the no available data.otherwise it will be return the get_available_message details.
		if (get_rppayment_user_id.length == 0) {
			return { response_code: 1, response_status: 204, response_msg: 'No data available' };
		}
		else {
			return { response_code: 1, response_status: 200, num_of_rows: get_rppayment_user_id.length, response_msg: 'Success', report: get_rppayment_user_id };
		}

	}
	catch (e) {// any error occurres send error response to client
		logger_all.info("[Rppayment_User_id failed response] : " + e)
		return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
	}
}
// AvailableCreditsList - end

// using for module exporting
module.exports = {
	Rppayment_User_id,
}