/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in logout funtion

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

// Import the required packages and libraries
const db = require("../db_connect/connect");
require("dotenv").config();
const main = require('../logger');

// Start Function - Logout
async function logout(req) {
  const logger_all = main.logger_all;
  const logger = main.logger;

  const day = new Date();
  const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();
  const today_time = day.getHours() + ":" + day.getMinutes() + ":" + day.getSeconds();
  const current_date = today_date + ' ' + today_time;

  let user_id;
  let slt_logout;
  const header_json = req.headers;
  const ip_address = header_json['x-forwarded-for'];

  logger_all.info("[Logout query parameters] : " + JSON.stringify(req.body));
  try {

    user_id = req.body.user_id;

    logger_all.info("[Select query request] : " + `Select * from user_log WHERE user_id ='${user_id}' and login_date ='${today_date}' and user_log_status = 'I'`);
    slt_logout = await db.query(`Select * from user_log WHERE user_id ='${user_id}' and login_date = '${today_date}' and user_log_status = 'I'`);
    logger_all.info("[Select query response] : " + JSON.stringify(slt_logout));

    if (slt_logout.length > 0) {
      logger_all.info("[Update query request] : " + `UPDATE user_log SET logout_time = '${current_date}',user_log_status ='O' WHERE user_id ='${user_id}' and login_date ='${today_date}' and user_log_status = 'I' `);
      const sql_logout = await db.query(
        `UPDATE user_log SET logout_time = '${current_date}',user_log_status ='O' WHERE user_id ='${user_id}' and  login_date ='${today_date}' and user_log_status = 'I' `);
      logger_all.info("[Update query response] : " + JSON.stringify(sql_logout));

      return { response_code: 1, response_status: 200, response_msg: "Success" };
    }
    else {
      return { response_code: 1, response_status: 200, response_msg: "Success" };
    }
  }

  catch (err) {
    // Failed - call_index_signin Sign in function
    logger_all.info(": [Logout ] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
// End Function - Logout

// Start Function - Mobile Logout
async function mobile_logout(req) {
  const logger_all = main.logger_all;

  logger_all.info("[Logout query parameters] : " + JSON.stringify(req.body));
  try {

    const request_id = req.body.request_id;
    const logout = `UPDATE sender_id_master SET device_token = '-',sender_id_status ='N' WHERE mobile_no ='${req.body.mobile_number}' AND sender_id_status = 'Y'`;
    logger_all.info("[Update query request] : " + logout);
    const sql_logout = await db.query(logout);
    logger_all.info("[Update query response] : " + JSON.stringify(sql_logout));

    return { response_code: 1, response_status: 200, response_msg: "Success", request_id: req.body.request_id };
  }

  catch (err) {
    // Failed - call_index_signin Sign in function
    logger_all.info(": [Logout ] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }
}
// End Function - Mobile Logout

module.exports = {
  logout,
  mobile_logout
};
