/*
It is used to one of which is user input validation
Send message function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const SendMsg = Joi.object().keys({
  user_id: Joi.string().required(),
  api_key: Joi.string().required(),
  db_name: Joi.string().required(),
  table_names: Joi.array().required(),
  compose_whatsapp_id: Joi.string().required(),
  sender_numbers: Joi.array().required(),
  mobile_numbers: Joi.array().required(),
  mobile_numbers_insertid: Joi.array().required(),
  messages: Joi.array().required(),

}).options({ abortEarly: false });

//Exports the SendMSG module
module.exports = SendMsg