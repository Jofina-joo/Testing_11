/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in change password functions which is used to change password for user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const md5 = require("md5");
const main = require('../../logger');

// Start function to change password
async function change_password(req) {
	var logger_all = main.logger_all
	var logger = main.logger
	try {
		//  Get all the req header data
		const header_token = req.headers['authorization'];

		// get all the req data
		var ex_password = md5(req.body.ex_password);
		var new_password = md5(req.body.new_password);
		// query parameters
		logger_all.info("[Change Password query parameters] : " + JSON.stringify(req.body));
		// To get the User_id
		user_id = req.body.user_id;
		// get_change_password this condition is true.process will be continued. otherwise process are stoped.
		logger_all.info("[select query request - get user details] : " + `SELECT * FROM user_management where user_id = '${user_id}' and login_password = '${ex_password}'`);
		const get_change_password = await db.query(`SELECT * FROM user_management where user_id = '${user_id}' and login_password = '${ex_password}'`);
		logger_all.info("[select query response - get user details] : " + JSON.stringify(get_change_password))
		// if the get_change_password length is not available to send the Invalid Existing Password. Kindly try again!.otherwise the process was continued
		if (get_change_password.length == 0) {
			return {
				response_code: 0,
				response_status: 201,
				response_msg: 'Invalid Existing Password. Kindly try again!'
			};
		} else { // to update the user_management new password.
			logger_all.info("[update query request - update user details] : " + `UPDATE user_management SET login_password = '${new_password}' WHERE user_id = '${user_id}'`)
			const update_succ = await db.query(`UPDATE user_management SET login_password = '${new_password}' WHERE user_id = '${user_id}'`);
			logger_all.info("[update query response - update user details] : " + JSON.stringify(update_succ))

			return { // to return the success message
				response_code: 1,
				response_status: 200,
				response_msg: 'Success'
			};
		}

	} catch (e) {// any error occurres send error response to client
		logger_all.info("[ChangePassword failed response] : " + e)
		return {
			response_code: 0,
			response_status: 201,
			response_msg: 'Error occured'
		};
	}
}
// End function to change password

// using for module exporting
module.exports = {
	change_password
}
