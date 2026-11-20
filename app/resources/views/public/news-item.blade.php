@extends('layouts.public')
@section('content')<main class="container my-5"><article class="bg-white border rounded shadow-sm p-4"><h1 class="h3 text-success">{{ \App\Support\ContentHtml::decode($item->title) }}</h1><p class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</p><div>{!! \App\Support\ContentHtml::decode($item->news) !!}</div></article></main>@endsection
