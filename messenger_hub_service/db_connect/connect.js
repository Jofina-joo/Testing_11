const mysql = require('mysql2/promise');
const config = require('./config');
const main = require('../logger');
const pool = mysql.createPool(config.db);
var logger_all = main.logger_all
async function query(sql, params) {
  logger_all.info("[SQL QUERY] : " + sql);

  const [rows, fields] = await pool.execute(sql, params);
  logger_all.info("[SQL QUERY RESULT] : " + JSON.stringify(rows));
  return rows;
}

module.exports = {
  query
}