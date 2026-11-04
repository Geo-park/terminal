/**
 * WARLAB Smooth SPA Navigation Engine
 * Memberikan pengalaman navigasi instan & halus (tanpa reload putih layar),
 * mendukung browser history (back/forward), form AJAX, dan re-inisialisasi Alpine.js.
 */

class SPANavigator {
    constructor() {
        this.mainElement = null;
        this.progressBar = null;
        this.isNavigating = false;
    }

    init() {
        const start = () => {
            this.setupProgressBar();
            this.setupListeners();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }
    }

    setupProgressBar() {
        if (document.getElementById('spa-progress-bar')) return;
        const bar = document.createElement('div');
        bar.id = 'spa-progress-bar';
        bar.className = 'fixed top-0 left-0 h-[2.5px] bg-[#424dc4] z-[9999] transition-all duration-200 opacity-0 pointer-events-none';
        bar.style.width = '0%';
        document.body.appendChild(bar);
        this.progressBar = bar;
    }

    showProgress() {
        if (!this.progressBar) return;
        this.progressBar.style.opacity = '1';
        this.progressBar.style.width = '35%';
        setTimeout(() => {
            if (this.isNavigating && this.progressBar) {
                this.progressBar.style.width = '75%';
            }
        }, 150);
    }

    hideProgress() {
        if (!this.progressBar) return;
        this.progressBar.style.width = '100%';
        setTimeout(() => {
            this.progressBar.style.opacity = '0';
            setTimeout(() => {
                this.progressBar.style.width = '0%';
            }, 200);
        }, 100);
    }

    setupListeners() {
        // Tangkap klik semua tautan internal
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href) return;

            // Abaikan tautan luar, anchor lokal, javascript, atau target blank
            if (href.startsWith('#') || 
                href.startsWith('javascript:') || 
                href.startsWith('mailto:') || 
                link.target === '_blank' || 
                link.hasAttribute('download') ||
                link.dataset.noSpa !== undefined) {
                return;
            }

            // Validasi URL adalah origin yang sama
            try {
                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.origin !== window.location.origin) return;

                // Jangan navigasi ulang jika sudah di halaman yang persis sama
                if (targetUrl.href === window.location.href) {
                    e.preventDefault();
                    return;
                }

                // Jangan tangani rute luar portal aplikasi (misal: logout, login)
                if (targetUrl.pathname === '/login' || targetUrl.pathname === '/logout') {
                    return;
                }

