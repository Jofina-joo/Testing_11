/*
It is used to one of which is user input validation.
Reject Campaign function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

// Declare Approve object
const Approve = Joi.object().keys({

  //Define object properties
  user_id: Joi.string().optional().label("User ID"),
  compose_message_id: Joi.string().required().label("Compose Message ID"),
  reason: Joi.string().required().label("Rejected reason"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
  product_name: Joi.string().required().label("Product Name"),
  request_id: Joi.string().required().label("Request ID"),
}).options({ abortEarly: false });

//Exports the Reject Campaign module
module.exports = Approve