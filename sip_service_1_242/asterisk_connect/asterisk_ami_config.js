const net = require('net');
const Logger = require('../logger');

// Store connected clients
const clients = new Set();

// Asterisk Manager Interface (AMI) configuration
const host = '127.0.0.1'; // AMI host
const port = 5038; // AMI port
const username = 'yeejai'; // AMI username
const password = 'yeejai@123'; // AMI password

// Connect to Asterisk Manager Interface
const connectToAMI = () => {
    const socket = net.createConnection({ host, port }, () => {
        console.log('Connected to AMI successfully');
        Logger.info('Connected to AMI successfully');
        login();
    });

    socket.on('error', (err) => {
        console.error('Error connecting to AMI:', err);
        Logger.error('Error connecting to AMI:', err);
        process.exit(1);
    });

    socket.on('data', (data) => {
        const response = data.toString();
        if (response.includes('Message: Authentication accepted')) {
            console.log('Logged in to AMI successfully');
            Logger.info('Logged in to AMI successfully');
	    clients.add(socket);
        } else {
            console.log('Response from AMI:', response);
	    Logger.info('Response from AMI:', response);
        }
    });

    function login() {
        // Send login information
        socket.write(`Action: Login\r\nUsername: ${username}\r\nSecret: ${password}\r\nEvents: Off\r\n\r\n`);
    }

    // Handle socket disconnections
    socket.on('close', () => {
        console.log('AMI connection closed');
        Logger.info('AMI connection closed');
        clients.delete(socket); // Remove the socket from the clients set
    });

    return socket; // Return the socket object
};

module.exports = {
   connectToAMI,
   clients
};
