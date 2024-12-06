/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/


const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const env = process.env
const DB_NAME = env.DB_NAME;

async function campaign_list(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  try {

    var user_product = req.body.user_product;
    const header_token = req.headers['authorization'];

    var array_list_user_id = [];
    user_id = req.body.user_id;
    // to get the product id from select query
    var get_product = `SELECT * FROM rights_master where rights_name = '${user_product}' AND rights_status = 'Y' `;

    logger_all.info("[select query request] : " + get_product);
    const get_product_id = await db.query(get_product);
    logger_all.info("[select query response] : " + JSON.stringify(get_product_id));

   const user_product_id = get_product_id[0].rights_id;

    var campaign_list = '';
    // to get the select query
    logger_all.info(` SELECT user_id FROM user_management where user_status = 'Y' `);
    var select_query = await db.query(` SELECT user_id FROM user_management where user_status = 'Y' `);

    logger_all.info("[select query response] : " + JSON.stringify(select_query))
    // if number of select_query length  is available then process the will be continued
    // loop all the get the user id to push the list_user_id, the array_list_user_id array
    if (select_query.length > 0) {
      for (var i = 0; i < select_query.length; i++) {
        array_list_user_id.push(select_query[i].user_id);
      }


      campaign_list += `SELECT compose_message_id,campaign_name, total_mobile_no_count,user_id,DATE_FORMAT(cm_entry_date, '%d-%m-%Y %H:%i:%s') as cm_entry_date FROM (`;
      // loop for array_list_user_id length
      for (var i = 0; i < array_list_user_id.length; i++) {
        campaign_list += ` SELECT compose_message_id,campaign_name,total_mobile_no_count,user_id,DATE_FORMAT(cm_entry_date, '%d-%m-%Y %H:%i:%s') as cm_entry_date FROM ${DB_NAME}_${array_list_user_id[i]}.compose_message_${array_list_user_id[i]} WHERE cm_status IN ('Y', 'S') and product_id='${user_product_id}' union`
      }
      //To remove extra 'union all'
      campaign_list = campaign_list.slice(0, -5) + `) AS combined_data ORDER BY STR_TO_DATE(cm_entry_date, '%d-%m-%Y %H:%i:%s') DESC`;

      logger_all.info("[Select query request - campaign list] : " + campaign_list);
      var campaign_list_result = await db.query(campaign_list);
      logger_all.info("[Select query response - campaign list] : " + JSON.stringify(campaign_list_result))
    }
    if (campaign_list_result.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else {
      return { response_code: 1, response_status: 200, response_msg: 'Success', campaign_list: campaign_list_result };
    }

  }

  catch (err) {
    logger_all.info("[campaign list report] Failed - " + err);

    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  campaign_list
};
