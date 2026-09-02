<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra Mandiri Bojong — Sistem Informasi Stok Gudang Bahan Bangunan</title>
    <meta name="description" content="Sistem Informasi Pengelolaan Stok Gudang Bahan Bangunan: POS kasir, manajemen stok, purchase order, retur, piutang, dan laporan.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏗️</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --amber: #f59e0b;
            --amber-2: #fbbf24;
            --accent: #3b82f6;
            --line: rgba(148,163,184,.16);
        }
        * { font-family: 'Inter', sans-serif; }
        body { background: var(--ink); color: #e2e8f0; overflow-x: hidden; }

        /* ── Hero background ── */
        .hero-bg {
            position: relative;
            background:
                radial-gradient(900px 420px at 85% -10%, rgba(245,158,11,.14), transparent 60%),
                radial-gradient(700px 420px at 5% 110%, rgba(59,130,246,.15), transparent 60%),
                var(--ink);
        }
        .hero-bg::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(148,163,184,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148,163,184,.06) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 30%, transparent 75%);
            pointer-events: none;
        }

        /* ── Navbar ── */
        .site-nav { transition: .3s; }
        .site-nav.scrolled {
            background: rgba(15,23,42,.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--line);
        }
        .brand-badge {
            width: 40px; height: 40px; border-radius: 11px;
            background: linear-gradient(135deg, var(--amber), #ea580c);
            display: grid; place-items: center;
            box-shadow: 0 8px 20px rgba(245,158,11,.3);
        }

        /* ── Hero ── */
        .eyebrow {
            display: inline-flex; align-items: center; gap: .5rem;
            font-size: .76rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase;
            color: var(--amber-2);
            border: 1px solid rgba(245,158,11,.35);
            background: rgba(245,158,11,.08);
            padding: .4rem .9rem; border-radius: 999px;
        }
        .hero-title {
            font-size: clamp(2.1rem, 4.6vw, 3.3rem);
            line-height: 1.1; letter-spacing: -.02em;
        }
        .grad-text {
            background: linear-gradient(92deg, var(--amber-2) 10%, #f97316 50%, var(--accent) 95%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }
        .btn-amber {
            background: linear-gradient(135deg, var(--amber-2), var(--amber));
            color: #1c1917; font-weight: 700; border: 0;
            box-shadow: 0 10px 26px rgba(245,158,11,.32);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-amber:hover { color: #1c1917; transform: translateY(-2px); box-shadow: 0 14px 34px rgba(245,158,11,.42); }
        .btn-ghost { border: 1px solid var(--line); color: #e2e8f0; font-weight: 600; }
        .btn-ghost:hover { border-color: rgba(245,158,11,.5); color: var(--amber-2); background: rgba(245,158,11,.06); }

        /* ── Stats ── */
        .stat-box {
            background: rgba(30,41,59,.55);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.2rem 1rem;
            text-align: center;
        }
        .stat-num { font-size: clamp(1.5rem, 2.6vw, 2.1rem); font-weight: 800; color: #fff; }
        .stat-label { font-size: .82rem; color: #94a3b8; }

        /* ── Features ── */
        .feature-card {
            background: rgba(30,41,59,.5);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.5rem;
            transition: transform .25s, border-color .25s;
        }
        .feature-card:hover { transform: translateY(-5px); border-color: rgba(245,158,11,.4); }
        .feature-ico {
            width: 48px; height: 48px; border-radius: 13px; font-size: 1.3rem;
            display: grid; place-items: center;
            background: rgba(245,158,11,.12); color: var(--amber-2);
            border: 1px solid rgba(245,158,11,.25);
        }

        /* ── CTA ── */
        .cta-band {
            background: linear-gradient(135deg, #16233d, #101a2e);
            border: 1px solid var(--line);
            border-radius: 24px;
        }

        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        .footer-link { color: #94a3b8; text-decoration: none; font-size: .9rem; }
        .footer-link:hover { color: var(--amber-2); }
    </style>
</head>
<body>

<!-- ═══════════ NAVBAR ═══════════ -->
<nav class="navbar navbar-expand-lg fixed-top site-nav py-3" id="siteNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing') }}">
            <span class="brand-badge"><i class="bi bi-boxes text-white"></i></span>
            <span class="fw-bold text-white" style="letter-spacing:-.01em">
                Mitra Mandiri <span class="text-warning">Bojong</span>
            </span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm px-3 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </a>
        </div>
    </div>
</nav>

<!-- ═══════════ HERO ═══════════ -->
<header class="hero-bg" style="padding-top: 9rem; padding-bottom: 4rem;">
    <div class="container position-relative">
        <div class="text-center mx-auto" style="max-width:760px">
            <span class="eyebrow mb-4">
                <i class="bi bi-lightning-charge-fill"></i> Sistem Informasi Stok Gudang
            </span>
            <h1 class="fw-bold text-white hero-title mt-3 mb-3">
                Kelola Stok &amp; Penjualan <span class="grad-text">Bahan Bangunan</span>
            </h1>
            <p class="mx-auto mb-4" style="color:#94a3b8; font-size:1.05rem; max-width:520px">
                POS kasir, stok real-time, purchase order, retur, piutang, dan laporan laba —
                semua dalam satu sistem untuk toko bahan bangunan.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-amber btn-lg px-4">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </a>
                <a href="#fitur" class="btn btn-ghost btn-lg px-4">
                    Lihat Fitur <i class="bi bi-arrow-down ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- ═══════════ STATISTIK ═══════════ -->
<section style="padding: 2.5rem 0 1rem;">
    <div class="container">
        <div class="row g-3">
            @php
                $stats = [
                    ['label' => 'Item Barang',    'value' => $totalBarang,    'money' => false],
                    ['label' => 'Kategori',       'value' => $totalKategori,  'money' => false],
                    ['label' => 'Supplier',       'value' => $totalSupplier,  'money' => false],
                    ['label' => 'Transaksi',      'value' => $totalTransaksi, 'money' => false],
                ];
            @endphp
            @foreach ($stats as $s)
            <div class="col-6 col-md col-sm-4 reveal">
                <div class="stat-box">
                    <div class="stat-num" data-count="{{ $s['value'] }}" data-money="{{ $s['money'] ? '1' : '0' }}">0</div>
                    <div class="stat-label mt-1">{{ $s['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ═══════════ FITUR ═══════════ -->
<section id="fitur" style="padding: 4.5rem 0;">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <h2 class="fw-bold text-white mb-2" style="letter-spacing:-.01em">Fitur Utama</h2>
            <p class="mx-auto text-secondary" style="max-width:480px">
                Dirancang khusus untuk toko bahan bangunan — dari mencatat stok hingga menghitung laba penjualan.
            </p>
        </div>
        <div class="row g-4">
            @php
                $fitur = [
                    ['bi-speedometer2',       'POS Kasir',         'Transaksi cepat dengan struk otomatis, tunai atau kredit.'],
                    ['bi-box-seam',           'Stok Real-time',    'Penjualan, penerimaan, dan retur langsung mengubah stok.'],
                    ['bi-clipboard-check',    'Purchase Order',    'Pesan ke supplier dengan alur persetujuan pemilik.'],
                    ['bi-arrow-counterclockwise', 'Retur Barang',  'Retur per item dengan alasan: rusak, tidak sesuai, tidak terpakai.'],
                    ['bi-cash-coin',          'Piutang & Cicilan', 'Catat penjualan kredit dan terima cicilan pelanggan.'],
                    ['bi-graph-up-arrow',     'Laporan & Laba',    'Laporan penjualan dan laba berbasis HPP, siap cetak.'],
                ];
            @endphp
            @foreach ($fitur as $f)
            <div class="col-md-6 col-lg-4 reveal">
                <div class="feature-card h-100">
                    <div class="feature-ico mb-3"><i class="bi {{ $f[0] }}"></i></div>
                    <h6 class="fw-bold text-white mb-1">{{ $f[1] }}</h6>
                    <p class="text-secondary mb-0" style="font-size:.9rem">{{ $f[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ CTA (DINONAKTIFKAN SEMENTARA) ═══════════ --}}
{{--
<section style="padding: 1rem 0 5rem;">
    <div class="container">
        <div class="cta-band p-5 text-center reveal">
            <h2 class="fw-bold text-white mb-2" style="letter-spacing:-.01em">Siap Mengelola Gudang Lebih Rapi?</h2>
            <p class="text-secondary mx-auto mb-4" style="max-width:480px">
                Silakan login untuk masuk ke sistem. Sistem Informasi Pengelolaan Stok Gudang Bahan Bangunan — Mitra Mandiri Bojong.
            </p>
            <a href="{{ route('login') }}" class="btn btn-amber btn-lg px-5">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
        </div>
    </div>
</section>
--}}

<!-- ═══════════ FOOTER ═══════════ -->
<footer style="border-top:1px solid var(--line); padding:2rem 0;">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="brand-badge" style="width:32px;height:32px;border-radius:9px"><i class="bi bi-boxes text-white small"></i></span>
                <span class="fw-bold text-white">Mitra Mandiri Bojong</span>
            </div>
            <div class="d-flex gap-4">
                <a class="footer-link" href="#fitur">Fitur</a>
                <a class="footer-link" href="{{ route('login') }}">Login</a>
            </div>
            <div class="small text-secondary">
                © {{ date('Y') }} Mitra Mandiri Bojong · Skripsi Sistem Informasi
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    // Navbar blur saat scroll
    const nav = document.getElementById('siteNav');
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 24);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Reveal on scroll
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: .15 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    // Count-up angka statistik
    const fmtMoney = n => 'Rp ' + n.toLocaleString('id-ID');
    const fmtNum = n => n.toLocaleString('id-ID');
    const cio = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            cio.unobserve(e.target);
            const el = e.target;
            const target = parseFloat(el.dataset.count || '0');
            const isMoney = el.dataset.money === '1';
            const dur = 1000, start = performance.now();
            const tick = now => {
                const p = Math.min((now - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                const val = Math.round(target * eased);
                el.textContent = isMoney ? fmtMoney(val) : fmtNum(val);
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        });
    }, { threshold: .4 });
    document.querySelectorAll('[data-count]').forEach(el => cio.observe(el));
})();
</script>
</body>
</html>
