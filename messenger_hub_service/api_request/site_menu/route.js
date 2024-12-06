/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the product menu.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const express = require("express");
const router = express.Router();
require("dotenv").config();
const validator = require('../../validation/middleware')
const valid_user = require("../../validation/valid_user_middleware");

const Product = require("./product_menu");
const CampaignListValidation = require("../../validation/menu_list_validation");

const main = require('../../logger');

router.get(
  "/product_menu",
  validator.body(CampaignListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      const result = await Product.product_menu(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);

// Start route to retrieve the product header
router.get(
  "/product_header",
  validator.body(CampaignListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      // Call the product_header function to fetch the user's product header
      const result = await Product.product_header(req);

      logger.info("[API RESPONSE - product header] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// End route to retrieve the product header

// Start route to retrieve the Waiting approval Count
router.get(
  "/waiting_approval",
  //validator.body(CampaignListValidation),
  valid_user,
  async function (req, res, next) {
    try {
      const logger = main.logger

      // Call the Waiting_Approval function to fetch the user's product header
      const result = await Product.Waiting_Approval(req);

      logger.info("[API RESPONSE - Waiting_Approval] " + JSON.stringify(result))

      res.json(result);

    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
// End route to retrieve the Waiting_Approval

module.exports = router;