/*
It is used to one of which is user input validation.
smsreportGenerate function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

// Declare smsreportGenerate object
const smsreportGenerate = Joi.object().keys({

  //Define object	properties
  receiver_number: Joi.string().optional().label("Receiver number"),
  compose_message_id: Joi.string().required().label("Compose message ID"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
  request_id: Joi.string().required().label("Request ID"),

}).options({ abortEarly: false });

//Exports the smsreportGenerate module
module.exports = smsreportGenerate