/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the list functions.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const express = require("express");
const router = express.Router();
const List = require("./country_list");
require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");
const valid_user_reqID = require("../../validation/valid_user_middleware_reqID");

const CampaignList = require("./campaign_list");
const CampaignListRep = require("./campaign_list_report");
const ChangePass = require("./change_password");
const view_user = require("./view_user");
const manageusers_list = require("./manageusers_list");
const update_profile = require("./update_profile");
const senderID_process_list = require("./senderID_process_list");
const campaign_list_stop = require("./campaign_list_stop");
const senderID_update_list = require("./senderID_update_list");
const senderID_stop_list = require("./senderID_stop_list");
const Product_Name = require("./products_name");
const approve_reject_onboarding = require("./approve_rej_onboarding");
const approve_prompt_list = require("./approve_prompt_list");
const prompt_list = require("./prompt_list");
const ActivePromptList = require("./active_prompt_list");
const PromptStatusUpdate = require("./prompt_status_update");
const ChannelMasters = require("./channel_masters");
const All_Users_List = require("./all_users_list");
const OBD_CampaignList = require("./obd_campaign_list");
const language_list = require("./language_list");
const location_list = require("./location_list");
const ProcessServersList = require("./process_servers_list");
const WaitingApprovals = require("./waiting_approval_list");
// Validation file 
const PromptListValidation = require("../../validation/prompt_list_validation");
const CountryListValidation = require("../../validation/country_list_validation");
const LanguageListValidation = require("../../validation/language_list_validation");
const LocationListValidation = require("../../validation/location_list_validation");
const ListCampaignValidation = require("../../validation/list_campaign_validation");
const ChangePasswordValidation = require("../../validation/change_pass_validation");
const ViewuserValidation = require("../../validation/view_user_validation");
const ManageUsersListValidation = require("../../validation/manage_user_list_validation");
const update_profile_details_validation = require("../../validation/update_profile_details_validation");
const senderIDProcessValidation = require("../../validation/senderID_process_validation");
const ListValidation = require("../../validation/sender_id_validation");
const senderIDUpdateValidation = require("../../validation/sender_id_update_validation");
const UseridValidation = require("../../validation/user_id_optional_validation");
const approve_reject_onboarding_validation = require("../../validation/approve_reject_onboarding");
const PromptStatusUpdate_Validation = require("../../validation/update_prompt_status_validation");
const CommonValidation = require("../../validation/common_validation");

const main = require('../../logger');
const db = require("../../db_connect/connect");


