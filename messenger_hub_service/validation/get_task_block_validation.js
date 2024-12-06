/*
It is used to one of which is user input validation
Get Task Block function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const GetBlock = Joi.object().keys({
  mobile_number: Joi.string().required().label("Mobile number"),
  com_msg_block_id: Joi.string().required().label("Compose Block ID"),
  selected_user_id: Joi.string().required().label("Selected user ID"),
}).options({ abortEarly: false });

//Exports the GetBlock module
module.exports = GetBlock