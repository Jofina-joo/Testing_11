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
const Prompt_Status = Joi.object().keys({
  // Object Properties are define
  request_id: Joi.string().required().label("Request Id"),
  prompt_id: Joi.string().required().label("Prompt Id"),
  user_id: Joi.string().optional().label("User id"),
  context: Joi.string().required().label("Context"),
  reason: Joi.string().optional().label("Reason"),
  prompt_status: Joi.string().required().label("Prompt Status"),

}).options({ abortEarly: false });
// To exports the Update profile module
module.exports = Prompt_Status