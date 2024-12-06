/*
It is used to one of which is user input validation.
GetReport_RCS function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// Declare GetReport_RCS object
const GetReport_RCS = Joi.object().keys({
  mobile_number: Joi.string().required().label("Mobile number"),
  compose_message_id: Joi.string().required().label("Compose message ID"),
  receiver_number: Joi.string().optional().label("Receiver number"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
  request_id: Joi.string().required().label("Request ID")

}).options({ abortEarly: false });

//Exports the GetReport_RCS module
module.exports = GetReport_RCS