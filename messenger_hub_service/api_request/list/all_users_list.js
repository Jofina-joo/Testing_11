/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for report.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/


const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

async function AllUsersList(req) {
  const logger_all = main.logger_all
  const logger = main.logger

  try {

    // to get the select query
    const get_allusers = `SELECT * FROM user_management where user_status = 'Y' and user_master_id != '4'`;
    logger_all.info(get_allusers);
    const allusers_results = await db.query(get_allusers);

    logger_all.info("[select query response] : " + JSON.stringify(allusers_results))
    // if number of select_query length  is available then process the will be continued


    if (allusers_results.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else {
      return { response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: allusers_results.length, result: allusers_results };
    }

  }

  catch (err) {
    logger_all.info("[AllUsersList report] Failed - " + err);

    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  AllUsersList
};
