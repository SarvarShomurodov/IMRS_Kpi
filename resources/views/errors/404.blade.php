<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Sahifa Topilmadi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF3366;
            --secondary: #6C5CE7;
            --accent: #00D4FF;
            --dark: #0F0F1E;
            --light: #FFFFFF;
            --grain-opacity: 0.05;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lexend', sans-serif;
            background: var(--dark);
            color: var(--light);
            overflow: hidden;
            position: relative;
            min-height: 100vh;
        }

        /* Animated gradient background */
        .gradient-bg {
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 30%, var(--primary) 0%, transparent 30%),
                radial-gradient(circle at 80% 70%, var(--secondary) 0%, transparent 30%),
                radial-gradient(circle at 50% 50%, var(--accent) 0%, transparent 40%);
            opacity: 0.15;
            animation: gradientMove 20s ease-in-out infinite;
            filter: blur(80px);
        }

        @keyframes gradientMove {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(10%, 5%) rotate(120deg); }
            66% { transform: translate(-5%, 10%) rotate(240deg); }
        }

        /* Grain texture overlay */
        .grain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='4' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.3'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: var(--grain-opacity);
            mix-blend-mode: overlay;
        }

        /* Floating geometric shapes */
        .shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            border: 2px solid var(--accent);
            opacity: 0.1;
            animation: float 15s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
            border-color: var(--primary);
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: 3s;
            border-color: var(--secondary);
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            bottom: 20%;
            left: 20%;
            animation-delay: 6s;
            border-color: var(--accent);
        }

        .shape:nth-child(4) {
            width: 180px;
            height: 180px;
            top: 30%;
            right: 30%;
            animation-delay: 9s;
            border-color: var(--primary);
            transform: rotate(45deg);
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(20px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        /* Main container */
        .container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }

        /* 404 Number */
        .error-code {
            font-family: 'Fraunces', serif;
            font-size: clamp(8rem, 25vw, 20rem);
            font-weight: 900;
            line-height: 0.9;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% 200%;
            animation: gradientShift 4s ease infinite, glitch 3s ease-in-out infinite;
            position: relative;
            margin-bottom: 1rem;
            text-shadow: 0 0 80px rgba(255, 51, 102, 0.5);
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes glitch {
            0%, 100% { transform: translate(0, 0) skew(0deg); }
            20% { transform: translate(-2px, 1px) skew(-0.5deg); }
            40% { transform: translate(2px, -1px) skew(0.5deg); }
            60% { transform: translate(-1px, 2px) skew(-0.3deg); }
            80% { transform: translate(1px, -2px) skew(0.3deg); }
        }

        /* Title */
        .title {
            font-size: clamp(1.5rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease 0.3s both;
            letter-spacing: -0.02em;
        }

        /* Description */
        .description {
            font-size: clamp(1rem, 2vw, 1.25rem);
            max-width: 600px;
            margin: 0 auto 3rem;
            opacity: 0.8;
            line-height: 1.6;
            animation: fadeInUp 1s ease 0.5s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Button container */
        .button-group {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeInUp 1s ease 0.7s both;
        }

        /* Buttons */
        .btn {
            padding: 1rem 2.5rem;
            font-family: 'Lexend', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--light);
            box-shadow: 0 10px 30px rgba(255, 51, 102, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(255, 51, 102, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        /* Decorative elements */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--accent);
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 4s ease-in-out infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 0.5s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 1s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 1.5s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 2s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 2.5s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 3s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 3.5s; }

        @keyframes particleFloat {
            0% {
                bottom: 0;
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                bottom: 100%;
                opacity: 0;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .button-group {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        /* Custom cursor effect */
        @media (hover: hover) {
            body {
                cursor: none;
            }

            .cursor {
                position: fixed;
                width: 20px;
                height: 20px;
                border: 2px solid var(--accent);
                border-radius: 50%;
                pointer-events: none;
                z-index: 9999;
                transition: transform 0.2s ease;
                mix-blend-mode: difference;
            }

            .cursor.active {
                transform: scale(2);
                border-color: var(--primary);
            }
        }
    </style>
</head>
<body>
    <!-- Background effects -->
    <div class="gradient-bg"></div>
    <div class="grain"></div>

    <!-- Floating shapes -->
    <div class="shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Floating particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Custom cursor -->
    <div class="cursor" id="cursor"></div>

    <!-- Main content -->
    <div class="container">
        <div class="error-code">404</div>
        <h1 class="title">Sahifa Topilmadi</h1>
        <p class="description">
            Kechirasiz, siz qidirayotgan sahifa mavjud emas yoki ko'chirilgan. 
            Bosh sahifaga qaytishingiz yoki izlash orqali kerakli ma'lumotni topishingiz mumkin.
        </p>
        <div class="button-group">
            <a href="/" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Bosh sahifa
            </a>
            <button class="btn btn-secondary" onclick="window.history.back()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Ortga qaytish
            </button>
        </div>
    </div>

    <script>
        // Custom cursor effect
        const cursor = document.getElementById('cursor');
        let mouseX = 0;
        let mouseY = 0;
        let cursorX = 0;
        let cursorY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        function animateCursor() {
            const diffX = mouseX - cursorX;
            const diffY = mouseY - cursorY;
            
            cursorX += diffX * 0.2;
            cursorY += diffY * 0.2;
            
            cursor.style.left = cursorX + 'px';
            cursor.style.top = cursorY + 'px';
            
            requestAnimationFrame(animateCursor);
        }

        animateCursor();

        // Add active class on click
        document.addEventListener('mousedown', () => {
            cursor.classList.add('active');
        });

        document.addEventListener('mouseup', () => {
            cursor.classList.remove('active');
        });

        // Button hover effects
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                cursor.classList.add('active');
            });
            btn.addEventListener('mouseleave', () => {
                cursor.classList.remove('active');
            });
        });
    </script>
</body>
</html>