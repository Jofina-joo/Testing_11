// Import the 'mysql2/promise' library
const mysql = require('mysql2/promise');
const Logger = require('./../logger');

// Database connection configuration
const dbConfig = {
  host: '192.168.29.244',       // Database host
  user: 'yj_msghub',               // Database user
  password: 'Msg_HUB@2021*!A1_b2&c3',    // Database user password
  database: 'messenger_hub',   // Database name
};

// Function to connect to the database asynchronously
async function connectToDatabase() {
  try {
    // Create a new database connection
    const connection = await mysql.createConnection(dbConfig);
    Logger.info('Database connection established successfully.');
    return connection;
  } catch (error) {
    // Log the error and rethrow it
    Logger.error('Error connecting to the database:', error);
    throw error;
  }
}

// Export the 'connectToDatabase' function to make it accessible from other modules
module.exports = {
  connectToDatabase,
};
