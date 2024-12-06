/*
It is used to one of which is user input validation.
reportGenerate function to validate the user.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/


// Import the required packages and libraries
const Joi = require("@hapi/joi");

// Declare reportGenerate object
const reportGenerate = Joi.object().keys({

    //Define object	properties
    receiver_number: Joi.string().optional().label("Receiver number"),
    selected_user_id: Joi.string().optional().label("Selected User ID"),
    compose_whatsapp_id: Joi.string().required().label("Compose Whatsapp ID"),
    request_id: Joi.string().required().label("Request ID")
}).options({ abortEarly: false });

//Exports the reportGenerate module
module.exports = reportGenerate