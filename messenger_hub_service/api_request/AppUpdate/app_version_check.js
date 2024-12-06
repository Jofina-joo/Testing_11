/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in app_version_check functions which is used to check app version is already exist

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 20-Jan-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function - App version check
async function app_version_check(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  //Get all request data
  var app_version = req.body.app_version;
  var request_id = req.body.request_id;
  try {

    //Query to Get app version
    var select_app_version = `SELECT app_version from app_version_update where app_version = '${app_version}'`
    logger_all.info("[select query app_version] : " + select_app_version);
    const select_app_version_res = await db.query(select_app_version);
    logger_all.info("[select query response] : " + JSON.stringify(select_app_version_res));

    //Check if select app version not equal to zero, update app version already exists
    if (select_app_version_res.length != 0) {
      logger_all.info("[update query request - App Version already exists.] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'App Version already exists.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'App Version already exists.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response - App Version already exists.] : " + JSON.stringify(update_api_log))
      logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'App Version already exists.' }))
      return { response_code: 0, response_status: 201, response_msg: 'App Version already exists.' };
    }

    //Otherwise send success response
    logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))
    return { response_code: 1, response_status: 200, response_msg: 'Success' };
  }
  catch (err) {
    logger_all.info("[app version - check] Failed - " + err);

    //Send failure response if error occurs     
    logger_all.info("[update query request - Error Occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
    logger_all.info(": [check] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  app_version_check
};
//End Function - App version check
