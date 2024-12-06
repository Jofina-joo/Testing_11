const Joi = require("@hapi/joi");

const Compose = Joi.object({
  user_id: Joi.string().optional().label("User ID"),
  request_id: Joi.string().required().label("Request ID"),
  // receiver_numbers:Joi.array().required().label("receiver Numbers"),
  messages: Joi.string().required().label("Messages"),
  message_type: Joi.string().required().valid('text', 'image', 'video').label("Message Type"),
  is_same_msg: Joi.bool().required().label("Is same msg"),
  is_same_media: Joi.bool().optional().label("Is same media"),
  media_url: Joi.string().optional().label("Media URL"),
  receiver_no: Joi.string().allow("").optional().label("receiver Numbers Path"),
  variable_count: Joi.string().required().label("Variable count"),
  //  variable_values: Joi.array().optional().label("Variable values"),

}).options({ abortEarly: false });

module.exports = Compose