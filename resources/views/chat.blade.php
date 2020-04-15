@extends('layouts.app')

@push('css')
    <style>
    #conversation-ul {
        list-style-type: none;
        padding: 0px;
        min-height: 20vh;
    }

    #conversation-ul>li {
        display: flex;
        margin-bottom: 4px;
    }
    #conversation-ul>li.from {
        justify-content: flex-end;
    }
    #conversation-ul>li.from>p {
        background-color: #395d7f;
        color: white;
    }

    #conversation-ul>li>p {
        background-color: #e8e8e8;
        display: inline-flex;
        padding: 0px 12px;
        border-radius: 10px;
        margin-bottom: 0px;
        align-self: center;
        margin-left: 4px;
    }

    #conversation-ul>li>small {
        background-color: #28b8c3;
        color: #ffffff;
        display: inline-flex;
        width: 23px;
        height: 23px;
        padding: 0px 9px;
        font-size: 15px;
        border-radius: 50%;
        justify-self: center;
    }

</style>
@endpush
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Chat Conversation with <strong>{!! ucfirst($friend->name) !!}</strong></div>

                <div class="card-body">
                    <ul id="conversation-ul">

                    </ul>
                </div>
            </div>
            <div class="footer" style="margin-top:20px;">
                <form id="message-form">
                    @csrf
                    <textarea class="form-control" name="message" id="message-textarea" cols="30" rows="2"></textarea>
                    <button class="btn btn-sm btn-primary" style="margin-top: 5px; width:100%;" type="submit">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        const conversation_ul = $("#conversation-ul");
        const msg_textarea = $("#message-textarea");
        const auth_id = "{!! auth()->user()->id !!}";
        let ip_address = "{!! request()->ip() !!}";
        let socket_port = '{!! config("constants.socket_port") !!}';
        let socket = io(ip_address + ':' + socket_port);

        socket.on('connect', function() {
            socket.emit('user_connected', auth_id);
        });

        msg_textarea.keypress(function (e) {
            if (e.which == 13) {
                $('#message-form').submit();
                return false;    //<---- Add this line
            }
        });

        $("#message-form").on('submit', function(event) {
            event.preventDefault();
            let message = msg_textarea.val();
            let li = '<li class="from">\
                <p>'+message+'</p>\
                </li>';
            conversation_ul.append(li);
            let url = "{!! route('users.message') !!}";
            let form = $(this);
            let formData = new FormData(form[0]);
            let friend_id = "{!! $friend->id !!}";
            formData.append('receiver_id', friend_id);
            msg_textarea.val("");
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                async: false,
                success: function(response) {
                    console.log(response);
                }
            });
        });

        // socket.on('privateMessage', function(data) {
        //     console.log(data);
        // });

        socket.on("private-channel:App\\Events\\PrivateMessageEvent", function(message) {
            let li = '<li>\
                    <small>'+(message.sender_name).substring(0,1)+'</small>\
                    <p>'+message.content+'</p>\
                </li>';
            conversation_ul.append(li);
        });
    });
</script>
@endsection
