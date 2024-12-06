/*
It is used to one of which is user input validation.
LoginSchema function to validate the user.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 05-Jul-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare LoginSchema object 
const LoginSchema = Joi.object().keys({
  // Object Properties are define  
  request_id: Joi.string().required().label("Request ID"),
  txt_username: Joi.string().required().label("Username"),
  txt_password: Joi.string().required().label("Password"),
}).options({ abortEarly: false });
// To exports the LoginSchema module
module.exports = LoginSchema