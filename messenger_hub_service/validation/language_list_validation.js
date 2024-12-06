/*
It is used to one of which is user input validation
Country list function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const LanguageListSchema = Joi.object().keys({
    user_id: Joi.string().optional().label("User Id"),
    user_product: Joi.string().optional().label("User Product"),
}).options({ abortEarly: false });

//Exports the Language list module
module.exports = LanguageListSchema