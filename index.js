var app = require('express')();
var http = require('http').createServer(app);
var io = require('socket.io')(http);
var redis = require("redis"),
    client = redis.createClient();

app.get('/', function(req, res){
  res.send('<h1>Hello world</h1>');
});

io.on('connection', function(socket){
  client.on('message', function(channel, message) {
    socket.emit('update', message);
  });
  client.subscribe("goober");
  socket.on('disconnect', function(){
    console.log('user disconnected');
  });
});

http.listen(3000, function(){
  console.log('listening on *:3000');
});
