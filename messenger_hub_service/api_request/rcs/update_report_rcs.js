/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be used.
This API is used in update report which is used to update delivery and read report for RCS messages.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
const main = require('../../logger');
require("dotenv").config();
const env = process.env
const DB_NAME = env.DB_NAME;
// Start function to update report
async function update_report_rcs(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;

    try {
        logger_all.info("[update report rcs] - " + JSON.stringify(req.body));
        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers));

        // Function to date format
        function date_format(originalDateStr) {
            const dateMatch = originalDateStr.match(/(\d{1,2}\/\d{1,2}\/\d{2}, \d{1,2}:\d{2} [APM]+)/);
            if (dateMatch) {
                const datePart = dateMatch[0];
                const originalDate = new Date(datePart + ' UTC'); // Specify UTC timezone
                const formattedDate = originalDate.toISOString().slice(0, 19).replace('T', ' ');
                return formattedDate;
            }
        }

        // Get query parameters
        let { data: str, mobile_number, compose_message_id: compose_id, selected_user_id: user_id, request_id } = req.body;
        //Check if received empty data
        if (str == "" || str == undefined) {
            str = [];
        }
        //Otherwise continue process
        else {
            let rcs_array_data = [];
            let rcs_list = str.split("˜");

            // Loop based on receiver numbers
            for (let j = 0; j < rcs_list.length; j++) {
                let rcs_array = rcs_list[j].split("||");

                // Store receiver numbers to array
                rcs_array_data.push({ number: rcs_array[0] });

                // Array initialization
                rcs_array_data[j].read_date = 'NULL';
                rcs_array_data[j].read_status = 'NULL';
                rcs_array_data[j].delivery_date = 'NULL';
                rcs_array_data[j].delivery_status = 'NULL';

                // Loop for report data
                for (let i = 7; i < rcs_array.length; i++) {
                    // Check report data have undefined value
                    if (rcs_array[i + 1] != undefined) {
                        // Check if 'Received' text is available, get delivered data
                        if (rcs_array[i + 1].includes("Received:")) {
                            const delivery_date = date_format(rcs_array[i + 1]);
                            rcs_array_data[j].delivery_date = delivery_date;
                            rcs_array_data[j].delivery_status = 'Y';
                            logger_all.info("delivery data");
                        }
                        // Check if 'Read' text is available, get read data
                        else if (rcs_array[i + 1].includes("Read:")) {
                            const read_date = date_format(rcs_array[i + 1]);
                            logger_all.info("Read data available");
                            rcs_array_data[j].read_date = read_date;
                            rcs_array_data[j].read_status = 'Y';
                        }
                    } else {
                        logger_all.info("undefined value");
                    }
                }
                let update_data;
                // Loop for update report based on receiver numbers
                for (let k = 0; k < rcs_array_data.length; k++) {
                    update_data = `UPDATE ${DB_NAME}_${user_id}.compose_msg_status_${user_id} SET`;

                    // Check delivery status and read status not equal to null, update delivery and read data
                    if ((rcs_array_data[k].delivery_status != 'NULL') && (rcs_array_data[k].read_status != 'NULL')) {
                        update_data = `${update_data} delivery_status = '${rcs_array_data[k].delivery_status}', delivery_date = '${rcs_array_data[k].delivery_date}', read_status = '${rcs_array_data[k].read_status}', read_date = '${rcs_array_data[k].read_date}'`;
                    } else {
                        // Check if delivery status not equal to null, update delivery data
                        if (rcs_array_data[k].delivery_status != 'NULL') {
                            update_data = `${update_data} delivery_status = '${rcs_array_data[k].delivery_status}', delivery_date = '${rcs_array_data[k].delivery_date}'`;
                        }
                        // Check if read status not equal to null, update read data
                        else if (rcs_array_data[k].read_status != 'NULL') {
                            update_data = `${update_data} read_status = '${rcs_array_data[k].read_status}', read_date = '${rcs_array_data[k].read_date}'`;
                        }
                        // Otherwise update response status
                        else {
                            update_data = `${update_data} response_status=response_status`;
                        }
                    }

                    update_data = `${update_data} WHERE compose_message_id = '${compose_id}' AND sender_mobile_no = '${mobile_number}' AND receiver_mobile_no = '${rcs_array_data[k].number}' AND response_status ='Y'`;

                    logger_all.info("[update query request] : " + update_data);
                    const update_data_result = await db.query(update_data);
                    logger_all.info("[update query response] : " + JSON.stringify(update_data_result));
                }
            }
        }
        //Update on senderid status
        var update_sender_sts = `UPDATE sender_id_master SET sender_id_status = 'Y' WHERE mobile_no='${mobile_number}'`
        logger_all.info("[update query request] : " + update_sender_sts);
        const update_sender_sts_result = await db.query(update_sender_sts);
        logger_all.info("[update query response] : " + JSON.stringify(update_sender_sts_result));

        // Send success response
        logger.info("[API SUCCESS RESPONSE] " + JSON.stringify({ response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id }));
        return { response_code: 1, response_status: 200, response_msg: 'Success', request_id: req.body.request_id };
    } catch (err) {
        logger_all.info(": [update report rcs] Failed - " + err);
        logger.info("[Failed response] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id: req.body.request_id }));

        return { response_code: 0, response_status: 201, response_msg: 'Error Occurred.', request_id };
    }
}

// End function to update report
module.exports = {
    update_report_rcs
};