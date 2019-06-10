<li class="dropdown messages-menu">
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