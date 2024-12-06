/*
It is used to one of which is user input validation
Sender ID status function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");
const Senderid_status = Joi.object().keys({
  user_id: Joi.string().optional().label("User Id"),
  mobile_number: Joi.string().required("Mobile Number")

}).options({ abortEarly: false });
module.exports = Senderid_status