router.get(
  "/obd_campaign_list",
  validator.body(ListCampaignValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await OBD_CampaignList.OBDcampaign_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/process_server",
  // validator.body(ListCampaignValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await ProcessServersList.process_servers(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);


router.get(
  "/channels",
  validator.body(CommonValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await ChannelMasters.channel_masters(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/get_users",
  validator.body(CommonValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await All_Users_List.AllUsersList(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.put(
  "/update_prompt_status",
  validator.body(PromptStatusUpdate_Validation),
  valid_user_reqID,
  async function (req, res, next) {
    try {// access the ChangePassword function
      const logger = main.logger

      const logger_all = main.logger_all;

      const result = await PromptStatusUpdate.Prompt_Status_Update(req);

      result['request_id'] = req.body.request_id;

      //update api log for failure response
      if (result.response_code == 0) {
        logger.silly("[update query request] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger.silly("[update query response] : " + JSON.stringify(update_api_log))
      }
      else {
        //Otherwise update api log with success response
        logger.silly("[update query request] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger.silly("[update query response] : " + JSON.stringify(update_api_log))
      }

      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/approve_prompt_list",
  validator.body(CommonValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await approve_prompt_list.Approve_Prompt(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/prompt_list",
  validator.body(PromptListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await prompt_list.Prompt_List(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/active_prompt_list",
  validator.body(PromptListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await ActivePromptList.Active_Prompt_List(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/language_list",
  validator.body(LanguageListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger
      const logger_all = main.logger_all

      const result = await language_list.language_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);


router.get(
  "/location_list",
  validator.body(LocationListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger
      const logger_all = main.logger_all

      const result = await location_list.location_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/campaign_list",
  validator.body(ListCampaignValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await CampaignList.campaign_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.get(
  "/country_list",
  validator.body(CountryListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var logger_all = main.logger_all

      var result = await List.country_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
router.get(
  "/campaign_list_report",
  validator.body(ListCampaignValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await CampaignListRep.campaign_list_report(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

router.post(
  "/change_password",
  validator.body(ChangePasswordValidation),
  valid_user_reqID,
  async function (req, res, next) {
    try {// access the ChangePassword function
      var logger = main.logger

      var logger_all = main.logger_all;

      var result = await ChangePass.change_password(req);

      result['request_id'] = req.body.request_id;

      //update api log for failure response
      if (result.response_code == 0) {
        logger.silly("[update query request] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger.silly("[update query response] : " + JSON.stringify(update_api_log))
      }
      else {
        //Otherwise update api log with success response
        logger.silly("[update query request] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger.silly("[update query response] : " + JSON.stringify(update_api_log))
      }

      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

// Start route for view user
router.get(
  "/view_user",
  validator.body(ViewuserValidation),
  // valid_user,
  async function (req, res, next) {
    try {// access the ViewUser function
      var logger = main.logger

      var result = await view_user.ViewUser(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// End route for view user

//Start route for manage users list
router.get(
  "/manageusers_list",
  validator.body(ManageUsersListValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the ManageUsersList function
      var logger = main.logger

      var result = await manageusers_list.ManageUsersList(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for manage users list

//Start route for edit profile - start
router.post(
  "/edit_profile",
  validator.body(update_profile_details_validation),
  // valid_user_reqID,
  async function (req, res, next) {
    try { // access the update_profile_details function
      var logger = main.logger

      var logger_all = main.logger_all;
      var result = await update_profile.UpdateProfileDetails(req);
      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);
    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//Start route for edit profile - end


//Start route for sender id process list
router.get(
  "/senderID_process_list",
  validator.body(senderIDProcessValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the ManageUsersList function
      var logger = main.logger

      var result = await senderID_process_list.senderID_process_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for sender id process list

//Start route for stop campaign list
router.get(
  "/campaign_list_stop",
  validator.body(ListCampaignValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the ManageUsersList function
      var logger = main.logger

      var result = await campaign_list_stop.campaign_list_stop(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for stop campaign list

//Start route for stop campaign list
router.get(
  "/senderID_update_list",
  validator.body(senderIDUpdateValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the ManageUsersList function
      var logger = main.logger

      var result = await senderID_update_list.senderID_update_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for stop campaign list

// Start route for getting a list of products_name
router.get(
  "/products_name",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try {
      var logger = main.logger
      var logger_all = main.logger_all

      // Call the 'products_name' function from the List module to get a list of products_name
      var result = await Product_Name.product_name(req);
      logger.info("[API RESPONSE - country list] " + JSON.stringify(result))
      // Send the response in JSON
      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// End route for getting a list of products_name

//Start route for sender id stop list
router.get(
  "/senderID_stop_list",
  validator.body(senderIDProcessValidation),
  valid_user,
  async function (req, res, next) {
    try {// access the ManageUsersList function
      var logger = main.logger

      var result = await senderID_stop_list.senderID_stop_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {// any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End route for sender id stop list

// approve_reject_onboarding - start
router.post(
  "/approve_reject_onboarding",
  validator.body(approve_reject_onboarding_validation),
  valid_user,
  async function (req, res, next) {
    try { // access the approve_reject_onboarding function
      var logger = main.logger
      var logger_all = main.logger_all;
      var result = await approve_reject_onboarding.ApproveRejectOnboarding(req);

      result['request_id'] = req.body.request_id;

      //update api log for failure response
      if (result.response_code == 0) {
        logger.silly("[update query request] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = '${result.response_msg}' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger.silly("[update query response] : " + JSON.stringify(update_api_log))
      } else {
        //Otherwise update api log with success response
        logger.silly("[update query request] : " + `UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        const update_api_log = await db.query(`UPDATE api_log SET response_status = 'S',response_date = CURRENT_TIMESTAMP, response_comments = 'Success' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
        logger.silly("[update query response] : " + JSON.stringify(update_api_log))
      }

      logger.info("[API RESPONSE] " + JSON.stringify(result))
      res.json(result);

    } catch (err) { // any error occurres send error response to client
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// approve_reject_onboarding - End

router.get(
  "/obd_campaign_list",
  validator.body(ListCampaignValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await OBD_CampaignList.OBDcampaign_list(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);


router.get(
  "/waiting_approvals",
  validator.body(UseridValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await WaitingApprovals.WaitingApprovals(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
module.exports = router;
