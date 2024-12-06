/*
It is used to one of which is user input validation
Logout function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const LogoutSchema = Joi.object().keys({
  user_id: Joi.string().optional().label("User Id"),
  request_id: Joi.string().required().label("Request ID"),

}).options({ abortEarly: false });

//Exports the Logout module
module.exports = LogoutSchema