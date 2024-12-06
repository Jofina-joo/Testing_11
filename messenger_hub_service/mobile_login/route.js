/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This API is used to the mobile login function.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 22-Nov-2023
*/

// Import the required packages and libraries
const express = require("express");
const router = express.Router();
const Login = require("./login");
const validator = require('../validation/middleware')

const LoginValidation = require("../validation/mobile_login_validation");
const main = require('../logger')
const db = require("../db_connect/connect");

//Start Route - Mobile Login
router.post(
  "/",
  validator.body(LoginValidation),
  async function (req, res, next) {
    try {
      var logger = main.logger

      var result = await Login.login(req);

      logger.info("[API RESPONSE] " + JSON.stringify(result))

      res.json(result);
    } catch (err) {
      console.error(`Error while getting data`, err.message);
      next(err);
    }
  }
);
//End Function - Mobile Login

module.exports = router;