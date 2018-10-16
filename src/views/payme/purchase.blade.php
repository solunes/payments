@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
  <script type="text/javascript" src="{{ $url }}js/modalcomercio.js"></script>
@endsection

@section('content')
<div class="container solunes-store">
	@include('payments::includes.payme-purchase')
</div>
@endsection

@section('script')
@endsection