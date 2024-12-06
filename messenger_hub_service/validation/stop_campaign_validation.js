/*
It is used to one of which is user input validation
Stop campaign function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const Stop_Campaign = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  sender_numbers: Joi.array().required().label("Sender Numbers"),
  request_id: Joi.string().required().label("Request ID"),
  user_product: Joi.string().required().label("User Product"),
  campaign_name: Joi.string().required().label("Campaign Name"),

}).options({ abortEarly: false });

//Exports the Stop campaign module
module.exports = Stop_Campaign