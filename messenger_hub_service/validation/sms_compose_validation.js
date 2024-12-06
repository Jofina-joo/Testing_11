/*
It is used to one of which is user input validation
SMS Compose function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const SMS = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  request_id: Joi.string().required().label("Request ID"),
  // receiver_numbers:Joi.array().required().label("receiver Numbers"),
  receiver_nos_path: Joi.string().required().label("receiver Numbers Path"),
  messages: Joi.string().required().label("Messages"),
  message_type: Joi.string().required().label("Message Type"),
  is_same_msg: Joi.bool().required().label("Is same msg"),
  media_url: Joi.string().optional().label("Media URL"),
  receiver_nos_path: Joi.string().required().label("receiver Numbers Path"),
  variable_count: Joi.string().required().label("Variable count"),
  variable_values: Joi.array().optional().label("Variable values"),

}).options({ abortEarly: false });
module.exports = SMS