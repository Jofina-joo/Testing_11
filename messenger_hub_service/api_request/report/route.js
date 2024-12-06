/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const express = require("express");
const router = express.Router();
const Report = require("./report");
const CallHoldingReports = require("./call_holding_reports");
const ObdReport = require("./obd_detailed_report");
const OBDReportGeneration = require("./obd_report_generation");


require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");

//Process validation
const OtpsummaryreportValidation = require("../../validation/summary_report");
const ReportValidation = require("../../validation/report_validation");
const report_generate_validation = require("../../validation/report_generate_validation");
const smsreport_generate_validation = require("../../validation/smsreport_generate_validation");
const rcsreport_generate_validation = require("../../validation/rcsreport_generate_validation");
const main = require('../../logger');

//Start route for getting a report generation
router.post(
  "/report_generation_obd",
  validator.body(ReportValidation),
  //valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      // Call the 'campaign_report' function from the Report module to get a report generation
      const result = await OBDReportGeneration.OBD_Report_Generation(req);

      logger.info("[API RESPONSE - report generation] " + JSON.stringify(result))

      // Send the response in JSON
      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for getting a report generation

//Start route for getting a detailed report
router.get(
  "/detailed_report",
  validator.body(ReportValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      // Call the 'campaign_report' function from the Report module to get a detailed report
      const result = await Report.campaign_report(req);

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

//Start route for getting a obd detailed report
router.get(
  "/obd_detailed_report",
  validator.body(ReportValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      // Call the 'Obd_Detailed_reports' function from the Report module to get a detailed report
      const result = await ObdReport.Obd_Detailed_reports(req);

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

//Start route for getting a detailed report
router.get(
  "/call_holding_report",
  validator.body(ReportValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      // Call the 'campaign_report' function from the Report module to get a detailed report
      const result = await CallHoldingReports.Call_Holding_Reports(req);

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
router.post(
  "/summary_report",
  validator.body(OtpsummaryreportValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the OtpSummaryReport function
      const logger = main.logger
      // Call the 'SummaryReport' function from the Report module to generate a summary report       
      const result = await Report.SummaryReport(req);
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
  "/report_generation",
  validator.body(report_generate_validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      const logger = main.logger
      // Call the 'report_generation' function from the Report module to generate a report
      const result = await Report.report_generation(req);
      logger.info("[API RESPONSE - report generation] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for generating a report


//Start route for generating a sms report
router.post(
  "/report_generation_sms",
  validator.body(smsreport_generate_validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {// access the OtpSummaryReport function
      const logger = main.logger
      const result = await Report.sms_report_generation(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for generating a sms report

//Start route for generating a rcs report
router.post(
  "/report_generation_rcs",
  validator.body(rcsreport_generate_validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {// access the OtpSummaryReport function
      const logger = main.logger
      const result = await Report.rcs_report_generation(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for generating a rcs report

module.exports = router;