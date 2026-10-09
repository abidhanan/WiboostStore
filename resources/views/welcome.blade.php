<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wiboost Store</title>
    @include('partials.seo-meta', [
        'title' => 'Wiboost Store',
        'description' => 'Platform penyedia layanan suntik sosmed, top up game, paket data operator, dan aplikasi premium dengan proses cepat, aman, dan praktis.',
        'canonical' => route('home'),
    ])
    @include('partials.site-icons')
    <link rel="preload" as="image" href="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" fetchpriority="high">

    @include('partials.font-nunito')
    @include('partials.vite-preload')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.vite-fallback')
    @include('partials.ui-performance-tuning')
    @include('partials.microsoft-clarity')
    @include('partials.google-analytics')
    @include('partials.responsive-qa-guards')
    <style>
         html {
             scrollbar-color: #bde0fe #f4f9ff;
             scrollbar-width: thin;
         }
         ::-webkit-scrollbar { width: 8px; height: 8px; }
         ::-webkit-scrollbar-track { background: #f4f9ff; }
         ::-webkit-scrollbar-thumb {
             background: #bde0fe;
             border: 2px solid #f4f9ff;
             border-radius: 999px;
         }
         ::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
         body { font-family: 'Nunito', sans-serif; }
         .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
         .bg-wiboost-card { background-color: rgba(255, 255, 255, 0.9); }
         
         .animate-float { animation: float 6s ease-in-out infinite; }
         .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
         @keyframes float {
             0% { transform: translateY(0px); }
             50% { transform: translateY(-15px); }
             100% { transform: translateY(0px); }
         }
         .hide-scroll::-webkit-scrollbar { display: none; }
         .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
         .welcome-section-inner {
             width: 100%;
             margin-left: auto;
             margin-right: auto;
             display: flex;
             flex-direction: column;
             align-items: center;
             text-align: center;
         }
         .welcome-cta-gap {
             margin-bottom: clamp(4.5rem, 9vw, 7.5rem);
         }
         .welcome-hero-section {
             min-height: 100svh;
             padding-top: clamp(8.25rem, 15vh, 10.75rem);
             padding-bottom: clamp(3.25rem, 5.5vh, 4.75rem);
         }
         .welcome-hero-section > .welcome-section-inner {
             transform: translateY(-2.5rem);
         }
         @supports not (height: 100svh) {
             .welcome-hero-section {
                 min-height: 100vh;
             }
         }
         .welcome-hero-title {
             font-size: clamp(1.8rem, 10vw, 2.8rem);
             line-height: 0.98;
             letter-spacing: 0;
             white-space: nowrap;
         }
         .welcome-hero-logo {
             width: 6rem;
             height: 6rem;
             margin-bottom: 1.25rem;
             object-fit: contain;
             filter: drop-shadow(0 10px 16px rgba(90, 118, 200, 0.24));
         }
         .welcome-live-stats {
             width: min(100%, 64rem);
             margin-left: auto;
             margin-right: auto;
         }
         .welcome-stats-grid {
             display: grid;
             grid-template-columns: repeat(2, minmax(0, 1fr));
             gap: 1rem;
             width: 100%;
             align-items: center;
         }
         .welcome-stat-card {
             text-align: center;
             padding: 0.5rem 0.25rem;
         }
         .welcome-stat-users {
             order: 1;
         }
         .welcome-stat-transactions {
             order: 2;
         }
         .welcome-stat-services {
             order: 3;
             grid-column: 1 / -1;
         }
         .order-flow {
             position: relative;
             width: 100%;
         }
         .order-step-card {
             position: relative;
         }
         .order-step-node {
             position: relative;
             z-index: 2;
             display: flex;
             align-items: center;
             justify-content: center;
             width: 3.25rem;
             height: 3.25rem;
             border-radius: 1.2rem;
             border: 4px solid #ffffff;
             background: #5a76c8;
             color: #ffffff;
             font-weight: 900;
             font-size: 1.35rem;
             box-shadow: 0 16px 30px rgba(90, 118, 200, 0.28);
             flex-shrink: 0;
         }
         .order-step-content {
             flex: 1;
             border: 4px solid #ffffff;
             border-radius: 1.6rem;
             background: #f4f9ff;
             padding: 1.1rem 1.2rem;
             box-shadow: 0 18px 40px rgba(189, 224, 254, 0.35);
         }
        .welcome-faq-list {
            width: min(100%, 64rem);
        }
        .welcome-faq-list > details {
            display: block;
            width: 100%;
            min-width: 100%;
            max-width: 100%;
            align-self: stretch;
            box-sizing: border-box;
            text-align: left;
        }
        .welcome-faq-list > details > summary {
            width: 100%;
            box-sizing: border-box;
        }
         .welcome-category-icon {
             width: 4.75rem;
             height: 4.75rem;
             flex: 0 0 4.75rem;
             display: flex;
             align-items: center;
             justify-content: center;
             overflow: hidden;
             border-radius: 1.25rem;
             border: 4px solid #ffffff;
             background: #f4f9ff;
             box-shadow: inset 0 0 0 2px rgba(189, 224, 254, 0.45), 0 8px 18px rgba(189, 224, 254, 0.28);
             padding: 0;
         }
         .welcome-category-title {
             font-size: 0.75rem;
             line-height: 1.15;
         }
         .welcome-category-icon img {
             width: 100%;
             height: 100%;
             object-fit: cover;
             object-position: center;
             border-radius: 1rem;
         }
         .welcome-category-grid {
             width: 100%;
         }
         .discount-corner-ribbon {
             pointer-events: none;
             position: absolute;
             right: -1px;
             top: -1px;
             z-index: 20;
             width: 7rem;
             height: 7rem;
             overflow: hidden;
             border-top-right-radius: 1.55rem;
         }
         .discount-corner-ribbon__text {
             position: absolute;
             right: -2.55rem;
             top: 1.2rem;
             display: block;
             width: 9.75rem;
             transform: rotate(45deg);
             transform-origin: center;
             background: #5a76c8;
             padding: 0.42rem 0;
             text-align: center;
             font-size: 0.5rem;
             font-weight: 900;
             letter-spacing: 0.14em;
             color: white;
             text-transform: uppercase;
             box-shadow: 0 12px 26px rgba(90, 118, 200, 0.28);
         }
         /* Pita diskon welcome -> banner SKEU mengilap (selaras partial global aturan 29) */
         body.wiboost-welcome-page .discount-corner-ribbon__text {
             background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.55),
                 inset 0 -3px 5px rgba(43, 58, 103, 0.40),
                 0 10px 22px rgba(43, 58, 103, 0.30) !important;
         }

         @media (pointer: coarse) {
             .welcome-hero-section {
                 min-height: 100svh;
                 padding-top: clamp(7.5rem, 14vh, 9rem);
                 padding-bottom: clamp(3.5rem, 7vh, 5rem);
             }
             .welcome-cta-gap {
                 margin-bottom: clamp(3rem, 7vw, 4.5rem);
             }
             #kategori {
                 padding-top: clamp(4rem, 8vw, 5.5rem);
             }
             .welcome-category-grid {
                 display: grid;
                 grid-template-columns: repeat(2, minmax(0, 1fr));
                 width: min(100%, 34rem);
                 gap: 1rem;
             }
             .welcome-category-card {
                 width: 100% !important;
                 max-width: none;
             }
         }
         @media (min-width: 640px) {
             .welcome-hero-logo {
                 width: 8rem;
                 height: 8rem;
                 margin-bottom: 1.5rem;
             }
             .welcome-hero-title {
                 font-size: 4.5rem;
             }
             .welcome-category-icon {
                 width: 6rem;
                 height: 6rem;
                 flex-basis: 6rem;
                 margin-bottom: 1.25rem;
             }
             .welcome-category-title {
                 font-size: 1.125rem;
             }
         }
         @media (min-width: 768px) {
             .welcome-hero-title {
                 font-size: 5.8rem;
             }
             .welcome-category-title {
                 font-size: 1rem;
                 line-height: 1.2;
                 white-space: nowrap;
             }
         }
         @media (min-width: 1024px) {
             .welcome-hero-section {
                 padding-top: clamp(11rem, 19vh, 14rem);
                 padding-bottom: clamp(4.5rem, 9vw, 7.5rem);
             }

             .welcome-hero-section > .welcome-section-inner {
                 transform: translateY(-4rem);
             }

             .welcome-hero-logo {
                 width: 9rem;
                 height: 9rem;
             }

             .welcome-hero-title {
                 font-size: clamp(5rem, 10vw, 8rem);
             }
         }
         @media (pointer: coarse) {
             .welcome-hero-section {
                 min-height: 100svh;
                 padding-top: clamp(7.5rem, 14vh, 9rem);
                 padding-bottom: clamp(3.5rem, 7vh, 5rem);
             }
         }
        @media (pointer: coarse) and (min-width: 768px) {
            .welcome-hero-section {
                min-height: min(100svh, 56rem);
                padding-top: 8rem;
                padding-bottom: 5rem;
             }
             .welcome-hero-section > .welcome-section-inner {
                transform: none;
            }
        }
        @media (pointer: coarse) and (max-width: 767px) {
            .welcome-hero-section > .welcome-section-inner {
                transform: translateY(-1.5rem);
            }
        }
        @media (min-width: 1024px) and (hover: hover) and (pointer: fine) {
            .welcome-hero-section {
                --welcome-desktop-navbar-height: 5.25rem;
                --welcome-desktop-logo-gap: 3rem;
                min-height: auto;
                align-items: flex-start;
                padding-top: calc(var(--welcome-desktop-navbar-height) + var(--welcome-desktop-logo-gap));
                padding-bottom: 3rem;
            }

            .welcome-hero-section > .welcome-section-inner {
                transform: none;
            }

            .welcome-hero-logo {
                margin-bottom: calc(var(--welcome-desktop-logo-gap) - 2rem);
            }

            .welcome-cta-gap {
                margin-bottom: 3rem;
            }
        }
        .welcome-feature-icon-wrap {
             width: 6rem;
             height: 6rem;
             flex: 0 0 6rem;
             display: flex;
             align-items: center;
             justify-content: center;
             overflow: hidden;
             border-radius: 1.25rem;
             border: 4px solid #ffffff;
             background: linear-gradient(180deg, #ffffff 0%, #f4f9ff 100%);
             color: #5a76c8;
             box-shadow: inset 0 0 0 2px rgba(189, 224, 254, 0.45), 0 8px 18px rgba(189, 224, 254, 0.28);
             padding: 1.3rem;
         }
         .welcome-feature-icon {
             width: 100%;
             height: 100%;
             filter: drop-shadow(0 8px 14px rgba(90, 118, 200, 0.16));
         }
         .welcome-why-heading {
             white-space: nowrap;
          }
         @media (max-width: 767px) {
             .welcome-why-heading {
                 font-size: clamp(1.25rem, 6.4vw, 1.7rem) !important;
                 line-height: 1.12;
             }
             .welcome-live-stats {
                 width: min(100%, 24rem);
             }
             .welcome-stats-grid {
                 background: transparent;
                 border: 0;
                 box-shadow: none;
                 padding: 0;
                 row-gap: 1.2rem;
             }
             .welcome-stat-card {
                  min-height: 6.25rem;
                  display: flex;
                  flex-direction: column;
                  justify-content: center;
                  background: rgba(255, 255, 255, 0.9);
                  border: 4px solid #ffffff;
                  border-radius: 1.35rem;
                  box-shadow: 0 16px 34px rgba(189, 224, 254, 0.32);
                  padding: 1rem 0.75rem;
             }
             .order-flow {
                 max-width: 34rem;
                 margin-inline: auto;
             }
             .order-flow::before {
                 content: "";
                 position: absolute;
                 left: 1.55rem;
                 top: 1.6rem;
                 bottom: 1.6rem;
                 width: 4px;
                 border-radius: 999px;
                 background: linear-gradient(180deg, #5a76c8, #4bc6b9);
                 opacity: 0.28;
             }
             .order-step-card {
                 display: flex;
                 gap: 1rem;
                  align-items: center;
                 text-align: left;
             }
             .order-step-card + .order-step-card {
                 margin-top: 1rem;
             }
             .order-step-content h3,
             .order-step-content p {
                 text-align: left;
             }
         }

         @media (min-width: 768px) {
             .welcome-stats-grid {
                 grid-template-columns: repeat(3, minmax(0, 1fr));
                 background: rgba(255, 255, 255, 0.9);
                 backdrop-filter: blur(12px);
                 border: 4px solid #ffffff;
                 border-radius: 2rem;
                 box-shadow: 0 22px 45px rgba(189, 224, 254, 0.4);
                 padding: 2rem;
             }
             .welcome-stat-services {
                 order: initial;
                 grid-column: auto;
             }
             .welcome-stat-users,
             .welcome-stat-transactions {
                 order: initial;
             }
             .order-flow-grid {
                 display: grid;
                 grid-template-columns: repeat(4, minmax(0, 1fr));
                 gap: 1.25rem;
             }
             .order-step-card {
                 display: flex;
                 min-height: 18rem;
                 flex-direction: column;
                 align-items: center;
                 justify-content: flex-start;
                 text-align: center;
                 border: 4px solid #ffffff;
                 border-radius: 2rem;
                 background: linear-gradient(180deg, #ffffff 0%, #f4f9ff 100%);
                 padding: 1.5rem 1.1rem;
                 box-shadow: 0 22px 45px rgba(189, 224, 254, 0.35);
                 transition: transform 0.25s ease, box-shadow 0.25s ease;
             }
             .order-step-card:hover {
                 transform: translateY(-6px);
                 box-shadow: 0 28px 55px rgba(90, 118, 200, 0.18);
             }
             .order-step-node {
                 width: 5rem;
                 height: 5rem;
                 border-radius: 1.7rem;
                 font-size: 2rem;
                 margin-bottom: 1.2rem;
             }
             .order-step-content {
                 border: 0;
                 background: transparent;
                 padding: 0;
                 box-shadow: none;
             }
             .order-step-content p {
                 padding-inline: 0;
             }
         }
         
         /* Animasi mulus untuk FAQ accordion */
         details > summary { list-style: none; }
         details > summary::-webkit-details-marker { display: none; }
         
         /* Panah FAQ berputar menunjuk ke atas saat elemen terbuka */
         details[open] summary .faq-icon {
             transform: rotate(180deg);
         }
         .faq-icon {
             transition: transform 0.3s ease-in-out;
         }
         body.wiboost-welcome-page > nav,
         body.wiboost-welcome-page > nav > div {
             width: 100% !important;
             max-width: none !important;
         }
         body.wiboost-welcome-page > nav > div > div {
             border-radius: 0 !important;
         }
         @media (min-width: 1024px) {
             .welcome-section-inner,
             footer > div {
                 max-width: min(1600px, calc(100vw - 8rem)) !important;
             }

             .welcome-section-inner {
                 padding-left: 2rem !important;
                 padding-right: 2rem !important;
             }
         }
         /* Land the catalog heading just below the fixed navbar when arriving via #kategori. */
         #kategori-heading {
             scroll-margin-top: 5.75rem;
         }
         @media (min-width: 640px) {
             #kategori-heading {
                 scroll-margin-top: 6.5rem;
             }
         }

         /* ===================== SKEUOMORPHISM (tema warna tetap) ===================== */
         /* Permukaan mengilap (gradasi), tepi bevel (highlight atas + bayangan dalam bawah),
            pantulan cahaya, dan bayangan jatuh berlapis. Warna dari tema. */

         body.wiboost-welcome-page .skeu {
             background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 inset 0 -3px 6px rgba(163, 187, 251, 0.28),
                 0 2px 3px rgba(90, 118, 200, 0.14),
                 0 12px 24px -6px rgba(90, 118, 200, 0.30) !important;
             transition: transform 0.25s ease, box-shadow 0.25s ease;
         }
         body.wiboost-welcome-page .skeu-hover:hover {
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 inset 0 -3px 6px rgba(163, 187, 251, 0.30),
                 0 4px 6px rgba(90, 118, 200, 0.16),
                 0 20px 34px -8px rgba(90, 118, 200, 0.36) !important;
         }

         body.wiboost-welcome-page .skeu-pill {
             position: relative;
             overflow: hidden;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.9),
                 inset 0 -2px 4px rgba(163, 187, 251, 0.30),
                 0 4px 10px rgba(90, 118, 200, 0.22) !important;
         }
         body.wiboost-welcome-page .skeu-pill::before {
             content: "";
             position: absolute;
             left: 0; right: 0; top: 0;
             height: 52%;
             border-radius: inherit;
             background: linear-gradient(180deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0) 100%);
             pointer-events: none;
         }

         body.wiboost-welcome-page .skeu-primary {
             position: relative;
             overflow: hidden;
             background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.60),
                 inset 0 -4px 7px rgba(43, 58, 103, 0.45),
                 0 8px 18px rgba(43, 58, 103, 0.35) !important;
             transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
         }
         body.wiboost-welcome-page .skeu-primary::before {
             content: "";
             position: absolute;
             left: 0; right: 0; top: 0;
             height: 50%;
             border-radius: inherit;
             background: linear-gradient(180deg, rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0.05) 100%);
             pointer-events: none;
         }
         body.wiboost-welcome-page .skeu-primary:hover { filter: brightness(1.04); }
         body.wiboost-welcome-page .skeu-primary:active {
             transform: translateY(1px);
             box-shadow:
                 inset 0 2px 6px rgba(43, 58, 103, 0.50),
                 inset 0 1px 0 rgba(255, 255, 255, 0.25),
                 0 3px 7px rgba(43, 58, 103, 0.30) !important;
         }

         body.wiboost-welcome-page .welcome-category-card {
             border-radius: 1.9rem;
             background-image: linear-gradient(180deg, #ffffff 0%, #e8f0ff 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 inset 0 -3px 6px rgba(163, 187, 251, 0.30),
                 0 2px 3px rgba(90, 118, 200, 0.14),
                 0 14px 26px -6px rgba(90, 118, 200, 0.30) !important;
             transition: transform 0.25s ease, box-shadow 0.25s ease;
         }
         body.wiboost-welcome-page .welcome-category-card:hover {
             transform: translateY(-4px);
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 inset 0 -3px 6px rgba(163, 187, 251, 0.32),
                 0 4px 6px rgba(90, 118, 200, 0.16),
                 0 24px 40px -8px rgba(90, 118, 200, 0.36) !important;
         }

         body.wiboost-welcome-page .welcome-category-icon,
         body.wiboost-welcome-page .welcome-feature-icon-wrap {
             position: relative;
             overflow: hidden;
             background-image: linear-gradient(160deg, #ffffff 0%, #dbe7ff 100%) !important;
             box-shadow:
                 inset 0 2px 1px rgba(255, 255, 255, 0.95),
                 inset 0 -4px 7px rgba(163, 187, 251, 0.40),
                 0 8px 16px rgba(90, 118, 200, 0.28) !important;
         }

         body.wiboost-welcome-page .order-step-node {
             position: relative;
             overflow: hidden;
             background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.60),
                 inset 0 -4px 7px rgba(43, 58, 103, 0.45),
                 0 8px 16px rgba(43, 58, 103, 0.30) !important;
         }
         body.wiboost-welcome-page .order-step-node::before {
             content: "";
             position: absolute;
             left: 0; right: 0; top: 0;
             height: 50%;
             border-radius: inherit;
             background: linear-gradient(180deg, rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0.05) 100%);
             pointer-events: none;
         }

         body.wiboost-welcome-page .nav-skeu {
             background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 0 6px 16px rgba(90, 118, 200, 0.25) !important;
         }

         /* Garis footer dibuat sederhana agar pemisahnya tetap bersih. */
         body.wiboost-welcome-page .footer-top-skeu {
             border-top: 0 !important;
             background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
             box-shadow: none !important;
         }

         body.wiboost-welcome-page .footer-skeu-divider {
             border-top: 2px solid #dce7f8 !important;
             box-shadow: none !important;
         }

         body.wiboost-welcome-page .footer-description {
             width: auto;
             max-width: 22rem;
             font-size: 12px;
             line-height: 1.5;
             transform: none;
         }
         @media (min-width: 380px) {
             body.wiboost-welcome-page .footer-description {
                 max-width: 22rem;
             }
         }
         @media (min-width: 640px) {
             body.wiboost-welcome-page .footer-description {
                 max-width: 36rem;
                 font-size: 12px;
             }
         }
         @media (min-width: 1024px) {
             body.wiboost-welcome-page .footer-description {
                 width: max-content;
                 max-width: none;
                 font-size: 14px;
                 white-space: nowrap;
             }
         }
         @media (max-width: 639px) {
             body.wiboost-welcome-page .footer-top-skeu > div {
                 padding-left: 0.5rem !important;
                 padding-right: 0.5rem !important;
             }
             body.wiboost-welcome-page .footer-legal-links {
                 display: flex !important;
                 flex-wrap: nowrap !important;
                 justify-content: center !important;
                 gap: 0.25rem !important;
             }
             body.wiboost-welcome-page .footer-legal-link {
                 flex: 0 0 auto !important;
                 width: auto !important;
                 min-height: 0;
                 padding: 0.375rem 0.75rem !important;
                 font-size: 9px !important;
             }
         }

         /* Ikon sosial footer mengilap (khusus ikon, bukan link kredit Abid) */
         body.wiboost-welcome-page footer a.footer-social {
             position: relative;
             overflow: hidden;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.9),
                 inset 0 -3px 5px rgba(163, 187, 251, 0.30),
                 0 6px 13px rgba(90, 118, 200, 0.24) !important;
             transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
         }

         /* Label legal footer: efek mengilap TANPA border & TANPA menambah padding,
            jadi ukuran label tetap ringkas dan tulisannya tetap terlihat besar seperti semula */
         body.wiboost-welcome-page .footer-legal-link {
             background-image: linear-gradient(180deg, #ffffff 0%, #e8f0ff 100%) !important;
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 inset 0 -2px 4px rgba(163, 187, 251, 0.30),
                 0 4px 9px -2px rgba(90, 118, 200, 0.22) !important;
             transition: transform 0.18s ease, box-shadow 0.18s ease;
         }
         body.wiboost-welcome-page .footer-legal-link:hover {
             box-shadow:
                 inset 0 1px 0 rgba(255, 255, 255, 0.95),
                 inset 0 -2px 4px rgba(163, 187, 251, 0.32),
                 0 7px 14px -3px rgba(90, 118, 200, 0.28) !important;
         }

         /* Sisa garis putus-putus -> garis skeu putih rapi (selaras partial global aturan 27) */
         body.wiboost-welcome-page [class*="border-dashed"] {
             border-style: solid !important;
             border-color: #ffffff !important;
             box-shadow:
                 0 1px 0 rgba(189, 224, 254, 0.55),
                 0 -1px 0 rgba(189, 224, 254, 0.55) !important;
         }

         @media (max-width: 767px) {
             body.wiboost-welcome-page .welcome-stat-card {
                 background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
                 box-shadow:
                     inset 0 1px 0 rgba(255, 255, 255, 0.95),
                     inset 0 -3px 6px rgba(163, 187, 251, 0.28),
                     0 12px 24px -6px rgba(90, 118, 200, 0.30) !important;
             }
             body.wiboost-welcome-page .order-step-content {
                 box-shadow:
                     inset 0 1px 0 rgba(255, 255, 255, 0.9),
                     0 8px 16px rgba(189, 224, 254, 0.40) !important;
             }
         }
         @media (min-width: 768px) {
             body.wiboost-welcome-page .welcome-stats-grid {
                 background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.97) 0%, #eaf2ff 100%) !important;
                 box-shadow:
                     inset 0 1px 0 rgba(255, 255, 255, 0.95),
                     inset 0 -3px 6px rgba(163, 187, 251, 0.28),
                     0 16px 30px -6px rgba(90, 118, 200, 0.32) !important;
             }
             body.wiboost-welcome-page .order-step-card {
                 background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
                 box-shadow:
                     inset 0 1px 0 rgba(255, 255, 255, 0.95),
                     inset 0 -3px 6px rgba(163, 187, 251, 0.28),
                     0 14px 26px -6px rgba(90, 118, 200, 0.30) !important;
             }
             body.wiboost-welcome-page .order-step-card:hover {
                 transform: translateY(-6px);
                 box-shadow:
                     inset 0 1px 0 rgba(255, 255, 255, 0.95),
                     0 24px 40px -8px rgba(90, 118, 200, 0.36) !important;
             }
         }
    </style>
    @include('partials.ios-layout-fixes')
