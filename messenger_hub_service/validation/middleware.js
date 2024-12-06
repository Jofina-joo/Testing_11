const main = require('../logger')
var logger_all = main.logger_all

exports.body = (schema) => (req, res, next) => {
  console.log(req.body);
  const {
    error
  } = schema.validate(req.body);
  if (error) {
    var error_array = [];
    for (var i = 0; i < error.details.length; i++) {
      error_array.push(error.details[i].message)
    }
    logger_all.info(error_array.push);
    res.status(200)
      .send({ response_code: 0, response_status: 201, response_msg: 'Error occurred', data: error_array });
  } else {
    next();
  }
};