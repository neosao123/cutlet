var mysql = require('mysql');
var connection = mysql.createConnection({
		host: "localhost",
		user: "myvegizc_cutlet",
		password: "cutlet@123",
		database: "myvegizc_fooddelivery"
});
connection.connect(function(err) {
	if (err) throw err;
});
module.exports = connection;