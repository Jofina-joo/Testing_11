/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in sender ID limit functions which is used to list sender ID details based on sender ID limits.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start function to retrieve a list of sender IDs limits
async function sender_id_limits(req) {
  var logger_all = main.logger_all;
  var logger = main.logger
  try {
    user_id = req.body.user_id;

    //Call Stored procedure - senderid_limits
    var getsenderlimits = `CALL senderid_limits('${req.body.user_product}')`;
    logger_all.info("[select query getsenderlimits] : " + getsenderlimits);
    const get_senderlimits = await db.query(getsenderlimits);
    logger_all.info("[select query response] : " + JSON.stringify(get_senderlimits));

    //Check if response data length is not equal to zero, send success response
    if (Array.isArray(get_senderlimits) && get_senderlimits.length > 0) {
      // Return a success response if not checking sender id limits
      return {
        response_code: 1,
        response_status: 200,
        response_msg: 'Success',
        num_of_rows: get_senderlimits[0].length,
        sender_id: get_senderlimits[0]
      };
    } else {
      return {
        response_code: 0,
        response_status: 204,
        response_msg: 'No data available.'
      };
    }
  } catch (err) {
    logger_all.info(": [sender id limits ] Failed - " + err);
    return {
      response_code: 0,
      response_status: 201,
      response_msg: 'Error Occurred.'
    };
  }
}

module.exports = {
  sender_id_limits
};
//End function to retrieve a list of sender IDs limits