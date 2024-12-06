/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in products functions which is used to get user product.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import necessary modules and dependencies
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start function to get user product
async function product_name(req) {
  var logger_all = main.logger_all
  var logger = main.logger
  // Retrieve the user's bearer token from the request headers
  try {
    var user_id = req.body.user_id;
    var selected_user_id = req.body.select_user_id;
    if (selected_user_id) {
      user_id = selected_user_id;
    }
    // Fetch a product data from the database
    var get_rights_name = `SELECT * from rights_master rm left join user_rights ur on ur.rights_id = rm.rights_id where rights_status = 'Y' and user_id = '${user_id}' group by rights_name`;
    logger_all.info("[Select query request] : " + get_rights_name);
    var product_name = await db.query(get_rights_name);
    logger_all.info("[Select query response] : " + JSON.stringify(product_name))

    //check product length is equal to zero, send error response as 'No data available'
    if (product_name.length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    }
    else {
      //Otherwise send success response with user product data
      return { response_code: 1, response_status: 200, response_msg: 'Success', num_of_rows: product_name.length, product_name: product_name };
    }

  }

  catch (err) {
    logger_all.info("[user product list] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
// End function to get user product
module.exports = {
  product_name
};