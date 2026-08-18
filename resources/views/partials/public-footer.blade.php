<footer class="public-footer">
    <div class="container footer-grid">
        <div>
            <h2>Nigerian Indigene Management and Certification System</h2>
            <p>
                A secure registration and certificate-verification platform implemented
                and supported by Haigha Tech for participating government authorities.
            </p>
        </div>
        <div>
            <h3>Service</h3>
            <ul>
                <li><a href="{{ route('certificates.verify.form') }}">Verify certificate</a></li>
                <li><a href="{{ route('login') }}">Staff login</a></li>
                <li><a href="{{ route('support') }}">Support</a></li>
                <li><a href="{{ route('fraud-reports.create') }}">Report suspected fraud</a></li>
            </ul>
        </div>
        <div>
            <h3>Legal</h3>
            <ul>
                <li><a href="{{ route('privacy') }}">Privacy notice</a></li>
                <li><a href="{{ route('terms') }}">Terms of use</a></li>
                <li><a href="{{ route('accessibility') }}">Accessibility</a></li>
                <li><a href="{{ route('system-status') }}">System status</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        <div class="container">
            &copy; {{ now()->year }} Participating Government Authority.
            Technology service by
            <a href="https://haighatech.com" rel="noopener">Haigha Tech</a>.
        </div>
    </div>
</footer>

<style>
    .public-footer { margin-top: 5.5rem; color: #ced9d4; background: #071629; }
    .public-footer .footer-grid {
        padding: 3.5rem 0 2rem;
        display: grid;
        grid-template-columns: 1.4fr repeat(2, .8fr);
        gap: 2rem;
    }
    .public-footer h2, .public-footer h3 { color: #fff; }
    .public-footer h2 { margin: 0 0 .6rem; font-size: 1.2rem; }
    .public-footer h3 { margin: 0 0 .7rem; font-size: .95rem; }
    .public-footer p { max-width: 520px; margin: 0; }
    .public-footer ul { margin: 0; padding: 0; list-style: none; }
    .public-footer li + li { margin-top: .45rem; }
    .public-footer a { color: inherit; text-decoration: none; }
    .public-footer a:hover { color: #fff; text-decoration: underline; }
    .public-footer .copyright {
        padding: 1.1rem 0;
        border-top: 1px solid rgba(255,255,255,.12);
        color: #aebdb6;
        font-size: .86rem;
    }
    @media (max-width: 860px) {
        .public-footer .footer-grid { grid-template-columns: 1fr; }
    }
</style>
