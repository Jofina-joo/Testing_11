/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in upload version functions which is used to upload app's latest version

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function - Upload App version
async function upload_version(req) {
  var logger_all = main.logger_all
  var logger = main.logger

  //Get all request data
  var app_file_name = req.body.app_file_name;
  var app_version = req.body.app_version;
  var request_id = req.body.request_id;
  try {

    //Query to get app version
    var select_app_version = `SELECT app_version from app_version_update where app_version = '${app_version}'`
    logger_all.info("[select query request - app version] : " + select_app_version);
    const select_app_version_res = await db.query(select_app_version);
    logger_all.info("[select query response - app version] : " + JSON.stringify(select_app_version_res));

    //check if select query length is not equal to zero, send failure response
    if (select_app_version_res.length != 0) {
      logger_all.info("[update query request - App Version already exists.] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'App Version already exists.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'App Version already exists.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
      logger_all.info("[update query response - App Version already exists.] : " + JSON.stringify(update_api_log))
      logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'App Version already exists.' }))
      return { response_code: 0, response_status: 201, response_msg: 'App Version already exists.' };
    }

    //Insert app version details
    var insert_app_version = `INSERT INTO app_version_update VALUES(NULL,'${app_file_name}','${app_version}','N',CURRENT_TIMESTAMP)`
    logger_all.info("[insert query request -  app version details] : " + insert_app_version)
    const insert_app_version_result = await db.query(insert_app_version);
    logger_all.info("[insert query response - app version details] : " + JSON.stringify(insert_app_version_result))

    //Update app status as null to find latest app version
    var update_app_sts = `UPDATE sender_id_master SET app_update_status = ''`;
    logger_all.info("[update query request - app status] : " + update_app_sts);
    var update_app_sts_res = await db.query(update_app_sts);
    logger_all.info("[update query response - app status] : " + JSON.stringify(update_app_sts_res))
    logger_all.info("[update query request - success] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - success] : " + JSON.stringify(update_api_log))
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success.' }))
    return { response_code: 1, response_status: 200, response_msg: 'Success' };
  }
  catch (err) {
    logger_all.info("[upload version] Failed - " + err);
    logger_all.info("[update query request - Error Occurred] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Error occurred.' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
    logger_all.info("[update query response - Error Occurred] : " + JSON.stringify(update_api_log))
    logger_all.info(": [check] Failed - " + err);
    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.' }))
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  upload_version
};
//End Function - Upload App version
