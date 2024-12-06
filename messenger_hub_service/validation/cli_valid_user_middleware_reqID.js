const db = require("../db_connect/connect");
const jwt = require("jsonwebtoken")
const main = require('../logger');

const VerifyUser = async (req, res, next) => {
    var logger_all = main.logger_all
    var logger = main.logger

    try {
        var header_json = req.headers;
        let ip_address = header_json['x-forwarded-for'];
        var request_id = req.body.request_id;

        logger_all.info("request ID : " + request_id)

        logger.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address)
        logger_all.info("[API REQUEST] " + req.originalUrl + " - " + JSON.stringify(req.body) + " - " + JSON.stringify(req.headers) + " - " + ip_address)

        var user_id;
        const bearerHeader = req.headers['authorization'];


        logger_all.info("[insert query request] : " + `INSERT INTO api_log VALUES(NULL,'00','${req.originalUrl}','${ip_address}','${req.body.request_id}','N','-','0000-00-00 00:00:00','Y',CURRENT_TIMESTAMP)`);
        const insert_api_log = await db.query(`INSERT INTO api_log VALUES(NULL,'00','${req.originalUrl}','${ip_address}','${req.body.request_id}','N','-','0000-00-00 00:00:00','Y',CURRENT_TIMESTAMP)`);
        logger_all.info("[insert query response] : " + JSON.stringify(insert_api_log))
        //Query to get request id
        logger_all.info("[select query request] : " + `SELECT * FROM api_log WHERE request_id = '${req.body.request_id}' AND response_status != 'N' AND api_log_status='Y'`);
        const check_req_id = await db.query(`SELECT * FROM api_log WHERE request_id = '${req.body.request_id}' AND response_status != 'N' AND api_log_status='Y'`);
        logger_all.info("[select query response] : " + JSON.stringify(check_req_id));
        //check if request id length is not equal to zero, send error response Request already processed
        if (check_req_id.length != 0) {

            logger_all.info("[Valid User Middleware failed response] : Request already processed");
            logger_all.info("[update query request] : " + `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Request already processed' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            //Query to update api log for response comments
            const update_api_log = await db.query(`UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP,response_comments = 'Request already processed' WHERE request_id = '${req.body.request_id}' AND response_status = 'N'`);
            logger_all.info("[update query response] : " + JSON.stringify(update_api_log))
            logger.info("[API RESPONSE] " + JSON.stringify({ request_id: req.body.request_id, response_code: 201, response_status: 'Failed', response_msg: 'Request already processed' }))
            return res.json({ response_code: 201, response_status: 'Failure', response_msg: 'Request already processed', request_id: req.body.request_id });

        }

        if (bearerHeader) {

            var user_bearer_token = bearerHeader.split('Bearer ')[1];

            var check_bearer = `SELECT * FROM user_management WHERE user_bearer_token = '${bearerHeader}' AND user_status = 'Y'`;
            var invalid_msg = 'Invalid Token';
            if (req.body.user_id) {
                check_bearer = check_bearer + ' AND user_id = ' + req.body.user_id;
                invalid_msg = 'Invalid token or User ID'
            }

            logger_all.info("[select query request] : " + check_bearer);
            const check_bearer_response = await db.query(check_bearer);
            logger_all.info("[select query response] : " + JSON.stringify(check_bearer_response));

            if (check_bearer_response.length == 0) {

                const insert_log = `INSERT INTO api_log VALUES(NULL,'00','${req.originalUrl}','${ip_address}','${request_id}','F','${invalid_msg}',CURRENT_TIMESTAMP,'Y',CURRENT_TIMESTAMP)`
                logger_all.info("[insert query request] : " + insert_log);
                const insert_log_result = await db.query(insert_log);
                logger_all.info("[insert query response] : " + JSON.stringify(insert_log_result))

                var response_json = { request_id: request_id, response_code: 403, response_status: 'Failed', response_msg: invalid_msg }
                logger_all.info("[API RESPONSE] " + JSON.stringify(response_json))
                logger.info("[API RESPONSE] " + JSON.stringify(response_json))

                return res
                    .status(403)
                    .send(response_json);
            }
            else {
                logger.info("right")
                user_id = check_bearer_response[0].user_id;

                const update_log_user = `UPDATE api_log SET user_id='${user_id}' WHERE request_id = '${request_id}' AND response_status = 'N'`
                logger_all.info("[update query request] : " + update_log_user);
                const update_log_user_result = await db.query(update_log_user);
                logger_all.info("[update query response] : " + JSON.stringify(update_log_user_result))

                try {

                    jwt.verify(user_bearer_token, process.env.ACCESS_TOKEN_SECRET);
                    req['body']['user_id'] = user_id;
                    next();
                } catch (e) {

                    logger_all.info("[Validate user error] : " + e);

                    const update_logout = `UPDATE user_log SET user_log_status = 'O',logout_time = CURRENT_TIMESTAMP WHERE  user_id = '${user_id}'`
                    logger_all.info("[update query request] : " + update_logout);
                    var update_logout_result = await db.query(update_logout);
                    logger_all.info("[update query Response] : " + JSON.stringify(update_logout_result));

                    const update_log = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = 'Token expired' WHERE request_id = '${request_id}' AND response_status = 'N'`
                    logger_all.info("[update query request] : " + update_log);
                    const update_api_log = await db.query(update_log);
                    logger_all.info("[update query response] : " + JSON.stringify(update_api_log))

                    var response_json_3 = { request_id: request_id, response_code: 403, response_status: 'Failed', response_msg: 'Token expired' }
                    logger_all.info("[API RESPONSE] " + JSON.stringify(response_json_3))
                    logger.info("[API RESPONSE] " + JSON.stringify(response_json_3))

                    return res
                        .status(403)
                        .send(response_json_3);
                }

            }
        }
        else {
            const update_log = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = 'Token is required' WHERE request_id = '${request_id}' AND response_status = 'N'`
            logger_all.info("[update query request] : " + update_log);
            const update_api_log = await db.query(update_log);
            logger_all.info("[update query response] : " + JSON.stringify(update_api_log))

            var response_json_4 = { request_id: request_id, response_code: 403, response_status: 'Failed', response_msg: 'Token is required' }
            logger_all.info("[API RESPONSE] " + JSON.stringify(response_json_4))
            logger.info("[API RESPONSE] " + JSON.stringify(response_json_4))

            return res
                .status(403)
                .send(response_json_4);
        }
    }

    catch (e) {
        logger_all.info("[Validate user error] : " + e);
        const update_log = `UPDATE api_log SET response_status = 'F',response_date = CURRENT_TIMESTAMP, response_comments = 'Error occurred' WHERE request_id = '${request_id}' AND response_status = 'N'`
        logger_all.info("[update query request] : " + update_log);
        const update_api_log = await db.query(update_log);
        logger_all.info("[update query response] : " + JSON.stringify(update_api_log))

        var response_json_5 = { request_id: request_id, response_code: 201, response_status: 'Failure', response_msg: 'Error occurred' }
        logger_all.info("[API RESPONSE] " + JSON.stringify(response_json_5))
        logger.info("[API RESPONSE] " + JSON.stringify(response_json_5))
        res.json(response_json_5);
    }
}
module.exports = VerifyUser;