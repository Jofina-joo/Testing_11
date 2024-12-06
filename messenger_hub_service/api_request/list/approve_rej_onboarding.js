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

// ApproveRejectOnboarding - start
async function ApproveRejectOnboarding(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  try {
    // get all the req data

    var user_id = req.body.user_id;
    var change_user_id = req.body.change_user_id;
    var txt_remarks = req.body.txt_remarks;
    var aprj_status = req.body.aprj_status;
    var reseller_masterid = req.body.reseller_masterid;
    var reselleruserids = req.body.reselleruserids;
    var media_url = req.body.media_url;
    var update_query_values = '';
    // query parameters
    var comments;
    var upload_media = '';

    logger_all.info("[select query request] : " + `SELECT * from user_management where user_id ='${change_user_id}' order by user_id desc`);
    get_manage_users = await db.query(`SELECT * from user_management where user_id ='${change_user_id}' order by user_id desc`);
    logger_all.info("[select query response] : " + JSON.stringify(get_manage_users))


    if (aprj_status == 'A') {
      update_query_values = `UPDATE user_management SET user_status = 'Y' WHERE user_id = ${change_user_id}`;
      comments = "Account Activated!";
    } else if (aprj_status == 'R') {
      update_query_values = `UPDATE user_management SET user_status = 'R', rejected_comments = '${txt_remarks}'  WHERE user_id = ${change_user_id}`;
      comments = "Account Rejected!";
    }
    else if (aprj_status == 'D') {
      update_query_values = `UPDATE user_management SET user_status = 'D' WHERE user_id = ${change_user_id}`;
      comments = "Account Suspended";
    }
    else if (reseller_masterid == '2') {
      update_query_values = `UPDATE user_management SET user_master_id = '2' WHERE user_id = ${change_user_id}`;
      comments = "Reseller Created";
    } else if (reselleruserids) {
      if (get_manage_users.length > 0) {
        upload_media = `,logo_media= '${get_manage_users[0].logo_media}'`;
      }
      update_query_values = `UPDATE user_management SET parent_id='${change_user_id}' ${upload_media} WHERE user_id in (${reselleruserids})`;
      comments = "Reseller added users";
    } else if (media_url) {
      update_query_values = `UPDATE user_management SET logo_media = '${media_url}' WHERE user_id = ${change_user_id} or parent_id = ${change_user_id}`;
      comments = "Logo Changed";
    }

    logger_all.info("[ApproveRejectOnboarding query parameters] : " + JSON.stringify(req.body));

    // ApproveRejectOnboarding to execute this query
    logger_all.info("[Update query request - User details] : " + ` ${update_query_values}`);
    const update_profile_details = await db.query(`${update_query_values}`);
    logger_all.info("[Update query request - User details] : " + JSON.stringify(update_profile_details));

    // if the get_available_message length is not available to send the no available data.otherwise it will be return the get_available_message details.
    if (update_profile_details.affectedRows > 0) {
      return { response_code: 1, response_status: 200, num_of_rows: 1, response_msg: comments };
    } else {
      return { response_code: 1, response_status: 204, response_msg: 'No data available' };
    }

  }
  catch (e) {// any error occurres send error response to client
    logger_all.info("[ApproveRejectOnboarding failed response] : " + e)
    return { response_code: 0, response_status: 201, response_msg: 'Error occured' };
  }
}
// ApproveRejectOnboarding - end

// using for module exporting
module.exports = {
  ApproveRejectOnboarding,
}

