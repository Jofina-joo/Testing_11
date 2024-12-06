/*
It is used to one of which is user input validation.
FilterCampName function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare check_available_msg object 
const user_sms_credit_raise = Joi.object().keys({
  // Object Properties are define  
  user_id: Joi.string().required().label("User Id"),
  parent_id: Joi.string().required().label("Parent Id"),
  pricing_slot_id: Joi.string().required().label("Pricing Slot Id"),
  exp_date: Joi.string().required().label("Expiry date"),
  slt_expiry_date: Joi.string().required().label("Slot Expiry date"),
  raise_sms_credits: Joi.string().required().label("Raise sms Credits"),
  sms_amount: Joi.string().required().label("Sms Amount"),
  paid_status_cmnts: Joi.string().optional().label("Paid Status comments"),
  paid_status: Joi.string().required().label("Paid Status"),
  usrcrdbt_comments: Joi.string().required().label("User credit comments"),

}).options({ abortEarly: false });
// To exports the check_available_msg module
module.exports = user_sms_credit_raise