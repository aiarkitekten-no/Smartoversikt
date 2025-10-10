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
});

Alpine.start();

