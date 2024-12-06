/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This API is used in login functions which is used to login portal

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 16-Nov-2023
*/

// Import the required packages and libraries
const db = require("../db_connect/connect");
const jwt = require("jsonwebtoken");
const md5 = require("md5")
const main = require('../logger')
require("dotenv").config();
const dynamic_db = require("../db_connect/dynamic_connect");
const nodemailer = require('nodemailer');

// Start Function - Login
async function login(req) {
  const logger_all = main.logger_all;
  const logger = main.logger;

  // get current Date and time
  const day = new Date();
  const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();
  const today_time = day.getHours() + ":" + day.getMinutes() + ":" + day.getSeconds();
  const current_date = today_date + ' ' + today_time;

  // get all the req data

  let txt_username = req.body.txt_username;
  let txt_password = md5(req.body.txt_password);
  let request_id = req.body.request_id;

  const header_json = req.headers;
  let ip_address = header_json['x-forwarded-for'];

  // JWT Token Accessing value...
  const user =
  {
    username: req.body.txt_username,
    user_password: req.body.txt_password,
  };
  const accessToken_1 = jwt.sign(user, process.env.ACCESS_TOKEN_SECRET, {
    expiresIn: process.env.ONEWEEK
  });
  const bearer_token = "Bearer " + accessToken_1;

  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address);
  logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address);

  logger.info("[txt_password] " + txt_password);
  try {
    const login_query = `CALL LoginProcedure('${txt_username}', '${txt_password}', '${request_id}','${bearer_token}','${ip_address}','${req.originalUrl}')`;
    logger_all.info("[Select query request] : " + login_query);
    const sql_stat = await db.query(login_query);
    const [results] = sql_stat; // Destructure the single result set
    logger_all.info("results" + JSON.stringify(results));

    // Check if the result set contains any rows
    if (results && results.length > 0) {
      const successMessage = results[0].response_msg;
      const user_id = results[0].user_id;
      const user_master_id = results[0].user_master_id;
      const parent_id = results[0].parent_id;
      const user_name = results[0].user_name;
      const user_status = results[0].user_status;
      return {
        response_code: 1,
        response_status: 200,
        num_of_rows: 1,
        response_msg: successMessage,
        bearer_token,
        user_id,
        user_master_id,
        parent_id,
        user_name,
        user_status
      };
    } else {
      logger_all.info(": [Login] Failed - Error occurred.");

      return {
        response_code: 0,
        response_status: 201,
        response_msg: "Error occurred.",
      };
    }
  } catch (err) {
    // Handle other errors
    logger_all.info(": [Login] Failed - " + err.message);
    return {
      response_code: 0,
      response_status: 201,
      response_msg: err.message,
    };
  }
}
// End Function - Login

// Start function to signup
async function Signup(req) {
  const logger_all = main.logger_all;
  try {
    let user_name = req.body.user_name;
    let user_email = req.body.user_email;
    let login_password = md5(req.body.login_password);
    let user_mobile = req.body.user_mobile;
    let login_id = req.body.login_id;

    const signup_query = `CALL SignUpProcedure('${user_name}', '${user_email}', '${login_password}','${user_mobile}','${login_id}')`;
    logger_all.info("[Select query request] : " + signup_query);
    const sql_stat = await db.query(signup_query);

    const [results] = sql_stat; // Destructure the single result set
    logger_all.info("results" + JSON.stringify(results));

    // Check if the result set contains any rows
    if (results && results.length > 0) {
      const successMessage = results[0].response_msg;
      logger_all.info("[signup] Success" + successMessage);

      return {
        response_code: 1,
        response_status: 200,
        num_of_rows: 1,
        response_msg: successMessage,
      };
    } else {
      logger_all.info(": [signup] Failed - Unknown error occurred.");

      return {
        response_code: 0,
        response_status: 201,
        response_msg: "Error occurred.",
      };
    }
  } catch (err) {
    // Handle other errors
    logger_all.info(": [signup] Failed - " + err.message);
    return {
      response_code: 0,
      response_status: 201,
      response_msg: err.message,
    };
  }
}
// End function to signup

// Reset Password start 

