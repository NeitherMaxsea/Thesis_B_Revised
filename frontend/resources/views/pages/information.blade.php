@extends('layout.app')

@section('content')
<section class="min-h-[70vh] bg-gradient-to-br from-emerald-50 via-white to-slate-100 px-5 pb-20 pt-32 sm:pt-36">
    <div class="mx-auto max-w-5xl">
        <div class="rounded-2xl border border-emerald-100 bg-white p-7 shadow-[0_24px_70px_rgba(15,23,42,0.10)] sm:p-10">
            <p class="text-sm font-extrabold uppercase tracking-[0.16em] text-[#176c3a]">{{ $page['eyebrow'] }}</p>
            <h1 class="mt-3 max-w-3xl text-3xl font-extrabold leading-tight text-slate-950 sm:text-5xl">
                {{ $page['title'] }}
            </h1>
            <p class="mt-5 max-w-3xl text-base font-medium leading-8 text-slate-600 sm:text-lg">
                {{ $page['description'] }}
            </p>

            @if (! empty($page['items']))
                <div class="mt-9 grid gap-4 md:grid-cols-2">
                    @foreach ($page['items'] as $item)
                        <article
                            @isset($item['id']) id="{{ $item['id'] }}" @endisset
                            class="scroll-mt-28 rounded-xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <h2 class="text-lg font-extrabold text-slate-900">{{ $item['title'] }}</h2>
                            <p class="mt-2 text-sm font-medium leading-6 text-slate-600">{{ $item['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('home') }}" class="auth-primary-button px-6">Back to Home</a>
                <a href="{{ route('faq') }}" class="auth-secondary-button px-6">View FAQ</a>
            </div>
        </div>
    </div>
</section>
@endsection
