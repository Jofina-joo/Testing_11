/*
It is used to one of which is user input validation.
AvailableCreditsList function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare AvailableCreditsList object 
const AvailableCreditsList = Joi.object().keys({
  // Object Properties are define
  user_id: Joi.string().optional().label("User Id"),
  select_user_id: Joi.string().optional().label(" Select User Id"),
  product_id: Joi.string().optional().label("Product Id"),
}).options({ abortEarly: false });
// To exports the AvailableCreditsList module
module.exports = AvailableCreditsList