/*
It is used to one of which is user input validation.
SummaryValidation function to validate the user.

Version : 1.0
Author : Madhubala (YJ0009)
Date : 05-Jul-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare SummaryValidation object 
const OTPSummaryValidation = Joi.object().keys({
  // Object Properties are define    
  user_id: Joi.string().optional().label("User Id"),
  date_filter: Joi.string().optional("Date Filter"),
  user_filter: Joi.string().optional("User Filter"),
  campaign_filter: Joi.array().optional("Campaign Filter"),
  user_product: Joi.string().required("User Product"),
  // user_product: Joi.string().optional().valid('sms', 'whatsapp').label("User Product"),
  // filter_department: Joi.string().optional("Filter Department"),
}).options({ abortEarly: false });
// To exports the SummaryValidation module
module.exports = OTPSummaryValidation