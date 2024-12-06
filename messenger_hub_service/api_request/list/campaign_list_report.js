/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list report functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const env = process.env
const DB_NAME = env.DB_NAME;
// Start Function - Campaign List
async function campaign_list_report(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  logger_all.info(" [campaign list report] - " + req.body);
  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
  try {
    // get all the req filter data
    let date_filter = req.body.date_filter;
    var user_product = req.body.user_product;
    var user_filter = req.body.user_filter;
    const header_token = req.headers['authorization'];

    // get current Date and time
    var day = new Date();
    var today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();

    // Initialize an array to store user IDs
    var array_list_user_id = [];

    user_id = req.body.user_id;
    // Retrieve the product ID for the specified user product
    var get_product = `SELECT * FROM rights_master where rights_name = '${user_product}' AND rights_status = 'Y' `;

    logger_all.info("[select query request - get product] : " + get_product);
    const get_product_id = await db.query(get_product);
    logger_all.info("[select query response - get product] : " + JSON.stringify(get_product_id));

    user_product_id = get_product_id[0].rights_id;
    //Filter baesd on user
    if (user_filter) {
      var get_user_name = `SELECT * FROM user_management where user_name = '${user_filter}' AND user_status = 'Y' `;

      logger_all.info("[select query request - get user name] : " + get_user_name);
      const get_user_id = await db.query(get_user_name);
      logger_all.info("[select query response - get user name] : " + JSON.stringify(get_user_id));

      var slt_user_id = get_user_id[0].user_id;
      filter_condition = `and user_id = '${slt_user_id}'`;
      user_id = slt_user_id;
    } else { //Otherwise return null
      filter_condition = '';
    }

    var campaign_list = '';
    var array_list = [];
    var select_query = await db.query(` SELECT user_id FROM user_management where (user_id = '${user_id}' or parent_id in ('${user_id}'))  `);
    logger_all.info("[select query request - get_all_user] : " + select_query);
    logger_all.info("[select query response - get_all_user] : " + JSON.stringify(select_query))


    //check select query length is greater than zero,get campaign list data
    if (select_query.length > 0) {

      for (var i = 0; i < select_query.length; i++) {
        array_list_user_id.push(select_query[i].user_id);
      }
      if (!date_filter) {
        for (var i = 0; i < array_list_user_id.length; i++) {

          campaign_list += ` SELECT compose_message_id,campaign_name,total_mobile_no_count,user_id FROM ${DB_NAME}_${array_list_user_id[i]}.compose_message_${array_list_user_id[i]} WHERE product_id='${user_product_id}' union`;
        }

        logger_all.info("[select query request - campaignlist] : " + campaign_list);
      } else { //Otherwise get data with date filter
        // date function for looping in one by one date
        filter_date_1 = date_filter.split("-");
        filter_date_first = Date.parse(filter_date_1[0]);
        filter_date_second = Date.parse(filter_date_1[1]);
        function dateRange(startDate, endDate, steps = 1) {
          const dateArray = [];
          let currentDate = new Date(startDate);

          while (currentDate <= new Date(endDate)) {
            dateArray.push(new Date(currentDate));
            function convert(dates) {
              var date = new Date(dates),
                mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                day = ("0" + date.getDate()).slice(-2);
              return [date.getFullYear(), mnth, day].join("-");
            }
            slt_date = convert(currentDate);

            // loop for array_list_user_id length
            for (var i = 0; i < array_list_user_id.length; i++) {
              campaign_list += ` SELECT compose_message_id,campaign_name,total_mobile_no_count,user_id FROM ${DB_NAME}_${array_list_user_id[i]}.compose_message_${array_list_user_id[i]} WHERE product_id='${get_product_id[0].rights_id}' and (date(cm_entry_date) BETWEEN '${slt_date}' AND '${slt_date}') ${filter_condition} union`;

            }
            currentDate.setUTCDate(currentDate.getUTCDate() + steps);
          }

          return dateArray;
        }

        const dates = dateRange(filter_date_1[0], filter_date_1[1]);
      }

      var lastIndex = campaign_list.lastIndexOf(" ");
      campaign_list = campaign_list.substring(0, lastIndex);

      logger_all.info("[Select query request - get campaign list] : " + campaign_list + " ORDER BY compose_message_id DESC");
      var campaign_list_result = await db.query(campaign_list + " ORDER BY compose_message_id DESC ");
      logger_all.info("[Select query response - get campaign list] : " + JSON.stringify(campaign_list_result))

      // Store the campaign list result in an array
      array_list.push(campaign_list_result);

    }


    //check campaign list result length is equal to zero, send error response as 'No data available.'
    if (campaign_list_result.length == 0) {
      logger.info("[Failed response - No data available] : " + JSON.stringify({ response_code: 0, response_status: 204, response_msg: 'No data available.' }))
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else { //Otherwise send success response with campaign list data
      logger.info("[API SUCCESS RESPONSE] : " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: campaign_list_result.length, campaign_list: campaign_list_result }))
      return { response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: campaign_list_result.length, campaign_list: campaign_list_result };
    }

  }

  catch (err) {
    logger_all.info("[campaign list report] Failed - " + err);
    // send error response as 'Error occured'
    logger.info("[Failed response - Error Occurred] : " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}

// End Function - Campaign List
module.exports = {
  campaign_list_report
};