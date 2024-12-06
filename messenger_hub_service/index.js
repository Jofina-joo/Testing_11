/*
API that allows your frontend to communicate with your backend server (Node.js) for processing and retrieving data.
To access a MySQL database with Node.js and can be use it.
This is a main page for starting API the process.This page to routing the subpages page and then process are executed.

Version : 1.0
Author : Sabena yasmin P(YJ0008)
Date : 23-Sep-2023
*/

// Import the required packages and libraries
const https = require("https");
const express = require("express");
const dotenv = require('dotenv');
dotenv.config();
// const router = express.Router();
var cors = require("cors");
const { Client, LocalAuth, Buttons, MessageMedia, Location, List } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const qrcode_img = require('qrcode');
const fse = require('fs-extra');
const validator = require('./validation/middleware')
const csv = require("csv-stringify");
const { logger, logger_all } = require('./logger')
const moment = require("moment")
const cron = require('node-cron');


// Database Connections
const app = express();
const port = 10023;
const db = require("./db_connect/connect");

// Process Validations
const valid_user = require("./validation/valid_user_middleware");
const MobileLogin = require("./mobile_login/route");
const Login = require("./login/route");
const Logout = require("./logout/route");
const SenderID = require("./api_request/sender_id/route");
const ListApi = require("./api_request/list/route");
const Report = require("./api_request/report/route");
const Compose = require("./api_request/compose/route");
const SMS_Compose = require("./api_request/sms_compose/route");
const Campaign_list = require("./api_request/approve_user/route");
const Site_Menu = require("./api_request/site_menu/route");
const WP_rep = require("./api_request/wtsp/route");
const SMS_rep = require("./api_request/sms/route");
const RCS_rep = require("./api_request/rcs/route");
const DashboardAPI = require("./api_request/dashboard/route");
const RCS_Compose = require("./api_request/rcs_compose/route");
const Purchasecredit = require("./api_request/purchase_credits/route");
const APPUpdate = require("./api_request/AppUpdate/route");
const Backup_DB = require("./api_request/backup_datas/route");
const cronfolder = require("./api_request/cron/route");
const clientAPI = require("./clientAPI/route");
const clientRep = require("./client_report/route");
const OBD_rep = require("./api_request/obd_compose/route");
const CRON_msg = require("./api_request/cron_msg/route");
var client_data;
const env = process.env

const chrome_path = env.GOOGLE_CHROME;
const waiting_time = env.WAITING_TIME;
const media_storage = env.MEDIA_STORAGE;

const bodyParser = require('body-parser');
const fs = require('fs');

app.use(cors());
//app.use(cors({
//    origin: 'https://yourpostman.in' // Restrict access to this domain only
//}));
app.use(express.json({ limit: '500mb' }));
app.use(
  express.urlencoded({
    extended: true,
    limit: '500mb'
  })
);

app.get("/", (req, res) => {
  res.json({ message: "ok" });
});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// parse application/x-www-form-urlencoded
app.use(bodyParser.urlencoded({ extended: false }));

// parse application/json
app.use(bodyParser.json());

//API initialization
app.use("/login", Login);
app.use("/mobile_login", MobileLogin);
app.use("/logout", Logout);
app.use("/sender_id", SenderID);
app.use("/list", ListApi);
app.use("/report", Report);
app.use("/compose", Compose);
app.use("/sms_compose", SMS_Compose);
app.use("/approve_user", Campaign_list);
app.use("/site_menu", Site_Menu);
app.use("/obd_call", OBD_rep);
app.use("/wtsp", WP_rep);
app.use("/sms", SMS_rep);
app.use("/rcs", RCS_rep);
app.use("/dashboard", DashboardAPI);
app.use("/rcs_compose", RCS_Compose);
app.use("/purchase_credit", Purchasecredit);
app.use("/app_update", APPUpdate);
app.use("/backup_db", Backup_DB);
app.use("/cl_send_msg", clientAPI);
app.use("/client_report", clientRep);

// Schedule a cron job to run every 5 seconds
cron.schedule('0 0 * * *', async () => {
  logger_all.info("Cron Running ");

  try {
    logger_all.info("Calling Folder");
    cronfolder(); // Call the function defined in the route.js file
  } catch (error) {
    logger_all.info("Error in cron job:", error);
    console.error("Error in cron job:", error);
  }
});

// Schedule the task to run every second
/*cron.schedule('* * * * * *', async () => {
  logger_all.info("Cron Running ");

  try {
    logger_all.info("Calling cron send msg every second");
    CRON_msg(); // Call the function defined in the route.js file
  } catch (error) {
    logger_all.error("Error in cron job:", error);
    console.error("Error in cron job:", error);
  }
});*/

cron.schedule('*/5 * * * * *', async () => {
   logger_all.info("Cron Running ");
   try {
     logger_all.info("Calling cron send msg every 5 seconds");
     CRON_msg(); // Call the function defined in the route.js file
   } catch (error) {
     logger_all.error("Error in cron job:", error);
     console.error("Error in cron job:", error);
   }
 });

/*const options = {
  key: fs.readFileSync("/etc/letsencrypt/live/yjtec.in/privkey.pem"),
  cert: fs.readFileSync("/etc/letsencrypt/live/yjtec.in/cert.pem")
};*/

/*https.createServer(options,app)
  .listen(port, function (req, res) {
    logger.info("Server started at port " + port);
  }); 


// httpServer.listen(port, function (req, res) {
  //      logger_all.info("Server started at port " + port);
// });
*/

app.listen(port, () => {
  // getFilesizeInBytes();
  logger.info(`App started listening at http://localhost:${port}`);
});

module.exports = client_data
