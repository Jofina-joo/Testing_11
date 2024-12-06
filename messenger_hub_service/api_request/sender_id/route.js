/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the sender ID details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const express = require("express");
const router = express.Router();
const List = require("./sender_id_list");
const Limit = require("./sender_id_limits");
const Delete = require("./delete_sender_id");
const sender_id_status = require("./sender_id_status");
require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
const db = require("../../db_connect/connect");
const ListValidation = require("../../validation/sender_id_validation");
const LimitValidation = require("../../validation/sender_id_limit_validation");
const DeleteValidation = require("../../validation/delete_sender_id_validation");
const sender_idstatusValidation = require("../../validation/sender_idstatusValidation");
const main = require('../../logger');

//Start Route - Sender ID List
router.get(
  "/sender_id_list",
  validator.body(ListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await List.sender_id_list(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result));
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Sender ID List

//Start Route - Sender ID Limits
router.post(
  "/sender_id_limits",
  validator.body(LimitValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Limit.sender_id_limits(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Sender ID Limits

//Start	Route - Delete Sender ID
router.delete(
  "/delete_sender_id",
  validator.body(DeleteValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger_all = main.logger_all
      var logger = main.logger
      var result = await Delete.delete_sender_id(req);
      result['request_id'] = req.body.request_id;
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      if (result.response_code == 0) {
        logger_all.info("[update query request] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger_all.info("[update query response] : " + JSON.stringify(update_api_log))
      }
      else {
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
//End Route - Delete Sender ID

//Start Route - Sender ID status 
router.post(
  "/senderid_status_check",
  validator.body(sender_idstatusValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await sender_id_status.Senderid_status(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Route - Delete Sender ID

module.exports = router;