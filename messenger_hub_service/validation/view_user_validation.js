/*It is used to one of which is user input validation.
Signup function to validate the user.

Version : 1.0
Author : Sabena Yasmin (YJ0008)
Date : 04-Nov-2023
*/
// Import the required packages and libraries
const Joi = require("@hapi/joi");
// To declare Viewuser object
const Viewuser = Joi.object().keys({
  // Object Properties are define

  user_id: Joi.string().required().label("User ID")


}).options({ abortEarly: false });
// To exports the Signup module
module.exports = Viewuser