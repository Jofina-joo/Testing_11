/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in sender ID list functions which is used to list sender ID.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function - Sender ID List
async function sender_id_list(req) {
  var logger_all = main.logger_all
  try {

    //Call Stored procedure - Sender ID List
    var get_sender_number = `CALL senderid_list('${req.body.user_id}')`;
    logger_all.info("[Select query request] : " + get_sender_number);
    var select_sender_id = await db.query(get_sender_number);
    logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id[0]))

    //Check if response data length is equal to zero, send failure response 'No data available.'
    if (select_sender_id[0].length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else {

      //Otherwise send success response
      return { response_code: 1, response_status: 200, response_msg: 'Success', sender_id: select_sender_id[0] };
    }
  }
  catch (err) {
    logger_all.info(": [sender id list ] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  sender_id_list
};
//End Function - Sender ID List