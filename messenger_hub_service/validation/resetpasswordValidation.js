// resetPasswordValidation.js

// Import the required packages and libraries
const Joi = require('@hapi/joi');

// Define the validation schema
const ResetPassword = Joi.object({
    user_emailid: Joi.string().email().required().label("User Email ID"),
    request_id: Joi.string().required().label("Request ID")
}).options({ abortEarly: false });

// Exports the ResetPassword module
module.exports = ResetPassword;