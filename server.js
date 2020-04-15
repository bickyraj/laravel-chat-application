const http = require('http');

http.createServer(function(req, res) {
    console.log('here');
}).listen(8081, function(error) {
    console.log('listening to port:8081');
});