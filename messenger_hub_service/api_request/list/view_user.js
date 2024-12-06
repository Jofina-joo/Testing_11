/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in view user functions which is used to view user details for signup.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require("../../logger");

// Start Function to view user details
async function ViewUser(req) {
  var logger_all = main.logger_all;
  var logger = main.logger;
  try {
    let user_id = req.body.user_id;
    //  Get all the req header data
    const header_token = req.headers["authorization"];
    // query parameters
    logger_all.info("[View User query parameters] : " + JSON.stringify(req.body));
    // get_view_user this condition is true.process will be continued. otherwise process are stoped.
    logger_all.info("[select query request] : " + `SELECT mgt.user_id,mgt.user_master_id,mgt.user_name,mgt.user_short_name,mgt.api_key,mgt.login_id,mgt.login_password,mgt.user_email,mgt.user_mobile,mgt.user_bearer_token,mgt.user_status,DATE_FORMAT(mgt.user_entry_date,'%d-%m-%Y %H:%i:%s') user_entry_date,usr.user_type,usr.user_details,usr.user_master_status,DATE_FORMAT(usr.um_entry_date,'%d-%m-%Y %H:%i:%s') um_entry_date FROM user_management mgt left join user_master usr on mgt.user_master_id = usr.user_master_id where mgt.user_id = '${user_id}'`);
    const get_view_user = await db.query(
      `SELECT mgt.user_id,mgt.user_master_id,mgt.user_name,mgt.user_short_name,mgt.api_key,mgt.login_id,mgt.login_password,mgt.user_email,mgt.user_mobile,mgt.user_bearer_token,mgt.user_status,DATE_FORMAT(mgt.user_entry_date,'%d-%m-%Y %H:%i:%s') user_entry_date,usr.user_type,usr.user_details,usr.user_master_status,DATE_FORMAT(usr.um_entry_date,'%d-%m-%Y %H:%i:%s') um_entry_date FROM user_management mgt left join user_master usr on mgt.user_master_id = usr.user_master_id where mgt.user_id = '${user_id}'`
    );
    logger_all.info("[select query response] : " + JSON.stringify(get_view_user));
    // if the get_view_user length is not available to send the Invalid User. Kindly try again with valid user!.otherwise the process was continued
    if (get_view_user.length == 0) {
      return {
        response_code: 0,
        response_status: 201,
        response_msg: "Invalid User. Kindly try again with valid user!",
      };
    } else {
      return {
        // to return the success message
        response_code: 1,
        response_status: 200,
        num_of_rows: get_view_user.length,
        response_msg: 'Success',
        view_user: get_view_user
      };
    }
  } catch (e) {
    // any error occurres send error response to client
    logger_all.info("[View User failed response] : " + e);
    return {
      response_code: 0,
      response_status: 201,
      response_msg: "Error occurred",
    };
  }
}
// End Function to view user details

// using for module exporting
module.exports = {
  ViewUser,
};