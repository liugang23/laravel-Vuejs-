@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">消息通知</div>

                <div class="panel-body">
                    @foreach($user->notifications as $notification)
                    <!-- 根据snake_case(class_basename($notification->type))引用不同的视图 -->
                    <!-- {{ snake_case(class_basename($notification->type)) }} -->
                        @include('notifications.'.snake_case(class_basename($notification->type)))
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
