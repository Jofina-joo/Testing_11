const express = require("express");
const router = express.Router();
require("dotenv").config();
const Backup_DB = require("./backup");
const main = require('../../logger');

// Start route for view user
router.post(
    "/",
    async function (req, res, next) {
        try { // access the ViewUser function
            var logger = main.logger
            var result = await Backup_DB.backup_db(req);
            logger.info("[API RESPONSE] " + JSON.stringify(result))

            res.json(result);
        } catch (err) { // any error occurres send error response to client
            console.error(`Error while getting data`, err.message);
            next(err);
        }
    }
);
// End route for view user

module.exports = router;
