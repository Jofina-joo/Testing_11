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
// PaymentHistory - start
async function PaymentHistory(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {
		logger_all.info(" [PaymentHistory] - " + req.body);
		logger.info("[API REQUEST - PaymentHistory] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
		// get all the req data
		var user_id = req.body.user_id;
		var user_master_id = req.body.user_master_id;
		var get_payment_history;
		var payment_history = ``;
		if (user_master_id == 1) {
			//  to get_user_log_status 
			payment_history = `SELECT rse.*, usr.user_name, prn.user_name parent_name, pri.price_from, pri.price_to, pri.price_per_message,rm.rights_name FROM user_credit_raise rse left join user_management usr on rse.user_id = usr.user_id left join user_management prn on rse.parent_id = prn.user_id left join pricing_slot pri on rse.pricing_slot_id = pri.pricing_slot_id LEFT JOIN rights_master rm ON rm.rights_id = pri.rights_id where rse.user_id = '${user_id}' or rse.parent_id = '${user_id}' order by rse.usr_credit_entry_date desc`;
		} else {
			//  to get_user_log_status 
			payment_history = `SELECT rse.*, usr.user_name, prn.user_name parent_name, pri.price_from, pri.price_to, pri.price_per_message,rm.rights_name FROM user_credit_raise rse left join user_management usr on rse.user_id = usr.user_id left join user_management prn on rse.parent_id = prn.user_id left join pricing_slot pri on rse.pricing_slot_id = pri.pricing_slot_id LEFT JOIN rights_master rm ON rm.rights_id = pri.rights_id where rse.user_id = '${user_id}' order by rse.usr_credit_entry_date desc`;
		}
		logger_all.info("[select query ] : " + (payment_history));
		get_payment_history = await db.query(payment_history);
		logger_all.info("[select query response] : " + JSON.stringify(get_payment_history))
		// if the get message length is '0' to send the no available data.otherwise it will be return the get_user_log_status details.
		if (get_payment_history.length == 0) {
			return { response_code: 1, response_status: 204, response_msg: 'No data available' };
		}
		else {
			return { response_code: 1, response_status: 200, num_of_rows: get_payment_history.length, response_msg: 'Success', report: get_payment_history };
		}
	}
	catch (e) {// any error occurres send error response to client
		logger_all.info("[PaymentHistory failed response] : " + e)
		return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
	}
}
// PaymentHistory - end

// using for module exporting
module.exports = {
	PaymentHistory
}
