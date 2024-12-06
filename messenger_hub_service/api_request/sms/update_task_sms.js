/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update task sms functions which is used to update sms send message details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../../logger')
require("dotenv").config();
const { count } = require("sms-length");
const env = process.env
const DB_NAME = env.DB_NAME;
//Start Function - Update Task SMS
async function update_task_sms(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  try {
    console.log('update_task_sms')
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Get all request data
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_message_id;
    var user_id = req.body.selected_user_id;
    var str = req.body.data;
    var request_id = req.body.request_id;
    var product_id = req.body.sms_product_id;
    var success_number_count = 0;
    var failed_number_count = 0;


    //get user_master_id
     var get_usermaster_id = `SELECT user_master_id FROM user_management where user_id = '${user_id}'`
     logger_all.info("[Select query request] : " + get_usermaster_id);
     var get_usermaster_result = await db.query(get_usermaster_id);
     logger_all.info("[Select query response] : " + JSON.stringify(get_usermaster_result))

    //Query to get response status after stopped campaign
    var get_response = `SELECT *
                                 FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id}
                                 WHERE response_status ='T'
                                   AND compose_message_id = '${compose_id}'
                                   AND sender_mobile_no = '${mobile_number}'`
    logger_all.info("[Select query request] : " + get_response);
    var get_response_result = await db.query(get_response);
    logger_all.info("[Select query response] : " + JSON.stringify(get_response_result))

    var select_media = `SELECT text_title from ${DB_NAME}_${user_id}.compose_msg_media_${user_id} WHERE compose_message_id = '${compose_id}' AND cmm_status = 'Y'`
    logger_all.info("[select query request] : " + select_media);
    const select_media_result = await db.query(select_media);
    logger_all.info("[select query response] : " + JSON.stringify(select_media_result));


    message_content = select_media_result[0].text_title;
    var data = count(message_content);
    logger_all.info(JSON.stringify(data) + "SMS Calculation");

    txt_sms_count = data.messages;
    logger_all.info(txt_sms_count + " SMS count based");


    //check if sender numbers length is not equal to zero, get sender ID data
    if ((get_response_result.length > 0 )&&  (get_usermaster_result[0].user_mater_id != '1')) {

      var update_user_credits = `UPDATE user_credits SET used_credits = used_credits + ${get_response_result.length} ,available_credits = available_credits - ${get_response_result.length} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
      logger_all.info("[update query request - update user credits] : " + update_user_credits);
      var update_user_credits_res = await db.query(update_user_credits);
      logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
    }

    //Check if response data is null
    if (str == "" || str == undefined) {
      str = [];
    }
    //Otherwise continue process
    else {
      var list = str.split("˜")
      for (var i = 0; i < list.length; i++) {
        try {
          var array = list[i].split("||")

          //Check if send report data is empty, update as failed  
          if (array[1] == "") {
            // var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Failed', response_date = '${array[2]}' WHERE compose_message_id = '${compose_id}' AND receiver_mobile_no='${array[0]}' AND sender_mobile_no = '${mobile_number}' AND (response_status IS NULL  OR response_status = 'T')`
            var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Failed', response_date = CURRENT_TIMESTAMP WHERE compose_message_id = '${compose_id}' AND receiver_mobile_no='${array[0]}' AND sender_mobile_no = '${mobile_number}' AND (response_status IS NULL  OR response_status = 'T')`
            logger_all.info("[update query request] : " + update_data);
            const update_data_result = await db.query(update_data);
            logger_all.info("[update query response] : " + JSON.stringify(update_data_result));


            /*var update_user_credits =  `UPDATE user_credits SET used_credits = used_credits - 1,available_credits = available_credits + 1 WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
                                                                                  logger_all.info("[update query request - update user credits] : " + update_user_credits);
                                                                                  var update_user_credits_res = await db.query(update_user_credits);
                                                                                  logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));*/
             if( get_usermaster_result[0].user_mater_id != '1'){
            //Update credits based on SMS Calculation
            var update_user_credits = `UPDATE user_credits SET used_credits = used_credits - ${txt_sms_count},available_credits = available_credits + ${txt_sms_count} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
            logger_all.info("[update query request - update user credits] : " + update_user_credits);
            var update_user_credits_res = await db.query(update_user_credits);
            logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
             }

            failed_number_count = failed_number_count + 1
          }
          else {

            //Otherwise update as success
            var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'Y', response_message = 'Success', response_date = '${array[1]}' WHERE compose_message_id = '${compose_id}' AND receiver_mobile_no='${array[0]}' AND sender_mobile_no = '${mobile_number}' AND (response_status IS NULL  OR response_status = 'T')`
            logger_all.info("[update query request] : " + update_data);
            const update_data_result = await db.query(update_data);
            logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

            //Update sender ID limits if success response
            var select_sender_ID = `SELECT sender_id from sender_id_master where mobile_no = '${mobile_number}'`
            logger_all.info("[select query request_senderid] : " + select_sender_ID);
            const select_sender_ID_res = await db.query(select_sender_ID);
            logger_all.info("[select query response] : " + JSON.stringify(select_sender_ID_res));
            active_sender_ID = select_sender_ID_res[0].sender_id;

            var updatesenderlimits = `UPDATE sender_id_limits SET daily_used_credits = daily_used_credits + 1,total_used_credits = total_used_credits+1
                                                                      WHERE user_rights_id = '${product_id}' and sender_id = '${active_sender_ID}'`;
            logger_all.info("[update query request - update sender limits] : " + updatesenderlimits);
            var update_updatesenderlimits = await db.query(updatesenderlimits);

            success_number_count = success_number_count + 1

          }
        }
        catch (err) {
          logger_all.info(": [update task sms] Failed - " + err);

          //If get datetime value error, update as failed
          var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Failed', response_date = CURRENT_TIMESTAMP WHERE compose_message_id = '${compose_id}' AND receiver_mobile_no='${array[0]}' AND sender_mobile_no = '${mobile_number}' AND (response_status IS NULL  OR response_status = 'T')`
          logger_all.info("[update query request] : " + update_data);
          const update_data_result = await db.query(update_data);
          logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

          //var data = count(message_content);
          logger_all.info(JSON.stringify(data) + "SMS Calculation");

          //txt_sms_count = data.messages;
          logger_all.info(txt_sms_count + " SMS count based");
         if( get_usermaster_result[0].user_mater_id != '1'){
          var update_user_credits = `UPDATE user_credits SET used_credits = used_credits - ${txt_sms_count},available_credits = available_credits + ${txt_sms_count} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
          logger_all.info("[update query request - update user credits] : " + update_user_credits);
          var update_user_credits_res = await db.query(update_user_credits);
          logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
           }
          failed_number_count = failed_number_count + 1
          //To continue until loop finish
          if (i == list.length) {
            return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
          }
        }

      }

      //Update total process, total success in summary report
      var update_summary_rep_sms = `UPDATE ${DB_NAME}.user_summary_report SET total_process = total_process - ${success_number_count},total_success = total_success+ ${success_number_count},sum_end_date = CURRENT_TIMESTAMP  WHERE com_msg_id = '${compose_id}'`
      logger_all.info("[insert query request] : " + update_summary_rep_sms);
      var update_summary_rep_sms_res = await db.query(update_summary_rep_sms);
      logger_all.info("[insert query response] : " + JSON.stringify(update_summary_rep_sms_res))

      //Update total process, total failed in summary report
      var update_summary_rep_failedsms = `UPDATE ${DB_NAME}.user_summary_report SET total_process = total_process - ${failed_number_count},total_failed = total_failed+ ${failed_number_count},sum_end_date = CURRENT_TIMESTAMP  WHERE com_msg_id = '${compose_id}'`
      logger_all.info("[insert query request] : " + update_summary_rep_failedsms);
      var update_summary_rep_failedsms_res = await db.query(update_summary_rep_failedsms);
      logger_all.info("[insert query response] : " + JSON.stringify(update_summary_rep_failedsms_res))

      //Update total mobile number count
      var update_valid_mobile_no_cnt = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET valid_mobile_no_count = valid_mobile_no_count+'${success_number_count}' WHERE compose_message_id = '${compose_id}'`;
      logger_all.info("[insert query request] : " + update_valid_mobile_no_cnt);
      var update_valid_mobile_no_cnt_res = await db.query(update_valid_mobile_no_cnt);
      logger_all.info("[insert query response] : " + JSON.stringify(update_valid_mobile_no_cnt_res))

      //After complete all process, update sender ID status as 'Y'
      var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`
      logger_all.info("[update query request] : " + update_sender_sts);
      const update_sender_sts_result = await db.query(update_sender_sts);
      logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));

      //Check all sender ID gets active
      var get_res_senderID = `SELECT sender_mobile_no from ${DB_NAME}_${user_id}.compose_msg_status_${user_id}
                                             WHERE compose_message_id = '${compose_id}'`
      logger_all.info("[select query request_senderid] : " + get_res_senderID);
      const get_res_senderID_res = await db.query(get_res_senderID);
      logger_all.info("[select query response] : " + JSON.stringify(get_res_senderID_res));

      var get_res_sendersts = `SELECT m1.sender_mobile_no, m1.compose_message_id
                                             FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} m1
                                             LEFT JOIN ${DB_NAME}.sender_id_master m2 ON m1.sender_mobile_no = m2.mobile_no
                                             AND m2.sender_id_status != 'P'
                                             WHERE m1.compose_message_id = '${compose_id}'
                                               AND m2.mobile_no IS NOT NULL`
      logger_all.info("[select query request_senderidsts] : " + get_res_sendersts);
      const get_res_sendersts_res = await db.query(get_res_sendersts);
      logger_all.info("[select query response] : " + JSON.stringify(get_res_sendersts_res));
      //If all sender ID gets active, update campaign status as 'Y'
      if (get_res_senderID_res.length == get_res_sendersts_res.length) {
        var update_campaign_sts = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'Y' WHERE compose_message_id = '${compose_id}'`;
        logger_all.info("[insert query request] : " + update_campaign_sts);
        var update_campaign_sts_res = await db.query(update_campaign_sts);
        logger_all.info("[insert query response] : " + JSON.stringify(update_campaign_sts_res))
      }
      return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };
    }
  }

  catch (err) {
    logger_all.info(": [update task sms] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }
}
module.exports = {
  update_task_sms,
};
//End Function - Update Task SMS
