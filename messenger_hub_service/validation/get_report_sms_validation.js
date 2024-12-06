/*
It is used to one of which is user input validation.
GetReportSMS function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// Declare GetReportSMS object
const GetReportSMS = Joi.object().keys({
  //Define object properties
  mobile_number: Joi.string().required().label("Mobile number"),
  compose_message_id: Joi.string().required().label("Compose message ID"),
  receiver_number: Joi.string().optional().label("Receiver number"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
  request_id: Joi.string().required().label("Request ID")

}).options({ abortEarly: false });
//Exports the GetReportSMS module
module.exports = GetReportSMS