/*
It is used to one of which is user input validation
Dashboard function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare DashBoardOtpValidation object
const DashBoardValidation = Joi.object().keys({
  // Object Properties are define
  user_id: Joi.string().optional().label("User Id"),
}).options({ abortEarly: false });
// To exports the DashBoardOtpValidation module
module.exports = DashBoardValidation