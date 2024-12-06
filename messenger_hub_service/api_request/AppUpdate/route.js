/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This API is used to the update app version.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 22-Nov-2023
*/

// Import the required packages and libraries

const express = require("express");
const router = express.Router();
require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");
const UpdateVersion = require("./update_version");
const UploadVersion = require("./upload_version");
const App_version_check = require("./app_version_check");
const Update_task_version = require("./update_task_version");
const AppList = require("./app_list");
const UpdateVersionValidation = require("../../validation/update_version_validation");
const UploadVersionValidation = require("../../validation/upload_version_validation");
const update_task_version_validation = require("../../validation/update_task_version_validation");
const app_version_check_validation = require("../../validation/app_version_check_validation");
const main = require('../../logger');
const db = require("../../db_connect/connect");

//Start Function - Update version
router.post(
  "/update_version",
  validator.body(UpdateVersionValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await UpdateVersion.update_version(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Update version

//Start	Function - Upload version
router.post(
  "/upload_version",
  validator.body(UploadVersionValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await UploadVersion.upload_version(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Upload version

//Start	Function - App list
router.get(
  "/app_list",
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await AppList.app_list(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - App list

//Start	Function - Update task version
router.post(
  "/update_task_version",
  validator.body(update_task_version_validation),
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await Update_task_version.update_task_version(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Update task version

//Start	Function - App version check
router.post(
  "/app_version_check",
  validator.body(app_version_check_validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var result = await App_version_check.app_version_check(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - App version check

module.exports = router;