async function ResetPassword(req) {
  const logger_all = main.logger_all;
  const logger = main.logger;

  // Get current date and time
  const day = new Date();
  const today_date = day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate();
  const today_time = day.getHours() + ":" + day.getMinutes() + ":" + day.getSeconds();
  const current_date = today_date + ' ' + today_time;

  // Get all the req data
  const user_emailid = req.body.user_emailid;
  const request_id = req.body.request_id;
  const header_json = req.headers;
  const ip_address = header_json['x-forwarded-for'];

  logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address);
  logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address);

  try {
    logger_all.info("[insert query request] : " + `INSERT INTO api_log VALUES(NULL,0,'${req.originalUrl}','${ip_address}','${request_id}','N','-','0000-00-00 00:00:00','Y',CURRENT_TIMESTAMP)`);
    const insert_api_log = await db.query(`INSERT INTO api_log VALUES(NULL,0,'${req.originalUrl}','${ip_address}','${request_id}','N','-','0000-00-00 00:00:00','Y',CURRENT_TIMESTAMP)`);
    logger_all.info("[insert query response] : " + JSON.stringify(insert_api_log));

    logger_all.info("[select query request] : " + `SELECT * FROM api_log WHERE request_id = '${request_id}' AND response_status != 'N' AND api_log_status='Y'`);
    const check_req_id = await db.query(`SELECT * FROM api_log WHERE request_id = '${request_id}' AND response_status != 'N' AND api_log_status='Y'`);
    logger_all.info("[select query response] : " + JSON.stringify(check_req_id));

    if (check_req_id.length != 0) {
      logger_all.info("[Valid User Middleware failed response] : Request already processed");
      logger.info("[API RESPONSE] " + JSON.stringify({ request_id: req.body.request_id, response_code: 0, response_status: 201, response_msg: 'Request already processed' }));

      return { response_code: 0, response_status: 201, response_msg: 'Request already processed' };
    }

    const reset_pass = `SELECT * FROM user_management WHERE user_email = '${user_emailid}' AND user_status IN ('Y') ORDER BY user_id ASC`;
    logger_all.info("[select query request] : " + reset_pass);
    const sql_stat = await db.query(reset_pass);
    logger_all.info("[select query response] : " + JSON.stringify(sql_stat));

    if (sql_stat.length == 0) {
      logger_all.info(": [Reset Password] Failed - Invalid Email ID. Kindly try again!!");
      return { response_code: 0, response_status: 201, response_msg: "Invalid Email ID. Kindly try again!!", request_id: req.body.request_id };
    } else {
      const user_id = sql_stat[0].user_id;

      const passwordvalue = new Set();

      function generateResetPassword() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let reset_password;

        do {
          reset_password = '';
          for (let i = 0; i < 10; i++) {
            const randomIndex = Math.floor(Math.random() * characters.length);
            reset_password += characters.charAt(randomIndex);
          }
        } while (passwordvalue.has(reset_password));

        passwordvalue.add(reset_password);
        return reset_password;
      }

      const get_resetpassword = generateResetPassword();
      logger_all.info("[Reset Password] : " + get_resetpassword);

      const update_password = `UPDATE user_management SET login_password = '${md5(get_resetpassword)}' WHERE user_id = '${user_id}'`;
      logger_all.info("[Update query request] : " + update_password);
      const set_resetresult = await db.query(update_password);
      logger_all.info("[Update query response] : " + JSON.stringify(set_resetresult));

      const transporter = nodemailer.createTransport({
        service: 'gmail',
        auth: {
          user: 'suhasini.r@yeejai.com', // Your email address
          pass: 'cicauzxsnivlvhvs' // Your email password or app-specific password
        }
      });

      const mailOptions = {
        from: 'suhasini.r@yeejai.com', // Sender's email address and name
        to: user_emailid, // Recipient's email addresses separated by commas
        subject: 'Reset Password', // Email subject
        text: `Dear User,\n\nWelcome to Mobile Marketing \nYour Email ID: ${user_emailid}\nYour Password: ${get_resetpassword}`
      };

      return new Promise((resolve, reject) => {
        transporter.sendMail(mailOptions, async (error, info) => {
          if (error) {
            logger_all.info('Error occurred: Mail cannot be sent. Kindly check!!', error);
            return resolve({ response_code: 0, response_status: 201, response_msg: 'Mail cannot be sent. Kindly check!!' });
          } else {
            logger_all.info('Email sent: New password sent to your email. Kindly verify!!', info.response);
            return resolve({ response_code: 1, response_status: 200, response_msg: 'New password sent to your email. Kindly verify!!', request_id: req.body.request_id });
          }
        });
      });
    }

  } catch (err) {
    logger_all.info(": [Reset Password] Failed - " + err.message);
    return { response_code: 0, response_status: 201, response_msg: 'Error occurred.', request_id: req.body.request_id };
  }
}

// Reset Password end


// using for module exporting

module.exports = {
  login,
  Signup,
  ResetPassword
};
