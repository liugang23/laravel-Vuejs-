@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ $question->title }}
                    <span style="margin-left:10px">
                    @foreach($question->topics as $topic)
                        <a class="topic" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                    @endforeach
                    </span>
                </div>

                <div class="panel-body">
                    {!! $question->body !!}
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .panel-body img { width: 100%;}
    a.topic {
        background-color: #eff6fa;
        padding: 1px 10px 0;
        border-radius: 30px;
        text-decoration: none;
        margin: 0 5px 5px 0;
        display: inline-block;
        white-space: nowrap;
        cursor: pointer;
    }
    a.topic:hover {
        background: #259;
        color: #fff;
        text-decoration: none;
    }

</style>
@endsection