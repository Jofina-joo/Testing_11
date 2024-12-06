/*
It is used to one of which is user input validation
Resend function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const resendMSG = Joi.object().keys({
    user_id: Joi.string().optional().label("User ID"),
    sender_numbers: Joi.array().required().label("Sender Numbers"),
    request_id: Joi.string().required().label("Request ID"),
    user_product: Joi.string().required().label("User Product"),
    compose_whatsapp_id: Joi.string().required().label("Compose Whatsapp ID")

}).options({ abortEarly: false });

//Exports the Resend module
module.exports = resendMSG