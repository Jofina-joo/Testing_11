/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for approve.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');

// Start Function - Campaign list
async function campaign_lt(req) {
  var logger_all = main.logger_all;
  var logger = main.logger;
  try {
    // Call Stored procedure - approve_campaign_lists
    var campaign_list = `CALL approve_campaign_lists('${req.body.user_product}')`;
    logger_all.info("[Select query request] : " + campaign_list);

    // Execute the query
    var campaign_list_result = await db.query(campaign_list);
    //logger_all.info("[Select query response] : " + JSON.stringify(campaign_list_result[0]));

    // Check if selected data length is equal to zero, send failure response 'No data available'
    if (campaign_list_result[0].length == 0) {
      const no_data_msg = { response_code: 0, response_status: 204, response_msg: 'No data available.' };
      logger_all.info("[invalid_msg response] : " + JSON.stringify(no_data_msg));
      return no_data_msg;
    } else {
      const success_msg = { response_code: 1, response_status: 200, response_msg: 'Success', campaign_list: campaign_list_result[0] };
      // Otherwise, send success response
      //logger_all.info("[success_msg response] : " + JSON.stringify(success_msg));
      return success_msg;
    }

  } catch (err) {
    logger_all.error("[campaign list report] Failed - " + err);
    const error_msg = { response_code: 0, response_status: 500, response_msg: 'Error Occurred.' };
    logger_all.info("[error_msg response] : " + JSON.stringify(error_msg));
    return error_msg;
  }
}

module.exports = {
  campaign_lt
};
// End Function - Campaign list
