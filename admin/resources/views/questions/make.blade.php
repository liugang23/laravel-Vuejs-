@extends('layouts.app')

@section('content')
@include('vendor.ueditor.assets')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">发布问题</div>
                <div class="panel-body">
                	<form action="/questions" method="post">
                		{!! csrf_field() !!}
                		<div class="form-group{{ $errors->has('title') ? 'has-error' : '' }}">
							<label for="title">标 题</label>
							<input type="text" value="{{ old('title') }}" name="title" class="form-control" placeholder="标题" id="title">
							@if ($errors->has('title'))
								<span class="help-block">
									<strong>{{ $errors->first('title') }}</strong>
								</span>
							@endif
                		</div>

                		<div class="form-group{{ $errors->has('body') ? 'has-error' : '' }}">
                			<label for="body">描述</label>
		                    <!-- 编辑器容器 -->
		                    <!-- 非转义可能引起攻击,需要过滤 -->
							<script id="container" name="body" type="text/plain" style="height:200px;">
								{!! old('body') !!}
							</script>
							@if ($errors->has('body'))
								<span class="help-block">
									<strong>{{ $errors->first('body') }}</strong>
								</span>
							@endif
						</div>
						<button class="btn btn-success pull-right" type="submit">发布问题</button>
					</form>
                </div>
            </div>
        </div>
    </div>
</div>


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

