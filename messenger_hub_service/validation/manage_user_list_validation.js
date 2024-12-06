/*It is used to one of which is user input validation.
ManageUser function to validate the user.

Version : 1.0
Author : Sabena Yasmin (YJ0008)
Date : 04-Nov-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare ManageUser object
const ManageUser = Joi.object().keys({
  // Object Properties are define
  date_filter: Joi.string().optional().label("Date Filter"),
  user_id: Joi.string().optional().label("User Id"),
  status_filter: Joi.string().optional().label("Status Filter"),
}).options({ abortEarly: false });
// To exports the ManageUser module
module.exports = ManageUser