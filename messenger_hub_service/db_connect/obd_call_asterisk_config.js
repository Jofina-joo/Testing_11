const AsteriskManager = require('asterisk-manager');

// Function to connect to an Asterisk server and check its status
const checkServerStatus = (server) => {
  return new Promise((resolve) => {
    const ami = new AsteriskManager(server.port, server.host, server.username, server.password, true);

    ami.keepConnected();

    ami.on('connect', () => {
      console.log(`Connected to Asterisk server ${server.host}`);
      
      ami.action({ action: 'Ping' }, (err, res) => {
        if (err) {
          console.error(`Error pinging server ${server.host}:`, err);
          resolve({ server: server.host, success: false, error: err });
        } else {
          console.log(`Response from server ${server.host}:`, res);
          resolve({ server: server.host, success: true, response: res });
        }
        ami.disconnect();
      });
    });

    ami.on('error', (err) => {
      console.error(`Error connecting to server ${server.host}:`, err);
      resolve({ server: server.host, success: false, error: err });
    });
  });
};

// Check the status of all servers
const checkAllServers = async (servers) => {
  const promises = servers.map(server => checkServerStatus(server));
  try {
    const results = await Promise.all(promises);
    console.log('All server statuses:', results);
    return results; // Return the results to be used in main.js
  } catch (err) {
    console.error('Error checking server statuses:', err);
    throw err; // Throw the error to be caught in main.js
  }
};

module.exports = {
  checkAllServers,
};
