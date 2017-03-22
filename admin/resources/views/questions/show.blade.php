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
                        <span class="topic">{{ $topic->name }}</span>
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
    .topic {
        margin-right: 5px;
        background-color: #F5F8FA;
     };
</style>
@endsection