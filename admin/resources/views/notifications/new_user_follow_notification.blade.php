@if($notification->state == 'T')
<li class="notifications">
	<a href="{{ $notification->data['name'] }}">
		{{ $notification->data['name'] }}
	</a> &nbsp; <?php echo date("Y-m-d H:i:s", time()); ?> 关注了你。
</li>
@else
<li class="notifications">
	<a href="{{ $notification->data['name'] }}">
		{{ $notification->data['name'] }}
	</a> &nbsp; <?php echo date("Y-m-d H:i:s", time()); ?> 取消对你的关注。
</li>
@endif