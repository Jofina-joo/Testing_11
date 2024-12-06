/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update task functions which is used to update send whatsapp message details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const db = require("../../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../../logger')
require("dotenv").config();
const env = process.env
const DB_NAME = env.DB_NAME;
async function update_task(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  try {
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_whatsapp_id;
    var user_id = req.body.selected_user_id;
    var request_id = req.body.request_id;
    var wtsp_number_count = 0;
    var product_id = req.body.product_id;
    var str = req.body.data;

 //get user_master_id
 var get_usermaster_id = `SELECT user_master_id FROM user_management where user_id = '${user_id}'`
 logger_all.info("[Select query request] : " + get_usermaster_id);
 var get_usermaster_result = await db.query(get_usermaster_id);
 logger_all.info("[Select query response] : " + JSON.stringify(get_usermaster_result))

    var list = str.split("˜")
    for (var i = 0; i < list.length; i++) {
      var array = list[i].split("||")

      var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'Y', response_message = 'Success', response_date = '${array[1]}' WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}' AND response_status IS NULL`

      logger_all.info("[update query request] : " + update_data);
      const update_data_result = await db.query(update_data);
      logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

      var select_sender_ID = `SELECT sender_id from sender_id_master where mobile_no = '${mobile_number}'`
      logger_all.info("[select query request_senderid] : " + select_sender_ID);
      const select_sender_ID_res = await db.query(select_sender_ID);
      logger_all.info("[select query response] : " + JSON.stringify(select_sender_ID_res));
      active_sender_ID = select_sender_ID_res[0].sender_id;

      var updatesenderlimits = `UPDATE sender_id_limits SET daily_used_credits = daily_used_credits + 1,total_used_credits = total_used_credits+1
                                                           WHERE user_rights_id = '${product_id}' and sender_id = '${active_sender_ID}'`;
      logger_all.info("[update query request - update sender limits] : " + updatesenderlimits);
      var update_updatesenderlimits = await db.query(updatesenderlimits);

      logger_all.info("[update query response - update sender limits] : " + JSON.stringify(update_updatesenderlimits))

      wtsp_number_count = wtsp_number_count + 1

    }

    var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_process = 0,total_success = total_success+ ${wtsp_number_count},sum_end_date = CURRENT_TIMESTAMP  WHERE com_msg_id = '${compose_id}'`
    logger_all.info("[insert query request] : " + update_summary_report);
    var update_summary_report_res = await db.query(update_summary_report);
    logger_all.info("[insert query response] : " + JSON.stringify(update_summary_report_res))

    var update_valid_mobile_no_cnt = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET valid_mobile_no_count = valid_mobile_no_count+'${wtsp_number_count}' WHERE compose_message_id = '${compose_id}'`;
    logger_all.info("[insert query request] : " + update_valid_mobile_no_cnt);
    var update_valid_mobile_no_cnt_res = await db.query(update_valid_mobile_no_cnt);
    logger_all.info("[insert query response] : " + JSON.stringify(update_valid_mobile_no_cnt_res))
    var get_response = `SELECT *
                            FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id}
                            WHERE response_status IS NULL
                              AND response_message IS NULL
                              AND response_date IS NULL
                              AND compose_message_id = '${compose_id}'
                              AND sender_mobile_no = '${mobile_number}'`
    logger_all.info("[Select query request] : " + get_response);
    var get_response_result = await db.query(get_response);
    logger_all.info("[Select query response] : " + JSON.stringify(get_response_result))
    //check if sender numbers length is not equal to zero, get sender ID data
    if (get_response_result.length != 0) {
      var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'N', response_message = 'Failed', response_date = '${array[1]}' WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND response_status IS NULL`
      logger_all.info("[update query request] : " + update_data);
      const update_data_result = await db.query(update_data);
      logger_all.info("[update query response] : " + JSON.stringify(update_data_result));
      logger_all.info("[get_response_result.length] : " + get_response_result.length)

      for (var i = 0; i < get_response_result.length; i++) {
          if(get_usermaster_result[0].user_mater_id != '1'){
        var update_user_credits = `UPDATE user_credits SET used_credits = used_credits - 1,available_credits = available_credits + 1 WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;

        logger_all.info("[update query request - update user credits] : " + update_user_credits);
        var update_user_credits_res = await db.query(update_user_credits);

        logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
             }

        var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_process = 0,total_failed = total_failed+1,sum_end_date = CURRENT_TIMESTAMP  WHERE com_msg_id = '${compose_id}'`
        logger_all.info("[insert query request] : " + update_summary_report);
        var update_summary_report_res = await db.query(update_summary_report);
        logger_all.info("[insert query response] : " + JSON.stringify(update_summary_report_res))
      }


    }

    var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`

    logger_all.info("[update query request] : " + update_sender_sts);
    const update_sender_sts_result = await db.query(update_sender_sts);
    logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));

    var update_campaign_sts = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'Y' WHERE compose_message_id = '${compose_id}'`;
    logger_all.info("[insert query request] : " + update_campaign_sts);
    var update_campaign_sts_res = await db.query(update_campaign_sts);
    logger_all.info("[insert query response] : " + JSON.stringify(update_campaign_sts_res))
    return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };

  }

  catch (err) {
    logger_all.info(": Update task Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }

}

module.exports = {
  update_task,
};
