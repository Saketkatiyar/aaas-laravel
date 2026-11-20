@extends('admin.layout')
@section('content')
<h1 class="h3 mb-3">{{ $item ? 'Edit' : 'Add' }} {{ $config['label'] }}</h1>
<form method="post" action="{{ $item ? route('admin.resource.update',[$resource,$item->{$config['primary']}]) : route('admin.resource.store',$resource) }}">@csrf @if($item) @method('put') @endif
@foreach($config['fields'] as $field)
@if($field === 'contents')
@include('admin.partials.rich-editor', ['name'=>$field,'label'=>'Book Contents','value'=>old($field,$item->$field ?? '')])
@else
<div class="mb-3"><label class="form-label">{{ ucwords(str_replace('_',' ',$field)) }}</label>
@if(in_array($field,['description','news','venue','message','about']))<textarea name="{{ $field }}" class="form-control" rows="5">{{ old($field,$item->$field ?? '') }}</textarea>
@elseif($field === 'status')<select name="status" class="form-select"><option value="1" @selected(old('status',$item->$field ?? '') == 1)>Enabled</option><option value="0" @selected(old('status',$item->$field ?? '') == 0)>Disabled</option></select>
@elseif($field === 'type' && $resource === 'awards')<select name="type" class="form-select"><option value="national" @selected(old('type',$item->$field ?? '') === 'national')>National</option><option value="international" @selected(old('type',$item->$field ?? '') === 'international')>International</option></select>
@else<input name="{{ $field }}" class="form-control" value="{{ old($field,$item->$field ?? '') }}">@endif
</div>
@endif
@endforeach
<button class="btn btn-success">Save</button><a class="btn btn-outline-secondary" href="{{route('admin.resource.index',$resource)}}">Cancel</a></form>
@endsection
