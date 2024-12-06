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
async function AvailableCreditsList(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {
		logger_all.info(" [AvailableCreditsList] - " + req.body);
		logger.info("[API REQUEST - AvailableCreditsList] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
		// get all the req data
		var user_id = req.body.user_id;
		var select_user_id = req.body.select_user_id;
		var product_id = req.body.product_id;
		if (select_user_id) { //select userid are coming to execute this condition
			user_id = select_user_id;
		}
		// query parameters
		logger_all.info("[AvailableCreditsList query parameters] : " + JSON.stringify(req.body));
		var tocheck_credit = `SELECT lmt.total_credits, sum(lmt.available_credits) available_credits, lmt.expiry_date FROM user_credits lmt left join user_management usr on lmt.user_id = usr.user_id where lmt.uc_status = 'Y' and (usr.user_id = '${user_id}' )and lmt.rights_id = '${product_id}'`;
		// get_available_credits to execute this query
		logger_all.info("[select query request] : " + tocheck_credit);

		const get_available_credits = await db.query(tocheck_credit);

		logger_all.info("[select query response] : " + JSON.stringify(get_available_credits));

		if (get_available_credits.length == 0) {
			return { response_code: 1, response_status: 204, response_msg: 'No data available' };
		}
		else {
			return { response_code: 1, response_status: 200, num_of_rows: get_available_credits.length, response_msg: 'Success', report: get_available_credits };
		}

	}
	catch (e) {// any error occurres send error response to client
		logger_all.info("[AvailableCreditsList failed response] : " + e)
		return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
	}
}
// AvailableCreditsList - end

// using for module exporting
module.exports = {
	AvailableCreditsList,
}