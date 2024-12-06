/*
It is used to one of which is user input validation
Compose validation function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const Compose = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  request_id: Joi.string().required().label("Request ID"),
  messages: Joi.string().required().label("Messages"),
  message_type: Joi.string().required().label("Message Type"),
  is_same_msg: Joi.bool().required().label("Is same msg"),
  is_same_media: Joi.bool().optional().label("Is same media"),
  media_url: Joi.array().optional().label("Media URL"),
  receiver_nos_path: Joi.string().required().label("receiver Numbers Path"),
  variable_count: Joi.string().required().label("Variable count"),

}).options({ abortEarly: false });

//Exports the Compose module
module.exports = Compose