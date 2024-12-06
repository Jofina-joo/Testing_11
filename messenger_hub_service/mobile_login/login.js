/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in mobile login function

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/
const db = require("../db_connect/connect");
const jwt = require("jsonwebtoken"); //change
const md5 = require("md5") //change
const log_main = require('../logger')
require("dotenv").config();

//Start - Mobile Login
async function login(req) {
  var logger_all = log_main.logger_all
  var logger = log_main.logger

  //File generate
  // get all the req data
  var header_json = req.headers;
  let ip_address = header_json['x-forwarded-for'];

  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address)
  logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address)

  try { //check the user name

    const mobile_login = `CALL mobile_login('${req.body.mobile_number}', '${req.body.device_token}')`;
    logger_all.info("[Select query request] : " + mobile_login);
    var mobile_login_result = await db.query(mobile_login);
    logger_all.info("[select query response - mobile_login] : " + JSON.stringify(mobile_login_result[0]))

    if (mobile_login_result[0][0].response_msg == 'Success') {
      logger.info("[Success response - Success] : " + JSON.stringify({
        response_code: 1,
        response_status: 200,
        response_msg: 'Success.',
        user_short_name: mobile_login_result[0][0].user_short_name,
        request_id: req.body.request_id
      }))
      return {
        response_code: 1,
        response_status: 200,
        response_msg: 'Success.',
        user_short_name: mobile_login_result[0][0].user_short_name,
        request_id: req.body.request_id
      };

    } else if (mobile_login_result[0][0].Failed == 'failed') {
      const response = {
        response_code: 0,
        response_status: 201,
        response_msg: 'Already logged in.',
        request_id: req.body.request_id  // Include request_id property
      };
      logger.info("[Failed response - Already logged in] : " + JSON.stringify(response));
      return response;
    }
  } catch (err) {
    // Failed - call_index_signin Sign in function error
    logger_all.info(": [call_index_signin] Failed - " + err);
    return {
      response_code: 0,
      response_status: 201,
      response_msg: 'Error Occurred.',
      request_id: req.body.request_id
    };
  }
}
//End Function - Mobile Login

module.exports = {
  login,
};

