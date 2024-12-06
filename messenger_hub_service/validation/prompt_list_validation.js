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
  date_filter: Joi.string().optional().label("Date Filter")
}).options({ abortEarly: false });

//Exports the ReportSchema module
module.exports = ReportSchema