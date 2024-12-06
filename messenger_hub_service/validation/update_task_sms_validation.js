/*
It is used to one of which is user input validation
Update Task SMS function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const UpdateTaskSMS = Joi.object().keys({
  mobile_number: Joi.string().required().label("Mobile number"),
  compose_message_id: Joi.string().required().label("Compose message ID"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
  data: Joi.string().allow('').required().label("Data"),
  request_id: Joi.string().required().label("Request ID"),
  sms_product_id: Joi.string().required().label("Product ID")

}).options({ abortEarly: false });

//Exports the UpdateTaskSMS module
module.exports = UpdateTaskSMS