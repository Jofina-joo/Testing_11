/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in app list functions which is used to list app versions

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')
const env = process.env
const DB_NAME = env.DB_NAME;

//Start Function - App list
async function app_list(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  try {

    //Query to get app version details
    logger_all.info("[Select query request - app version details] : " + `SELECT app_update_id,app_version_file,app_version,app_update_status,DATE_FORMAT(app_update_entry_date,'%d-%m-%Y %H:%i:%s') app_update_entry_date from ${DB_NAME}.app_version_update ORDER BY app_update_id DESC`);
    var app_list = await db.query(`SELECT app_update_id,app_version_file,app_version,app_update_status,DATE_FORMAT(app_update_entry_date,'%d-%m-%Y %H:%i:%s') app_update_entry_date from ${DB_NAME}.app_version_update ORDER BY app_update_id DESC`);
    logger_all.info("[Select query response - app version details] : " + JSON.stringify(app_list))

    //Check if app list length is zero, send failure reponse - No data available
    if (app_list.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    //Otherwise send success response with app list data
    else {
      return { response_code: 1, response_status: 200, response_msg: 'Success', app_list: app_list };
    }
  }
  catch (err) {
    logger_all.info("[app list] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  app_list
};
//End Function - App list
