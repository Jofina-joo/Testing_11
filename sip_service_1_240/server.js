
// Import required modules
const http = require('http'); 	// For creating an HTTPS server
const fs = require('fs');       	// For file system operations
const express = require('express');	// For creating an Express app
const cors = require('cors');		// For enabling Cross-Origin Resource Sharing (CORS)
//const wss = require('./websocket_server'); 	// For the WebSocket Server
const app = express();			// Create an Express app
//const campaign_request = require('./campaign_call');
//const stop_call_request = require('./stop_call');
//const restart_call_request = require('./restart_call');
const bodyParser = require('body-parser');
const  Logger   = require('./logger')
const Calls_Request = require("./api_request/route");
app.use(cors());
require('dotenv').config();

// parse application/x-www-form-urlencoded
app.use(bodyParser.urlencoded({ extended: false }));

// parse application/json
app.use(bodyParser.json());


// Set JSON and URL-encoded request size limits
app.use(express.json({ limit: '200mb' }));
app.use 
(
  express.urlencoded
  ({
      extended: true,
      limit: '200mb',
  })
);


app.get("/", (req, res) => {
  res.json({ message: "ok" });
});

app.use("/", Calls_Request)

//app.use("/campaign_request", campaign_request);

//app.use("/stop_call_request", stop_call_request);

//app.use("/restart_call_request", restart_call_request);


// Path to SSL certificate and key
//const sslCertPath = "/etc/letsencrypt/live/yjtec.in/cert.pem";
//const sslKeyPath = "/etc/letsencrypt/live/yjtec.in/privkey.pem";

// SSL certificate options
//const sslOptions = 
//{
//  cert: fs.readFileSync(sslCertPath),
//  key: fs.readFileSync(sslKeyPath),
//};

// Create an HTTPS server
const httpServer = http.createServer((req, res) => 
{
  res.writeHead(200, { 'Content-Type': 'text/plain' });
  res.end('WebSocket server running');
});


// WebSocket server port
//const port = 5003;


// Handle WebSocket upgrade requests
httpServer.on('upgrade', (request, socket, head) => 
{
  wss.handleUpgrade(request, socket, head, (ws) => 
  {
    wss.emit('connection', ws, request);
  });
});


// Start the API server
const apiPort = 50112;
app.listen(apiPort, () => 
{
  console.log(`API server is listening on http://localhost:${apiPort}`);
});


// // Start the WebSocket server
// httpsServer.listen(port, () => 
// {
// 	Logger.info(`WebSocket server is listening on wss://103.120.178.190:${port}`);
// });
