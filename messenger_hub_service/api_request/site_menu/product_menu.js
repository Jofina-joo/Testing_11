/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in product menu functions which is used to list product menu & header.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

async function product_menu(req) {
	const logger_all = main.logger_all
	const logger = main.logger

	try {

		const menu_list = `CALL product_menu('${req.body.user_id}')`;

		logger_all.info("[Select query request] : " + menu_list);
		const menu_list_result = await db.query(menu_list);
		logger_all.info("[Select query response] : " + JSON.stringify(menu_list_result[0]))

		if (menu_list_result[0].length == 0) {
			return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
		}
		else {
			return { response_code: 1, response_status: 200, response_msg: 'Success', menu_list: menu_list_result[0] };
		}
	}

	catch (err) {
		logger_all.info("[product menu] Failed - " + err);
		return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
	}
}

async function product_header(req) {
	const logger_all = main.logger_all
	const logger = main.logger
	try {

		// get all the req data
		const user_id = req.body.user_id;
		const user_master_id = req.body.user_master_id;
		let header = '';
		// query parameters
		logger_all.info("[AvailableCreditsList query parameters] : " + JSON.stringify(req.body));
		// get_available_credits to execute this query
		if (user_master_id == 1) {
			header = `SELECT usr.user_id,usr.user_master_id,usr.user_name,usr.parent_id,sum(urc.available_credits) available_credits,urc.total_credits,urc.used_credits,urc.rights_id,rm.rights_name FROM user_management usr LEFT JOIN user_credits urc ON usr.user_id = urc.user_id LEFT JOIN rights_master rm ON rm.rights_id = urc.rights_id where usr.user_id = '${user_id}' or usr.parent_id = '${user_id}' group by rm.rights_name`;
		} else {
			header = `SELECT usr.user_id,usr.user_master_id,usr.user_name,usr.parent_id,sum(urc.available_credits) available_credits,urc.total_credits,urc.used_credits,urc.rights_id,rm.rights_name FROM user_management usr LEFT JOIN user_credits urc ON usr.user_id = urc.user_id LEFT JOIN rights_master rm ON rm.rights_id = urc.rights_id where usr.user_id = '${user_id}' group by rm.rights_name`;
		}

		get_available_credits = await db.query(header);
		logger_all.info("[select query response] : " + JSON.stringify(get_available_credits));

		// if the get_available_credits length is not available to send the no available data.otherwise it will be return the get_available_credits details.
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

// Waiting_Approval - start
async function Waiting_Approval(req) {
	const logger_all = main.logger_all
	const logger = main.logger
	try {
		// get all the req data
		const user_id = req.body.user_id;
		let array_user_id = [], get_waiting_whatsapp, get_waiting_sms;
		// query parameters
		logger_all.info("[Waiting Approval query parameters] : " + JSON.stringify(req.body));
		// To get the User_id
		const get_user = `SELECT * FROM user_management where user_status = 'Y' `;

		logger_all.info("[select query request] : " + get_user);
		const get_user_id = await db.query(get_user);
		logger_all.info("[select query response] : " + JSON.stringify(get_user_id));
		array_user_id.push(user_id);

		// Using a for loop to pass each element
		for (let i = 0; i < array_user_id.length; i++) {
			get_waiting_whatsapp += `SELECT count(compose_message_id) FROM whatsapp_report_${array_user_id[i]}.compose_message_${array_user_id[i]} WHERE cm_status = "W" and product_id = '1' `;
			// Add UNION if it's not the last iteration
			if (i < array_user_id.length - 1) {
				get_waiting_whatsapp += ' UNION ';
			}
			get_waiting_sms += `SELECT count(compose_message_id) FROM whatsapp_report_${array_user_id[i]}.compose_message_${array_user_id[i]} WHERE cm_status = "W" and product_id = '1' `;
			// Add UNION if it's not the last iteration
			if (i < array_user_id.length - 1) {
				get_waiting_sms += ' UNION ';
			}
		}
		const get_waiting_whatsapp_count = await db.query(get_waiting_whatsapp);
		logger_all.info("[select query response] : " + JSON.stringify(get_waiting_whatsapp_count))

		const get_waiting_sms_count = await db.query(get_waiting_sms);
		logger_all.info("[select query response] : " + JSON.stringify(get_waiting_sms_count))

		return { response_code: 1, response_status: 200, get_waiting_sms_count: get_waiting_sms_count.length, get_waiting_whatsapp_count: get_waiting_whatsapp_count.length, response_msg: 'Success' };

	}
	catch (e) {// any error occurres send error response to client
		logger_all.info("[Waiting_Approval failed response] : " + e)
		return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
	}
}
// Waiting_Approval - end

module.exports = {
	product_menu,
	product_header,
	Waiting_Approval
};

