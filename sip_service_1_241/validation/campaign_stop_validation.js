const Joi = require("joi");

const LoginSchema = Joi.object().keys(
{
  campaign_id: Joi.number().required().label("campaignId"),
  user_id: Joi.number().required().label("user_id"),
  context_id: Joi.number().required().label("Context Id")
}).options({abortEarly : false});

module.exports = LoginSchema
