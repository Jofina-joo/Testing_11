/*
It is used to one of which is user input validation
Add group function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

// Declare App_version_check object
const App_version_check = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  app_version:Joi.string().required().label("App Version"),
  request_id: Joi.string().required().label("Request ID")

}).options({abortEarly : false});

//Exports the App_version_check module
module.exports = App_version_check