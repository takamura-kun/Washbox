<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>WashBox —  Laundry Services </title>

  <link href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/bootstrap-icons.css') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />

  <style>
    /* ══════════════════════════════════════════
       TOKENS
    ══════════════════════════════════════════ */
    :root {
      --ink:        #060b1a;
      --ink2:       #0d1535;
      --ink3:       #111827;
      --slate:      #1e293b;
      --sky:        #00c6f8;
      --sky-glow:   rgba(0,198,248,.35);
      --lavender:   #8b5cf6;
      --gold:       #f5c842;
      --green:      #00ffb3;
      --white:      #ffffff;
      --cloud:      #cbd5e1;
      --muted:      rgba(255,255,255,.5);
      --glass:      rgba(255,255,255,.03);
      --glass-b:    rgba(255,255,255,.07);
      --glass-hov:  rgba(255,255,255,.08);
      --ease:       cubic-bezier(.16,1,.3,1);
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background: var(--ink);
      color: var(--white);
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }
    h1, h2, h3, h4 { font-family: 'Syne', sans-serif; font-weight: 700; }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--ink2); }
    ::-webkit-scrollbar-thumb { background: var(--sky); border-radius: 3px; }

    /* ══════════════════════════════════════════
       NAVBAR
    ══════════════════════════════════════════ */
    .wb-nav {
      position: fixed; inset: 0 0 auto 0; z-index: 1000;
      padding: 1.2rem clamp(1.5rem, 4vw, 3rem);
      display: flex; align-items: center;
      transition: all .4s var(--ease);
    }
    .wb-nav.scrolled {
      background: rgba(6,11,26,.88);
      backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(0,198,248,.12);
      padding: .8rem clamp(1.5rem, 4vw, 3rem);
    }
    .nav-brand {
      display: flex; align-items: center; gap: .6rem;
      font-family: 'Syne', sans-serif; font-size: 1.45rem; font-weight: 800;
      color: var(--white); text-decoration: none; margin-right: 3rem;
    }
    .nav-brand-logo {
      width: 36px; height: 36px; border-radius: 8px; object-fit: cover;
      box-shadow: 0 0 14px var(--sky-glow);
    }
    .nav-brand-fallback {
      width: 36px; height: 36px; border-radius: 8px;
      background: var(--sky); display: none;
      align-items: center; justify-content: center;
      font-size: .95rem; color: var(--ink); font-weight: 800;
    }
    .nav-brand em { font-style: normal; color: var(--sky); }
    .nav-links { display: flex; gap: 1.8rem; }
    .nav-link {
      color: var(--cloud); text-decoration: none; font-weight: 500;
      font-size: .88rem; letter-spacing: .3px;
      position: relative; transition: color .25s;
    }
    .nav-link::after {
      content: ''; position: absolute; bottom: -4px; left: 0;
      width: 0; height: 2px; background: var(--sky);
      transition: width .28s var(--ease);
    }
    .nav-link:hover { color: var(--white); }
    .nav-link:hover::after { width: 100%; }
    .nav-actions { display: flex; gap: .75rem; margin-left: auto; }
    .btn-ghost-pill {
      background: transparent;
      border: 1px solid rgba(255,255,255,.14);
      color: var(--cloud); padding: .52rem 1.4rem;
      border-radius: 40px; font-weight: 600; font-size: .85rem;
      text-decoration: none; transition: all .28s var(--ease);
      display: inline-flex; align-items: center; gap: .4rem;
    }
    .btn-ghost-pill:hover {
      border-color: var(--sky); color: var(--white);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px -8px var(--sky-glow);
    }
    .btn-sky-pill {
      background: var(--sky); border: none;
      color: var(--ink); padding: .52rem 1.4rem;
      border-radius: 40px; font-weight: 700; font-size: .85rem;
      text-decoration: none; transition: all .28s var(--ease);
      display: inline-flex; align-items: center; gap: .4rem;
    }
    .btn-sky-pill:hover {
      background: #19d4ff; transform: translateY(-2px);
      box-shadow: 0 10px 24px -6px var(--sky-glow); color: var(--ink);
    }

    /* ══════════════════════════════════════════
       HERO
    ══════════════════════════════════════════ */
    .hero {
      position: relative;
      min-height: 100vh;
      display: flex; align-items: center;
      overflow: hidden;
    }

    /* Full video background */
    .hero-video-bg {
      position: absolute; inset: 0; z-index: 0; overflow: hidden;
    }
    .hero-video-bg video {
      width: 100%; height: 100%; object-fit: cover;
    }
    /* Radial dark overlay — darker on left where text sits */
    .hero-overlay {
      position: absolute; inset: 0; z-index: 1; pointer-events: none;
      background:
        linear-gradient(to right, rgba(6,11,26,.92) 0%, rgba(6,11,26,.75) 40%, rgba(6,11,26,.2) 75%, transparent 100%),
        linear-gradient(to top, rgba(6,11,26,.6) 0%, transparent 40%);
    }

    /* Bubble canvas */
    .bubbles {
      position: absolute; inset: 0; z-index: 2; overflow: hidden; pointer-events: none;
    }
    .bubble {
      position: absolute; border-radius: 50%;
      background: radial-gradient(circle at 32% 28%,
        rgba(255,255,255,.88) 0%, rgba(0,198,248,.55) 26%,
        rgba(0,119,182,.28) 60%, rgba(0,30,80,.03) 100%);
      border: 1px solid rgba(0,198,248,.4);
      box-shadow: inset 2px 2px 5px rgba(255,255,255,.6), 0 0 8px rgba(0,198,248,.15);
      animation: bRise linear infinite;
    }
    .bubble::after {
      content: ''; position: absolute;
      top: 14%; left: 17%; width: 24%; height: 24%; border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,.92) 0%, transparent 100%);
      filter: blur(1px);
    }
    @keyframes bRise {
      0%   { opacity: 0; transform: translateY(0) translateX(0); }
      8%   { opacity: .65; }
      90%  { opacity: .45; }
      100% { opacity: 0; transform: translateY(-105vh) translateX(var(--drift,0px)); }
    }

    /* Main container */
    .hero-container {
      position: relative; z-index: 3;
      width: 100%; max-width: 1380px;
      margin: 0 auto;
      padding: 0 clamp(1.5rem, 5vw, 3rem);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
      align-items: center;
    }

    /* ─── LEFT ─── */
    .hero-content { padding-right: 1rem; }

    .pill-group {
      display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1.6rem;
    }
    .pill {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.1);
      backdrop-filter: blur(10px);
      padding: .42rem 1.1rem; border-radius: 40px;
      font-size: .78rem; font-weight: 500; color: var(--cloud);
      display: inline-flex; align-items: center; gap: .45rem;
      transition: all .25s;
    }
    .pill:hover { border-color: var(--sky); color: var(--white); }
    .pill i { color: var(--sky); font-size: .72rem; }

    .hero-title {
      font-size: clamp(2.8rem, 4.5vw, 5rem);
      line-height: 1; margin-bottom: 1.4rem;
      letter-spacing: -.5px;
    }
    .hero-title-line { display: block; }
    .hero-title-gradient {
      background: linear-gradient(135deg, var(--sky) 0%, var(--lavender) 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .hero-title-gold { color: var(--gold); }

    .hero-desc {
      font-size: 1rem; font-weight: 300; line-height: 1.7;
      color: rgba(255,255,255,.65); max-width: 460px; margin-bottom: 2rem;
    }

    .hero-cta { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 2.4rem; }
    .btn-primary-hero {
      background: linear-gradient(135deg, var(--sky), #3b82f6);
      border: none; color: var(--ink);
      padding: .9rem 2.2rem; border-radius: 40px;
      font-weight: 700; font-size: .95rem;
      text-decoration: none; transition: all .3s var(--ease);
      display: inline-flex; align-items: center; gap: .5rem;
    }
    .btn-primary-hero:hover {
      transform: translateY(-3px); color: var(--ink);
      box-shadow: 0 18px 30px -10px var(--sky-glow);
    }
    .btn-outline-hero {
      background: transparent;
      border: 1px solid rgba(255,255,255,.2); color: var(--white);
      padding: .9rem 2.2rem; border-radius: 40px;
      font-weight: 600; font-size: .95rem;
      text-decoration: none; transition: all .3s var(--ease);
      display: inline-flex; align-items: center; gap: .5rem;
    }
    .btn-outline-hero:hover {
      border-color: var(--sky); background: rgba(0,198,248,.08);
      transform: translateY(-3px); color: var(--white);
    }

    /* Stats */
    .hero-stats {
      display: flex; gap: 0;
      padding-top: 1.6rem;
      border-top: 1px solid rgba(0,198,248,.14);
    }
    .stat { flex: 1; }
    .stat-num {
      font-family: 'Syne', sans-serif;
      font-size: 2rem; font-weight: 800; line-height: 1;
      color: var(--white);
    }
    .stat-num sup { font-size: .95rem; color: var(--sky); vertical-align: super; }
    .stat-lbl {
      font-family: 'DM Mono', monospace;
      font-size: .58rem; letter-spacing: 1.5px; text-transform: uppercase;
      color: var(--muted); margin-top: .2rem;
    }
    .stat-sep { width: 1px; background: rgba(0,198,248,.15); margin: 0 1.2rem; }

    /* ─── RIGHT: Glass full circle carousel ─── */
    .hero-visual {
      display: flex; align-items: center; justify-content: center;
      position: relative; height: 560px;
    }

    .glass-circle {
      position: relative;
      width: 480px; height: 480px;
      border-radius: 50%;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,.1);
      box-shadow:
        0 0 0 1px rgba(0,198,248,.12),
        0 40px 80px -20px rgba(0,0,0,.7),
        0 0 60px -20px rgba(0,198,248,.15);
      background: rgba(255,255,255,.02);
      flex-shrink: 0;
    }

    /* Carousel slide images */
    .carousel-slide {
      position: absolute; inset: 0;
      background-size: cover; background-position: center;
      opacity: 0;
      transition: opacity 1.1s ease;
      will-change: opacity;
    }
    .carousel-slide.active { opacity: 1; z-index: 1; }

    /* Inner overlays */
    .circle-glow {
      position: absolute; inset: 0; z-index: 2; pointer-events: none; border-radius: 50%;
      background: radial-gradient(circle at 28% 28%, rgba(0,198,248,.18) 0%, transparent 65%);
    }
    .circle-vignette {
      position: absolute; inset: 0; z-index: 2; pointer-events: none; border-radius: 50%;
      background: radial-gradient(circle at 72% 72%, rgba(6,11,26,.6) 0%, transparent 60%);
    }

    /* Dots */
    .carousel-dots {
      position: absolute; top: 24px; left: 50%;
      transform: translateX(-50%);
      z-index: 4; display: flex; gap: 6px;
    }
    .cdot {
      width: 7px; height: 7px; border-radius: 50%;
      background: rgba(255,255,255,.28); border: none;
      cursor: pointer; padding: 0; transition: all .3s ease;
    }
    .cdot.active {
      width: 26px; border-radius: 4px;
      background: var(--sky); box-shadow: 0 0 10px rgba(0,198,248,.7);
    }

    /* Progress bar */
    .circle-progress {
      position: absolute; bottom: 0; left: 0; right: 0; z-index: 4;
      height: 2px; background: rgba(255,255,255,.08);
    }
    .circle-progress-fill {
      height: 100%; width: 0%;
      background: linear-gradient(to right, var(--sky), #19d4ff);
      transition: width 4s linear;
    }

    /* Arrows */
    .c-arrow {
      position: absolute; top: 50%; transform: translateY(-50%);
      z-index: 4; width: 40px; height: 40px; border-radius: 50%;
      background: rgba(6,11,26,.5); backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,.14);
      color: var(--white); font-size: .9rem;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: all .28s var(--ease);
    }
    .c-arrow:hover {
      background: var(--sky); border-color: var(--sky);
      color: var(--ink); box-shadow: 0 0 18px var(--sky-glow);
    }
    .c-arrow-prev { left: -22px; }
    .c-arrow-next { right: -22px; }

    /* Mute btn */
    .mute-btn {
      position: absolute; bottom: 2rem; left: 2rem; z-index: 10;
      width: 44px; height: 44px; border-radius: 50%;
      background: rgba(255,255,255,.05); backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.12);
      color: var(--white); cursor: pointer; font-size: 1rem;
      display: flex; align-items: center; justify-content: center;
      transition: all .28s var(--ease);
    }
    .mute-btn:hover { background: var(--sky); border-color: var(--sky); color: var(--ink); }

    /* Bottom wave */
    .hero-wave {
      position: absolute; bottom: 0; left: 0; right: 0; z-index: 4;
      line-height: 0; pointer-events: none;
    }
    .hero-wave svg { width: 100%; display: block; }

    /* ══════════════════════════════════════════
       SERVICES
    ══════════════════════════════════════════ */
    .services-section {
      padding: 100px clamp(1.5rem, 6vw, 4rem);
      background: var(--ink2);
      position: relative; overflow: hidden;
    }
    .services-section::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, var(--sky), transparent);
    }
    .services-section::after {
      content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .022;
      background-image: radial-gradient(rgba(255,255,255,.8) 1px, transparent 1px);
      background-size: 28px 28px;
    }
    .section-header { text-align: center; margin-bottom: 3.5rem; }
    .section-eyebrow {
      font-family: 'DM Mono', monospace;
      font-size: .72rem; letter-spacing: 3px; text-transform: uppercase;
      color: var(--sky); margin-bottom: .8rem;
    }
    .section-title {
      font-size: clamp(2.2rem, 4vw, 3.2rem);
      color: var(--white); margin-bottom: .8rem;
    }
    .section-title span {
      background: linear-gradient(135deg, var(--sky), var(--lavender));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .section-sub { color: var(--cloud); max-width: 520px; margin: 0 auto; line-height: 1.65; opacity: .85; }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem; max-width: 1100px; margin: 0 auto;
    }
    .service-card {
      background: var(--glass); border: 1px solid var(--glass-b);
      border-radius: 22px; padding: 2.2rem 1.8rem;
      transition: all .3s var(--ease); position: relative; overflow: hidden;
    }
    .service-card::before {
      content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
      background: radial-gradient(circle at 50% 0%, rgba(0,198,248,.1), transparent 70%);
      opacity: 0; transition: opacity .3s ease;
    }
    .service-card:hover {
      transform: translateY(-8px); border-color: rgba(0,198,248,.28);
      box-shadow: 0 28px 50px -24px rgba(0,198,248,.25);
    }
    .service-card:hover::before { opacity: 1; }
    .service-icon {
      width: 56px; height: 56px; border-radius: 14px;
      background: rgba(0,198,248,.1); border: 1px solid rgba(0,198,248,.18);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; color: var(--sky); margin-bottom: 1.3rem;
      transition: all .3s var(--ease);
    }
    .service-card:hover .service-icon {
      background: var(--sky); color: var(--ink);
      transform: scale(1.1) rotate(5deg);
    }
    .service-name {
      font-size: 1.4rem; color: var(--white); margin-bottom: .6rem;
    }
    .service-desc {
      font-size: .88rem; color: var(--cloud); line-height: 1.65;
      margin-bottom: 1.3rem; opacity: .85;
    }
    .service-price {
      font-family: 'DM Mono', monospace;
      font-size: .75rem; color: var(--sky); letter-spacing: 1px;
    }

    /* ══════════════════════════════════════════
       PORTAL
    ══════════════════════════════════════════ */
    .portal-section {
      padding: 100px clamp(1.5rem, 6vw, 4rem);
      background: var(--ink);
    }
    .portal-grid {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 4rem; max-width: 1100px; margin: 0 auto; align-items: center;
    }
    .portal-visual { position: relative; height: 380px; }
    .portal-cards-wrap {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .floating-card {
      position: absolute; width: 158px;
      padding: 1.4rem 1.2rem;
      background: var(--glass); backdrop-filter: blur(12px);
      border: 1px solid var(--glass-b);
      border-radius: 18px; text-align: center;
      animation: floatY 6s ease-in-out infinite;
    }
    .floating-card:nth-child(1) { top: 15%; left: 8%; animation-delay: 0s; }
    .floating-card:nth-child(2) {
      top: 50%; left: 50%; transform: translate(-50%,-50%);
      width: 175px; border-color: rgba(0,198,248,.3);
      background: rgba(0,198,248,.08);
      animation-delay: 2s;
    }
    .floating-card:nth-child(3) { bottom: 12%; right: 8%; animation-delay: 4s; }
    .floating-card i { font-size: 1.9rem; color: var(--sky); display: block; margin-bottom: .5rem; }
    .floating-card strong { display: block; font-size: .95rem; margin-bottom: .2rem; }
    .floating-card small { font-size: .7rem; color: var(--cloud); opacity: .7; }
    @keyframes floatY {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(-18px); }
    }
    .floating-card:nth-child(2) {
      animation: floatY2 6s ease-in-out 2s infinite;
    }
    @keyframes floatY2 {
      0%, 100% { transform: translate(-50%,-50%) translateY(0); }
      50%       { transform: translate(-50%,-50%) translateY(-18px); }
    }

    .portal-content { padding-left: 1rem; }
    .portal-content .section-eyebrow { text-align: left; margin-bottom: .6rem; }
    .portal-content .section-title { text-align: left; margin-bottom: 1rem; }
    .portal-content .section-sub { margin: 0 0 2rem; text-align: left; }

    .portal-btns { display: flex; flex-direction: column; gap: .85rem; }
    .portal-btn {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1.1rem 1.4rem; border-radius: 14px; text-decoration: none;
      background: var(--glass); border: 1px solid var(--glass-b);
      color: var(--white); transition: all .28s var(--ease);
    }
    .portal-btn:hover {
      border-color: var(--sky); background: var(--glass-hov);
      transform: translateX(8px); color: var(--white);
    }
    .portal-btn-left { display: flex; align-items: center; gap: .85rem; }
    .portal-btn-icon {
      width: 42px; height: 42px; border-radius: 11px;
      background: rgba(0,198,248,.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.15rem; color: var(--sky);
    }
    .portal-btn-info strong { display: block; font-size: .9rem; font-weight: 600; }
    .portal-btn-info span {
      font-family: 'DM Mono', monospace; font-size: .6rem;
      letter-spacing: 1px; text-transform: uppercase; color: var(--cloud); opacity: .7;
    }
    .portal-btn .arrow { color: var(--sky); font-size: .95rem; }

    /* ══════════════════════════════════════════
       FOOTER
    ══════════════════════════════════════════ */
    .site-footer {
      background: var(--ink2);
      border-top: 1px solid rgba(0,198,248,.08);
      padding: 2rem clamp(1.5rem, 6vw, 4rem);
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
    }
    .footer-brand {
      font-family: 'Syne', sans-serif; font-size: 1.2rem; font-weight: 800;
    }
    .footer-brand em { font-style: normal; color: var(--sky); }
    .footer-copy {
      font-family: 'DM Mono', monospace;
      font-size: .6rem; letter-spacing: 1px; color: var(--muted);
    }

    /* ══════════════════════════════════════════
       ANIMATIONS
    ══════════════════════════════════════════ */
    .fade-up {
      opacity: 0; transform: translateY(24px);
      animation: fadeUpAnim .65s var(--ease) forwards;
    }
    .fade-up:nth-child(1) { animation-delay: .1s; }
    .fade-up:nth-child(2) { animation-delay: .2s; }
    .fade-up:nth-child(3) { animation-delay: .3s; }
    .fade-up:nth-child(4) { animation-delay: .4s; }
    .fade-up:nth-child(5) { animation-delay: .5s; }
    @keyframes fadeUpAnim {
      to { opacity: 1; transform: translateY(0); }
    }

    /* ══════════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════════ */
    @media (max-width: 1024px) {
      .hero-container { grid-template-columns: 1fr; text-align: center; padding-top: 100px; }
      .hero-content { padding-right: 0; }
      .pill-group, .hero-cta, .hero-stats { justify-content: center; }
      .hero-desc { margin-left: auto; margin-right: auto; }
      .glass-circle { width: 380px; height: 380px; }
      .hero-visual { height: 440px; }
      .portal-grid { grid-template-columns: 1fr; }
      .portal-content { padding-left: 0; text-align: center; }
      .portal-btns { max-width: 400px; margin: 0 auto; }
      .nav-links { display: none; }
    }
    @media (max-width: 640px) {
      .hero-title { font-size: 2.6rem; }
      .glass-circle { width: 300px; height: 300px; }
      .hero-visual { height: 360px; }
      .hero-cta { flex-direction: column; align-items: center; }
      .hero-stats { gap: 0; }
    }
  </style>
</head>
<body>

  <!-- ════ NAVBAR ════ -->
  <nav class="wb-nav" id="mainNav">
    <a href="#" class="nav-brand">
      <img src="{{ asset('images/logo.png') }}" alt="WashBox" class="nav-brand-logo"
           onerror="this.style.display='none';document.getElementById('logoFb').style.display='flex';" />
      <div class="nav-brand-fallback" id="logoFb"><i class="bi bi-droplet-fill"></i></div>
      Wash<em>Box</em>
    </a>
    <div class="nav-links">
      <a href="#home"     class="nav-link">Home</a>
      <a href="#services" class="nav-link">Services</a>
      <a href="#portal"   class="nav-link">Portal</a>
    </div>
    <div class="nav-actions">
      <a href="{{ route('admin.login') }}" class="btn-ghost-pill">
        <i class="bi bi-shield-lock-fill"></i> Admin
      </a>
      <a href="{{ route('staff.login') }}" class="btn-sky-pill">
        <i class="bi bi-person-badge-fill"></i> Staff
      </a>
    </div>
  </nav>
 
  <!-- ════ HERO ════ -->
  <section class="hero" id="home">

    <!-- Full-hero video -->
    <div class="hero-video-bg">
      <video autoplay muted loop playsinline id="heroVideo">
        <source src="{{ asset('assets/videos/washbox.mp4') }}" type="video/mp4" />
      </video>
    </div>
    <div class="hero-overlay"></div>
    <div class="bubbles" id="bubbles"></div>

    <div class="hero-container">

      <!-- LEFT: copy -->
      <div class="hero-content">
        <div class="pill-group fade-up">
          <span class="pill"><i class="bi bi-geo-alt-fill"></i> Bais City, Negros Oriental</span>
          <span class="pill"><i class="bi bi-circle-fill" style="color:var(--green);font-size:.5rem;"></i> Open Now</span>
        </div>

        <h1 class="hero-title fade-up">
          <span class="hero-title-line">Fresh Laundry,</span>
          <span class="hero-title-line hero-title-gradient">Zero Hassle</span>
          <span class="hero-title-line hero-title-gold">Every Time.</span>
        </h1>

        <p class="hero-desc fade-up">
          Drop-off, self-service, or pickup &amp; delivery — WashBox handles it all.
          Professional care, fast turnaround, and prices that work for you.
        </p>

        <div class="hero-cta fade-up">
          <a href="#services" class="btn-primary-hero">
            <i class="bi bi-grid-3x3-gap-fill"></i> View Services
          </a>
          <a href="#portal" class="btn-outline-hero">
            <i class="bi bi-arrow-right"></i> Team Portal
          </a>
        </div>

        <div class="hero-stats fade-up">
          <div class="stat">
            <div class="stat-num">100<sup>%</sup></div>
            <div class="stat-lbl">Satisfaction</div>
          </div>
          <div class="stat-sep"></div>
          <div class="stat">
            <div class="stat-num">3<sup>+</sup></div>
            <div class="stat-lbl">Branches</div>
          </div>
          <div class="stat-sep"></div>
          <div class="stat">
            <div class="stat-num">₱79<sup>+</sup></div>
            <div class="stat-lbl">Starting</div>
          </div>
          <div class="stat-sep"></div>
          <div class="stat">
            <div class="stat-num">1<sup>day</sup></div>
            <div class="stat-lbl">Turnaround</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: full circle carousel -->
      <div class="hero-visual">
        <div class="glass-circle" id="glassCarousel">
          <!-- Slides -->
          <div class="carousel-slide active" style="background-image:url('{{ asset('images/washbox.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox1.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox2.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox3.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox4.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox5.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox6.jpg') }}')"></div>
          <div class="carousel-slide" style="background-image:url('{{ asset('images/washbox7.jpg') }}')"></div>

          <!-- Inner glow overlays -->
          <div class="circle-glow"></div>
          <div class="circle-vignette"></div>

          <!-- Dots -->
          <div class="carousel-dots" id="carouselDots"></div>

          <!-- Progress -->
          <div class="circle-progress"><div class="circle-progress-fill" id="circleProgress"></div></div>
        </div>

        <!-- Arrows outside circle -->
        <button class="c-arrow c-arrow-prev" id="cPrev" aria-label="Previous">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button class="c-arrow c-arrow-next" id="cNext" aria-label="Next">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Mute -->
    <button class="mute-btn" id="muteBtn" aria-label="Toggle mute">
      <i class="bi bi-volume-mute-fill" id="muteIcon"></i>
    </button>

    <!-- Wave -->
    <div class="hero-wave">
      <svg viewBox="0 0 1440 72" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 72 L0 38 C240 8 480 60 720 38 C960 16 1200 52 1440 26 L1440 72 Z" fill="#0d1535" />
        <path d="M0 72 L0 54 C360 20 720 66 1080 42 C1260 30 1380 52 1440 46 L1440 72 Z" fill="rgba(0,198,248,.05)" />
      </svg>
    </div>
  </section>

  <!-- ════ SERVICES ════ -->
  <section class="services-section" id="services">
    <div class="section-header">
      <div class="section-eyebrow">What We Offer</div>
      <h2 class="section-title">Premium <span>Services</span></h2>
      <p class="section-sub">Everything your clothes need, handled with care. Choose what works best for you.</p>
    </div>
    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon"><i class="bi bi-bag-check-fill"></i></div>
        <h3 class="service-name">Drop-Off Service</h3>
        <p class="service-desc">Drop your laundry with us and pick it up fresh and folded. Quick, easy, and reliable every time.</p>
        <div class="service-price">FROM ₱79 / LOAD</div>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="bi bi-arrow-repeat"></i></div>
        <h3 class="service-name">Self-Service</h3>
        <p class="service-desc">Use our modern machines at your own pace. Coin-operated, clean, and always available when you need them.</p>
        <div class="service-price">FROM ₱50 / CYCLE</div>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="bi bi-truck"></i></div>
        <h3 class="service-name">Pickup &amp; Delivery</h3>
        <p class="service-desc">We come to you. Schedule a pickup and get your laundry back clean and delivered to your door.</p>
        <div class="service-price">FREE PICKUP · BAIS CITY &amp; NEARBY</div>
      </div>
    </div>
  </section>

  <!-- ════ PORTAL ════ -->
  <section class="portal-section" id="portal">
    <div class="portal-grid">
      <div class="portal-visual">
        <div class="portal-cards-wrap">
          <div class="floating-card">
            <i class="bi bi-clipboard-data"></i>
            <strong>Laundries</strong>
            <small>Real-time tracking</small>
          </div>
          <div class="floating-card">
            <i class="bi bi-person-badge-fill"></i>
            <strong>Staff</strong>
            <small>Daily operations</small>
          </div>
          <div class="floating-card">
            <i class="bi bi-shield-lock-fill"></i>
            <strong>Admin</strong>
            <small>Full access</small>
          </div>
        </div>
      </div>

      <div class="portal-content">
        <div class="section-eyebrow">Team Access</div>
        <h2 class="section-title">Team <span>Portal</span></h2>
        <p class="section-sub">Secure login access for WashBox staff and administrators. Manage Laundries, track operations, and keep everything running smoothly.</p>
        <div class="portal-btns">
          <a href="{{ route('admin.login') }}" class="portal-btn">
            <div class="portal-btn-left">
              <div class="portal-btn-icon"><i class="bi bi-shield-lock-fill"></i></div>
              <div class="portal-btn-info">
                <strong>Admin Portal</strong>
                <span>Full system access</span>
              </div>
            </div>
            <i class="bi bi-arrow-right arrow"></i>
          </a>
          <a href="{{ route('staff.login') }}" class="portal-btn">
            <div class="portal-btn-left">
              <div class="portal-btn-icon"><i class="bi bi-person-badge-fill"></i></div>
              <div class="portal-btn-info">
                <strong>Staff Portal</strong>
                <span>Daily operations</span>
              </div>
            </div>
            <i class="bi bi-arrow-right arrow"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ════ FOOTER ════ -->
  <footer class="site-footer">
    <div class="footer-brand">Wash<em>Box</em></div>
    <div class="footer-copy">© {{ date('Y') }} WashBox Laundry Services · Negros Oriental</div>
  </footer>

  <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script>
    /* ── Navbar scroll ── */
    const nav = document.getElementById('mainNav');
    window.addEventListener('scroll', () => nav.classList.toggle('scrolled', scrollY > 40), { passive: true });

    /* ── Smooth scroll ── */
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
      });
    });

    /* ── Bubbles ── */
    (function () {
      const wrap = document.getElementById('bubbles');
      if (!wrap) return;
      for (let i = 0; i < 24; i++) {
        const b = document.createElement('div');
        b.className = 'bubble';
        const s = Math.random() * 65 + 10;
        const drift = (Math.random() - .5) * 55;
        b.style.cssText =
          `width:${s}px;height:${s}px;` +
          `left:${Math.random() * 100}%;bottom:${-s}px;` +
          `animation-duration:${Math.random() * 11 + 9}s;` +
          `animation-delay:-${Math.random() * 10}s;` +
          `opacity:${Math.random() * .3 + .35};` +
          `--drift:${drift}px;`;
        wrap.appendChild(b);
      }
    })();

    /* ── Video autoplay + mute ── */
    document.addEventListener('DOMContentLoaded', () => {
      const v = document.getElementById('heroVideo');
      if (v) v.play().catch(() => {});
    });
    document.getElementById('muteBtn').addEventListener('click', () => {
      const v = document.getElementById('heroVideo');
      const i = document.getElementById('muteIcon');
      if (!v) return;
      v.muted = !v.muted;
      i.className = v.muted ? 'bi bi-volume-mute-fill' : 'bi bi-volume-up-fill';
    });

    /* ── Carousel ── */
    (function () {
      const carousel  = document.getElementById('glassCarousel');
      const slides    = Array.from(carousel.querySelectorAll('.carousel-slide'));
      const dotsWrap  = document.getElementById('carouselDots');
      const prog      = document.getElementById('circleProgress');
      const prevBtn   = document.getElementById('cPrev');
      const nextBtn   = document.getElementById('cNext');
      const N = slides.length;
      let cur = 0, timer = null;

      /* Build dots */
      slides.forEach((_, i) => {
        const d = document.createElement('button');
        d.className = 'cdot' + (i === 0 ? ' active' : '');
        d.setAttribute('aria-label', `Slide ${i + 1}`);
        d.addEventListener('click', () => { stop(); goTo(i); start(); });
        dotsWrap.appendChild(d);
      });

      function updateDots() {
        dotsWrap.querySelectorAll('.cdot').forEach((d, i) => d.classList.toggle('active', i === cur));
      }

      function goTo(n) {
        slides[cur].classList.remove('active');
        cur = ((n % N) + N) % N;
        slides[cur].classList.add('active');
        updateDots();
        resetProg();
      }

      function resetProg() {
        prog.style.transition = 'none'; prog.style.width = '0%';
        requestAnimationFrame(() => requestAnimationFrame(() => {
          prog.style.transition = 'width 4s linear'; prog.style.width = '100%';
        }));
      }

      function start() { resetProg(); timer = setInterval(() => goTo(cur + 1), 4000); }
      function stop()  { clearInterval(timer); prog.style.transition = 'none'; prog.style.width = '0%'; }

      prevBtn.addEventListener('click', () => { stop(); goTo(cur - 1); start(); });
      nextBtn.addEventListener('click', () => { stop(); goTo(cur + 1); start(); });
      carousel.addEventListener('mouseenter', stop);
      carousel.addEventListener('mouseleave', start);

      let tx = 0;
      carousel.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
      carousel.addEventListener('touchend', e => {
        const d = tx - e.changedTouches[0].clientX;
        if (Math.abs(d) > 40) { stop(); goTo(cur + (d > 0 ? 1 : -1)); start(); }
      }, { passive: true });

      updateDots();
      start();
    })();
  </script>
</body>
</html>
