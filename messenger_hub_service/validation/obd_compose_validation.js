/*
It is used to one of which is user input validation
SMS Compose function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const OBDCALLSIP = Joi.object().keys({
  user_id: Joi.string().optional().label("User ID"),
  request_id: Joi.string().required().label("Request ID"),
  receiver_nos_path: Joi.string().required().label("receiver Numbers Path"),
  message_type: Joi.string().required().label("Message Type"),
  is_same_msg: Joi.bool().required().label("Is same msg"),
  call_retry_count: Joi.string().optional().label("Call Retry Count"),
  retry_time: Joi.string().required().label("Retry Time"),
  slt_context_id: Joi.string().required().label("Slt Context Id"),
  sms_duration: Joi.string().optional().label("SMS Duration"),
  sms_message: Joi.string().optional().label("SMS Message"),
  send_sms: Joi.string().optional().label("SMS send"),
}).options({ abortEarly: false });
module.exports = OBDCALLSIP
