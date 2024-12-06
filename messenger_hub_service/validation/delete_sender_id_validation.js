/*
It is used to one of which is user input validation
Delete sender ID function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");
const DeleteSender = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  sender_id: Joi.string().required().label("Sender ID"),
  request_id: Joi.string().required().label("Request ID"),

}).options({ abortEarly: false });

//Exports the Delete sender ID module
module.exports = DeleteSender