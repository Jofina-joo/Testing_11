/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in sender ID update list functions which is used to list updated sender ID.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function to update sender ID
async function senderID_update_list(req) {

  var logger_all = main.logger_all
  var response_array = [];
  try {
    //Query to get sender ID details based on campaign
    var get_sender_number = `CALL senderid_update_list('${req.body.user_id}', '${req.body.app_update_id}')`;

    logger_all.info("[select query request - sender ID] : " + get_sender_number);
    const get_sender_number_result = await db.query(get_sender_number);
    logger_all.info("select query response - sender ID" + JSON.stringify(get_sender_number_result[0][0].json_response));

    //check if array length is zero, send failure response
    //check if array length is zero, send failure response
    if (get_sender_number_result[0][0].json_response) {
      const jsonData = get_sender_number_result[0][0].json_response;

      // Process the "process", "notUpdated", and "updated" arrays
      const processedArray = (jsonData.process[0] || '').split(',').filter(Boolean);
      const notupdatededArray = (jsonData.notUpdated[0] || '').split(',').filter(Boolean);
      const updatedArray = (jsonData.updated[0] || '').split(',').filter(Boolean);
      console.log('Received JSON object:', jsonData); // Log the JSON object

      return {
        response_code: 1,
        response_status: 200,
        response_msg: 'Success',
        process: processedArray.length > 0 ? processedArray : [],
        notUpdated: notupdatededArray.length > 0 ? notupdatededArray : [],
        updated: updatedArray.length > 0 ? updatedArray : []
      };
    }
    else {

      return {
        response_code: 0,
        response_status: 204,
        response_msg: 'No Data Available.'
      };
    }
  }
  catch (err) {
    logger_all.info(": [sender id update list ] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
//End Function to update sender ID

module.exports = {
  senderID_update_list
};