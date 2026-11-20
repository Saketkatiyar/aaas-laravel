<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <textarea name="{{ $name }}" class="summernote-editor">{{ \App\Support\ContentHtml::decode($value ?? '') }}</textarea>
</div>
@once
@push('scripts')
<script>
$('.summernote-editor').summernote({
    height: 360,
    toolbar: [
        ['style', ['style']], ['font', ['bold', 'underline', 'clear']],
        ['fontname', ['fontname']], ['fontsize', ['fontsize']], ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']], ['table', ['table']],
        ['insert', ['link', 'picture', 'video']], ['view', ['fullscreen', 'help']]
    ]
});
</script>
@endpush
@endonce
