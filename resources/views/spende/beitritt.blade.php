@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
	<link type="text/css" rel="stylesheet" href="{{ elixir('/css/beitritt.css') }}" />
	<h1>{{ trans('beitritt.heading.1') }}</h1>
	<form id="donation-form">
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="name">{{ trans('beitritt.beitritt.1') }}</label>
				<input type="text" class="form-control beitritt-input" name="name" placeholder="{{trans('beitritt.placeholder.1')}}" required>
			</div>
		</div>	
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="firma">{{ trans('beitritt.beitritt.2') }}</label>
				<input type="text" class="form-control beitritt-input" name="firma" placeholder="{{trans('beitritt.placeholder.2')}}">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="funktion">{{ trans('beitritt.beitritt.3') }}</label>
				<input type="text" class="form-control beitritt-input" name="funktion" placeholder="{{trans('beitritt.placeholder.3')}}">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="adresse">{{ trans('beitritt.beitritt.4') }}</label>
				<input type="text" class="form-control beitritt-input" name="adresse" placeholder="{{trans('beitritt.placeholder.4')}}" required>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="email">{{ trans('beitritt.beitritt.5') }}</label>
				<input type="email" class="form-control beitritt-input" name="email" placeholder="">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="homepage">{{ trans('beitritt.beitritt.6') }}</label>
				<input type="text" class="form-control beitritt-input" name="homepage" placeholder="http://">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group beitritt-form-group">
				<label for="telefon">{{ trans('beitritt.beitritt.7') }}</label>
				<input type="text" class="form-control beitritt-input" name="telefon" placeholder="{{trans('beitritt.placeholder.7')}}">
			</div>
		</div>
		<div class="col-sm-12">
			<p>{{ trans('beitritt.beitritt.8') }}</p>
			<input type="text" class="form-control" name="betrag" required>
			<p> {{ trans('beitritt.beitritt.9') }}</p>
		</div>
		<div class="col-sm-12">
			<p>{{ trans('beitritt.beitritt.10') }}</p>
			<p>{{ trans('beitritt.beitritt.11') }}</p>
		</div>
		<div class="col-sm-12">
			<input type="radio" name="veröffentlichung" checked> {{ trans('beitritt.beitritt.12') }}
			<input type="radio" name="veröffentlichung"> {{ trans('beitritt.beitritt.13') }}
		</div>
		<div class="col-sm-12">
			<div class="form-group beitritt-form-group">
				<label for="ort">{{ trans('beitritt.beitritt.14') }}</label>
				<input type="text" class="form-control beitritt-input" id="ort" placeholder="">
			</div>
			<p class="signature">{{ trans('beitritt.unterschrift') }}</p>
		</div>
		<div class="newpage row"></div>
		<h1>{{ trans('beitritt.abbuchung.2') }}</h1>
		<p>{{ trans('beitritt.abbuchung.3') }}</p>
		<div class="form-group beitritt-form-group">
			<label for="kontoname">{{ trans('beitritt.abbuchung.4') }}</label>
			<input type="text" class="form-control" name="kontoname" placeholder="">
		</div>
		<div class="row">
			<div class="col-sm-4 form-group beitritt-form-group">
				<label for="bankverbindung">{{ trans('beitritt.abbuchung.5') }}</label>
				<input type="text" class="form-control" name="bankverbindung" placeholder="">
			</div>
			<div class="col-sm-5 form-group beitritt-form-group">
				<label for="iban">{{ trans('beitritt.abbuchung.6') }}</label>
				<input type="text" class="form-control" name="iban" maxlength="22" placeholder="">
			</div>
			<div class="col-sm-3 form-group beitritt-form-group">
				<label for="bic">{{ trans('beitritt.abbuchung.7') }}</label>
				<input type="text" class="form-control" name="bic" placeholder="">
			</div>
		</div>
		<div class="form-group beitritt-form-group">
			<label for="ort2">{{ trans('beitritt.abbuchung.8') }}</label>
			<input type="text" class="form-control beitritt-input" id="ort2" placeholder="">
		</div>
		<p class="signature">{{ trans('beitritt.unterschrift') }}</p>
	</form>
	<div class="beitritt-formular-info">
		<p>{{ trans('beitritt.anweisung.1') }}</p>
		<ul class="dotlist">
			<li>{{ trans('beitritt.anweisung.2') }}</li>
			<li>{{ trans('beitritt.anweisung.3') }}</li>
			<li>{{ trans('beitritt.anweisung.4') }}</li>
		</ul>
		<p>{{ trans('beitritt.anweisung.5') }}</p>
		<p>{{ trans('beitritt.anweisung.6') }}</p>
	</div>
	<button type="button" class="btn btn-lg btn-primary noprint" onclick="window.print();">{{ trans('beitritt.anweisung.7') }}</button>
	<!-- <script src="{{ elixir('js/scriptJoinPage.js') }}"></script> -->
@endsection
