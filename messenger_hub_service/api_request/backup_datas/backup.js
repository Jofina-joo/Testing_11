const db = require("../../db_connect/connect");
require("dotenv").config();
const main = require('../../logger')

async function backup_db(req) {
    const logger_all = main.logger_all;

    try {
        var success_message;

        // declare the array
        const array_list_user_id = [];
        const select_query = await db.query(`SELECT user_id FROM user_management where user_status = 'Y'  `);
        logger_all.info("[select query request - select_user_id] : " + `SELECT user_id FROM user_management where user_status = 'Y' `);

        logger_all.info("[select query response - select_user_id] : " + JSON.stringify(select_query));
        if (select_query.length > 0) {
            for (let i = 0; i < select_query.length; i++) {
                array_list_user_id.push(select_query[i].user_id);
            }

            // call the procedure
            const report_query = `CALL backup_data('${array_list_user_id}')`;
            logger_all.info("[Select query request] : " + report_query);
            var backup_db_result = await db.query(report_query);
            logger_all.info("[select query response - get_product_id] : " + JSON.stringify(backup_db_result[0][0]))
            success_message = backup_db_result[0][0]['SUCCESS'];
            if (success_message) {
                return {
                    response_code: 1,
                    response_status: 200,
                    response_msg: 'Success'
                };
            } else {
                return {
                    response_code: 0,
                    response_status: 201,
                    response_msg: 'Error Occurred.'
                };
            }

        } else {
            return {
                response_code: 0,
                response_status: 204,
                response_msg: 'No data available.'
            };
        }
    } catch (err) {
        // Failed - call_index_signin Sign in function
        logger_all.info("[ Move To Archieve Database] Failed - " + err);
        return {
            response_code: 0,
            response_status: 201,
            response_msg: 'Error Occurred.'
        };
    }
}

module.exports = {
    backup_db
};
