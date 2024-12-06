/*
It is used to one of which is user input validation.
ChangePassword function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 25-Oct-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare ChangePassword object
const ChangePassword = Joi.object().keys({
  // Object Properties are define
  request_id: Joi.string().required().label("Request ID"),
  user_id: Joi.string().optional().label("User Id"),
  ex_password: Joi.string().required().label("Ex Password"),
  new_password: Joi.string().required().label("New Password"),

}).options({ abortEarly: false });
// To exports the ChangePassword module
module.exports = ChangePassword