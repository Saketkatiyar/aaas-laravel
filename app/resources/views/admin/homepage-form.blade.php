@extends('admin.layout')

@section('content')
<h1 class="h3 mb-3">{{ $item ? 'Edit' : 'Add' }} {{ $config['label'] }}</h1>
<form method="post" enctype="multipart/form-data" action="{{ $item ? route('admin.resource.update', [$resource, $item->{$config['primary']}]) : route('admin.resource.store', $resource) }}">
    @csrf
    @if($item) @method('put') @endif

    @if($resource === 'sliders')
        <div class="mb-3"><label class="form-label">Title</label><input required name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}"></div>
        <div class="mb-3"><label class="form-label">Image</label>@if(!empty($item->image))<div class="mb-2"><img src="{{ asset('image/'.$item->image) }}" alt="Current slider" style="max-width:180px;max-height:100px"></div>@endif<input type="file" name="image_file" accept="image/*" class="form-control" @if(!$item) required @endif><div class="form-text">Choose an image from your computer. The existing image remains unchanged unless you select a new one.</div></div>
        <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $item->sort ?? 0) }}"></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="homepage" value="1" id="homepage" @checked(old('homepage', $item->homepage ?? 0))><label class="form-check-label" for="homepage">Homepage</label></div>
        <div class="mb-3"><label class="form-label">URL</label><input required name="url" class="form-control" value="{{ old('url', $item->url ?? '') }}"></div>
    @elseif($resource === 'news')
        <div class="mb-3"><label class="form-label">Title</label><input required name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}"></div>
        <div class="mb-3"><label class="form-label">Date</label><input required type="date" name="date" class="form-control" value="{{ old('date', $item->date ?? '') }}"></div>
        @include('admin.partials.rich-editor', ['name' => 'news', 'label' => 'News', 'value' => old('news', $item->news ?? '')])
    @elseif($resource === 'downloads')
        <div class="mb-3"><label class="form-label">Title</label><input required name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}"></div>
        <div class="mb-3"><label class="form-label">Upload File</label>@if(!empty($item->upload))<div class="mb-2"><a href="{{ asset('image/'.$item->upload) }}" target="_blank">Current uploaded file</a></div>@endif<input type="file" name="upload_file" class="form-control" @if(!$item) required @endif><div class="form-text">The existing file remains unchanged unless you select a new one.</div></div>
    @else
        @include('admin.partials.rich-editor', ['name' => 'notice', 'label' => 'Homepage', 'value' => old('notice', $item->notice ?? '')])
    @endif

    <button class="btn btn-success">Save</button>
    <a class="btn btn-outline-secondary" href="{{ route('admin.resource.index', $resource) }}">Cancel</a>
</form>
@endsection
