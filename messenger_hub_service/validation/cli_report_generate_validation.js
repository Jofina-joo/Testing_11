const Joi = require("@hapi/joi");

const reportGenerate = Joi.object().keys({
  receiver_number: Joi.string().optional().label("Receiver number"),
  campaign_id: Joi.string().required().label("Campaign ID"),
  request_id: Joi.string().required().label("Request ID")

}).options({ abortEarly: false });

module.exports = reportGenerate