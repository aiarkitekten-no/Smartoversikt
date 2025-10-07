<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SMARTOVERSIKT - SYSTEM ACCESS</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        body {
            font-family: 'Orbitron', sans-serif;
            overflow: hidden;
        }
        
        /* Matrix rain effect */
        #matrix-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.15;
        }
        
        /* Scan line effect */
        @keyframes scan {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100vh); }
        }
        
        .scanline {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to bottom, transparent, rgba(0, 255, 255, 0.5), transparent);
            animation: scan 8s linear infinite;
            z-index: 5;
            pointer-events: none;
        }
        
        /* Glitch effect */
        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }
        
        .glitch {
            animation: glitch 0.3s ease-in-out;
        }
        
        /* Pulse effect */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Neon glow */
        .neon-text {
            text-shadow: 
                0 0 10px rgba(0, 255, 255, 0.8),
                0 0 20px rgba(0, 255, 255, 0.6),
                0 0 30px rgba(0, 255, 255, 0.4),
                0 0 40px rgba(0, 255, 255, 0.2);
        }
        
        .neon-border {
            box-shadow:
                0 0 10px rgba(0, 255, 255, 0.5),
                inset 0 0 10px rgba(0, 255, 255, 0.2);
        }
        
        /* Self destruct animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }
        
        @keyframes explode {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); }
            100% { transform: scale(0); opacity: 0; }
        }
        
        @keyframes flash {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        .explode {
            animation: explode 0.8s ease-out forwards;
        }
        
        .flash {
            animation: flash 0.1s ease-in-out 5;
        }
        
        /* Grid background */
        .grid-bg {
            background-image: 
                linear-gradient(rgba(0, 255, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }
        
        @keyframes gridMove {
            0% { background-position: 0 0; }
            100% { background-position: 50px 50px; }
        }
        
        /* Typing cursor */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        
        .typing-cursor {
            animation: blink 1s step-end infinite;
        }
        
        /* Particles */
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-20px) translateX(10px); }
            50% { transform: translateY(-10px) translateX(-10px); }
            75% { transform: translateY(-30px) translateX(5px); }
        }
        
        .particle {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-black text-cyan-400">
    <!-- Matrix Canvas -->
    <canvas id="matrix-canvas"></canvas>
    
    <!-- Grid Background -->
    <div class="grid-bg fixed inset-0 z-0"></div>
    
    <!-- Scanline -->
    <div class="scanline"></div>
    
    <!-- Particles -->
    <div class="fixed inset-0 z-2 pointer-events-none">
        @for ($i = 0; $i < 20; $i++)
            <div class="particle absolute w-1 h-1 bg-cyan-400 rounded-full opacity-30" 
                 style="left: {{ rand(0, 100) }}%; top: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 3000) }}ms;"></div>
        @endfor
    </div>
    
    <!-- Main Container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
        <div id="login-container" class="w-full max-w-md">
            <!-- System Header -->
            <div class="text-center mb-8">
                <div class="text-6xl font-black neon-text mb-2">
                    SMARTOVERSIKT
                </div>
                <div class="text-xs tracking-widest text-cyan-300 mb-4">
                    <span class="pulse">SYSTEM ACCESS TERMINAL v3.14.159</span>
                </div>
                <div class="text-xs font-mono">
                    <span id="system-time" class="text-green-400"></span>
                </div>
            </div>
            
            <!-- Access Panel -->
            <div class="neon-border bg-black bg-opacity-80 border-2 border-cyan-400 p-8 rounded-lg backdrop-blur-sm">
                <div class="mb-6">
                    <div class="text-sm text-cyan-300 mb-2 font-mono">
                        &gt; INITIALIZE AUTHENTICATION PROTOCOL
                    </div>
                    <div class="h-1 bg-gradient-to-r from-transparent via-cyan-400 to-transparent"></div>
                </div>
                
                <form id="login-form" action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <!-- Email Input -->
                    <div class="mb-6">
                        <label class="block text-xs text-cyan-300 mb-2 font-mono tracking-wider">
                            USER_ID
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            class="w-full bg-black border-2 border-cyan-400 text-cyan-400 px-4 py-3 rounded focus:outline-none focus:border-cyan-300 focus:shadow-lg focus:shadow-cyan-400/50 transition-all font-mono"
                            placeholder="user@system.com"
                            autocomplete="email"
                            required
                        />
                    </div>
                    
                    <!-- Password Input -->
                    <div class="mb-6">
                        <label class="block text-xs text-cyan-300 mb-2 font-mono tracking-wider">
                            ACCESS_CODE
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="w-full bg-black border-2 border-cyan-400 text-cyan-400 px-4 py-3 rounded focus:outline-none focus:border-cyan-300 focus:shadow-lg focus:shadow-cyan-400/50 transition-all font-mono"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        />
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="mb-6 flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember"
                            class="w-4 h-4 bg-black border-2 border-cyan-400 rounded text-cyan-400 focus:ring-cyan-400"
                        />
                        <label for="remember" class="ml-2 text-xs text-cyan-300 font-mono">
                            MAINTAIN_SESSION
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        id="access-btn"
                        class="w-full bg-gradient-to-r from-cyan-600 to-cyan-400 hover:from-cyan-500 hover:to-cyan-300 text-black font-black py-4 rounded uppercase tracking-wider transition-all transform hover:scale-105 hover:shadow-xl hover:shadow-cyan-400/50"
                    >
                        <span id="btn-text">⚡ INITIATE ACCESS ⚡</span>
                    </button>
                </form>
                
                <!-- System Status -->
                <div class="mt-6 pt-6 border-t border-cyan-400 border-opacity-30">
                    <div class="flex justify-between text-xs font-mono">
                        <span class="text-green-400">● SYSTEM_ONLINE</span>
                        <span class="text-cyan-300">SECURITY: <span class="text-green-400">MAX</span></span>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-6 text-xs text-cyan-400 font-mono opacity-50">
                <p>AUTHORIZED PERSONNEL ONLY</p>
                <p class="mt-1">UNAUTHORIZED ACCESS WILL BE PROSECUTED</p>
            </div>
        </div>
    </div>
    
    <!-- Self Destruct Overlay -->
    <div id="destruct-overlay" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-red-900 opacity-0" id="red-flash"></div>
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <div class="text-8xl font-black text-red-500 mb-4" id="destruct-text">
                    ACCESS DENIED
                </div>
                <div class="text-2xl text-red-400 font-mono" id="countdown-text">
                    SELF DESTRUCT IN <span id="countdown">5</span>
                </div>
                <div class="text-sm text-red-300 mt-4 font-mono">
                    UNAUTHORIZED ACCESS ATTEMPT DETECTED
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Matrix Rain Effect
        const canvas = document.getElementById('matrix-canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノ';
        const fontSize = 14;
        const columns = canvas.width / fontSize;
        const drops = Array(Math.floor(columns)).fill(1);
        
        function drawMatrix() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = '#0ff';
            ctx.font = fontSize + 'px monospace';
            
            for (let i = 0; i < drops.length; i++) {
                const text = chars[Math.floor(Math.random() * chars.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }
        
        setInterval(drawMatrix, 35);
        
        // System Time
        function updateTime() {
            const now = new Date();
            const timeStr = now.toISOString().replace('T', ' ').substr(0, 19);
            document.getElementById('system-time').textContent = `[${timeStr}] UTC`;
        }
        updateTime();
        setInterval(updateTime, 1000);
        
        // Form Submission Handler
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('access-btn');
            const btnText = document.getElementById('btn-text');
            const form = this;
            
            btnText.textContent = '⚡ AUTHENTICATING ⚡';
            btn.disabled = true;
            
            try {
                // Use FormData to send proper form data (not JSON)
                const formData = new FormData(form);
                
                const response = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (response.ok && !data.errors) {
                    // Success - Access Granted
                    playAccessGranted();
                    
                    btnText.textContent = '✓ ACCESS GRANTED ✓';
                    btn.classList.remove('from-cyan-600', 'to-cyan-400');
                    btn.classList.add('from-green-600', 'to-green-400');
                    
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 500);
                } else {
                    // Failed - SELF DESTRUCT
                    btn.disabled = false;
                    btnText.textContent = '⚡ INITIATE ACCESS ⚡';
                    initiateSelfDestruct();
                }
            } catch (error) {
                btn.disabled = false;
                btnText.textContent = '⚡ INITIATE ACCESS ⚡';
                initiateSelfDestruct();
            }
        });
        
        // Audio Context for sound effects
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioCtx = null;
        
        function initAudio() {
            if (!audioCtx) {
                audioCtx = new AudioContext();
            }
            return audioCtx;
        }
        
        // Sound effect: Alarm beep
        function playAlarmBeep(frequency = 800, duration = 0.15) {
            const ctx = initAudio();
            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);
            
            oscillator.frequency.value = frequency;
            oscillator.type = 'square';
            
            gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);
            
            oscillator.start(ctx.currentTime);
            oscillator.stop(ctx.currentTime + duration);
        }
        
        // Sound effect: Countdown beep (getting higher pitched)
        function playCountdownBeep(countNumber) {
            const baseFreq = 600;
            const freq = baseFreq + (5 - countNumber) * 100; // Higher pitch as count decreases
            playAlarmBeep(freq, 0.2);
        }
        
        // Sound effect: Explosion with electrical short circuit
        function playExplosion() {
            const ctx = initAudio();
            
            // Create white noise for explosion
            const bufferSize = ctx.sampleRate * 2;
            const noiseBuffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
            const output = noiseBuffer.getChannelData(0);
            
            for (let i = 0; i < bufferSize; i++) {
                output[i] = Math.random() * 2 - 1;
            }
            
            const noise = ctx.createBufferSource();
            noise.buffer = noiseBuffer;
            
            const noiseGain = ctx.createGain();
            const noiseFilter = ctx.createBiquadFilter();
            noiseFilter.type = 'lowpass';
            noiseFilter.frequency.value = 1000;
            
            noise.connect(noiseFilter);
            noiseFilter.connect(noiseGain);
            noiseGain.connect(ctx.destination);
            
            // Explosion envelope
            noiseGain.gain.setValueAtTime(0.5, ctx.currentTime);
            noiseGain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.5);
            
            noise.start(ctx.currentTime);
            noise.stop(ctx.currentTime + 1.5);
            
            // Add electrical zapping sounds
            for (let i = 0; i < 8; i++) {
                setTimeout(() => {
                    const zapFreq = 100 + Math.random() * 300;
                    playAlarmBeep(zapFreq, 0.05 + Math.random() * 0.1);
                }, i * 150 + Math.random() * 100);
            }
        }
        
        // Sound effect: Access Denied warning
        function playAccessDenied() {
            const ctx = initAudio();
            
            // Two-tone alarm
            playAlarmBeep(800, 0.3);
            setTimeout(() => playAlarmBeep(600, 0.3), 350);
            setTimeout(() => playAlarmBeep(800, 0.3), 700);
        }
        
        // Sound effect: Access Granted (success melody)
        function playAccessGranted() {
            const ctx = initAudio();
            
            // Success melody: ascending notes
            const notes = [523.25, 659.25, 783.99]; // C5, E5, G5
            notes.forEach((freq, i) => {
                setTimeout(() => {
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    
                    oscillator.frequency.value = freq;
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.2, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    
                    oscillator.start(ctx.currentTime);
                    oscillator.stop(ctx.currentTime + 0.3);
                }, i * 100);
            });
        }
        
        // Self Destruct Sequence
        function initiateSelfDestruct() {
            const container = document.getElementById('login-container');
            const overlay = document.getElementById('destruct-overlay');
            const redFlash = document.getElementById('red-flash');
            const countdownEl = document.getElementById('countdown');
            
            // Play ACCESS DENIED alarm
            playAccessDenied();
            
            // Shake the container
            container.classList.add('shake');
            
            setTimeout(() => {
                // Show destruct overlay
                overlay.classList.remove('hidden');
                redFlash.classList.add('flash');
                
                // Countdown
                let count = 5;
                
                // Play initial countdown beep
                playCountdownBeep(count);
                
                const interval = setInterval(() => {
                    count--;
                    countdownEl.textContent = count;
                    
                    // Play countdown beep
                    if (count > 0) {
                        playCountdownBeep(count);
                    }
                    
                    // Flash screen
                    redFlash.style.opacity = '0.8';
                    setTimeout(() => {
                        redFlash.style.opacity = '0';
                    }, 100);
                    
                    if (count === 0) {
                        clearInterval(interval);
                        
                        // EXPLODE with sound
                        playExplosion();
                        
                        document.body.classList.add('explode');
                        redFlash.style.opacity = '1';
                        
                        setTimeout(() => {
                            // Reset after explosion
                            window.location.reload();
                        }, 1500);
                    }
                }, 1000);
            }, 500);
        }
        
        // Add glitch effect randomly
        setInterval(() => {
            if (Math.random() > 0.95) {
                document.querySelector('.neon-text').classList.add('glitch');
                setTimeout(() => {
                    document.querySelector('.neon-text').classList.remove('glitch');
                }, 300);
            }
        }, 2000);
    </script>
</body>
</html>
