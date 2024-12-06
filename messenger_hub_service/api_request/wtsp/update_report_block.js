/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to update blocked numbers report

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
//Start Function - Update Report Block
async function update_report_block(req) {

  var logger_all = main.logger_all
  var logger = main.logger
  try {

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
        return moment(original_date).format("YYYY-MM-DD HH:mm:") + "00"
      }
      else {
        return moment(dateStr, 'MMMM DD, h:mm A').format('YYYY-MM-DD HH:mm:') + "00"
      }
    }

    logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))
    logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers))

    //Get all request data
    var str = req.body.data;
    var mobile_number = req.body.mobile_number;
    var compose_id = req.body.compose_whatsapp_id;
    var user_id = req.body.selected_user_id;
    var request_id = req.body.request_id;
    var array_data = [];

    //Check if received data is empty
    if (str == "" || str == undefined) {
      str = [];
    }
    else {
      var list = str.split("˜")

      for (var j = 0; j < list.length; j++) {
        var array = list[j].split("||")
        array_data.push({ "number": array[0] })

        // if (array.length == 1) {
        array_data[j]['read_date'] = 'NULL'
        array_data[j]['read_status'] = 'NULL'
        array_data[j]['delivery_date'] = 'NULL'
        array_data[j]['delivery_status'] = 'NULL'
        //}
        for (var i = 2; i < array.length; i++) {
          if (array[i] == "Seen" || array[i] == "Read") {
            if (array[i + 1] == "Delivered" || array[i + 1] == undefined) {
              array_data[j]['read_date'] = 'NULL'
              array_data[j]['read_status'] = 'NULL'
            }
            else {
              var read_date = date_format(array[i + 1], array[1]);
              array_data[j]['read_date'] = read_date
              array_data[j]['read_status'] = 'Y'
            }
          }
          if (array[i] == "Delivered") {
            if (array[i + 1] == "Seen" || array[i] == "Read" || array[i + 1] == undefined) {
              array_data[j]['delivery_date'] = 'NULL'
              array_data[j]['delivery_status'] = 'NULL'
            }
            else {
              var delivery_date = date_format(array[i + 1], array[1]);
              array_data[j]['delivery_date'] = delivery_date
              array_data[j]['delivery_status'] = 'Y'
            }
          }
        }
      }

      //Update delivered and read status
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

        //          //Update summary Report
        var update_summary_report = `UPDATE ${DB_NAME}.user_summary_report SET total_read = ${get_compose_read_result.length} WHERE com_msg_id = '${compose_id}'`
        logger_all.info("[insert query request] : " + update_summary_report);
        var update_summary_report_res = await db.query(update_summary_report);
        logger_all.info("[insert query response] : " + JSON.stringify(update_summary_report_res))

      }

    }

    //Update sender ID status as 'B'
    var update_sender_sts = `UPDATE ${DB_NAME}.sender_id_master SET sender_id_status = 'B' WHERE mobile_no='${mobile_number}'`
    logger_all.info("[update query request] : " + update_sender_sts);
    const update_sender_sts_result = await db.query(update_sender_sts);
    logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));
    return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };
  }

  catch (err) {
    logger_all.info(": [update_report_block] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id };
  }
}

module.exports = {
  update_report_block
};
//End Function - Update Report Block