/*
It is used to one of which is user input validation
Group List function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const SenderGroupListSchema = Joi.object().keys({
    user_id: Joi.string().optional().label("User Id"),
    sender_id: Joi.string().optional().label("Sender ID"),
}).options({ abortEarly: false });

//Exports the Grouplist module
module.exports = SenderGroupListSchema