/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in campaign list functions which is used to list campaign for approve.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function - CampaignListOBD
async function CampaignListOBD(req) {
  // Destructure loggers from main
  const { logger_all, logger } = main;

  try {
    // get current Date and time
    const day = new Date();
    const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();
    const date_filter = req.body.date_filter;
    let call_procedure = '';
    // if date filter is coming this condition is execute.otherwise else is execute. 
    if (date_filter) {
      // date function for looping in one by one date
      const filter_date_1 = date_filter.split("-");
      //Call Stored procedure - OBD_campaign_lists
      call_procedure = `CALL OBD_campaign_lists('${req.body.user_product}','${req.body.user_id}','${filter_date_1[0]}','${filter_date_1[1]}')`;
    } else {
      //Call Stored procedure - OBD_campaign_lists
      call_procedure = `CALL OBD_campaign_lists('${req.body.user_product}','${req.body.user_id}','${today_date}','${today_date}')`
    }
    // Execute the prompt list query.
    let campaign_list_result = await db.query(call_procedure);
    //Check if selected data length is equal to zero, send failure response 'No data available'
    if (campaign_list_result[0].length == 0) {
      return { response_code: 0, response_status: 204, response_msg: 'No data available.' };
    } else {
      //Otherwise send success response
      return { response_code: 1, response_status: 200, response_msg: 'Success', campaign_list: campaign_list_result[0] };
    }

  } catch (err) {
    logger_all.info("[campaign list report] Failed - " + err);
    return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
  }
}
module.exports = {
  CampaignListOBD
};
//End Function - CampaignListOBD