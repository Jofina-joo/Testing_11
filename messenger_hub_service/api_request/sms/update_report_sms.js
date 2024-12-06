/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to update sms delivered report

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
const moment = require('moment');
const env = process.env
const DB_NAME = env.DB_NAME;

//Start Function - Update Report SMS
async function update_report_sms(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  try {
    function date_format(originalDateStr) {
      logger_all.info("[originalDateStr] " + originalDateStr)
      // Remove "Received: " from the beginning of the string
      const dateString = originalDateStr.replace('Received: ', '');
      // Parse the date string and format it to railway time
      const formattedDate = moment(dateString, 'MM-DD-YYYY h:mm A').format('YYYY-MM-DD HH:mm:ss');
      console.log('formattedDate:', formattedDate);
      return formattedDate;

    }
    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Get all request data
    var str = req.body.data;
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_message_id;
    var user_id = req.body.selected_user_id;
    var request_id = req.body.request_id;

    //Check if received empty data
    if (str == "" || str == undefined) {
      str = [];
    }
    //Otherwise continue process
    else {
      var sms_array_data = [];
      var sms_list = str.split("˜")

      for (var j = 0; j < sms_list.length; j++) {
        var sms_array = sms_list[j].split("||")
        sms_array_data.push({ "number": sms_array[0] })
        sms_array_data[j]['read_date'] = 'NULL'
        sms_array_data[j]['read_status'] = 'NULL'
        sms_array_data[j]['delivery_date'] = 'NULL'
        sms_array_data[j]['delivery_status'] = 'NULL'

        //Loop through sms report data
        for (var i = 3; i < sms_array.length; i++) {
          if (sms_array[i + 1] != undefined) {

            //Check if report has 'Received' data and delivered status
            if (sms_array[i + 1].includes("Received:")) {
              var delivery_date = date_format(sms_array[i + 1]);
              sms_array_data[j]['delivery_date'] = delivery_date
              sms_array_data[j]['delivery_status'] = 'Y'
              logger_all.info("delivery data")
            }
          }
          else {
            logger_all.info("invalid value...")
          }
        }

        //If delivered data not null update delivered data, Otherwise update send response 
        for (var k = 0; k < sms_array_data.length; k++) {
          var update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET`
          if ((sms_array_data[k].delivery_status != 'NULL') && (sms_array_data[k].read_status != 'NULL')) {
            update_data = `${update_data} delivery_status = '${sms_array_data[k].delivery_status}', delivery_date = '${sms_array_data[k].delivery_date}',read_status = '${sms_array_data[k].read_status}', read_date = '${sms_array_data[k].read_date}'`
          }
          else {
            if (sms_array_data[k].delivery_status != 'NULL') {
              update_data = `${update_data} delivery_status = '${sms_array_data[k].delivery_status}', delivery_date = '${sms_array_data[k].delivery_date}'`
            }
            else if (sms_array_data[k].read_status != 'NULL') {
              update_data = `${update_data} read_status = '${sms_array_data[k].read_status}', read_date = '${sms_array_data[k].read_date}'`
            }
            else {
              update_data = `${update_data} response_status=response_status`
            }
          }
          update_data = `${update_data} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}' AND receiver_mobile_no = '${sms_array_data[k].number}' AND response_status ='Y'`
          logger_all.info("[update query request] : " + update_data);
          const update_data_result = await db.query(update_data);
          logger_all.info("[update query response] : " + JSON.stringify(update_data_result));
        }
      }

      //Get delivered count
      var get_compose_del = `SELECT receiver_mobile_no FROM ${DB_NAME}_${user_id}.compose_msg_status_${user_id} where compose_message_id = '${compose_id}' and delivery_status = 'Y'`;
      logger_all.info("[select query request] : " + get_compose_del);
      const get_compose_del_result = await db.query(get_compose_del);
      logger_all.info("[select query response] : " + JSON.stringify(get_compose_del_result));

      //Check if selected data not equal to zero, then update delivered count
      if (get_compose_del_result != 0) {
        var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_delivered = ${get_compose_del_result.length} WHERE com_msg_id = '${compose_id}'`
        logger_all.info("[insert query request] : " + update_summary_report);
        var update_summary_report_res = await db.query(update_summary_report);
        logger_all.info("[insert query response] : " + JSON.stringify(update_summary_report_res))

      }
    }

    //After complete all process, update sender ID status as 'Y'
    var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`
    logger_all.info("[update query request] : " + update_sender_sts);
    const update_sender_sts_result = await db.query(update_sender_sts);
    logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
    return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };
  }

  catch (err) {
    logger_all.info(": [update report sms] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }
}
module.exports = {
  update_report_sms
};
//End Function - Update SMS Report