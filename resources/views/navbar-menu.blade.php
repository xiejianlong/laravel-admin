<li class="dropdown messages-menu" id="navbar-msg">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-envelope-o"></i>
        @if($messages->count() > 0)
            <span class="label label-success">{{ $messages->count() }}</span>
        @endif
    </a>
    <ul class="dropdown-menu">
        <li class="header">您有{{$messages->count()}}条未读消息</li>
        <li>
            <!-- inner menu: contains the actual data -->
            <ul class="menu">

                @foreach($messages as $message)
                    <li><!-- start message -->
                        <a id="redMsg" href="/admin/msg?type=inbox">
                            <div class="pull-left">
                                <img src="{{$message->sender->avatar}}" class="img-circle" alt="User Image">
                            </div>
                            <h4>
                                {{$message->title}}
                                <small><i class="fa fa-clock-o"></i> {{ $message->created_at->diffForHumans() }}</small>
                            </h4>
                            <p>{{ str_limit($message->message, 30) }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
        <li class="footer"><a href="#">查看全部消息</a></li>
    </ul>
</li>
<script>
    $(function () {
        getMsg()
    });
    function getMsg(){
        setTimeout(getMsg,5*1000);
        $.ajax({
            method: 'get',
            url: "http://car.admin.com/admin/msg/get",
            data: {
                _token:LA.token,
            },
            success: function (data) {
                if(data){
                    $('#navbar-msg').html();
                    loadMsg(data);
                }
            }
        });
    }
    function loadMsg(data){
        var html = '<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope-o"></i>';
        if(data.length>0){
            html += '<span class="label label-success">'+data.length+'</span>';
        }
        html+='</a>';
        html+='<ul class="dropdown-menu">';
        html+='<li class="header">您有'+data.length+'条未读消息</li>';
        html+='<li><ul class="menu">';
        for(var i = 0 ;i<data.length;i++ ){
            html+='<li><a id="redMsg" href="/admin/msg?type=inbox"><div class="pull-left">';
            html+='<img src="'+data[i].sender.avatar+'" class="img-circle" alt="User Image"></div>';
            html+='<h4>'+data[i].title+'<small><i class="fa fa-clock-o"></i>'+data[i].created_at+'</small> </h4>';
            html+='<p>'+data[i].message.substr(0,30)+'</p></a></li>';
        }
        html+='</ul></li><li class="footer"><a href="#">查看全部消息</a></li></ul>';
        $('#navbar-msg').html(html);
    }
</script>