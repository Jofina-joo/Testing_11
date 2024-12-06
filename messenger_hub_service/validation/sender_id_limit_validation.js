/*
It is used to one of which is user input validation
Sender ID limit function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const SenderIDSchema = Joi.object().keys({
   user_id: Joi.string().optional().label("User Id"),
   user_product: Joi.string().required().label("User Product")
}).options({ abortEarly: false });

//Exports the SenderID limit module
module.exports = SenderIDSchema