                e.preventDefault();
                this.navigate(targetUrl.href, true);
            } catch (err) {
                // Biarkan browser default jika URL tidak valid
            }
        });

        // Tangkap tombol Browser Back / Forward
        window.addEventListener('popstate', () => {
            this.navigate(window.location.href, false);
        });

        // Tangkap submit form di dalam konten mahasiswa secara halus
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form || form.dataset.noSpa !== undefined) return;

            const action = form.getAttribute('action') || window.location.href;
            const targetUrl = new URL(action, window.location.origin);

            // Hanya proses form di dalam aplikasi (abaikan logout / auth form)
            if (targetUrl.pathname === '/login' || targetUrl.pathname === '/logout') return;

            e.preventDefault();
            this.submitForm(form);
        });
    }

    async navigate(url, updateHistory = true) {
        if (this.isNavigating) return;
        this.isNavigating = true;
        this.showProgress();

        const main = document.querySelector('main');
        if (main) {
            main.style.transition = 'opacity 90ms ease-out';
            main.style.opacity = '0.4';
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            });

            if (!response.ok) {
                window.location.href = url; // Fallback ke normal browser load jika 404/500
                return;
            }

            const htmlText = await response.text();
            this.renderPage(htmlText, response.url || url, updateHistory);
        } catch (error) {
            console.error('SPA Navigation Error:', error);
            window.location.href = url; // Fallback jika gagal
        } finally {
            this.isNavigating = false;
            this.hideProgress();
        }
    }

    async submitForm(form) {
        if (this.isNavigating) return;
        this.isNavigating = true;
        this.showProgress();

        const main = document.querySelector('main');
        if (main) {
            main.style.transition = 'opacity 90ms ease-out';
            main.style.opacity = '0.4';
        }

        const formData = new FormData(form);
        const action = form.getAttribute('action') || window.location.href;
        const method = (form.getAttribute('method') || 'POST').toUpperCase();

        try {
            const response = await fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml'
                }
            });

            const htmlText = await response.text();
            this.renderPage(htmlText, response.url || action, true);
        } catch (error) {
            console.error('SPA Form Submit Error:', error);
            form.submit(); // Fallback ke submission default
        } finally {
            this.isNavigating = false;
            this.hideProgress();
        }
    }

    renderPage(htmlText, targetUrl, updateHistory) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');

        // Update document title
        if (doc.title) {
            document.title = doc.title;
        }

        // Update topbar context title
        const newTopbarTitle = doc.getElementById('topbar-page-title');
        const currentTopbarTitle = document.getElementById('topbar-page-title');
        if (newTopbarTitle && currentTopbarTitle) {
            currentTopbarTitle.innerHTML = newTopbarTitle.innerHTML;
        }

        // Update main content
        const newMain = doc.querySelector('main');
        const currentMain = document.querySelector('main');

        if (newMain && currentMain) {
            if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                try {
                    window.Alpine.destroyTree(currentMain);
                } catch (e) {
                    console.warn('Alpine destroyTree error:', e);
                }
            }
            currentMain.innerHTML = newMain.innerHTML;

            // Eksekusi script inline baru jika ada
            const scripts = currentMain.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.textContent = oldScript.textContent;
                document.body.appendChild(newScript);
                newScript.remove();
            });

            // Re-inisialisasi Alpine.js pada DOM baru
            if (window.Alpine) {
                window.Alpine.initTree(currentMain);
            }

            // Kembalikan opacity secara mulus
            currentMain.style.transition = 'opacity 120ms ease-in';
            currentMain.style.opacity = '1';
        }

        // Sinkronisasi navigasi sidebar dari HTML respon server jika ada
        const newNav = doc.querySelector('aside nav');
        const currentNav = document.querySelector('aside nav');
        if (newNav && currentNav) {
            if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                try { window.Alpine.destroyTree(currentNav); } catch(e) {}
            }
            currentNav.innerHTML = newNav.innerHTML;
            if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                window.Alpine.initTree(currentNav);
            }
        }

        // Perbarui active link pada Sidebar Menu secara ketat
        this.updateSidebarActiveLinks(targetUrl);

        // Update URL di browser history
        if (updateHistory) {
            window.history.pushState({}, '', targetUrl);
        }

        // Scroll ke atas dengan halus
        window.scrollTo({ top: 0, behavior: 'instant' });
    }

    updateSidebarActiveLinks(currentUrl) {
        const urlObj = new URL(currentUrl, window.location.origin);
        const path = urlObj.pathname;

        const navLinks = document.querySelectorAll('aside nav a');
        navLinks.forEach(a => {
            const href = a.getAttribute('href');
            if (!href) return;

            const linkUrl = new URL(href, window.location.origin);

            // Normalisasi path agar mencocokkan baik URL baru maupun URL lama
            const cleanPath = path.replace(/^\/mahasiswa/, '');
            const cleanLinkPath = linkUrl.pathname.replace(/^\/mahasiswa/, '');

            // Menu Ajukan Reservasi: aktif jika URL saat ini diawali /reservasi
            const isReservasi = cleanPath.startsWith('/reservasi') && 
                                cleanLinkPath.startsWith('/reservasi');

            // Menu Buku Booking: aktif jika URL saat ini diawali /buku-booking
            const isBukuBooking = cleanPath.startsWith('/buku-booking') &&
                                  cleanLinkPath.startsWith('/buku-booking');

            // Menu Dashboard: HANYA aktif jika URL saat ini adalah /dashboard (dan bukan reservasi atau buku-booking)
            const isDashboard = !isReservasi && !isBukuBooking && 
                                (cleanPath === '/dashboard' || cleanPath === '' || cleanPath === '/') && 
                                (cleanLinkPath === '/dashboard');

            let isMatch = false;
            if (cleanLinkPath.startsWith('/reservasi')) {
                isMatch = isReservasi;
            } else if (cleanLinkPath.startsWith('/buku-booking')) {
                isMatch = isBukuBooking;
            } else if (cleanLinkPath === '/dashboard') {
                isMatch = isDashboard;
            }

            const icon = a.querySelector('svg');

            if (isMatch) {
                a.classList.add('text-[#424dc4]', 'bg-[#eef2ff]');
                a.classList.remove('text-[#64748b]', 'hover:text-[#0b1c30]', 'hover:bg-slate-50');
                if (icon) {
                    icon.classList.add('text-[#424dc4]');
                    icon.classList.remove('text-slate-400', 'group-hover:text-slate-600');
                }
                if (!a.querySelector('.active-indicator')) {
                    const span = document.createElement('span');
                    span.className = 'active-indicator absolute right-0 top-2 bottom-2 w-1 bg-[#424dc4] rounded-l-full';
                    a.appendChild(span);
                }
            } else {
                a.classList.remove('text-[#424dc4]', 'bg-[#eef2ff]');
                a.classList.add('text-[#64748b]', 'hover:text-[#0b1c30]', 'hover:bg-slate-50');
                if (icon) {
                    icon.classList.remove('text-[#424dc4]');
                    icon.classList.add('text-slate-400', 'group-hover:text-slate-600');
                }
                // Hapus semua elemen indicator bar pada menu yang tidak dipilih
                const indicators = a.querySelectorAll('.active-indicator, span[class*="rounded-l-full"]');
                indicators.forEach(el => el.remove());
            }
        });
    }
}

export default new SPANavigator();
