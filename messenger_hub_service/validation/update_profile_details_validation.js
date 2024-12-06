/*
It is used to one of which is user input validation.
Update profile function to validate the user.

Version : 1.0
Author : Sabena Yasmin (YJ0008)
Date : 04-Nov-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare Signup object
const update_profile = Joi.object().keys({
  // Object Properties are define
  // request_id: Joi.string().required().label("Request ID"),
  user_name: Joi.string().required().label("User name"),
  user_id: Joi.string().required().label("User id"),
  user_email: Joi.string().required().label("User email"),
  user_mobile: Joi.string().required().label("User mobile"),
  login_id: Joi.string().required().label("Login ID"),

}).options({ abortEarly: false });
// To exports the Update profile module
module.exports = update_profile