@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ $question->title }}
                    @foreach($question->topics as $topic)
                        <a class="topic pull-right" href="/topic/{{ $topic->id }}">{{ $topic->name }}</a>
                    @endforeach
                </div>

                <div class="panel-body">
                    {!! $question->body !!}
                </div>
                <div class="edit-actions">
                    @if(Auth::check() && Auth::user()->owns($question))
                        <span class="edif"><a href="/questions/{{ $question->id }}/edit">编 辑</a></span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.actions {
    display: flex;
    padding: 10px 20px;
}
</style>

@endsection