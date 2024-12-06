/*
It is used to one of which is user input validation
Upload version function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const Upload_Validation = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  app_file_name: Joi.string().required().label("App File"),
  app_version: Joi.string().required().label("App Version"),
  request_id: Joi.string().required().label("Request ID")

}).options({ abortEarly: false });

//Exports the Uploadversion module
module.exports = Upload_Validation