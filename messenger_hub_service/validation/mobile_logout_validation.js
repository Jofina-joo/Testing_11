/*
It is used to one of which is user input validation
Mobile Logout function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const LogoutSchema = Joi.object().keys({
    mobile_number: Joi.string().required().label("Mobile Number"),
    request_id: Joi.string().required().label("Request ID")


}).options({ abortEarly: false });

//Exports the MobileLogout module
module.exports = LogoutSchema