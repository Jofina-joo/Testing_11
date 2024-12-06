/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in sender ID status functions which is used to get sender ID status.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function - Sender ID status
async function Senderid_status(req) {
  var logger_all = main.logger_all

  //Get all request data
  var mobile_number = req.body.mobile_number;
  let user_id;
  const header_token = req.headers['authorization'];
  try {
    user_id = req.body.user_id;
    logger_all.info("[Select query request] : " + `SELECT * FROM sender_id_master where mobile_no = '${mobile_number}' and sender_id_status = "Y" ORDER BY sender_id DESC`);

    //Query to get active sender ID details
    var select_sender_id = await db.query(`SELECT * FROM sender_id_master where mobile_no = '${mobile_number}' and sender_id_status = "Y" ORDER BY sender_id DESC`);
    logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id))

    //Check if selected data length is equal to zero, send failure response 'No data available.'
    if (select_sender_id.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else {
      //Otherwise send success response
      return { response_code: 1, response_status: 200, response_msg: 'Success', sender_id: select_sender_id };
    }
  }

  catch (err) {
    logger_all.info(": [sender id status ] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  Senderid_status
};
//End Function - Sender ID status