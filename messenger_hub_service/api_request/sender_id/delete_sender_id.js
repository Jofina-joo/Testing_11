/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used to delete sender ID

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

//import the required packages and files
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

//Start Function - Delete Sender ID
async function delete_sender_id(req) {
    var logger_all = main.logger_all
    var logger = main.logger

    //Get all request data
    let user_id;
    var sender_id = req.body.sender_id;
    try {
        user_id = req.body.user_id;
        logger_all.info("[Select query request] : " + `SELECT * FROM sender_id_master where user_id = '${user_id}' AND sender_id = '${sender_id}' AND sender_id_status != 'D'`);

        //Query to get data if sender ID not equal to 'D'
        var select_sender_id = await db.query(`SELECT * FROM sender_id_master where user_id = '${user_id}' AND sender_id = '${sender_id}' AND sender_id_status != 'D'`);
        logger_all.info("[Select query response] : " + JSON.stringify(select_sender_id))

        //Check if selected data length is equal to zero, send failure response 'Sender ID not found.'
        if (select_sender_id.length == 0) {
            return { response_code: 0, response_status: 201, response_msg: 'Sender ID not found.', request_id: req.body.request_id };
        }
        else {
            //Otherwise update sender ID status as 'D'
            logger_all.info("[update query request] : " + `UPDATE sender_id_master SET sender_id_status = 'D' WHERE sender_id = '${sender_id}' AND sender_id_status != 'D'`);
            var update_sender_id = await db.query(`UPDATE sender_id_master SET sender_id_status = 'D' WHERE sender_id = '${sender_id}' AND sender_id_status != 'D'`);
            logger_all.info("[update query response] : " + JSON.stringify(update_sender_id))
            return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };
        }
    }

    catch (err) {
        logger_all.info(": [delete sender id ] Failed - " + err);
        return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.' };
    }
}
module.exports = {
    delete_sender_id
};
//End Function - Delete Sender ID