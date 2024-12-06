/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the rcs message report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const express = require("express");
const router = express.Router();
require("dotenv").config();
const Update_task_rcs = require("./update_task_rcs");
const Get_rep_rcs = require("./get_report_rcs");
const Get_task_rcs = require("./get_task_rcs");
const Update_report_rcs = require("./update_report_rcs");
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware")

//Process validations
const update_report_validation = require("../../validation/update_report_validation");
const get_task_rcs_validation = require("../../validation/get_task_rcs_validation");
const get_report_rcs_validation = require("../../validation/get_report_rcs_validation");
const update_report_rcs_validation = require("../../validation/update_report_rcs_validation");

const db = require("../../db_connect/connect");
const main = require('../../logger');

//Start route for updating task RCS
router.post(
  "/update_task_rcs",
  validator.body(update_report_validation),
  async function (req, res, next) {
    try {
      const logger = main.logger

// Call the update_task_rcs function to update task RCS
      const result = await Update_task_rcs.update_task_rcs(req);

      logger.info("[API RESPONSE - update task rcs] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for updating task RCS

//Start route for getting report RCS
router.post(
  "/get_report_rcs",
  validator.body(get_report_rcs_validation),
  async function (req, res, next) {
    try {
      const logger = main.logger

// Call the get_report_rcs function to get report RCS
      const result = await Get_rep_rcs.get_report_rcs(req);

      logger.info("[API RESPONSE - get report rcs] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for getting report RCS

//Start route for getting task RCS
router.post(
  "/get_task_rcs",
  validator.body(get_task_rcs_validation),
  async function (req, res, next) {
    try {
      const logger = main.logger

// Call the get_task_rcs function to get task RCS
      const result = await Get_task_rcs.get_task_rcs(req);

      logger.info("[API RESPONSE - get task rcs] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for getting task RCS

//Start route for update report RCS
router.post(
  "/update_report_rcs",
  validator.body(update_report_rcs_validation),
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await Update_report_rcs.update_report_rcs(req);

      logger.info("[API RESPONSE - update report rcs] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for update report RCS

module.exports = router;
