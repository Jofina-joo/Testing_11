
/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in country list functions which is used to list country details.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

async function language_list(req) {
  const logger_all = main.logger_all
  const logger = main.logger

  try {

    logger_all.info("[Select query request] : " + `SELECT * from master_languages`);
    const language_list = await db.query(`SELECT * from master_languages`);
    logger_all.info("[Select query response] : " + JSON.stringify(language_list))

    if (language_list.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else {
      return { response_code: 1, response_status: 200, response_msg: 'Success', language_list: language_list };
    }

  }

  catch (err) {
    // Failed - call_index_signin Sign in function
    logger_all.info("[language list report] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  language_list
};