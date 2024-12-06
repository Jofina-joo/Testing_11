/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the client report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 27-Apr-2024
*/

// Import necessary modules and dependencies
const express = require("express");
const router = express.Router();
const Report = require("./client_report");
require("dotenv").config();
const validator = require('../validation/middleware')
const valid_user = require("../validation/cli_valid_user_middleware");
const valid_user_reqID = require("../validation/cli_valid_user_middleware_reqID");

//Process validation
const OtpsummaryreportValidation = require("../validation/summary_report");
const ReportValidation = require("../validation/report_validation");
const cli_report_generate_validation = require("../validation/cli_report_generate_validation");
const smsreport_generate_validation = require("../validation/smsreport_generate_validation");
const rcsreport_generate_validation = require("../validation/rcsreport_generate_validation");
const main = require('../logger');

//Start route for getting a detailed report
router.get(
  "/cl_detailed_report",
  validator.body(ReportValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger

      // Call the 'campaign_report' function from the Report module to get a detailed report
      var result = await Report.cl_campaign_report(req);

      logger.info("[API RESPONSE - detailed report] " + JSON.stringify(result))

      // Send the response in JSON
      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for getting a detailed report

//Start route for generating a summary report
router.get(
  "/cl_summary_report",
  validator.body(OtpsummaryreportValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the OtpSummaryReport function
      var logger = main.logger
      // Call the 'SummaryReport' function from the Report module to generate a summary report
      var result = await Report.cl_SummaryReport(req);
      logger.info("[API RESPONSE - summary report] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for generating a summary report

//Start route for generating a report
router.post(
  "/cl_report_generation",
  validator.body(cli_report_generate_validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger = main.logger
      // Call the 'report_generation' function from the Report module to generate a report
      var result = await Report.cl_report_generation(req);
      logger.info("[API RESPONSE - report generation] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for generating a report

module.exports = router;