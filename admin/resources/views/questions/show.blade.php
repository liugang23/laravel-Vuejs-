@extends('layouts.app')

@section('content')
@include('vendor.ueditor.assets')
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

                <div class="panel-body content">
                    {!! $question->body !!}
                </div>
                <div class="edit-actions">
                    @if(Auth::check() && Auth::user()->owns($question))
                        <span class="edif"><a href="/questions/{{ $question->id }}/edit">编 辑</a></span>
                        <form action="/questions/{{$question->id}}" method="post" class="delete-form">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button class="button is-naked delete-button">删 除</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ $question->answers_count }}个回复
                </div>

                <div class="panel-body">
                    @foreach($question->answers as $answer)
                        <div class="media">
                            <div class="media-left">
                                <a href="">
                                    <img class="top-margin" width="36" src="{{ $answer->user->avatar }}" alt="{{ $answer->user->name }}">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading top-margin">
                                    <a href="/user/{{ $answer->user->name }}">
                                        {{ $answer->user->name }}
                                    </a>
                                </h4>
                                {!! $answer->body !!}
                            </div>
                        </div>
                    @endforeach
                    <form action="/questions/{{$question->id}}/answer" method="post">
                        {!! csrf_field() !!}
                        <div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                            <!-- 编辑器容器 -->
                            <!-- 非转义可能引起攻击,需要过滤 -->
                            <script id="container" name="body" type="text/plain" style="height:120px;">
                                {!! old('body') !!}
                            </script>
                            @if ($errors->has('body'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('body') }}</strong>
                                </span>
                            @endif
                        </div>
                        <button class="btn btn-success pull-right" type="submit">提交回复</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js')
<!-- 实例化编辑器 -->
<script type="text/javascript">
    var ue = UE.getEditor('container', {
        toolbars: [
                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
            ],
        elementPathEnabled: false,
        enableContextMenu: false,
        autoClearEmptyNode:true,
        wordCount:false,
        imagePopup:false,
        autotypeset:{ indent: true,imageBlockLine: 'center' }
    });
    ue.ready(function() {
        ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
    });
</script>
@endsection

@endsection