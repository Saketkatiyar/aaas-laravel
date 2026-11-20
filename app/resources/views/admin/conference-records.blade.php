@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><div><h1 class="h3 mb-0">{{ $heading }}</h1><div class="text-muted">{{ $conference->title }}</div></div><a class="btn btn-outline-secondary" href="{{ route('admin.resource.index', 'conferences') }}">Back to Conferences</a></div>
<div class="table-responsive"><table class="table table-striped bg-white"><thead><tr>@foreach($columns as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@forelse($records as $record)<tr>@foreach($columns as $field=>$label)<td>{{ \Illuminate\Support\Str::limit(\App\Support\ContentHtml::preview($record->$field ?? ''), 100) }}</td>@endforeach</tr>@empty<tr><td colspan="99">No records.</td></tr>@endforelse</tbody></table></div>
@endsection
