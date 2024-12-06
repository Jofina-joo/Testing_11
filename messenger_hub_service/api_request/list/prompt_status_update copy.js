// Import required packages and libraries
const db = require("../../db_connect/connect");
require("dotenv").config();
const { sprintf } = require('sprintf-js');
const { exec } = require('child_process');
const util = require('util');
const exec_wait = util.promisify(exec);
const ffmpeg = require('fluent-ffmpeg');
const path = require('path');
const main = require('../../logger');

const media_storage = process.env.MEDIA_STORAGE;
const env = process.env;

// Update prompt status
async function Prompt_Status_Update(req) {
// Destructure loggers from main
const { logger_all, logger } = main;
    const { prompt_status, prompt_id, reason } = req.body;

    try {
        // if prompt is Rejected execute the this condition and return Recjected successfully
        if (prompt_status === 'R') {
            await db.query(`UPDATE obd_prompt_masters SET prompt_status = ?, remarks = ? WHERE prompt_id = ?`, [prompt_status, reason, prompt_id]);
            return { response_code: 1, response_status: 200, response_msg: 'Recjected successfully' };
        }
        // Otherwise execute the obd_prompt_masters get the prompt location
        const get_prompt_result = await db.query(`SELECT prompt_path FROM obd_prompt_masters WHERE prompt_id = ? AND prompt_status = 'N'`, [prompt_id]);
        // If get_prompt_result length is zero execute the this condition
        if (get_prompt_result.length === 0) {
            return { response_code: 0, response_status: 204, response_msg: 'Prompt not found.' };
        }
        // Otherwise Get the prompt path
        const prompt_name = get_prompt_result[0].prompt_path;
        const prompt_location = path.join(media_storage, prompt_name);
        // const output_location = path.join(media_storage, `converted_${prompt_name}`);
        // upload audio file is convert to 8000 HZ and Mono type
        // await convertAudio(prompt_location, output_location);
        // using for one server to another server move
        /*const scp_error = await transferFile(output_location, env.Prompt_Location);
        // if any error occured execute the failed condition
        if (scp_error) {
            return { response_code: 0, response_status: 201, response_msg: 'scp failed' };
        }*/
        // converted audio is move to 3 sip servers 
        await transferToSIPServers(prompt_location);
        // calculate the prompt duration seconds
        const duration = await getDurationFromFile(prompt_location);
        // update the prompt status
        return await updatePromptDetails(prompt_status, duration, prompt_id);

    } catch (error) {
        // if any error occured execute the failed condition
        logger_all.error("[Prompt_Status_Update failed response] : " + error.message);
        return { response_code: 0, response_status: 201, response_msg: 'Error occurred' };
    }
}

// Convert audio file
function convertAudio(inputPath, outputPath) {
    return new Promise((resolve, reject) => {
        ffmpeg(inputPath)
            .audioChannels(1)
            .audioFrequency(8000)
            .on('end', () => resolve())
            .on('error', (err) => reject(err))
            .save(outputPath);
    });
}

// Transfer file using SCP
async function transferFile(source, destination) {
    const command = `scp ${source} ${destination}`;
    const { stderr } = await exec_wait(command);
    return stderr.toLowerCase().includes('error');
}

// Transfer file to SIP servers
async function transferToSIPServers(filePath) {
    const sip_servers = await db.query(`SELECT sip_auth, sip_host, sip_path FROM sip_servers WHERE sip_status IN ('Y', 'T', 'P')`);
    sip_servers.forEach(({ sip_auth, sip_host, sip_path }) => {
        const scp_command = sprintf("sudo scp -r '%s' '%s@%s:%s' 2>&1", filePath, sip_auth, sip_host, sip_path);
        exec(scp_command, (error, stdout, stderr) => {
            if (error) {
                main.logger_all.error(`SCP command failed for server ${sip_host}. Error: ${error.message}. Output: ${stderr}`);
            } else {
                main.logger_all.info(`SCP command succeeded for server ${sip_host}. Output: ${stdout}`);
            }
        });
    });
}

// Get audio file duration
function getDurationFromFile(filePath) {
    return new Promise((resolve, reject) => {
        ffmpeg.ffprobe(filePath, (err, metadata) => {
            if (err) return reject(err);
            resolve(Math.round(metadata.format.duration));
        });
    });
}

// Update prompt details using prompt status and duration,approve date.
async function updatePromptDetails(prompt_status, duration, prompt_id) {
    const update_prompt = `UPDATE obd_prompt_masters SET prompt_status = ?, audio_duration = ?, approve_date = CURRENT_TIMESTAMP WHERE prompt_id = ?`;
    const result = await db.query(update_prompt, [prompt_status, duration, prompt_id]);
    return result.affectedRows > 0
        ? { response_code: 1, response_status: 200, num_of_rows: 1, response_msg: 'Success' }
        : { response_code: 0, response_status: 204, response_msg: 'No data available' };
}

// Export module
module.exports = {
    Prompt_Status_Update,
};