</head>
@php
    $categoryDiscountLabel = \App\Models\Product::currentDiscountLabel();
@endphp
<body class="wiboost-compact-ui wiboost-welcome-page bg-[#f4f9ff] text-slate-800 antialiased selection:bg-[#7b9eed] selection:text-white flex flex-col min-h-screen">
    <nav class="fixed inset-x-0 top-0 z-50 px-0 pointer-events-none">
        <div class="w-full max-w-none pointer-events-auto">
            <div class="nav-skeu flex items-center justify-between rounded-none border-0 border-b-4 border-white bg-white/90 px-4 py-3 shadow-xl shadow-[#bde0fe]/50 backdrop-blur-md sm:px-6 lg:px-8">
                <a href="{{ Auth::check() ? (Auth::user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard')) : route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="h-12 w-12 shrink-0 rounded-full object-contain drop-shadow-sm transition-transform group-hover:scale-105 sm:h-14 sm:w-14">
                    <p class="text-lg font-black leading-none text-[#2b3a67] drop-shadow-sm sm:text-2xl">Wiboost Store</p>
                </a>
                <div class="flex items-center gap-3">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="skeu-primary text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Panel Admin</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="skeu-primary text-sm font-extrabold text-white bg-[#5a76c8] hover:bg-[#4760a9] px-6 py-2.5 rounded-full transition-transform active:scale-95 shadow-md">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="skeu-pill hidden rounded-full border-2 border-white bg-[#f0f5ff] px-6 py-2.5 text-sm font-extrabold text-[#5a76c8] shadow-md shadow-[#bde0fe]/30 transition-transform hover:-translate-y-0.5 hover:bg-white active:scale-95 sm:block">Daftar</a>
                        <a href="{{ route('login') }}" class="skeu-primary bg-[#5a76c8] hover:bg-[#4760a9] text-white text-sm font-extrabold px-7 py-2.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#8faaf3]/40 border-2 border-white">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- SECTION HERO & DATA REAL-TIME -->
    <main class="welcome-hero-section relative flex items-center justify-center bg-wiboost-sky overflow-hidden border-b-[6px] border-[#f0f5ff]">
        <div class="welcome-section-inner max-w-7xl px-6 sm:px-8 lg:px-8 relative z-10">
            <img src="{{ asset('images/wiboost-logo-nav.png') }}?v=20260613-nav2" alt="Logo Wiboost Store" width="192" height="192" fetchpriority="high" loading="eager" decoding="async" class="welcome-hero-logo">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <h1 class="welcome-hero-title font-black text-[#2b3a67] drop-shadow-sm">
                    Wiboost Store
                </h1>
            </div>

            <p class="mt-2 text-sm md:text-base text-[#4a5f96] max-w-5xl mx-auto font-bold leading-normal">
                Wiboost Store adalah platform layanan digital terintegrasi yang menyediakan katalog lengkap mencakup layanan Pemasaran Media Sosial (SMM) guna menunjang pertumbuhan metrik akun sosial media, top up game online secara instan, pembelian paket data operator, hingga penyediaan akses langganan aplikasi premium dengan harga kompetitif, di mana seluruh transaksi diproses secara otomatis dan didukung oleh pembayaran QRIS yang support semua metode pembayaran secara praktis dan aman.
            </p>
            
            <div class="welcome-cta-gap mt-12 flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('home') }}" onclick="event.preventDefault(); window.scrollToWiboostCatalog ? window.scrollToWiboostCatalog('smooth') : document.getElementById('kategori-heading').scrollIntoView({behavior: 'smooth', block: 'start'});" class="skeu-primary bg-[#5a76c8] hover:bg-[#4760a9] text-white font-extrabold text-lg px-10 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-3">
                    Beli Sekarang
                </a>
            </div>

            <div class="welcome-live-stats">
                <div class="welcome-stats-grid">
                    <div class="welcome-stat-card welcome-stat-users">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Pengguna</p>
                        <p class="mt-1 text-4xl font-black text-[#5a76c8]">{{ number_format($totalUsers, 0, ',', '.') }}</p>
                    </div>
                    <div class="welcome-stat-card welcome-stat-services">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Layanan</p>
                        <p class="mt-1 text-4xl font-black text-[#9a8ce5]">{{ number_format($activeProducts, 0, ',', '.') }}</p>
                    </div>
                    <div class="welcome-stat-card welcome-stat-transactions">
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Total Transaksi</p>
                        <p class="mt-1 text-4xl font-black text-[#4bc6b9]">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SECTION KATEGORI (PUTIH) -->
    <section id="kategori" class="py-24 lg:py-32 bg-white relative flex-1 z-10 border-b-[6px] border-[#f0f5ff]">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8">
            <div id="kategori-heading" class="text-center mb-12">
                <div class="skeu-pill inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-6 text-xs border-2 border-white">Katalog Utama</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Pilih Kategori</h2>
            </div>

            <div class="welcome-category-grid mx-auto flex max-w-5xl flex-wrap justify-center gap-4 md:gap-5">
                @forelse($categories as $cat)
                    @if(is_null($cat->parent_id))
                        <a href="{{ route('guest.order.category', $cat->slug) }}" class="welcome-category-card group relative flex aspect-square w-[calc(50%-0.5rem)] flex-col items-center justify-center overflow-hidden bg-[#f4f9ff] rounded-[1.5rem] p-2 sm:p-6 md:p-8 border-4 border-white shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 hover:border-[#bde0fe] transition-all duration-300 text-center sm:w-56 md:w-60 lg:w-56">
                            @if($categoryDiscountLabel)
                                <span class="discount-corner-ribbon">
                                    <span class="discount-corner-ribbon__text">
                                        {{ $categoryDiscountLabel }}
                                    </span>
                                </span>
                            @endif
                            <div class="welcome-category-icon mb-3 text-[#5a76c8] transition-transform duration-300 group-hover:scale-110">
                                @if($cat->image)
                                    <img src="{{ Storage::url($cat->image) }}" alt="{{ $cat->name }}" class="drop-shadow-sm">
                                @else
                                    <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['label' => $cat->name, 'class' => 'h-9 w-9'])</span>
                                @endif
                            </div>
                            <h3 class="welcome-category-title max-w-full break-words px-1 font-black text-[#2b3a67]">{{ $cat->name }}</h3>
                        </a>
                    @endif
                @empty
                    <div class="w-full max-w-md text-center py-10 bg-[#f4f9ff] rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                        <p class="text-[#5a76c8] font-black">Layanan sedang dipersiapkan...</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- SECTION KENAPA HARUS WIBOOST STORE (BIRU) -->
    <section id="fitur" class="py-24 lg:py-32 bg-[#f4f9ff] border-b-[6px] border-white relative z-10">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="skeu-pill inline-block px-4 py-1 bg-white text-[#4bc6b9] font-black rounded-full mb-6 text-xs shadow-sm">Keunggulan</div>
                <h2 class="welcome-why-heading text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Kenapa Harus Wiboost Store?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="skeu skeu-hover bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        @include('partials.wiboost-icon', ['name' => 'zap', 'class' => 'welcome-feature-icon'])
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Proses Otomatis</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Sistem kami berjalan 24/7. Transaksi diproses seketika tanpa perlu konfirmasi manual dari admin.</p>
                </div>
                <div class="skeu skeu-hover bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        @include('partials.wiboost-icon', ['name' => 'shield', 'class' => 'welcome-feature-icon'])
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Aman & Terpercaya</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Pembayaran menggunakan QRIS resmi yang aman, serta privasi akun media sosial Anda terjamin 100%.</p>
                </div>
                <div class="skeu skeu-hover bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        @include('partials.wiboost-icon', ['name' => 'package', 'class' => 'welcome-feature-icon'])
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Layanan Terlengkap</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Mulai dari SMM Panel, Top Up Game, Paket Data Operator, hingga Aplikasi Premium tersedia lengkap dalam satu platform.</p>
                </div>
                <div class="skeu skeu-hover bg-white rounded-[2rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/20 hover:-translate-y-2 transition-transform duration-300 text-center">
                    <div class="welcome-feature-icon-wrap mb-5 mx-auto">
                        @include('partials.wiboost-icon', ['name' => 'wallet', 'class' => 'welcome-feature-icon'])
                    </div>
                    <h3 class="font-black text-[#2b3a67] text-lg mb-3">Harga Kompetitif</h3>
                    <p class="text-sm text-[#4a5f96] font-bold leading-relaxed">Dapatkan layanan berkualitas dengan harga termurah. Sangat cocok untuk digunakan sendiri atau dijual kembali.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION ALUR PEMESANAN (PUTIH) -->
    <section id="alur-pemesanan" class="py-24 lg:py-32 bg-white relative z-10 border-b-[6px] border-[#f0f5ff]">
        <div class="welcome-section-inner max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="skeu-pill inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-6 text-xs border-2 border-white">Cara Kerja</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Alur Pemesanan</h2>
            </div>

            <div class="order-flow max-w-6xl mx-auto">
                <div class="order-flow-grid relative z-10">
                    <div class="order-step-card group">
                        <div class="order-step-node">1</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Pilih Layanan</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Telusuri katalog dan pilih layanan digital yang Anda butuhkan.</p>
                        </div>
                    </div>
                    <div class="order-step-card group">
                        <div class="order-step-node">2</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Isi Data</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Masukkan data yang diminta seperti User ID game atau Link Sosial Media.</p>
                        </div>
                    </div>
                    <div class="order-step-card group">
                        <div class="order-step-node">3</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Pembayaran</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Lakukan pembayaran dengan scan QRIS atau metode lainnya yang tersedia.</p>
                        </div>
                    </div>
                    <div class="order-step-card group">
                        <div class="order-step-node">4</div>
                        <div class="order-step-content">
                            <h3 class="font-black text-[#2b3a67] text-xl mb-2">Selesai!</h3>
                            <p class="text-sm text-[#4a5f96] font-bold">Pesanan Anda akan langsung diproses secara instan oleh sistem kami.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION PERTANYAAN UMUM (FAQ) (BIRU) -->
    <section id="faq" class="py-24 lg:py-32 bg-[#f4f9ff] border-b-[6px] border-white relative z-10">
        <div class="welcome-section-inner max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="skeu-pill inline-block px-4 py-1 bg-white text-[#4bc6b9] font-black rounded-full mb-6 text-xs shadow-sm">FAQ</div>
                <h2 class="text-3xl md:text-5xl font-black text-[#2b3a67] tracking-tight">Pertanyaan Umum</h2>
            </div>

            <div class="welcome-faq-list flex flex-col gap-4">
                <details class="skeu w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Berapa lama waktu yang dibutuhkan untuk proses pesanan?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#5a76c8] font-normal leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Sebagian besar layanan kami diproses secara otomatis dan instan dalam hitungan detik setelah pembayaran berhasil dikonfirmasi. Untuk layanan tertentu (seperti SMM dengan target tinggi), proses mungkin membutuhkan waktu yang sedikit lebih lama sesuai deskripsi produk.
                    </div>
                </details>

                <details class="skeu w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Apakah butuh password akun sosmed saya?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#5a76c8] font-normal leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Tidak! Layanan SMM (Social Media Marketing) kami 100% aman karena hanya membutuhkan Username atau Link/Tautan postingan Anda. Kami tidak akan pernah meminta password akun Anda.
                    </div>
                </details>

                <details class="skeu w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Metode pembayaran apa saja yang tersedia?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#5a76c8] font-normal leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Kami mendukung pembayaran via QRIS yang dapat di-scan menggunakan seluruh aplikasi E-Wallet (Dana, Ovo, Gopay, LinkAja, ShopeePay) maupun Mobile Banking (BCA, Mandiri, BRI, BNI, dll). Pembayaran diproses otomatis tanpa perlu mengirim bukti transfer.
                    </div>
                </details>

                <details class="skeu w-full bg-white rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 overflow-hidden cursor-pointer">
                    <summary class="flex justify-between items-center p-6 font-black text-[#2b3a67] text-base md:text-lg focus:outline-none text-left">
                        <span>Bagaimana jika pesanan saya gagal?</span>
                        <span class="text-[#5a76c8]">
                            <svg class="faq-icon w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-sm text-[#5a76c8] font-normal leading-relaxed border-t-2 border-[#f0f5ff] pt-4 mt-1 bg-white text-left">
                        Jika terjadi gangguan teknis dari pusat yang menyebabkan pesanan Anda berstatus gagal (Error/Canceled), sistem kami akan secara otomatis mengembalikan dana Anda dalam bentuk saldo akun yang bisa Anda gunakan kembali untuk transaksi lainnya.
                    </div>
                </details>
            </div>
        </div>
    </section>
    
    @include('partials.site-footer')

    @include('partials.hut-ri-decoration')
    @include('partials.tv-safe')

    <script>
        (function () {
            // Trigger via a sessionStorage flag (set by the back button) instead of a URL hash,
            // so the address bar stays clean — no #kategori / #kategori-heading behind the link.
            var RETURN_KEY = 'wiboostReturnCatalog';
            var CATALOG_HASHES = ['#kategori', '#kategori-heading', '#katalog-utama'];
            var pending = false;
            var cancelled = false;

            function isCatalogHash() {
                return CATALOG_HASHES.indexOf(window.location.hash) !== -1;
            }

            function hasReturnFlag() {
                try { return sessionStorage.getItem(RETURN_KEY) === '1'; } catch (e) { return false; }
            }

            function clearReturnFlag() {
                try { sessionStorage.removeItem(RETURN_KEY); } catch (e) {}
            }

            // Remove any leftover catalog hash (e.g. from old bookmarks) so the URL stays clean.
            function stripCatalogHash() {
                if (!isCatalogHash()) {
                    return;
                }

                try {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                } catch (e) {}
            }

            // scrollIntoView respects the heading's CSS scroll-margin-top, so the landing
            // is deterministic. <html> has scroll-behavior:smooth, so the load-time landing
            // MUST be 'instant' — otherwise it animates and never settles exactly at the heading.
            function landOnCatalog(behavior) {
                var el = document.getElementById('kategori-heading');

                if (!el) {
                    return;
                }

                el.scrollIntoView({ block: 'start', behavior: behavior || 'instant' });
            }

            window.scrollToWiboostCatalog = function (behavior) {
                landOnCatalog(behavior || 'smooth');
            };

            function apply() {
                if (pending && !cancelled) {
                    landOnCatalog('instant');
                }
            }

            function arm() {
                if (!(isCatalogHash() || hasReturnFlag())) {
                    return;
                }

                pending = true;
                cancelled = false;
                apply();
                requestAnimationFrame(apply);
                [80, 240, 600].forEach(function (delay) { setTimeout(apply, delay); });
                setTimeout(function () {
                    pending = false;
                    clearReturnFlag();
                    stripCatalogHash();
                }, 700);
            }

            // Any real user scroll input stops the forced landing so nothing yanks the page.
            ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(function (evt) {
                window.addEventListener(evt, function () { cancelled = true; }, { passive: true });
            });

            if ('scrollRestoration' in history && (isCatalogHash() || hasReturnFlag())) {
                try { history.scrollRestoration = 'manual'; } catch (e) {}
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', arm);
            } else {
                arm();
            }

            window.addEventListener('load', arm, { once: true });
            window.addEventListener('pageshow', arm);
            window.addEventListener('hashchange', arm);
        })();
    </script>
</body>
</html>
