/*
It is used to one of which is user input validation.
ReportSchema function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

// Declare ReportSchema object
const ReportSchema = Joi.object().keys({

  //Define object	properties
  user_id: Joi.string().optional().label("User Id"),
  date_filter: Joi.string().optional().label("Date Filter"),
  status_filter: Joi.string().optional().label("Status Filter"),
  delivery_filter: Joi.string().optional().label("Delivery Filter"),
  read_filter: Joi.string().optional().label("Read Filter"),
  user_filter: Joi.string().optional().label("User Filter"),
  user_product: Joi.string().optional().label("User Product"),
  selected_user_id: Joi.string().optional().label("Selected User Id"),
  campaign_id: Joi.string().optional().label("Campaign ID"),
  request_id: Joi.string().optional().label("Request ID"),
}).options({ abortEarly: false });

//Exports the ReportSchema module
module.exports = ReportSchema