@extends('layouts.default')
@section('content')
    <h1>Layout: {{ $page->title()->html() }}</h1>
@endsection
