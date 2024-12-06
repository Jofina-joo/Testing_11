const Logger = require('./../logger');
const { exec } = require('child_process');
const config = require('./../db_connect/config.js');
const net = require('net');
const { connectToDatabase } = require('./../db_connect/db');
const { connectToAMI } = require('./../asterisk_connect/asterisk_ami_config');
const socket = connectToAMI();
require('dotenv').config();
const env = process.env;
const DB_NAME = env.DB_NAME;
const SIP_ID = env.SIP_ID;
const Report_generation = env.Report_generation;
// generate_call function start 
async function generate_call(campaign_id, user_id, status, retry_count_index, message_type, promptSecond) {
  Logger.info("generate call function started");

  const db = await connectToDatabase();

  let call_index = 0, mobile_numbers_index = 0, channel_count_index = 0, originate_count_index = 0, mobile, audio_url, senderNumber, updateCallTime, accountcode, TotalChannelCount, channelCount, originateCommand;

  try {

    let retry_check_value = retry_count_index === 0 ? retry_count_index : retry_count_index - 1;

    // Execute the query with parameters
    const [mobile_numbers] = await db.execute(`SELECT campaignId, accountcode, src, dst, audio_url, name FROM ${DB_NAME}_${user_id}.obd_cdrs_${user_id} WHERE campaignId = '${campaign_id}' AND retry_count = '${retry_check_value}' AND channel_id = '${SIP_ID}' AND cdrs_status ${status}`);

    Logger.info(mobile_numbers.length)
    if (!mobile_numbers || mobile_numbers.length === 0) {
      Logger.info("No mobile numbers found for the given criteria.");
      return 0;
    }

    for (mobile_numbers_index = 0; mobile_numbers_index < mobile_numbers.length; mobile_numbers_index) {
      Logger.info("mobile number loop started");

      let channel_flag = false;

      for (channel_count_index = 0; channel_count_index < 5; channel_count_index++) {
        const activeChannels = await getActiveChannelCount();
        Logger.info(activeChannels);
        TotalChannelCount = config.TotalChannelCount;
        channelCount = TotalChannelCount - activeChannels;

        Logger.info("channelCount - " + channelCount);

        if (channelCount !== 0) {
          channel_flag = true;
          break;
        }
        await sleep(10000);
      }

      if (!channel_flag) {
        Logger.info("All channels are busy terminating the loop process");
        return 0;
      }

      for (originate_count_index = 0; originate_count_index < channelCount; originate_count_index++) {
        if (originate_count_index % 4 === 0) {
          await sleep(1000);
        }

        // Check if mobile_numbers_index is within bounds
        if (mobile_numbers_index >= mobile_numbers.length) {
          break;
        }

        const mobileNumberData = mobile_numbers[mobile_numbers_index];

        // Check if mobileNumberData is defined
        if (!mobileNumberData) {
          Logger.error(`Mobile number data is undefined at index ${mobile_numbers_index}`);
          continue;
        }

        mobile = mobileNumberData.dst;
        audio_url = mobileNumberData.audio_url;
        person_name = mobileNumberData.name;
        senderNumber = mobileNumberData.src;
        accountcode = mobileNumberData.accountcode;
        campaign_id = mobileNumberData.campaignId;

        if (message_type === "Personal") {
          originateCommand = `Action: Originate\r\n` +
            `Channel: SIP/${senderNumber}/+91${mobile}\r\n` +
            `Exten: 1234\r\n` +
            `Context: personalized\r\n` +
            `Account: ${accountcode}\r\n` +
            `Priority: 1\r\n` +
            `Async: true\r\n` +
            `Timeout: 40000\r\n` +
            `ChannelId: ${senderNumber}\r\n` +
            `CallerID: ${mobile}\r\n` +
            `Variable: name=${person_name}\r\n` +
            `Variable: CDR(userfield)=${campaign_id}\r\n\r\n`;
        } else {
          originateCommand = `Action: Originate\r\n` +
            `Channel: SIP/${senderNumber}/+91${mobile}\r\n` +
            `Exten: ${mobile}\r\n` +
            `Context: testing\r\n` +
            `Account: ${accountcode}\r\n` +
            `Priority: 1\r\n` +
            `Async: true\r\n` +
            `Timeout: 40000\r\n` +
            `CallerID: ${mobile}\r\n` +
            `Variable: AUDIO_URL=${audio_url}\r\n` +
            `Variable: ABSOLUTE_TIMEOUT=${promptSecond}\r\n` +
            `Variable: CDR(user_id)=${user_id}\r\n` +
            `Variable: CDR(userfield)=${campaign_id}\r\n\r\n`;
        }

        await socket.write(originateCommand);
        Logger.info(`**************** ${originate_count_index} ***************`);
        Logger.info("Sent campaign_request to SIP:" + originateCommand);

        try {

          if (retry_count_index != 0) {
            updateCallTime = `UPDATE ${DB_NAME}_${user_id}.obd_cdrs_${user_id} SET retry_count = '${retry_count_index}' WHERE campaignId = '${campaign_id}' AND dst = '${mobile}' `;
          } else {
            updateCallTime = `UPDATE ${DB_NAME}_${user_id}.obd_cdrs_${user_id} SET cdrs_status = 'S', retry_count = '${retry_count_index}' WHERE accountcode = '${accountcode}' AND dst = '${mobile}'`;
          }
          Logger.info("Update call status:" + updateCallTime);

          const updateCallTime_result = await db.execute(updateCallTime);
          Logger.info("After Database Update - Status:" + updateCallTime_result);

        } catch (e) {
          Logger.error("Error message", e);
          updateCallTime = `UPDATE ${DB_NAME}_${user_id}.obd_cdrs_${user_id} SET cdrs_status = 'F', retry_count = '${retry_count_index}' WHERE accountcode = '${accountcode}' AND dst = '${mobile}'`;
          const updateCallTimeResult = await db.execute(updateCallTime);
          Logger.info("After Database Update - Status:" + updateCallTimeResult);
        }

        mobile_numbers_index++;
      }
      if (call_index === 0) {
        Logger.info("first 20 sec delay");
        await sleep(20000);
        call_index = 1;
      } else {
        Logger.info("after 12 sec delay");
        await sleep(12000);
      }
    }

    return 1;
  } catch (error) {
    Logger.error('Error:', error);
    throw error;
  }
}

function getActiveChannelCount() {
  return new Promise((resolve, reject) => {
    exec('asterisk -rx "core show channels"', (error, stdout, stderr) => {
      if (error) {
        console.error('Error executing command:', error);
        reject(error);
        return;
      }

      Logger.info('Command output:', stdout);

      const lines = stdout.trim().split('\n');
      Logger.info('Split lines:', lines);

      const activeChannelsLine = lines.find(line => line.includes('active channels'));
      Logger.info('Active channels line:', activeChannelsLine);

      const activeChannels = activeChannelsLine ? parseInt(activeChannelsLine.split(' ')[0]) : 0;
      Logger.info('Active channels:', activeChannels);

      resolve(activeChannels);
    });
  });
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}
// setTimeout(generate_call, 3000);
module.exports = generate_call; 
