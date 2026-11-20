@extends('layouts.public')

@section('content')
<section id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        @forelse ($slides as $slide)
            <div class="carousel-item hero-slide {{ $loop->first ? 'active' : '' }}"><a href="{{ $slide->url ?: '#' }}"><img src="{{ asset('image/' . $slide->image) }}" alt="{{ $slide->title ?: 'AAAS banner' }}"></a></div>
        @empty
            <div class="carousel-item active hero-slide"><img src="{{ asset('aaas/banners/1.jpeg') }}" alt="AAAS banner"></div>
        @endforelse
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button><button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
</section>
<main class="container my-4">
    <section class="row g-4 bg-success-subtle p-3 p-md-4 rounded mb-5">
        <div class="col-lg-7"><h2 class="h4">Academy for Advancement of Agricultural Sciences</h2><p class="text-justify">The Academy for Advancement of Agricultural Sciences (AAAS) was founded on January 12, 2022 with HQ at Kalyani. AAAS is a common platform for biologists and agricultural scientists to interact for mutual benefit and to advance agricultural sciences, with special emphasis on the rural economy and marginal farmers in Eastern India.</p><a class="fw-bold text-success" href="{{ url('aboutus') }}">Click here to read more</a></div>
        <div class="col-lg-5"><div class="news-box p-3"><div class="d-flex justify-content-between"><h2 class="h5 text-success">Latest News Updates</h2><a class="btn btn-outline-success btn-sm" href="{{ url('news') }}">View All</a></div><div class="news-list">@foreach($news as $item)<article class="border-bottom py-2"><a class="fw-semibold text-decoration-none" href="{{ url('news/' . $item->id) }}">{{ \App\Support\ContentHtml::decode($item->title) }}</a><div class="small text-muted"><i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</div></article>@endforeach</div></div></div>
    </section>
    <section class="row g-4">@foreach ([['membership','aaas/membership.png'],['publication','aaas/publication.png'],['conference','aaas/conference.png'],['contactus','aaas/contact.png']] as [$path, $image])<div class="col-lg-3 col-md-6"><a href="{{ url($path) }}" class="text-decoration-none"><div class="feature-card card h-100 shadow-sm"><div class="card-body d-flex flex-column align-items-center justify-content-center"><img src="{{ asset($image) }}" alt="{{ $path }}"><hr class="w-75"><h2 class="h6 text-uppercase text-success">Read More</h2></div></div></a></div>@endforeach</section>
</main>
@endsection
