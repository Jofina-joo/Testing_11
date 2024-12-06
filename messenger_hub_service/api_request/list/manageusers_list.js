/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in manageusers list functions which is used to list users.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
// Start function to ManageUsersList
async function ManageUsersList(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {
		//  Get all the req header data
		const header_token = req.headers['authorization'];
		// To initialize a variable with an empty string value
		var get_manage_users = '';
		var whrcondition = ` `;
		var get_manage_users_1 = '';
		// declare the variables
		var user_id = req.body.user_id;
		// query parameters
		logger_all.info("[ManageUsersList query parameters] : " + JSON.stringify(req.body));
		// To get the User_id

		logger_all.info("[select query request] : " + `SELECT usr.user_id,ums.user_type, usr.user_name,usr.logo_media, ums.user_master_id, prn.user_id parent_id, prn.user_name parent_name, usr.login_id, usr.user_email, usr.user_mobile, usr.user_status,  DATE_FORMAT(usr.user_entry_date,'%d-%m-%Y %H:%i:%s') user_entry_date FROM user_management usr left join user_master ums on usr.user_master_id = ums.user_master_id left join user_management prn on usr.parent_id = prn.user_id where usr.user_master_id != '1' order by user_id desc`);
		get_manage_users = await db.query(`SELECT usr.user_id, usr.user_name,ums.user_type,usr.logo_media, ums.user_master_id, prn.user_id parent_id, prn.user_name parent_name, usr.login_id, usr.user_email, usr.user_mobile, usr.user_status,  DATE_FORMAT(usr.user_entry_date,'%d-%m-%Y %H:%i:%s') user_entry_date FROM user_management usr left join user_master ums on usr.user_master_id = ums.user_master_id left join user_management prn on usr.parent_id = prn.user_id where usr.user_master_id != '1' order by user_id desc`);
		logger_all.info("[select query response] : " + JSON.stringify(get_manage_users))

		// if the get_manage_users length is '0' to get the no available data.otherwise it will be return the push the get_manage_users details.
		if (get_manage_users.length == 0) {
			return {
				response_code: 1,
				response_status: 204,
				response_msg: 'No data available'
			};
		} else {
			return {
				response_code: 1,
				response_status: 200,
				num_of_rows: get_manage_users.length,
				response_msg: 'Success',
				report: get_manage_users
			};
		}

	} catch (e) {  // any error occurres send error response to client
		logger_all.info("[ManageUsersList failed response] : " + e)
		return {
			response_code: 0,
			response_status: 201,
			response_msg: 'Error occured'
		};
	}
}
// End Function to ManageUsersList

// using for module exporting
module.exports = {
	ManageUsersList
}