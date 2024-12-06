/*
It is used to one of which is user input validation
Approve whatsapp function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const Approve = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  compose_whatsapp_id: Joi.string().required().label("Compose Whatsapp ID"),
  sender_numbers: Joi.array().required().label("Sender Numbers"),
  selected_user_id: Joi.string().optional().label("Selected userid"),
  isscrub: Joi.bool().optional().label("isscrub"),
  request_id: Joi.string().required().label("Request ID"),

}).options({ abortEarly: false });

//Exports the Approve whatsapp module
module.exports = Approve