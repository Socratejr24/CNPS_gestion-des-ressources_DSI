<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page introuvable — Gestion des ressources DSI · CNPS</title>

    {{-- Favicon CNPS --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-cnps.jpeg') }}">

    {{-- Polices --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #f5f6fa;
            font-family: 'Inter', system-ui, sans-serif;
            color: #1b2140;
            display: flex;
            flex-direction: column;
        }

        /* --- Bandeau quadricolore --- */
        .bar {
            display: flex;
            height: 6px;
        }
        .bar span { flex: 1; }
        .bar span:nth-child(1) { background: #27357e; }
        .bar span:nth-child(2) { background: #de7c0f; }
        .bar span:nth-child(3) { background: #088e39; }
        .bar span:nth-child(4) { background: #f6d01d; }

        /* --- Conteneur --- */
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        /* --- Icône --- */
        .icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #e6eaf7;
            color: #27357e;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }

        /* --- Code --- */
        .code {
            font-family: 'Poppins', sans-serif;
            font-size: 5rem;
            font-weight: 700;
            color: #27357e;
            line-height: 1;
            letter-spacing: -0.03em;
        }

        /* --- Titre --- */
        .title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1b2140;
            margin: 0.75rem 0 0.5rem;
        }

        /* --- Texte --- */
        .text {
            font-size: 0.95rem;
            color: #6b7190;
            line-height: 1.6;
            max-width: 420px;
            margin: 0 0 2rem;
        }

        /* --- Actions --- */
        .actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn svg { width: 18px; height: 18px; }

        .btn--primary {
            background: #27357e;
            color: #ffffff;
        }

        .btn--primary:hover {
            background: #1d2865;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(39, 53, 126, 0.25);
        }

        .btn--ghost {
            background: transparent;
            color: #27357e;
            border-color: #e2e5f0;
        }

        .btn--ghost:hover {
            background: #f5f6fa;
            border-color: #27357e;
        }

        /* --- Footer --- */
        .footer {
            font-size: 0.75rem;
            color: #a8adc7;
            margin: 0;
        }

        /* --- Responsive --- */
        @media (max-width: 640px) {
            .code { font-size: 3.5rem; }
            .title { font-size: 1.25rem; }
            .icon { width: 90px; height: 90px; }
            .icon svg { width: 60px; height: 60px; }
            .actions {
                flex-direction: column;
                width: 100%;
                max-width: 300px;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    {{-- Bandeau quadricolore --}}
    <div class="bar">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    {{-- Contenu --}}
    <div class="container">

        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>

        <h1 class="code">404</h1>
        <h2 class="title">Page introuvable</h2>
        <p class="text">
            La page que vous cherchez n'existe pas ou a été déplacée.
        </p>

        <div class="actions">
            <a href="{{ url('/dashboard') }}" class="btn btn--primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Retour au tableau de bord
            </a>
            

<button type="button" onclick="history.back();" class="btn btn--ghost">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
    </svg>
    Page précédente
</button>



        </div>

        <p class="footer">
            © {{ date('Y') }} CNPS — Direction du Système d'Information
        </p>

    </div>

</body>
</html>