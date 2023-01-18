const express = require('express');
const app = express();
const fs = require('fs');
const portfinder = require('portfinder');
var db = require('./db.js');

let options = {
	key: fs.readFileSync("./server/my_key.key"),
	cert: fs.readFileSync("./server/my_cert.crt")
};
var server = require('https').Server(options,app);
const io = require("socket.io")(server, {
	cors : { origin : "*" }
});
io.on('connection', (socket) => {
	console.log('Client connected!');
	socket.on( 'new_message', function( data ) {
		io.sockets.emit( 'new_message', {
			latitude: data.latitude,
			longitude: data.longitude,
			addDate: data.addDate
		});
	});
});
portfinder.getPort((err, port) => {
	if (err) {
		console.log(err)
	} else {
		var isJunk=0;
		var checkQuery = "select * from activeports where status=1";
		db.query(checkQuery, function (err, result1) {
			if(result1.length==0){
				var checkQuery1 = "select id from activeports where port='"+port+"'";
				db.query(checkQuery1, function (err, result2) {
					if(result2.length>0){
						var isJunk = 1;
					}else{
						var isJunk = 0;
					}
				});
				server.listen(port,() => {
					console.log('listening on :'+port);
					var sql = "INSERT INTO `activeports` (port, status,isJunk) VALUES ("+port+",1,"+isJunk+")";
					db.query(sql, function (err, result) {
						if (err) throw err;
						console.log("1 record inserted");
						db.end();
					});
				});
			}else{
				db.end();
			}
		});
		//db.end();
	}
});


