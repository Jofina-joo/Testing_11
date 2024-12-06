const winston = require('winston');
const fs = require('fs');

// Create a log directory if it doesn't exist
const logDir = './logs';
if (!fs.existsSync(logDir)) 
{
  fs.mkdirSync(logDir);
}


// Function to create a new log file with a timestamp and rotate when exceeding the file size limit
function createDailyRotateFileTransport(logFileName) 
{
console.log("filename:"+logFileName);
  return new winston.transports.File(
  {
    filename: logFileName,
    format: winston.format.combine
    (
      winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }), // Format timestamp as date and time
      winston.format.timestamp(),
      winston.format.json()
    ),
    maxsize: 20 * 1024 * 1024, // 20MB file size limit
    maxFiles: 5, // Rotate to a new file after 5 log files are created
    tailable: true, // Append to the existing file when rotating
  });
}

// Function to generate a log file name with the current date
function createLogFileName(prefix) 
{
console.log("filename:"+prefix);
  const currentDate = new Date().toISOString().split('T')[0];
  return `${logDir}/${currentDate}_${prefix}.txt`;
}

// Configure Winston for API request logging
const LogFileName = createLogFileName('log');
const Logger = winston.createLogger(
{
  level: 'info', // log level
  transports: 
  [
    //new winston.transports.Console(),
    createDailyRotateFileTransport(LogFileName), // Log API requests with daily rotation
  ],
});


module.exports = Logger;

