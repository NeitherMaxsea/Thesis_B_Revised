<footer class="site-footer">
    <div class="mx-auto max-w-[1450px] px-6 py-9 lg:px-8 lg:py-11">
        <div class="grid gap-9 lg:grid-cols-[1fr_auto_auto] lg:items-start lg:gap-20">
            <a href="{{ route('home') }}" class="footer-logo-link" aria-label="PWD Job Employment home">
                <img src="{{ asset('images/job-employment-logo.png') }}" alt="PWD Job Employment Assistance Platform" class="footer-logo">
            </a>

            <nav aria-label="Explore" class="footer-link-group">
                <h2>Explore</h2>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('jobs') }}">Find Jobs</a>
                <a href="{{ route('faq') }}">FAQ</a>
                <a href="{{ route('pages.tutorial') }}">Tutorial</a>
                <a href="{{ route('pages.resources') }}">Resources</a>
            </nav>

            <nav aria-label="Support" class="footer-link-group">
                <h2>Support</h2>
                <a href="{{ route('pages.accessibility-policy') }}">Accessibility Policy</a>
                <a href="{{ route('pages.terms') }}">Terms and Conditions</a>
                <a href="{{ route('pages.privacy') }}">Privacy Policy</a>
                <a href="{{ route('pages.help') }}">Help Center</a>
            </nav>
        </div>

        <div class="footer-bottom">
            <p>&copy;2026 PWD Job Employment Assistance Platform. All right reserved</p>
            <p>Designed to support inclusive hiring and accessible career growth.</p>
        </div>
    </div>
</footer>
