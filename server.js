var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var Redis = require('ioredis');
var redis = new Redis();
var users = [];
var groups = [];

redis.subscribe('private-channel', function () {
    // console.log('private-channel');
});

redis.subscribe('group-channel', function () {
    // console.log('private-channel');
});

redis.on('message', function (channel, message) {
    message = JSON.parse(message);
    if (channel == 'private-channel') {
        if (message.data.data.type == 1) {
            io.to(`${users[message.data.data.receiver_id]}`).emit(channel + ':' + message.event, message.data.data);
        }
    }

    if (channel == 'group-channel') {
        if (message.data.data.type == 2) {
            let socket_id = getSocketIdOfUserInGroup(message.data.data.sender_id, message.data.data.group_id);
            let socket = io.sockets.connected[socket_id];
            socket.broadcast.to('group' + message.data.data.group_id).emit('groupMessage', message.data.data);
            // io.to(`${users[message.data.data.receiver_id]}`).emit(channel + ':' + message.event, message.data.data);
        }

        // if (!checkIfUserExistINGroup(message.data.data.receiver_id, message.data.data.group_id)) {
        // console.log(message.data);
        // }
    }
});

io.on('connection', function (socket) {
    console.log('a user connected');
    socket.on("user_connected", function (user_id) {
        users[user_id] = socket.id;
    });

    socket.on('userMessage', function (data) {
        // io.to(`${users[2]}`).emit('getMessage', data);
        io.emit('getMessage', data);
        socket.broadcast.emit('getMessage', data); //sending to all the client except the sender
        // socket.emit('getMessage', data);
    });

    // instant messaging
    socket.on('privateMessage', function (data) {
        // message = JSON.parse(message);
        if (users[data.receiver_id] != 0) {
            console.log('got it');
            io.to(`${users[data.receiver_id]}`).emit("privateMessage", data);
        }
    });
    // end of instant messaging.

    socket.on('groupMessage', function (data) {
        socket.broadcast.to('group' + data.group_id).emit('groupMessage', data);
    });

    socket.on('joinGroup', function (data) {
        data['socket_id'] = socket.id;
        if (groups[data.group_id]) {
            var valid = checkIfUserExistINGroup(data.user_id, data.group_id);
            if (!valid) {
                groups[data.group_id].push(data);
                socket.join(data.room);
            }
        } else {
            groups[data.group_id] = [data];
            socket.join(data.room);
        }
    });

    socket.on('disconnect', function () {
        var i = users.indexOf(socket.id);
        users.splice(i, 1, 0);
        console.log('disconnected');
    });

    socket.on('leaveRoom', function (data) {
        socket.leave(data.room);
        checkoutFromGroup(data.user_id, data.group_id);
    });
});

function checkoutFromGroup(user_id, group_id) {
    var group = groups[group_id];
    for (var i = 0; i < group.length; i++) {
        if (group[i]['user_id'] == user_id) {
            group.splice(i, 1);
            break;
        }
    }
}

function checkIfUserExistINGroup(user_id, group_id) {
    var group = groups[group_id];
    var valid = false;
    if (group.length > 0) {
        for (var i = 0; i < group.length; i++) {
            if (group[i]['user_id'] == user_id) {
                valid = true;
                break;
            }
        }
    }

    return valid;
}

function getSocketIdOfUserInGroup(user_id, group_id) {
    var group = groups[group_id];
    if (group.length > 0) {
        for (var i = 0; i < group.length; i++) {
            if (group[i]['user_id'] == user_id) {
                return group[i]['socket_id'];
            }
        }
    }
}

http.listen(8002, function () {
    console.log('Listening on Port 8002');
});
