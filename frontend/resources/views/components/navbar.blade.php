<nav id="site-navbar" class="site-navbar fixed left-0 top-0 z-50 w-full bg-white/95 transition-all duration-300">
    <div class="navbar-shell mx-auto flex max-w-[1450px] items-center justify-between px-6 py-2.5 transition-all duration-300 lg:px-8">

        <!-- Logo -->
      <div class="shrink-0 -ml-2 lg:-ml-4">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/job-employment-logo.png') }}" alt="Job Employment Personal with Disabilities" class="navbar-logo h-14 w-auto drop-shadow-md transition-all duration-300 sm:h-16 lg:h-20">
            </a>
        </div>

        <!-- Navigation -->
        <ul class="hidden items-center space-x-6 text-sm font-medium text-gray-800 lg:flex xl:space-x-7">
            <li>
                <a href="{{ route('home') }}" class="hover:text-green-600 transition duration-300">
                    Home
                </a>
            </li>

            <li>
                <a href="{{ route('jobs') }}" class="hover:text-green-600 transition duration-300">
                    Jobs
                </a>
            </li>

            <li>
                <a href="{{ route('faq') }}" class="hover:text-green-600 transition duration-300">
                    FAQ
                </a>
            </li>

            <li>
                <a href="{{ route('pages.about') }}" class="hover:text-green-600 transition duration-300">
                    About Us
                </a>
            </li>

            <li>
                <a href="{{ route('pages.contact') }}" class="hover:text-green-600 transition duration-300">
                    Contact Us
                </a>
            </li>

            <li class="relative" data-dropdown>
              <button
    type="button"
    class="inline-flex cursor-pointer items-center gap-1 transition duration-300 hover:text-green-600"
    data-dropdown-button
    aria-expanded="false"
>
    Tutorial
    <svg xmlns="http://www.w3.org/2000/svg"
        class="h-3.5 w-3.5 transition"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2.25"
        aria-hidden="true"
        data-dropdown-icon>
        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
    </svg>
</button>

                <div class="invisible absolute right-0 top-full mt-2 w-56 rounded-xl border border-gray-100 bg-white p-2 opacity-0 shadow-xl shadow-black/10 transition" data-dropdown-menu>
                    <a href="{{ route('pages.tutorial') }}#register-applicant" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-[#176c3a]">
                        Register as Applicant
                    </a>
                    <a href="{{ route('pages.tutorial') }}#register-employer" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-[#176c3a]">
                        Register as Employer
                    </a>
                    <a href="{{ route('pages.tutorial') }}#apply-work" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-[#176c3a]">
                        How to Apply Work
                    </a>
                </div>
            </li>
        </ul>

        <!-- Buttons -->
               <div class="hidden justify-self-end shrink-0 items-center space-x-2.5 lg:flex">


        <a href="{{ route('login') }}"
   data-auth-nav-link="login"
   class="flex items-center gap-2 rounded-xl bg-[#176c3a] px-6 py-3 text-sm font-bold text-white shadow-lg transition duration-300 hover:bg-[#135c31] hover:scale-105">

    <svg xmlns="http://www.w3.org/2000/svg"
         class="h-5 w-5 shrink-0"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2.25"
         aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
    </svg>

    Login
</a>

<a href="{{ route('register') }}"
   data-auth-nav-link="register"
   class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#2366a8] to-[#1f4f84] px-6 py-3 text-sm font-bold text-white shadow-lg transition duration-300 hover:from-[#215f9d] hover:to-[#1b4675] hover:scale-105">

    <svg xmlns="http://www.w3.org/2000/svg"
         class="h-5 w-5 shrink-0"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor"
         stroke-width="2.25"
         aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75a3.25 3.25 0 1 1-6.5 0 3.25 3.25 0 0 1 6.5 0ZM6 19.25a6.25 6.25 0 0 1 12.5 0M19 9.75v4.5M21.25 12h-4.5" />
    </svg>

    Create an Account
</a>
        </div>

    </div>
</nav>
