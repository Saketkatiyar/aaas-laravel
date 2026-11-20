@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Edit CMS page</h1><a class="btn btn-outline-secondary" href="{{ route('admin.resource.index', 'pages') }}">Back to CMS pages</a></div>
<form method="post" action="{{ route('admin.resource.update', ['resource' => 'pages', 'id' => $page->information_id]) }}">
    @csrf @method('put')
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" type="button" data-bs-target="#tab-general">General</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-target="#tab-status">Status</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-target="#tab-url">URL</button></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane show active" id="tab-general">
            <div class="mb-3"><label class="form-label">Title</label><input required name="title" class="form-control" value="{{ old('title', $page->title) }}"></div>
            @include('admin.partials.rich-editor', ['name' => 'description', 'label' => 'Description', 'value' => old('description', $page->description)])
            <div class="mb-3"><label class="form-label">Right column Heading</label><input name="meta_title" class="form-control" value="{{ old('meta_title', $page->meta_title) }}"></div>
            <div class="mb-3"><label class="form-label">Right column Description</label><textarea name="meta_description" rows="5" class="form-control">{{ old('meta_description', $page->meta_description) }}</textarea></div>
            <div class="mb-3"><label class="form-label">Meta Tag Keywords</label><textarea name="meta_keyword" rows="5" class="form-control">{{ old('meta_keyword', $page->meta_keyword) }}</textarea></div>
        </div>
        <div class="tab-pane" id="tab-status">
            <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="1" @selected(old('status', $information->status) == 1)>Enabled</option><option value="0" @selected(old('status', $information->status) == 0)>Disabled</option></select></div>
        </div>
        <div class="tab-pane" id="tab-url">
            <div class="alert alert-info">Use lowercase letters, numbers, and hyphens only. Keep the current URL unchanged unless you deliberately need a new one.</div>
            <div class="mb-3"><label class="form-label">SEO URL</label><input name="seo_keyword" class="form-control" value="{{ old('seo_keyword', $seo) }}" pattern="[a-z0-9-]*"></div>
        </div>
    </div>
    <button class="btn btn-success mt-3">Save</button>
</form>
@endsection
