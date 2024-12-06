/*
It is used to one of which is user input validation
Update version function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const Update_Validation = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  app_update_id: Joi.string().required().label("Version ID"),
  sender_numbers: Joi.array().required().label("Sender Numbers"),
  request_id: Joi.string().required().label("Request ID")

}).options({ abortEarly: false });

//Exports the UpdateVersion module
module.exports = Update_Validation