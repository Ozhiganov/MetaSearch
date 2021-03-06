<div class="content-wrapper container">
	<header id="research" class="row">
		<nav class="navbar navbar-default navbar-fixed-top navbar-resultpage">
			<div class="container">
				<div class="row">
					<div class="col-xs-3 logo dense-col">
						<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}"><h1>M<span class="hidden-xs">eta</span>G<span class="hidden-xs">er</span></h1></a>
					</div>
					<div class="col-xs-9 dense-col">
						<form method="{{ Request::method() }}" accept-charset="UTF-8" class="form" id="submitForm">
							<div class="input-group">
								<input autocomplete="off" class="form-control" form="submitForm" id="eingabeTop" name="eingabe" placeholder="Suchbegriffe erweitern/verändern, oder völlig neue Suche:" tabindex="1" type="text" value="{{ $eingabe }}" required />
								<div class="input-group-addon">
									<button type='submit' form="submitForm" id='search'><i class="fa fa-search" aria-hidden="true"></i>
									</button>
								</div>
							</div>
							@foreach( $metager->request->all() as $key => $value)
								@if($key !== "eingabe" && $key !== "page" && $key !== "next")
									<input type='hidden' name='{{ $key }}' value='{{ $value }}' form='submitForm' />
								@endif
							@endforeach
						</form>
					</div>
				</div>
			</div>
		</nav>
		<ul id="foki" class=" resultpage-foki nav nav-tabs" role="tablist">

			@if( $metager->getFokus() === "web" )
				<li id="webTabSelector" role="presentation" data-loaded="1" class="active tab-selector">
					<a aria-controls="web" data-href="#web" href="#web">
						<i class="fa fa-globe" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.web') }}</span>
					</a>
				</li>
			@else
				<li data-loaded="0" id="webTabSelector" class="tab-selector" role="presentation">
					<a aria-controls="web" data-href="{!! $metager->generateSearchLink('web') !!}" href="{!! $metager->generateSearchLink('web', false) !!}">
						<i class="fa fa-globe" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.web') }}</span>
					</a>
				</li>
			@endif

			@if( $metager->getFokus() === "nachrichten" )
				<li id="nachrichtenTabSelector" role="presentation" data-loaded="1" class="active tab-selector">
					<a aria-controls="nachrichten" data-href="#nachrichten" href="#nachrichten">
						<i class="fa fa-bullhorn" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.nachrichten') }}</span>
					</a>
				</li>
			@else
				<li data-loaded="0" id="nachrichtenTabSelector" class="tab-selector" role="presentation" >
					<a aria-controls="nachrichten" data-href="{!! $metager->generateSearchLink('nachrichten') !!}" href="{!! $metager->generateSearchLink('nachrichten', false) !!}">
						<i class="fa fa-bullhorn" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.nachrichten') }}</span>
					</a>
				</li>
			@endif

			@if( $metager->getFokus() === "wissenschaft" )
				<li id="wissenschaftTabSelector" role="presentation" data-loaded="1" class="active tab-selector">
					<a aria-controls="wissenschaft" data-href="#wissenschaft" href="#wissenschaft">
						<i class="fa fa-file-text" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.wissenschaft') }}</span>
					</a>
				</li>
			@else
				<li data-loaded="0" id="wissenschaftTabSelector" class="tab-selector" role="presentation">
					<a aria-controls="wissenschaft" data-href="{!! $metager->generateSearchLink('wissenschaft') !!}" href="{!! $metager->generateSearchLink('wissenschaft', false) !!}">
						<i class="fa fa-file-text" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.wissenschaft') }}</span>
					</a>
				</li>
			@endif

			@if( $metager->getFokus() === "produktsuche" )
				<li id="produktsucheTabSelector" role="presentation" data-loaded="1" class="active tab-selector">
					<a aria-controls="produktsuche" data-href="#produktsuche" href="#produktsuche">
						<i class="fa fa-shopping-cart" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.produkte') }}</span>
					</a>
				</li>
			@else
				<li data-loaded="0" id="produktsucheTabSelector" class="tab-selector" role="presentation" >
					<a aria-controls="produktsuche" data-href="{!! $metager->generateSearchLink('produktsuche') !!}" href="{!! $metager->generateSearchLink('produktsuche', false) !!}">
						<i class="fa fa-shopping-cart" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.produkte') }}</span>
					</a>
				</li>
			@endif

			{{-- Fix for older Versions --}}
			@if( $metager->getFokus() === "angepasst" )
				<li id="angepasstTabSelector" role="presentation" data-loaded="1" class="active tab-selector">
					<a aria-controls="angepasst" data-href="#angepasst" href="#angepasst">
						<i class="fa fa-cog" aria-hidden="true"></i>
						<span class="hidden-xs">{{ trans('index.foki.angepasst') }}</span>
					</a>
				</li>
			@endif

			<li id="mapsTabSelector" role="presentation" class="tab-selector">
				<a href="https://maps.metager.de/map/{{ $metager->getQ() }}/9.7380161,52.37119740000003,12" target="_blank">
					<i class="fa fa-map-marker" aria-hidden="true"></i>
					<span class="hidden-xs">{!! trans('index.foki.maps') !!}</span>
				</a>
			</li>
		</ul>
	</header>
	<main id="main-content-tabs" class="tab-content row">

		@if( $metager->getFokus() === "web" )
			<div role="tabpanel" class="tab-pane active" id="web">
				<div class="row">
					@yield('results')
				</div>
			</div>
		@else
			<div role="tabpanel" class="tab-pane" id="web">
				<div class="loader">
					<img src="/img/ajax-loader.gif" alt="" />
				</div>
			</div>
		@endif

		@if( $metager->getFokus() === "bilder" )
			<div role="tabpanel" class="tab-pane active" id="bilder">
				<div class="row">
					@yield('results')
				</div>
			</div>
		@else
			<div role="tabpanel" class="tab-pane" id="bilder">
				<div class="loader">
					<img src="/img/ajax-loader.gif" alt="" />
				</div>
			</div>
		@endif

		@if( $metager->getFokus() === "nachrichten" )
			<div role="tabpanel" class="tab-pane active" id="nachrichten">
				<div class="row">
					@yield('results')
				</div>
			</div>
		@else
			<div role="tabpanel" class="tab-pane" id="nachrichten">
				<div class="loader">
					<img src="/img/ajax-loader.gif" alt="" />
				</div>
			</div>
		@endif

		@if( $metager->getFokus() === "wissenschaft" )
			<div role="tabpanel" class="tab-pane active" id="wissenschaft">
				<div class="row">
					@yield('results')
				</div>
			 </div>
		@else
			<div role="tabpanel" class="tab-pane" id="wissenschaft">
				<div class="loader">
					<img src="/img/ajax-loader.gif" alt="" />
				</div>
			</div>
		@endif

		@if( $metager->getFokus() === "produktsuche" )
			<div role="tabpanel" class="tab-pane active" id="produktsuche">
				<div class="row">
						@yield('results')
				</div>
			 </div>
		@else
			<div role="tabpanel" class="tab-pane" id="produktsuche">
				<div class="loader">
					<img src="/img/ajax-loader.gif" alt="" />
				</div>
			</div>
		@endif

		@if( $metager->getFokus() === "angepasst" )
			<div role="tabpanel" class="tab-pane active" id="angepasst">
				<div class="row">
					@yield('results')
				</div>
			</div>
		@endif
	</main>
</div>
