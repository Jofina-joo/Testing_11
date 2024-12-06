// Import environment variables
const env = process.env;

// Configuration object
const config = {
  db: {
    host: env.DB_HOST || '192.168.29.244',         // Database host
    user: env.DB_USER || 'yj_msghub',             // Database user
    password: env.DB_PASSWORD || 'Msg_HUB@2021*!A1_b2&c3', // Database password
    database: env.DB_NAME || 'messenger_hub', // Database name
  },
  listPerPage: Number(env.LIST_PER_PAGE) || 10, // Ensure it's a number
};

// Function to validate configuration values
function validateConfig() {
  if (isNaN(config.listPerPage) || config.listPerPage <= 0) {
    throw new Error(`Invalid LIST_PER_PAGE value: ${env.LIST_PER_PAGE}. It should be a positive number.`);
  }
}

// Validate the configuration
validateConfig();

// Export the configuration object
module.exports = {
  config,
  TotalChannelCount: 20 // Default total channel count
};
