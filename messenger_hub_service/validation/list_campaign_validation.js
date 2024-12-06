/*
It is used to one of which is user input validation
List Campaign function to validate the user.
Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const Joi = require("@hapi/joi");

const CampaignListSchema = Joi.object().keys({
    user_id: Joi.string().optional().label("User Id"),
    selected_user_id: Joi.string().optional().label("Selected User Id"),
    user_product: Joi.string().required().label("User Product"),
     date_filter: Joi.string().optional().label("Date Filter")
}).options({ abortEarly: false });

//Exports the ListCampaign module
module.exports = CampaignListSchema
