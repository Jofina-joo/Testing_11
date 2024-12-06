/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to list the pricing slot page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require('dotenv').config()
const main = require('../../logger');

// get_pricing_slot_status - start
async function pricingslot(req) {

	try {
		var user_id = req.body.user_id;
		var logger_all = main.logger_all
		var logger = main.logger
		logger_all.info(" [pricingslot] - " + req.body);
		logger.info("[API REQUEST - pricingslot] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

		//  to get_user_log_status 
		var pricing_slot = `SELECT usr.user_id,usr.user_name,usr.user_master_id,usr.parent_id,rm.rights_name,rm.rights_id,ps.pricing_slot_status,ps.pricing_slot_id,ps.plan_name,ps.price_from,ps.price_to,ps.pricing_slot_date,ps.price_per_message,rm.rights_name,rm.rights_short_name,rm.rights_status,rm.rights_entry_date FROM user_management usr LEFT JOIN user_rights ur ON usr.user_id = ur.user_id LEFT JOIN rights_master rm ON rm.rights_id = ur.rights_id LEFT JOIN pricing_slot ps on rm.rights_id = ps.rights_id where ur.user_id ='${user_id}' and ps.pricing_slot_status = 'Y'`;
		logger_all.info("[select query request] : " + pricing_slot);

		const get_pricing_slot_status = await db.query(pricing_slot);
		logger_all.info("[select query response] : " + JSON.stringify(get_pricing_slot_status))
		// if the get message length is '0' to send the no available data.otherwise it will be return the get_user_log_status details.
		if (get_pricing_slot_status.length == 0) {
			return { response_code: 1, response_status: 204, response_msg: 'No data available' };
		}
		else {
			return { response_code: 1, response_status: 200, num_of_rows: get_pricing_slot_status.length, response_msg: 'Success', pricing_slot: get_pricing_slot_status };
		}

	}
	catch (e) {// any error occurres send error response to client
		logger_all.info("[get_pricing_slot_status failed response] : " + e)
		return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
	}
}
// get_pricing_slot_status - end

// using for module exporting
module.exports = {
	pricingslot
}