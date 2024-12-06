/*
It is used to one of which is user input validation
Compose validation function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const Compose = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  request_id: Joi.string().required().label("Request ID"),
  call_type: Joi.string().required().label("Call Type"),
  language_code: Joi.string().required().label("Lnaguage Code"),
  location: Joi.string().required().label("Location"),
  type: Joi.string().required().label("Type"),
  upload_prompt: Joi.string().optional().label("Upload Prompt"),
  company_name: Joi.string().required().label("Company Name"),
  context: Joi.string().required().label("Context"),
  prompt_remarks: Joi.string().required().label("Prompt Remarkss"),
    prompt_second: Joi.string().optional().label("Prompt Second")
}).options({ abortEarly: false });

//Exports the Compose module
module.exports = Compose
