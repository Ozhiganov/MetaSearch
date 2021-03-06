@if( sizeof($errors) > 0 )
	<div class="alert alert-danger">
		<ul>
			@foreach($errors as $error)
				<li>{!! $error !!}</li>
			@endforeach
		</ul>
	</div>
@endif
@if( sizeof($warnings) > 0)
	<div class="alert alert-warning">
		<ul>
			@foreach($warnings as $warning)
				<li>{!! $warning !!}</li>
			@endforeach
		</ul>
	</div>
@endif
<div class="col-xs-12 col-md-8">
	@for($i = 0; $i <= 2; $i++)
		@include('layouts.ad', ['ad' => $metager->popAd()])
	@endfor
	@foreach($metager->getResults() as $result)
		@if($result->number % 7 === 0)
			@include('layouts.ad', ['ad' => $metager->popAd()])
		@endif
		@include('layouts.result', ['result' => $result])
	@endforeach
	<nav aria-label="...">
		<ul class="pager">
			<li @if($metager->getPage() === 1) class="disabled" @endif><a href="@if($metager->getPage() === 1) # @else javascript:history.back() @endif">{{ trans('results.zurueck') }}</a></li>
			<li @if($metager->nextSearchLink() === "#") class="disabled" @endif><a href="{{ $metager->nextSearchLink() }}">{{ trans('results.weiter') }}</a></li>
		</ul>
	</nav>
</div>
@if( $metager->showQuicktips() )
	<div class="col-md-4 hidden-xs hidden-sm" id="quicktips"></div>
@endif
</div>
