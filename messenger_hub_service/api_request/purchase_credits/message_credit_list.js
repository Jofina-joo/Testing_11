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
// MessageCreditList Function - start
async function MessageCreditList(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {
		//  Get all the req header data
		var user_id = req.body.user_id;
		var user_master_id = req.body.user_master_id;
		var message_credit = ``;
		logger_all.info(" [MessageCreditList] - " + req.body);
		logger.info("[API REQUEST - MessageCreditList] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
		if (user_master_id == 1) { // primary admin - admin are following this to get the parent id
			// get_message_credit to execute the query
			message_credit = `SELECT crt.message_credit_log_id, prn.user_id parntid,rm.rights_name, prn.user_name parntname,um.user_type, usr.user_id usrid, usr.user_name usrname, crt.provided_message_count, crt.message_comments, crt.message_credit_log_status, crt.message_credit_log_entdate FROM message_credit_log crt left join user_management usr on usr.user_id = crt.user_id LEFT JOIN rights_master rm ON rm.rights_id = crt.rights_id
                left join user_management prn on prn.user_id = crt.parent_id LEFT JOIN user_master um ON um.user_master_id = usr.user_master_id order by crt.message_credit_log_entdate desc`;

		} else {  // otherwise following in this query
			// to get get_message_credit query was executed
			message_credit = `SELECT crt.message_credit_log_id, prn.user_id parntid,rm.rights_name, prn.user_name parntname,um.user_type, usr.user_id usrid, usr.user_name usrname, crt.provided_message_count, crt.message_comments, crt.message_credit_log_status, crt.message_credit_log_entdate FROM message_credit_log crt left join user_management usr on usr.user_id = crt.user_id LEFT JOIN rights_master rm ON rm.rights_id = crt.rights_id left join user_management prn on prn.user_id = crt.parent_id LEFT JOIN user_master um ON um.user_master_id = usr.user_master_id where usr.parent_id = '${user_id}'  order by crt.message_credit_log_entdate desc`;
		}
		logger_all.info("[select query response] : " + message_credit);
		const get_message_credit = await db.query(message_credit);
		logger_all.info("[select query response] : " + JSON.stringify(get_message_credit))
		// if the get get_message_credit length is '0' to send the no available data.otherwise it will be return the get_message_credit details.
		if (get_message_credit.length == 0) {
			return {
				response_code: 1,
				response_status: 204,
				response_msg: 'No data available'
			};
		} else {
			return {
				response_code: 1,
				response_status: 200,
				num_of_rows: get_message_credit.length,
				response_msg: 'Success',
				report: get_message_credit
			};
		}
		// any error occurres send error response to client
	} catch (e) {
		logger_all.info("[MessageCreditList failed response] : " + e)
		return {
			response_code: 0,
			response_status: 201,
			response_msg: 'Error occured'
		};
	}
}
// MessageCreditList - end
// using for module exporting
module.exports = {
	MessageCreditList
}