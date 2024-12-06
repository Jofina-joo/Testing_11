/*
Routes are used in direct incoming API requests to backend resources.
It defines how our application should handle all the HTTP requests by the client.
This page is used to routing the dashboard.

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 30-Sep-2023
*/

// Import the required packages and libraries
const express = require("express");
const router = express.Router();
// Import the functions page
const dashboard_list = require("./dashboard_list");
// Import the validation page
const DashBoardValidation = require("../../validation/dashboard_validation");
// Import the default validation middleware
const validator = require('../../validation/middleware');
const valid_user = require("../../validation/valid_user_middleware");
const main = require('../../logger');

//Start Route -  Dashboard
router.post(
    "/dashboard_list",
    validator.body(DashBoardValidation),
    valid_user,
    async function (req, res, next) {
        try {
            var logger = main.logger
            var result = await dashboard_list.Dash_Board(req);
            logger.info("[API RESPONSE] " + JSON.stringify(result))
            res.json(result);
        } catch (err) { // any error occurres send error response to client
            console.error(`Error while getting data`, err.message);
            next(err);
        }
    }
);
//End Route - Dashboard

module.exports = router;