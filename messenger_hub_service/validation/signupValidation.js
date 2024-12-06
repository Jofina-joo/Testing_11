/*
It is used to one of which is user input validation.
Signup function to validate the user.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 05-Jul-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare Signup object
const Signup = Joi.object().keys({
  // Object Properties are define
  user_name: Joi.string().required().label("User name"),
  user_email: Joi.string().required().label("User email"),
  login_password: Joi.string().required().label("Login password"),
  user_mobile: Joi.string().required().label("User mobile"),
  login_id: Joi.string().required().label("Login ID"),

}).options({ abortEarly: false });
// To exports the Signup module
module.exports = Signup