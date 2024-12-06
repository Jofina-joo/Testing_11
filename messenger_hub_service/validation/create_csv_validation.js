/*
It is used to one of which is user input validation
Create CSV function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries

const Joi = require("@hapi/joi");

const createCsvValidation = Joi.object().keys({
  user_id: Joi.string().optional().label("User Id"),
  mobile_number: Joi.array().required().label("Mobile Number"),
  request_id: Joi.string().required().label("Request ID"),

}).options({ abortEarly: false });

//Exports the Create CSV module
module.exports = createCsvValidation