/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This API is used to logout application.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 22-Nov-2023
*/

// Import the required packages and libraries
const express = require("express");
const router = express.Router();
const Logout = require("./logout");
require("dotenv").config();
const validator = require('../validation/middleware')
const valid_user = require("../validation/valid_user_middleware");
const valid_user_reqID = require("../validation/valid_user_middleware_reqID");
const db = require("../db_connect/connect");

const LogoutValidation = require("../validation/logout_validation");
const MobileLogoutValidation = require("../validation/mobile_logout_validation");

const main = require('../logger');

//Start Route - Logout
router.post(
  "/",
  validator.body(LogoutValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      const logger = main.logger
      const logger_all = main.logger_all

      var result = await Logout.logout(req);
      result['request_id'] = req.body.request_id;

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      if (result.response_code == 0) {
        //api_log_new
        logger_all.info("[update query request] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response] : " + JSON.stringify(update_api_log))
      }
      else {
        //api_log_new
        logger_all.info("[update query request] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response] : " + JSON.stringify(update_api_log))
      }
      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Logout

//Start Route - Mobile Logout
router.post(
  "/mobile_logout",
  validator.body(MobileLogoutValidation),
  async function (req, res, next) {
    try {
      const logger = main.logger

      var result = await Logout.mobile_logout(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Mobile Logout

module.exports = router;