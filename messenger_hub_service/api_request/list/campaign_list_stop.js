/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list stop functions which is used to list stopped campaign.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

async function campaign_list_stop(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  try {
    campaign_list = `CALL campaign_list_stop('${req.body.user_id}', '${req.body.user_product}')`;

    logger_all.info("[Select query request] : " + campaign_list);
    var campaign_list_result = await db.query(campaign_list);
    logger_all.info("[select query response - get_product_id] : " + JSON.stringify(campaign_list_result[0]))
    if (campaign_list_result[0].length == 0) {
      return {
        response_code: 0,
        response_status: 204,
        response_msg: 'No data available.'
      };
    } else {
      return {
        response_code: 1,
        response_status: 200,
        response_msg: 'Success',
        campaign_list: campaign_list_result[0]
      };
    }
  } catch (err) {
    // Failed - call_index_signin Sign in function
    logger_all.info("[campaign list stop report] Failed - " + err);

    return {
      response_code: 0,
      response_status: 201,
      response_msg: 'Error Occurred.'
    };
  }
}
module.exports = {
  campaign_list_stop
};