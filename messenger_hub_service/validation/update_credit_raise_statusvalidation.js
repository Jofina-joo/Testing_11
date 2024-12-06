/*
It is used to one of which is user input validation
Update credit raise function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare check_available_msg object 
const update_credit_raise_status = Joi.object().keys({
  // Object Properties are define  
  user_id: Joi.string().optional().label("User Id"),
  usrsmscrd_id: Joi.string().required().label("usrsmscrd_id"),
  usrsmscrd_status: Joi.string().required().label("user sms credit status"),
  usrsmscrd_status_comments: Joi.string().required().label("user sms credit comments")
}).options({ abortEarly: false });
// To exports the check_available_msg module
module.exports = update_credit_raise_status