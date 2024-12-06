const Joi = require("joi");

const CampaignStopSchema = Joi.object().keys(
{
  campaignId: Joi.number().required().label("campaignId"),
  user_id: Joi.number().required().label("user_id"),
  retry_count_value: Joi.number().required().label("retry_count_value"),
  message_type: Joi.string().required().label("message_type"),
  retry_in_millisec: Joi.number().required().label("retry_in_millisec"),
  context_id: Joi.number().required().label("Context Id")
}).options({abortEarly : false});

module.exports = CampaignStopSchema
