@extends('layout.app')

@section('content')
<section class="min-h-screen bg-[#f4f8f5] pt-20 lg:pt-24">
    <div class="grid min-h-[calc(100vh-5rem)] lg:grid-cols-[0.9fr_1.1fr]">
        <aside class="relative overflow-hidden bg-gradient-to-br from-[#041d16] via-[#0d5832] to-[#18a06b] px-6 py-10 text-white sm:px-10 lg:px-16 lg:py-16">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/8 blur-sm"></div>
            <div class="absolute -bottom-28 -left-28 h-72 w-72 rounded-full bg-white/8 blur-sm"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_28%,rgba(255,255,255,.14),transparent_34%)]"></div>

            <div class="relative z-10 max-w-[620px]">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.32em] text-[#dcefd7]">Hireable Proximity</p>
                    <h2 class="mt-4 max-w-[520px] text-4xl font-bold leading-[1.12] tracking-normal sm:text-5xl">
                        Find Work Built Around Ability
                    </h2>
                    <p class="mt-5 max-w-[560px] text-sm leading-7 text-white/82">
                        A focused space for PWD applicants and inclusive employers to meet, apply, and manage opportunities with confidence.
                    </p>
                </div>

                <div class="mt-10 space-y-4 text-sm font-semibold text-white/90">
                    <p class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#176c3a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42.002L3.29 9.207a1 1 0 0 1 1.42-1.408l4.04 4.09 6.54-6.593a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Guided applications for PWD applicants
                    </p>
                    <p class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#176c3a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42.002L3.29 9.207a1 1 0 0 1 1.42-1.408l4.04 4.09 6.54-6.593a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Inclusive job matching with partner employers
                    </p>
                    <p class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#176c3a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42.002L3.29 9.207a1 1 0 0 1 1.42-1.408l4.04 4.09 6.54-6.593a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Simple access for applicants and employers
                    </p>
                </div>
            </div>
        </aside>

        <div class="flex items-center justify-center px-5 py-10 lg:px-10">
            <div class="auth-card w-full max-w-[620px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_22px_65px_rgba(15,23,42,0.12)]">
                <div class="grid grid-cols-2 border-b border-gray-100">
                    <a href="{{ route('login') }}" class="auth-tab is-active flex h-14 items-center justify-center text-sm font-semibold" aria-current="page">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="auth-tab flex h-14 items-center justify-center text-sm font-semibold">
                        Create Account
                    </a>
                </div>

                <div class="px-6 py-8 sm:px-8">
                    <form class="auth-panel" action="{{ route('login.store') }}" method="POST">
                        @csrf
                        <h2 class="text-2xl font-bold text-slate-950">Welcome back!</h2>
                        <p class="mt-2 text-sm text-slate-500">Sign in to continue your inclusive job search.</p>

                        @if ($errors->any())
                            <div class="mt-5 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="mt-7">
                            <label for="login-email" class="text-sm font-semibold text-slate-900">Email Address</label>
                            <div class="auth-input-shell mt-2 flex h-12 items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9m19.5 0A2.25 2.25 0 0 0 19.5 5.25h-15A2.25 2.25 0 0 0 2.25 7.5m19.5 0-8.47 5.33a2.25 2.25 0 0 1-2.56 0L2.25 7.5" />
                                </svg>
                                <input id="login-email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email or PWD ID Number"class="h-full w-full bg-transparent text-sm text-slate-900 outline-none placeholder:text-gray-400">
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="flex items-center justify-between gap-4">
                                <label for="login-password" class="text-sm font-semibold text-slate-900">Enter your Password</label>
                                <a href="#" class="text-xs font-semibold text-[#176c3a] hover:text-[#0f4e2b]">Forgot password?</a>
                            </div>
                            <div class="auth-input-shell mt-2 flex h-12 items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.25a4.5 4.5 0 0 0-9 0v3.25M6.75 10.5h10.5A1.75 1.75 0 0 1 19 12.25v6A1.75 1.75 0 0 1 17.25 20H6.75A1.75 1.75 0 0 1 5 18.25v-6a1.75 1.75 0 0 1 1.75-1.75Z" />
                                </svg>
                                <input id="login-password" name="password" type="password" placeholder="Enter your password" class="h-full w-full bg-transparent text-sm text-slate-900 outline-none placeholder:text-gray-400">
                            </div>
                        </div>

                        <label class="mt-5 flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-[#176c3a] focus:ring-[#176c3a]">
                            Remember me for 30 days
                        </label>

                        <button type="submit" class="mt-7 flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-[#176c3a] to-[#17a36b] text-sm font-bold text-white shadow-lg shadow-green-900/16 transition hover:from-[#135c31] hover:to-[#12895a]">
                            Login
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 5.25 20.25 12m0 0-6.75 6.75M20.25 12H3.75" />
                            </svg>
                        </button>

                        <p class="mt-6 text-center text-sm text-slate-500">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-semibold text-[#176c3a] hover:text-[#0f4e2b]">Register</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
