/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to update send report

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../../logger')
require("dotenv").config();
const moment = require("moment")
const env = process.env
const DB_NAME = env.DB_NAME;
//Start Function - Update Task
async function update_task(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  //Date format
  function date_format(dateStr, original_date) {
    if (dateStr.toLowerCase().includes('yesterday')) {
      var split_data = dateStr.split(" ")
      var full_date = moment(original_date).subtract(1, 'days').format("YYYY-MM-DD") + " " + moment(`${split_data[1]}`, 'h:mm A').format("HH:mm:") + "00"
      return full_date
    }
    else if (dateStr.toLowerCase().includes('today')) {
      var split_data = dateStr.split(" ")
      var full_date = moment(original_date).format("YYYY-MM-DD") + " " + moment(`${split_data[1]}`, 'h:mm A').format("HH:mm:") + "00"
      return full_date
    }
    else if (dateStr.toLowerCase().includes('minutes ago')) {
      var split_data = dateStr.split(" ")
      return moment(original_date).subtract(split_data[0], 'minutes').format("YYYY-MM-DD HH:mm:") + "00"
    }
    else if (dateStr.toLowerCase().includes('minute ago')) {
      var split_data = dateStr.split(" ")
      return moment(original_date).subtract(split_data[0], 'minutes').format("YYYY-MM-DD HH:mm:") + "00"
    }
    else if (dateStr.toLowerCase().includes('just now')) {
      logger.info("[dateStr] " + dateStr)
      logger.info("[original_date] " + original_date)
      return moment(original_date).format("YYYY-MM-DD HH:mm:") + "00"
    }
    else {
      return moment(dateStr, 'MMMM DD, h:mm A').format('YYYY-MM-DD HH:mm:') + "00"
    }
  }
  try {
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Get all request data
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_whatsapp_id;
    var user_id = req.body.selected_user_id;
    var request_id = req.body.request_id;
    var wtsp_number_count = 0;
    var non_wtsp_number_count = 0;
    var product_id = req.body.product_id;
    var str = req.body.data;

    //get user_master_id
 var get_usermaster_id = `SELECT user_master_id FROM user_management where user_id = '${user_id}'`
 logger_all.info("[Select query request] : " + get_usermaster_id);
 var get_usermaster_result = await db.query(get_usermaster_id);
 logger_all.info("[Select query response] : " + JSON.stringify(get_usermaster_result))

    //Check response after stop campaign
    var get_response = `SELECT *
                            FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id}
                            WHERE response_status ='T'
                              AND compose_message_id = '${compose_id}'
                              AND sender_mobile_no = '${mobile_number}'`
    logger_all.info("[Select query request] : " + get_response);
    var get_response_result = await db.query(get_response);
    logger_all.info("[Select query response] : " + JSON.stringify(get_response_result))
    //check if sender numbers length is not equal to zero, get sender ID data
    if ((get_response_result.length > 0 )&&  (get_usermaster_result[0].user_mater_id != '1')) {

      var update_user_credits = `UPDATE user_credits SET used_credits = used_credits + ${get_response_result.length} ,available_credits = available_credits - ${get_response_result.length} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
      logger_all.info("[update query request - update user credits] : " + update_user_credits);
      var update_user_credits_res = await db.query(update_user_credits);
      logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
    }

    //Check if received data is empty
    if (str == "" || str == undefined) {
      str = [];
    }

    //Otherwise continue process
    else {
      var list1 = str.split("˜")

      for (var i = 0; i < list1.length; i++) {
        // try{
        var array = list1[i].split("||")
        logger_all.info("array " + array[1])
        var send_status = array[1].split("+")
        logger_all.info("array[0].length " + array[0].length)

        //Check if given number not in whatsapp, then update response as success
        if (array[1].length != 0 && array[1] != 'Mobile Number Not in Whatsapp') {

          var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'Y', response_message = 'Success', response_date = '${send_status[0]}' WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}' AND (response_status IS NULL  OR response_status = 'T')`
          logger_all.info("[update query request] : " + update_data);
          const update_data_result = await db.query(update_data);
          logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

          var select_sender_ID = `SELECT sender_id from sender_id_master where mobile_no = '${mobile_number}'`
          logger_all.info("[select query request_senderid] : " + select_sender_ID);
          const select_sender_ID_res = await db.query(select_sender_ID);
          logger_all.info("[select query response] : " + JSON.stringify(select_sender_ID_res));
          active_sender_ID = select_sender_ID_res[0].sender_id;

          //Update sender ID limits
          var updatesenderlimits = `UPDATE sender_id_limits SET daily_used_credits = daily_used_credits + 1,total_used_credits = total_used_credits+1
                                                           WHERE user_rights_id = '${product_id}' and sender_id = '${active_sender_ID}'`;
          logger_all.info("[update query request - update sender limits] : " + updatesenderlimits);
          var update_updatesenderlimits = await db.query(updatesenderlimits);

          logger_all.info("[update query response - update sender limits] : " + JSON.stringify(update_updatesenderlimits))

          wtsp_number_count = wtsp_number_count + 1
        }
        else {

          //Otherwise check if number not in whatsapp, update response as failed
          if (array[1] == 'Mobile Number Not in Whatsapp') {
            var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Mobile Number Not in Whatsapp',response_date = CURRENT_TIMESTAMP WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}' AND (response_status IS NULL  OR response_status = 'T')`
            logger_all.info("[update query request] : " + update_data);
            const update_data_result = await db.query(update_data);
            logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

          }
          else

          //Otherwise update response as failed
          {
            var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Failed',response_date = CURRENT_TIMESTAMP WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}' AND (response_status IS NULL  OR response_status = 'T')`
            logger_all.info("[update query request] : " + update_data);
            const update_data_result = await db.query(update_data);
            logger_all.info("[update query response] : " + JSON.stringify(update_data_result));

          }
          if( get_usermaster_result[0].user_mater_id != '1'){
          var update_user_credits = `UPDATE user_credits SET used_credits = used_credits - 1,available_credits = available_credits + 1 WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
          logger_all.info("[update query request - update user credits] : " + update_user_credits);
          var update_user_credits_res = await db.query(update_user_credits);
          logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
            }
          non_wtsp_number_count = non_wtsp_number_count + 1

        }
        /*
        }
         catch (err) {
              logger_all.info(": [update task] Failed - " + err);
              if(array[1] == 'Mobile Number Not in Whatsapp')
                {
                var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Mobile Number Not in Whatsapp',response_date = CURRENT_TIMESTAMP WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}' AND (response_status IS NULL  OR response_status = 'T')`
                        logger_all.info("[update query request] : " + update_data);
                        const update_data_result = await db.query(update_data);
                        logger_all.info("[update query response] : " + JSON.stringify(update_data_result));
        
                }
                else
                {
                var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Failed',response_date = CURRENT_TIMESTAMP WHERE compose_message_id = '${compose_id}' AND sender_mobile_no='${mobile_number}' AND receiver_mobile_no = '${array[0]}' AND (response_status IS NULL  OR response_status = 'T')`
                        logger_all.info("[update query request] : " + update_data);
                        const update_data_result = await db.query(update_data);
                        logger_all.info("[update query response] : " + JSON.stringify(update_data_result));
        
                 }
                             var update_user_credits =  `UPDATE user_credits SET used_credits = used_credits - 1,available_credits = available_credits + 1 WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
                                                                                        logger_all.info("[update query request - update user credits] : " + update_user_credits);
                                                                                        var update_user_credits_res = await db.query(update_user_credits);
                                                                                        logger_all.info("[update query response - update user credits] : " + JSON.stringify(update_user_credits_res));
                non_wtsp_number_count = non_wtsp_number_count+1
        
              if(i == list1.length)
              {
              return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.',request_id: req.body.request_id};
              }
            }
        */

      }

      //To update delivered and read status
      var array_data = [];
      var list2 = str.split("˜")
      for (var j = 0; j < list2.length; j++) {
        logger_all.info("[second for loop] : ")
        var array_val = list2[j].split("||")
        array_data.push({ "number": array_val[0] })
        logger_all.info("array " + array_val[1])
        var array_2 = array_val[1].split("+")

        logger_all.info("array_2 " + array_2)

        // if (array.length == 1) {
        array_data[j]['read_date'] = 'NULL'
        array_data[j]['read_status'] = 'NULL'
        array_data[j]['delivery_date'] = 'NULL'
        array_data[j]['delivery_status'] = 'NULL'
        //}

        for (var i = 1; i < array_2.length; i++) {
          logger_all.info("[second for loop2] : ")
          if (array_2[i] == "Seen" || array_2[i] == "Read") {
            if (array_2[i + 1] == "Delivered" || array_2[i + 1] == undefined) {
              array_data[j]['read_date'] = 'NULL'
              array_data[j]['read_status'] = 'NULL'
            }
            else {
              var read_date = date_format(array_2[i + 1], array_2[1]);
              array_data[j]['read_date'] = read_date
              array_data[j]['read_status'] = 'Y'
            }
          }
          if (array_2[i] == "Delivered") {
            if (array_2[i + 1] == "Seen" || array_2[i] == "Read" || array_2[i + 1] == undefined) {
              array_data[j]['delivery_date'] = 'NULL'
              array_data[j]['delivery_status'] = 'NULL'
            }
            else {
              var delivery_date = date_format(array_2[i + 1], array_2[1]);
              array_data[j]['delivery_date'] = delivery_date
              array_data[j]['delivery_status'] = 'Y'
            }
          }
        }
      }

      //Update delivered and read status and date, Otherwise update only response status
      for (var k = 0; k < array_data.length; k++) {
        var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET`
        if ((array_data[k].delivery_status != 'NULL') && (array_data[k].read_status != 'NULL')) {
          update_data = `${update_data} delivery_status = '${array_data[k].delivery_status}', delivery_date = '${array_data[k].delivery_date}',read_status = '${array_data[k].read_status}', read_date = '${array_data[k].read_date}'`
        }

        else {
          if (array_data[k].delivery_status != 'NULL') {
            update_data = `${update_data} delivery_status = '${array_data[k].delivery_status}', delivery_date = '${array_data[k].delivery_date}'`

          }
          else if (array_data[k].read_status != 'NULL') {
            update_data = `${update_data} read_status = '${array_data[k].read_status}', read_date = '${array_data[k].read_date}'`

          }
          else {
            update_data = `${update_data} response_status=response_status`
          }
        }

        update_data = `${update_data} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}' AND receiver_mobile_no = '${array_data[k].number}' AND response_status ='Y'`
        logger_all.info("[update query request] : " + update_data);
        const update_data_result = await db.query(update_data);
        logger_all.info("[update query response] : " + JSON.stringify(update_data_result));
      }


      var update_fail = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET response_status = 'F', response_message = 'Failed',response_date = CURRENT_TIMESTAMP WHERE response_status is NULL AND sender_mobile_no = '${mobile_number}' AND compose_message_id = '${compose_id}'`;
      logger_all.info("[insert query request] : " + update_fail);
      var update_fail_res = await db.query(update_fail);
      logger_all.info("[insert query response] : " + JSON.stringify(update_fail_res))

      //Update user credits
     if( get_usermaster_result[0].user_mater_id != '1'){
      var update_fail_cre = `UPDATE user_credits SET used_credits = used_credits - ${update_fail_res.affectedRows},available_credits = available_credits + ${update_fail_res.affectedRows} WHERE user_id = '${user_id}' AND rights_id = '${product_id}'`;
      logger_all.info("[insert query request] : " + update_fail_cre);
      var update_fail_cre_res = await db.query(update_fail_cre);
      logger_all.info("[insert query response] : " + JSON.stringify(update_fail_cre_res))
           }

      //Update total process and total success in summary report
      var update_summary_rep_wtsp = `UPDATE ${DB_NAME}.user_summary_report SET total_process = total_process - ${wtsp_number_count},total_success = total_success+ ${wtsp_number_count},sum_end_date = CURRENT_TIMESTAMP  WHERE com_msg_id = '${compose_id}'`
      logger_all.info("[insert query request] : " + update_summary_rep_wtsp);
      var update_summary_rep_wtsp_res = await db.query(update_summary_rep_wtsp);
      logger_all.info("[insert query response] : " + JSON.stringify(update_summary_rep_wtsp_res))

      //Update total process and total failed in summary report
      var update_summary_rep_nonwtsp = `UPDATE ${DB_NAME}.user_summary_report SET total_process = total_process - ${non_wtsp_number_count},total_failed = total_failed+ ${non_wtsp_number_count},sum_end_date = CURRENT_TIMESTAMP  WHERE com_msg_id = '${compose_id}'`
      logger_all.info("[insert query request] : " + update_summary_rep_nonwtsp);
      var update_summary_rep_nonwtsp_res = await db.query(update_summary_rep_nonwtsp);
      logger_all.info("[insert query response] : " + JSON.stringify(update_summary_rep_nonwtsp_res))

      //Update total valid mobile number count
      var update_valid_mobile_no_cnt = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET valid_mobile_no_count = valid_mobile_no_count+'${wtsp_number_count}' WHERE compose_message_id = '${compose_id}'`;
      logger_all.info("[insert query request] : " + update_valid_mobile_no_cnt);
      var update_valid_mobile_no_cnt_res = await db.query(update_valid_mobile_no_cnt);
      logger_all.info("[insert query response] : " + JSON.stringify(update_valid_mobile_no_cnt_res))


      //get delivery and read count
      var get_compose_del = `SELECT receiver_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} where compose_message_id = '${compose_id}' and delivery_status = 'Y'`;
      logger_all.info("[select query request] : " + get_compose_del);
      const get_compose_del_result = await db.query(get_compose_del);
      logger_all.info("[select query response] : " + JSON.stringify(get_compose_del_result));

      if (get_compose_del_result != 0) {
        var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_delivered = ${get_compose_del_result.length} WHERE com_msg_id = '${compose_id}'`
        logger_all.info("[insert query request] : " + update_summary_report);
        var update_summary_report_res = await db.query(update_summary_report);
        logger_all.info("[insert query response] : " + JSON.stringify(update_summary_report_res))

      }

      var get_compose_read = `SELECT receiver_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} where compose_message_id = '${compose_id}' and read_status = 'Y'`;
      logger_all.info("[select query request] : " + get_compose_read);
      const get_compose_read_result = await db.query(get_compose_read);
      logger_all.info("[select query response] : " + JSON.stringify(get_compose_read_result));

      if (get_compose_read_result != 0) {

        var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_read = ${get_compose_read_result.length} WHERE com_msg_id = '${compose_id}'`
        logger_all.info("[insert query request] : " + update_summary_report);
        var update_summary_report_res = await db.query(update_summary_report);
        logger_all.info("[insert query response] : " + JSON.stringify(update_summary_report_res))

      }

    }

    //Update sender ID status 'Y'
    var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`

    logger_all.info("[update query request] : " + update_sender_sts);
    const update_sender_sts_result = await db.query(update_sender_sts);
    logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));

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

    //Check all sender ID is in active status
    if (get_res_senderID_res.length == get_res_sendersts_res.length) {
      var update_campaign_sts = `UPDATE ${DB_NAME}_${user_id}.compose_message_${user_id} SET cm_status = 'Y' WHERE compose_message_id = '${compose_id}'`;
      logger_all.info("[insert query request] : " + update_campaign_sts);
      var update_campaign_sts_res = await db.query(update_campaign_sts);
      logger_all.info("[insert query response] : " + JSON.stringify(update_campaign_sts_res))
    }
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
//End Function - Update Task
