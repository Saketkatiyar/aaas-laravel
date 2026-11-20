<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AAAS - Academy for Advancement of Agricultural Sciences' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --aaas-green:#307e3d; --aaas-light:#e9f5e9; }
        body { color:#333; }
        .site-header { border-bottom:4px solid #6db577; background:#fff; }
        .brand-logo { height:110px; }
        .brand-subtitle { color:#737772; font-size:1rem; }
        .nav-link { color:#333; text-transform:uppercase; font-size:.84rem; }
        .nav-link:hover, .nav-link.active { color:#fff; background:var(--aaas-green); border-radius:.25rem; }
        .btn-aaas { background:var(--aaas-green); color:#fff; }
        .btn-aaas:hover { background:#266330; color:#fff; }
        .site-footer { background:var(--aaas-light); border-top:4px solid #6db577; }
        .site-footer img { max-height:72px; }
        .hero-slide img { height:380px; width:100%; object-fit:cover; }
        .news-box { background:#fff; border:1px solid #e7e7e7; border-radius:.5rem; }
        .news-list { max-height:490px; overflow:auto; }
        .feature-card { min-height:215px; transition:transform .2s; }
        .feature-card:hover { transform:translateY(-3px); }
        .feature-card img { max-height:118px; max-width:100%; object-fit:contain; }
        @media (max-width: 768px) { .brand-logo {height:68px}.brand-title{font-size:1.5rem!important}.hero-slide img{height:220px} }
    </style>
</head>
<body>
<header class="site-header sticky-top">
    <div class="container py-3">
        <a class="d-flex align-items-center text-decoration-none text-dark" href="{{ url('/home') }}">
            <img class="brand-logo me-3" src="{{ asset('aaas/logo.jpeg') }}" alt="AAAS logo">
            <div><h1 class="brand-title h2 mb-1">Academy for Advancement of Agricultural Sciences</h1><div class="brand-subtitle">(Reg. No. S0030438-2022-2023 registered under the West Bengal Societies Registration Act)</div></div>
        </a>
    </div>
    <nav class="navbar navbar-expand-lg py-0">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    @foreach (['Home'=>'home','About Us'=>'aboutus','Executive Council'=>'executive_council','Membership'=>'membership','Publications'=>'publication','Conferences'=>'conference','Awards'=>'aaasawards','Downloads'=>'downloads','Gallery'=>'gallery','Videos'=>'videos'] as $label => $path)
                        <li class="nav-item"><a class="nav-link px-3 py-2 {{ request()->is($path) ? 'active' : '' }}" href="{{ url($path) }}">{{ $label }}</a></li>
                    @endforeach
                    <li class="nav-item"><a class="nav-link px-3 py-2" href="{{ url('search') }}"><i class="bi bi-search"></i></a></li>
                </ul>
                <div class="d-flex gap-2 pb-2 pb-lg-0">@if(session('member_id'))<a href="{{url('account')}}" class="small text-decoration-none">{{session('member_name')}}</a><form method="post" action="{{url('logout')}}">@csrf<button class="btn btn-link btn-sm">Log out</button></form>@else<a href="{{url('login')}}" class="small text-decoration-none">Log In</a><a href="{{url('register')}}" class="btn btn-aaas btn-sm text-uppercase">Become a Member</a>@endif</div>
            </div>
        </div>
    </nav>
</header>

@yield('content')

<footer class="site-footer mt-5 py-4">
    <div class="container"><div class="row g-4"><div class="col-md-4"><img src="{{ asset('aaas/logo.jpeg') }}" alt="AAAS logo"><p class="mt-2 mb-0"><strong>Academy for Advancement of Agricultural Sciences</strong><br>Dr. Kajal Kumar Biswas (Secretary)<br>Dist. Nadia -741235, West Bengal, India</p></div><div class="col-md-4"><h2 class="h5">Information</h2><ul class="list-unstyled"><li><a href="{{ url('contactus') }}">CONTACT US</a></li><li><a href="{{ url('news') }}">LATEST NEWS</a></li><li><a href="{{ url('membership') }}">BECOME A MEMBER</a></li></ul></div><div class="col-md-4"><h2 class="h5">Latest Tweets</h2><p class="text-muted">(custom-twitter-feeds placeholder)</p></div></div><div class="text-center border-top mt-4 pt-3 small">© {{ date('Y') }} Academy for Advancement of Agricultural Sciences. All rights reserved.</div></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
