import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Widget Data Component
document.addEventListener('alpine:init', () => {
    Alpine.data('widgetData', (widgetKey) => ({
        widgetKey: widgetKey,
        data: null,
        loading: false,
        error: null,
        isFresh: false,
        lastUpdate: null,
        refreshInterval: null,
        statusLight: 'gray', // gray, yellow, green, red
        statusIcon: '', // ✓ or ✗

        init() {
            // Auto-start refresh based on widget type
            const refreshIntervals = {
                'demo.clock': 10,
                'system.uptime': 60,
                'system.cpu-ram': 30,
                'system.disk-usage': 60,
                'system.network': 30,
                'system.disk-io': 30,
                'system.disk': 120,
                'system.cron-jobs': 120,
                'system.error-log': 60,
                'mail.imap': 300,
                'weather.yr': 1800,
                'news.rss': 600,
                'dev.github': 300,
                'monitoring.uptime': 60,
                'business.stripe': 300,
                'security.ssl-certs': 3600,
                'security.events': 30,
                'project.trello': 300,
            };

            const interval = refreshIntervals[this.widgetKey] || 60;
            this.startRefresh(interval);
        },

        async fetchData() {
            this.loading = true;
            this.error = null;
            this.statusLight = 'yellow'; // Yellow while loading

            try {
                const response = await fetch(`/api/widgets/${this.widgetKey}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    if (response.status === 404) {
                        throw new Error('Widget ikke funnet');
                    } else if (response.status === 503) {
                        throw new Error('Data ikke tilgjengelig');
                    } else {
                        throw new Error(`HTTP ${response.status}`);
                    }
                }

                const result = await response.json();
                this.data = result.data;
                this.isFresh = result.is_fresh;
                this.lastUpdate = new Date(result.fresh_at).toLocaleTimeString('nb-NO');
                this.error = null; // Clear any previous errors
                
                // Success: Green light with checkmark
                this.statusLight = 'green';
                this.statusIcon = '✓';
                
                // Flash green for 2 seconds, then gray
                setTimeout(() => {
                    if (this.statusLight === 'green') {
                        this.statusLight = 'gray';
                        this.statusIcon = '';
                    }
                }, 2000);
                
            } catch (err) {
                this.error = `⚠️ ${err.message}`;
                console.error('Widget fetch error:', err);
                
                // Error: Red light with X
                this.statusLight = 'red';
                this.statusIcon = '✗';
                
                // Keep red for 3 seconds
                setTimeout(() => {
                    if (this.statusLight === 'red') {
                        this.statusLight = 'gray';
                        this.statusIcon = '';
                    }
                }, 3000);
                
                // Retry after 30 seconds on error
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    setTimeout(() => {
                        this.startRefresh(this.currentRefreshInterval);
                    }, 30000);
                }
            } finally {
                this.loading = false;
            }
        },

        formatDateTime(isoString) {
            if (!isoString) return 'Ukjent';
            try {
                const date = new Date(isoString);
                return date.toLocaleString('nb-NO', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            } catch (err) {
                return isoString;
            }
        },

        startRefresh(seconds) {
            // Store interval for retry logic
            this.currentRefreshInterval = seconds;
            
            // Initial fetch
            this.fetchData();

            // Auto-refresh
            this.refreshInterval = setInterval(() => {
                this.fetchData();
            }, seconds * 1000);
        },

        stopRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        },

        destroy() {
            this.stopRefresh();
        }
    }));

    // Tools: Quicklinks component (extracted from Blade to avoid quoting issues)
    Alpine.data('toolsQuicklinks', () => ({
        links: [],
        editMode: false,
        selectedIds: [],
        showAddForm: false,
        newTitle: '',
        newUrl: '',
        saving: false,
        loadingLinks: false,
        lastError: '',

        async loadLinks() {
            try {
                this.loadingLinks = true;
                this.lastError = '';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch('/api/quicklinks', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    this.lastError = `Kunne ikke laste lenker (HTTP ${response.status})`;
                    return;
                }
                const result = await response.json();
                if (result?.success && Array.isArray(result.links)) {
                    this.links = result.links;
                }
            } catch (error) {
                console.error('Quicklinks GET error:', error);
                this.lastError = 'Feil ved lasting av Hurtiglenker';
            } finally {
                this.loadingLinks = false;
            }
        },

        async addLink() {
            if (!this.newTitle || !this.newUrl) return;
            this.saving = true;
            try {
                // Normalize URL to https if missing scheme
                let url = this.newUrl.trim();
                if (!/^https?:\/\//i.test(url)) {
                    url = 'https://' + url.replace(/^\/+/, '');
                }

                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch('/api/quicklinks', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ title: this.newTitle, url })
                });
                if (!response.ok) {
                    this.lastError = `Kunne ikke lagre lenke (HTTP ${response.status})`;
                    return;
                }
                const result = await response.json();
                if (result?.success) {
                    if (Array.isArray(result.links)) this.links = result.links;
                    this.newTitle = '';
                    this.newUrl = '';
                    this.showAddForm = false;
                    await this.loadLinks();
                }
            } catch (error) {
                console.error('Quicklinks POST error:', error);
                this.lastError = 'Feil ved lagring av Hurtiglenke';
            } finally {
                this.saving = false;
            }
        },

        async deleteSelected() {
            if (this.selectedIds.length === 0) return;
            this.saving = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch('/api/quicklinks', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ ids: this.selectedIds })
                });
                if (!response.ok) {
                    this.lastError = `Kunne ikke slette lenker (HTTP ${response.status})`;
                    return;
                }
                const result = await response.json();
                if (result?.success) {
                    if (Array.isArray(result.links)) this.links = result.links;
                    this.selectedIds = [];
                    this.editMode = false;
                    await this.loadLinks();
                }
            } catch (error) {
                console.error('Quicklinks DELETE error:', error);
                this.lastError = 'Feil ved sletting av Hurtiglenker';
            } finally {
                this.saving = false;
            }
        },

        toggleSelection(id) {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) this.selectedIds.splice(index, 1); else this.selectedIds.push(id);
        },

        toggleEditMode() {
            this.editMode = !this.editMode;
            if (!this.editMode) this.selectedIds = [];
        },

        init() {
            this.loadLinks();
        }
    }));

    // Seasonal: Fireplace (high-fidelity canvas flames + embers + glow)
    Alpine.data('seasonFireplace', () => ({
        intensity: 1.0, // 0.5 - 2.0
        emberDensity: 1.0, // 0 - 2
        running: true,
        canvas: null,
        ctx: null,
        dpr: Math.max(1, window.devicePixelRatio || 1),
        width: 0,
        height: 0,
        rafId: 0,
        startTime: 0,
        lastTime: 0,
        embers: [],
        maxEmbers: 160,
        observer: null,
        // Audio
        audioOn: false,
        audioCtx: null,
        masterGain: null,
    // UI element refs (set in init via $refs)
    frameEl: null,
    glowEl: null,

        init() {
            this.canvas = this.$refs.fireCanvas;
            this.frameEl = this.$refs.fireFrame || null;
            this.glowEl = this.$refs.fireGlow || null;
            this.ctx = this.canvas.getContext('2d', { alpha: true });
            this.resize();
            this.startTime = performance.now();
            this.lastTime = this.startTime;
            window.addEventListener('resize', () => this.resize(), { passive: true });
            if (!this.ctx) {
                // Canvas unsupported: stop gracefully
                this.running = false;
                return;
            }

            // Pause when not visible
            this.observer = new IntersectionObserver((entries) => {
                for (const e of entries) {
                    this.running = e.isIntersecting;
                    if (this.running) this.loop();
                }
            }, { threshold: 0.05 });
            this.observer.observe(this.canvas);

            // Seed some embers
            for (let i = 0; i < this.maxEmbers / 2; i++) this.spawnEmber(true);

            // Draw a first frame immediately so something is visible even if paused
            try { this.renderFrame(0, 0); } catch {}
            this.loop();
        },

        destroy() {
            cancelAnimationFrame(this.rafId);
            if (this.observer) this.observer.disconnect();
        },

        resize() {
            const rect = this.canvas.getBoundingClientRect();
            this.width = Math.max(300, Math.floor(rect.width));
            this.height = Math.max(180, Math.floor(rect.height));
            this.canvas.width = Math.floor(this.width * this.dpr);
            this.canvas.height = Math.floor(this.height * this.dpr);
            this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);
        },

        loop() {
            if (!this.running) return;
            this.rafId = requestAnimationFrame(() => this.loop());
            const now = performance.now();
            const dt = Math.min(0.05, (now - this.lastTime) / 1000);
            this.lastTime = now;
            const t = (now - this.startTime) / 1000;

            this.renderFrame(t, dt);
            this.updateFlicker(t);
            this.maybeCrackle(dt);
        },

        // Simple pseudo-noise using layered sines for flame undulation
        noise(x, y, t) {
            return (
                Math.sin(x * 3.1 + t * 2.3) * 0.5 +
                Math.sin(y * 4.7 - t * 1.7) * 0.5 +
                Math.sin((x + y) * 2.1 + t * 0.8) * 0.5
            ) / 1.5;
        },

        renderFrame(t, dt) {
            const ctx = this.ctx;
            const w = this.width;
            const h = this.height;
            ctx.clearRect(0, 0, w, h);

            // Background wall gradient
            const bg = ctx.createLinearGradient(0, 0, 0, h);
            bg.addColorStop(0, '#1f2937');
            bg.addColorStop(1, '#0f172a');
            ctx.fillStyle = bg;
            ctx.fillRect(0, 0, w, h);

            // Hearth/base
            ctx.fillStyle = '#3b2e2e';
            ctx.fillRect(w * 0.1, h * 0.82, w * 0.8, h * 0.02);

            // Logs
            this.drawLogs();

            // Glow backdrop (adds warmth)
            this.drawGlow(t);

            // Flames (multiple layers with additive blending)
            ctx.globalCompositeOperation = 'lighter';
            this.drawFlameLayer(t, 1.0, '#facc15', 0.85, 0.9);
            this.drawFlameLayer(t + 0.7, 1.25, '#fb923c', 0.75, 0.95);
            this.drawFlameLayer(t + 1.3, 1.5, '#ef4444', 0.6, 1.0);
            ctx.globalCompositeOperation = 'source-over';

            // Embers (sparks)
            this.updateEmbers(dt);
            this.drawEmbers();

            // Heat shimmer overlay (subtle)
            this.drawHeatShimmer(t);
        },

        drawLogs() {
            const ctx = this.ctx, w = this.width, h = this.height;
            ctx.save();
            ctx.fillStyle = '#6b3f3a';
            const y = h * 0.8;
            const logs = [
                { x: w * 0.38, len: w * 0.28, rot: -0.05 },
                { x: w * 0.34, len: w * 0.32, rot: 0.04 },
                { x: w * 0.44, len: w * 0.22, rot: 0.02 },
            ];
            logs.forEach(l => {
                ctx.save();
                ctx.translate(l.x, y);
                ctx.rotate(l.rot);
                ctx.fillRect(-l.len / 2, -8, l.len, 16);
                // end caps
                ctx.fillStyle = '#7c4b45';
                ctx.beginPath();
                ctx.arc(-l.len / 2, 0, 8, 0, Math.PI * 2);
                ctx.arc(l.len / 2, 0, 8, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            });
            ctx.restore();
        },

        drawGlow(t) {
            const ctx = this.ctx, w = this.width, h = this.height;
            const grad = ctx.createRadialGradient(w * 0.5, h * 0.78, 10, w * 0.5, h * 0.78, w * 0.45);
            grad.addColorStop(0, 'rgba(251, 191, 36, 0.35)'); // amber-400
            grad.addColorStop(0.5, 'rgba(249, 115, 22, 0.18)'); // orange-500
            grad.addColorStop(1, 'rgba(0,0,0,0)');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, w, h);
        },

        drawFlameLayer(t, scale, color, heightFactor, flicker) {
            const ctx = this.ctx, w = this.width, h = this.height;
            const baseY = h * 0.8;
            const flameH = h * 0.3 * heightFactor * this.intensity;
            const segments = 14;
            ctx.beginPath();
            ctx.moveTo(w * 0.35, baseY);
            for (let i = 0; i <= segments; i++) {
                const u = i / segments;
                const x = w * (0.35 + u * 0.3);
                const n = this.noise(u * 2 + scale, t * (0.8 + 0.3 * scale), t * 0.3);
                const bulge = Math.sin(u * Math.PI) * flameH;
                const jitter = (n * 0.5 + 0.5) * 20 * flicker;
                const y = baseY - bulge - jitter;
                ctx.lineTo(x, y);
            }
            ctx.lineTo(w * 0.65, baseY);
            ctx.closePath();
            const grad = ctx.createLinearGradient(w * 0.5, baseY - flameH, w * 0.5, baseY);
            grad.addColorStop(0, color);
            grad.addColorStop(1, 'rgba(255, 255, 255, 0)');
            ctx.fillStyle = grad;
            ctx.fill();
        },

        spawnEmber(seed = false) {
            const w = this.width, h = this.height;
            const baseX = w * (0.4 + Math.random() * 0.2);
            const baseY = h * 0.78 + (seed ? Math.random() * 8 : 0);
            const speed = (30 + Math.random() * 60) * this.intensity;
            const life = 1.5 + Math.random() * 1.5;
            const size = 1 + Math.random() * 2;
            this.embers.push({ x: baseX, y: baseY, vx: (Math.random() - 0.5) * 10, vy: -speed, t: 0, life, size });
            if (this.embers.length > this.maxEmbers) this.embers.shift();
        },

        updateEmbers(dt) {
            const spawnRate = 40 * this.emberDensity * this.intensity;
            const count = Math.min(6, Math.floor(spawnRate * dt));
            for (let i = 0; i < count; i++) this.spawnEmber();
            const gravity = 8;
            for (let i = this.embers.length - 1; i >= 0; i--) {
                const e = this.embers[i];
                e.t += dt;
                e.x += e.vx * dt;
                e.y += e.vy * dt + gravity * dt; // slight gravity downward
                e.vx *= 0.98;
                e.vy *= 0.99;
                if (e.t > e.life || e.y < this.height * 0.3) this.embers.splice(i, 1);
            }
        },

        drawEmbers() {
            const ctx = this.ctx;
            for (const e of this.embers) {
                const alpha = Math.max(0, 1 - e.t / e.life);
                const grad = ctx.createRadialGradient(e.x, e.y, 0, e.x, e.y, 6 + e.size * 2);
                grad.addColorStop(0, `rgba(255,220,120,${0.5 * alpha})`);
                grad.addColorStop(1, 'rgba(255,220,120,0)');
                ctx.fillStyle = grad;
                ctx.beginPath();
                ctx.arc(e.x, e.y, 6 + e.size * 0.5, 0, Math.PI * 2);
                ctx.fill();
            }
        },

        drawHeatShimmer(t) {
            const ctx = this.ctx, w = this.width, h = this.height;
            ctx.save();
            ctx.globalAlpha = 0.25;
            // Wavy translucent bands above the flame region
            const bands = 12;
            for (let i = 0; i < bands; i++) {
                const yy = h * 0.5 + (i / bands) * h * 0.25;
                const amp = 2 + i * 0.5;
                const phase = t * (0.6 + i * 0.03);
                ctx.beginPath();
                ctx.moveTo(0, yy + Math.sin(phase) * amp);
                for (let x = 0; x <= w; x += 12) {
                    const y = yy + Math.sin(x * 0.03 + phase) * amp;
                    ctx.lineTo(x, y);
                }
                ctx.lineTo(w, yy + 40);
                ctx.lineTo(0, yy + 40);
                ctx.closePath();
                const grad = ctx.createLinearGradient(0, yy, 0, yy + 40);
                grad.addColorStop(0, 'rgba(255,255,255,0.03)');
                grad.addColorStop(1, 'rgba(255,255,255,0)');
                ctx.fillStyle = grad;
                ctx.fill();
            }
            ctx.restore();
        },

        // UI actions
        addLog() {
            this.intensity = Math.min(2.0, this.intensity + 0.15);
            this.emberDensity = Math.min(2.0, this.emberDensity + 0.1);
            // Gradual decay back to baseline
            const targetI = 1.0, targetE = 1.0;
            const decay = () => {
                this.intensity = this.intensity * 0.985 + targetI * 0.015;
                this.emberDensity = this.emberDensity * 0.985 + targetE * 0.015;
                if (Math.abs(this.intensity - targetI) > 0.02 || Math.abs(this.emberDensity - targetE) > 0.02) {
                    requestAnimationFrame(decay);
                }
            };
            requestAnimationFrame(decay);
        },

        updateFlicker(t) {
            // Create a soft, non-jittery flicker synced to intensity
            const i = Math.max(0.5, Math.min(2.0, this.intensity));
            const base = 0.12; // baseline glow
            const wave = (Math.sin(t * 7.3) + Math.sin(t * 5.1) * 0.5) / 1.5; // -1..1 -> softer
            const n = this.noise(0.3, 0.7, t * 0.8); // -~0.66..0.66
            const f = Math.max(0, Math.min(1, 0.5 + 0.35 * wave + 0.25 * n));
            const alpha = (base + f * 0.35) * (0.8 + 0.2 * (i - 0.5));
            const blur = 16 + f * 22;

            if (this.frameEl) {
                this.frameEl.style.boxShadow = `0 0 ${blur}px rgba(253,186,116, ${alpha})`;
            }
            if (this.glowEl) {
                // Inner glow subtle modulation
                this.glowEl.style.opacity = String(0.18 + f * 0.22);
            }
        },

        toggleAudio() {
            this.audioOn = !this.audioOn;
            if (this.audioOn && !this.audioCtx) {
                this.initAudio();
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
        },

        initAudio() {
            try {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                this.masterGain = this.audioCtx.createGain();
                this.masterGain.gain.value = 0.12; // subtle
                this.masterGain.connect(this.audioCtx.destination);
            } catch (e) {
                console.warn('Audio init failed', e);
                this.audioOn = false;
            }
        },

        maybeCrackle(dt) {
            if (!this.audioOn || !this.audioCtx) return;
            // Poisson process: avg rate scales with intensity
            const rate = 3.5 * this.intensity; // per second
            const expected = rate * dt;
            if (Math.random() < expected) {
                this.playCrackle();
            }
        },

        playCrackle() {
            const ctx = this.audioCtx;
            const duration = 0.04 + Math.random() * 0.06;
            const sampleRate = ctx.sampleRate;
            const frameCount = Math.floor(sampleRate * duration);
            const buffer = ctx.createBuffer(1, frameCount, sampleRate);
            const data = buffer.getChannelData(0);
            // envelope: sharp attack, exponential decay
            for (let i = 0; i < frameCount; i++) {
                const t = i / frameCount;
                const attack = Math.min(1, t / 0.05);
                const decay = Math.exp(-6 * t);
                const noise = (Math.random() * 2 - 1) * (0.8 + Math.random() * 0.2);
                data[i] = noise * attack * decay;
            }
            const src = ctx.createBufferSource();
            src.buffer = buffer;
            const g = ctx.createGain();
            const base = 0.05 + Math.random() * 0.1;
            g.gain.value = base;
            src.connect(g);
            g.connect(this.masterGain);
            // small pitch variance via playbackRate
            src.playbackRate.value = 0.9 + Math.random() * 0.4;
            src.start();
            src.onended = () => {
                try { src.disconnect(); g.disconnect(); } catch {}
            };
        },
    }));
});

Alpine.start();

