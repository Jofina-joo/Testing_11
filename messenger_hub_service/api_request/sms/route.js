/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the sms message details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const express = require("express");
const router = express.Router();
require("dotenv").config();
const Update_task_sms = require("./update_task_sms");
const Get_rep_sms = require("./get_report_sms");
const Get_task_sms = require("./get_task_sms");
const Update_report_sms = require("./update_report_sms");
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware")
const update_report_validation = require("../../validation/update_task_sms_validation");
//const update_report_validation = require("../../validation/update_report_validation");
const get_report_validation = require("../../validation/get_report_validation");
const get_task_validation = require("../../validation/get_task_validation");
const get_task_sms_validation = require("../../validation/get_task_sms_validation");
const get_report_sms_validation = require("../../validation/get_report_sms_validation");
const update_report_sms_validation = require("../../validation/update_report_sms_validation");
const db = require("../../db_connect/connect");
const main = require('../../logger');

//Start Route - Update Task SMS
router.post(
  "/update_task_sms",
  validator.body(update_report_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Update_task_sms.update_task_sms(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Update Task SMS

//Start Route - Get Report SMS
router.post(
  "/get_report_sms",
  validator.body(get_report_sms_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Get_rep_sms.get_report_sms(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Get Report SMS

//Start Route - Get Task SMS
router.post(
  "/get_task_sms",
  validator.body(get_task_sms_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Get_task_sms.get_task_sms(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Get Task SMS

//Start Route - Update Report SMS
router.post(
  "/update_report_sms",
  validator.body(update_report_sms_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Update_report_sms.update_report_sms(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Update Report SMS
module.exports = router;
