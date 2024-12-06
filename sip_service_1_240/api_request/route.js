const express = require("express");
const router = express.Router();
const Campaign_Call = require("./campaign_call");
const Generate_Call = require("./generate_call");
const Restart_Call = require("./restart_call");
const Stop_Call = require("./stop_call");
const { exec } = require('child_process');
const Campaign_Validation = require("../validation/campaign_validation");
const Campaign_Stop_Validation = require("../validation/campaign_stop_validation");
const validator = require("../validation/middleware");
const Logger = require('../logger');
const safeStringify = require('flatted').stringify; // Use Flatted to handle circular JSON
const util = require('util');

// Logging middleware
router.use((req, res, next) => {
  try {
    if (Logger && Logger.info) {
      Logger.info(`[API REQUEST] ${req.method} ${req.originalUrl} with data: ${JSON.stringify(req.body)}`);
    }
  } catch (err) {
    next(err); // Pass any logging errors to the error handler
  }
  next();
});

// Centralized error handler middleware
const errorHandler = (err, req, res, next) => {
  if (Logger && Logger.error) {
    Logger.error(`[API ERROR] ${err.message}`);
  }
  res.status(err.status || 500).json({ error: err.message });
};

// Utility function for response logging
const logResponse = (res, result) => {
  try {
    if (Logger && Logger.info) {
      Logger.info(`[API RESPONSE] ${util.inspect(result)}`);
    }
  } catch (err) {
    // Handle logging errors
    console.error('Error logging response:', err);
  }
  res.json(result);
};

router.post(
  "/campaign_request",
  validator.body(Campaign_Validation),
  async (req, res, next) => {
    try {
      const result = await Campaign_Call.campaign_call(req, res, next);
      logResponse(res, result);
    } catch (err) {
      next(err);
    }
  }
);

router.post("/generate_call", async (req, res, next) => {
  try {
    const result = await Generate_Call.generate_call(req.body);
    logResponse(res, result);
  } catch (err) {
    next(err);
  }
});

router.post(
  "/stop_call",
  validator.body(Campaign_Stop_Validation),
  async (req, res, next) => {
    try {
      const result = await Stop_Call.stop_calls(req, res, next);
      logResponse(res, result);
      exec('pm2 restart Messenger_Hub', (error, stdout, stderr) => {
        if (error || stderr) {
          console.error(`Error: ${error ? error.message : stderr}`);
          Logger.info(`Error: ${error ? error.message : stderr}`);
        } else {
          Logger.info(`stdout: ${stdout}`);
        }
      });
    } catch (err) {
      next(err);
    }
  }
);

router.post(
  "/restart_call",
  validator.body(Campaign_Validation),
  async (req, res, next) => {
    try {
      const result = await Restart_Call.Restart_Calls(req, res, next);
      console.log(result)
      await logResponse(res, result);
    } catch (err) {
      console.log(err)
      next(err);
    }
  }
);

// Use the centralized error handler
router.use(errorHandler);

module.exports = router;