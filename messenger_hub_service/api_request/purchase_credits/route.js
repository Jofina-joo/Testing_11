/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the dashboard.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const express = require("express");
const router = express.Router();
// Import the functions page
const PricingSlot = require("./pricing_slot");
const user_sms_credit_raise = require("./user_sms_credit_raise");
const Paymenthistory = require("./payment_history");
const rppayment_user_id = require("./rp_payment_get_user_id");
const update_credit_raise_status = require("./update_credit_raise_status");
const ApprovePayment = require("./approve_payment");
const messagecreditlist = require("./message_credit_list");
const mcreceiveruser = require("./slt_receiver_users");
const addmessagecredit = require("./add_message_credit");
const availablecreditslist = require("./available_credits");
// Import the validation page
const UseridValidation = require("../../validation/user_id_optional_validation");
const update_credit_raise_statusvalidation = require("../../validation/update_credit_raise_statusvalidation");
const AddMessageCreditValidation = require("../../validation/add_message_credit_validation");
// const  approve_payment_validation= require("../../validation/approve_payment_validation");
// Import the default validation middleware
const validator = require('../../validation/middleware');
const valid_user = require("../../validation/valid_user_middleware");
const user_sms_credit_raisevalidation = require("../../validation/user_sms_credit_raisevalidation");
const AvailableCreditsListValidation = require("../../validation/available_credits_validation");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
//Start route for pricing_slot
router.get(
  "/pricing_slot",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await PricingSlot.pricingslot(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for pricing_slot

// user_sms_credit_raise -start
router.post(
  "/user_credit_raise",
  validator.body(user_sms_credit_raisevalidation),
  valid_user,
  async function (req, res, next) {
    try { // access the user_sms_credit_raise function
      var logger = main.logger
      var result = await user_sms_credit_raise.user_credit_raise(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// user_sms_credit_raise - end

// Paymenthistory -start
router.get(
  "/payment_history",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try { // access the Paymenthistory function
      var logger = main.logger

      var result = await Paymenthistory.PaymentHistory(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// Paymenthistory - end

// Rppayment_User_id -start
router.get(
  "/rppayment_user_id",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try { // access the Rppayment_User_id function
      var logger = main.logger
      var result = await rppayment_user_id.Rppayment_User_id(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// Rppayment_User_id - end

// UpdateCreditRaisetatus - start
router.put(
  "/update_credit_raise_status",
  validator.body(update_credit_raise_statusvalidation),
  valid_user,
  async function (req, res, next) {
    try { // access the UpdateCreditRaisestatus function
      var logger = main.logger
      var result = await update_credit_raise_status.UpdateCreditRaisestatus(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);

    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// UpdateCreditRaisetatus - end

// ApprovePayment -start
router.get(
  "/approve_payment",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try { // access the Paymenthistory function
      var logger = main.logger
      var result = await ApprovePayment.approvepayment(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// ApprovePayment - end

// message_credit_list - start
router.get(
  "/message_credit_list",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the MessageCreditList function
      var logger = main.logger

      var result = await messagecreditlist.MessageCreditList(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// message_credit_list - end

// mc_receiver_user - start
router.get(
  "/slt_receiver_user",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the MCReceiverUser function
      var logger = main.logger

      var result = await mcreceiveruser.MCReceiverUser(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// mc_receiver_user - end


// add_message_credit - start
router.post(
  "/add_message_credit",
  validator.body(AddMessageCreditValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try { // access the AddMessageCredit function
      var logger_all = main.logger_all
      var logger = main.logger

      var result = await addmessagecredit.AddMessageCredit(req);
      result['request_id'] = req.body.request_id;
      //check if response code is equal to zero, update api log entry as response_status = 'F',response_comments = 'response msg'
      if (result.response_code == 0) {
        logger_all.info("[update query request - update api log] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - update api log] : " + JSON.stringify(update_api_log))
      }
      else {  // Otherwise update api log entry	as response_status = 'S',response_comments = 'Success'
        logger_all.info("[update query request - update api log] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP,response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response - update api log] : " + JSON.stringify(update_api_log))
      }

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// add_message_credit - end

// available_credits - start
router.get(
  "/available_credits",
  validator.body(AvailableCreditsListValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the AvailableCreditsList function
      var logger = main.logger

      var result = await availablecreditslist.AvailableCreditsList(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// available_credits - end

module.exports = router;