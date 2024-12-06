/*
It is used to one of which is user input validation
Get Report function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const GetReport = Joi.object().keys({
  mobile_number: Joi.string().required().label("Mobile number"),
  compose_whatsapp_id: Joi.string().required().label("Compose whatsapp ID"),
  receiver_number: Joi.string().optional().label("Receiver number"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
  request_id: Joi.string().required().label("Request ID")

}).options({ abortEarly: false });

//Exports the GetReport module
module.exports = GetReport