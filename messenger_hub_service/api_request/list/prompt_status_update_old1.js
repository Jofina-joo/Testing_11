/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in update profile functions which is used to update profile for signup page.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger');
const { sprintf } = require('sprintf-js'); // Import sprintf-js
const { exec } = require('child_process'); // Import exec from child_process
const media_storage = process.env.MEDIA_STORAGE;
const env = process.env;
const util = require('util');
const exec_wait = util.promisify(require('child_process').exec);
const fs = require('fs');
const ffmpeg = require('fluent-ffmpeg');
const path = require('path');

// Prompt_Status_Update - start
async function Prompt_Status_Update(req) {
    const logger_all = main.logger_all;
    const logger = main.logger;
    try {
        // get all the req data
        const prompt_status = req.body.prompt_status;
        const prompt_id = req.body.prompt_id;
        const reason = req.body.reason;
        let update_prompt = '';
        let prompt_name;
        let prompt_location,output_location;

        if (prompt_status === 'R') {
            update_prompt = `UPDATE obd_prompt_masters SET prompt_status = '${prompt_status}', remarks ='${reason}' WHERE prompt_id = '${prompt_id}'`;
            await db.query(update_prompt);
            return { response_code: 1, response_status: 200, response_msg: 'Updated successfully' };
        } else {
            const get_prompt = `SELECT * FROM obd_prompt_masters WHERE prompt_id = '${prompt_id}' AND prompt_status = 'N'`;
            logger_all.info("[Update query request] : " + get_prompt);
            const get_prompt_result = await db.query(get_prompt);
            logger_all.info("[Update query request] : " + JSON.stringify(get_prompt_result));

            if (get_prompt_result.length > 0) {
                prompt_name = get_prompt_result[0].prompt_path;
                prompt_location = `${media_storage}/${prompt_name}`;
              output_location = `${media_storage}/converted_${prompt_name}`;


                // Convert the audio file
                await new Promise((resolve, reject) => {
                    ffmpeg(prompt_location)
                        .audioChannels(1) // Set to mono
                        .audioFrequency(8000) // Set frequency to 8000 Hz
                        .on('end', () => {
                            console.log('Conversion finished successfully');
                            resolve();
                        })
                        .on('error', (err) => {
                            console.error('Error occurred: ' + err.message);
                            reject(err);
                        })
                        .save(output_location);
                });

                logger_all.info("prompt_location" + prompt_location);
                logger_all.info("output_location" + output_location);

      logger_all.info(`First File moving to server - scp ${env.Auth}@${env.Hostname}:${output_location} ${env.Prompt_Location}`);
                var { stdout, stderr } = await exec_wait(`scp ${env.Auth}@${env.Hostname}:${output_location} ${env.Prompt_Location}`);
                var firststderrLines = stderr.split('\n');

                // Filter out non-error messages
                var firsterrorLines = firststderrLines.filter(line => line.toLowerCase().includes('error'));
                if (firsterrorLines.length > 0) {
                    logger.info("[API RESPONSE] " + JSON.stringify({ response_code: 0, response_status: 201, response_msg: 'scp failed' }));
                    return { response_code: 0, response_status: 201, response_msg: 'scp failed' };
                }

            } else {
                return { response_code: 0, response_status: 204, response_msg: 'Prompt not found or inactive' };
            }

            const get_sip_servers = `SELECT * FROM sip_servers WHERE sip_status in ('Y','T','P')`;
            logger_all.info("[Update query request] : " + get_sip_servers);
            const get_sip_servers_result = await db.query(get_sip_servers);
            logger_all.info("[Update query request] : " + JSON.stringify(get_sip_servers_result));

            if (get_sip_servers_result.length > 0) {
                for (const sip_details of get_sip_servers_result) {
                    const scp_command = sprintf(
                        "sudo scp -r '%s' '%s@%s:%s' 2>&1",
                        prompt_location,
                        sip_details.sip_auth,
                        sip_details.sip_host,
                        sip_details.sip_path
                    );

                    logger_all.info(`Executing SCP command: ${scp_command}`);

                    exec(scp_command, (error, stdout, stderr) => {
                        if (error) {
                            logger_all.error(`SCP command failed for server ${sip_details.sip_host}. Error: ${error.message}. Output: ${stderr}`);
                        } else {
                            logger_all.info(`SCP command succeeded for server ${sip_details.sip_host}. Output: ${stdout}`);
                        }
                    });
                }
            } else {
                return { response_code: 0, response_status: 204, response_msg: 'No active SIP servers found' };
            }

            const duration = await getDurationFromFile(prompt_location);
            const response = await duration_func(prompt_status, duration, prompt_id);

            return response;
        }

    } catch (e) { // any error occurs send error response to client
        logger_all.error("[Prompt_Status_Update failed response] : " + e.message);
        return { response_code: 0, response_status: 201, response_msg: 'Error occurred' };
    }
}

function getDurationFromFile(filePath) {
    return new Promise((resolve, reject) => {
        ffmpeg.ffprobe(filePath, (err, metadata) => {
            if (err) {
                reject(err);
                return;
            }
            // Get the duration and round it to a whole number
            const duration = Math.round(metadata.format.duration);
            resolve(duration);
        });
    });
}

async function duration_func(prompt_status, roundedDuration, prompt_id) {
    const update_prompt = `UPDATE obd_prompt_masters SET prompt_status = '${prompt_status}', audio_duration = '${roundedDuration}',approve_date = CURRENT_TIMESTAMP WHERE prompt_id = '${prompt_id}'`;

    const logger_all = main.logger_all;

    logger_all.info("[Update query request] : " + update_prompt);

    const update_prompt_result = await db.query(update_prompt);
    logger_all.info("[Update query request] : " + JSON.stringify(update_prompt_result));

    if (update_prompt_result.affectedRows > 0) {
        return { response_code: 1, response_status: 200, num_of_rows: 1, response_msg: 'Success' };
    } else {
        return { response_code: 0, response_status: 204, response_msg: 'No data available' };
    }
}

// using for module exporting
module.exports = {
    Prompt_Status_Update,
}
