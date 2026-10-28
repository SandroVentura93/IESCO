/* ═══════════════════════════════════════════════════════════════
   app.js — Lógica principal del Sistema Web Institucional IE
   Versión Laravel: usa window.SCHOOL_CONFIG inyectado por Blade
   y llama a los endpoints del DriveProxyController en lugar de
   acceder directamente a la Google Drive API.
   Requiere: drive.js (para MIME_MAP, DEMO_DATA, DriveService helpers)
═══════════════════════════════════════════════════════════════ */
'use strict';

/* ─────────────────────────────────────────────────────────────
   TAGS PARA NOVEDADES
───────────────────────────────────────────────────────────────*/
const NEWS_TAGS = {
  'Eventos':          'fa-calendar-alt',
  'Académico':        'fa-graduation-cap',
  'Infraestructura':  'fa-building',
  'Deportes':         'fa-trophy',
  'Cultura':          'fa-palette',
  'default':          'fa-tag',
};

const UPLOADABLE_PROJECT_SECTIONS = new Map([
  ['Plan Estratégico IJENT', 'Plan_Estrategico_IJENT'],
  ['Recursos Pedagógicos', 'Recursos_Pedagogicos'],
]);

/* ═══════════════════════════════════════════════════════════════
   CLASE PRINCIPAL
═══════════════════════════════════════════════════════════════ */
class SchoolApp {

  /**
   * @param {object} config  — window.SCHOOL_CONFIG inyectado por Blade
   */
  constructor(config) {
    this.cfg    = config;
    this._stack = [];   // historial de navegación en el drive browser
    this._projectSections = [];
    this._projectSectionIndex = -1;
    this._init();
  }

  /* ──────────────────────────────────────────────────────────
     INIT
  ──────────────────────────────────────────────────────────*/
  _init() {
    this._setupLoader();
    this._setupNavbar();
    this._setupMobileMenu();
    this._setupDropdowns();
    this._setupFilesPanelViewToggle();
    this._setupProjectSectionNav();
    this._setupScrollAnimations();
    this._setupCounters();
    this._setupModal();
    this._setupVideoModal();
    this._setupImageModal();
    this._setupPdfModal();
    this._setupNovedadesCarousel();
    this._setupBackToTop();
    this._loadDriveContent();
  }

  /* ──────────────────────────────────────────────────────────
     FILES PANEL: view toggle (table <-> grid)
  ──────────────────────────────────────────────────────────*/
  _setupFilesPanelViewToggle() {
    const tableBtn = document.getElementById('viewTableBtn');
    const gridBtn  = document.getElementById('viewGridBtn');
    const panel = document.querySelector('.files-panel');
    if (!panel || !tableBtn || !gridBtn) return;

    const setView = (view) => {
      if (view === 'grid') {
        panel.classList.add('view-grid');
        gridBtn.classList.add('active');
        tableBtn.classList.remove('active');
      } else {
        panel.classList.remove('view-grid');
        tableBtn.classList.add('active');
        gridBtn.classList.remove('active');
      }
    };

    tableBtn.addEventListener('click', () => setView('table'));
    gridBtn.addEventListener('click', () => setView('grid'));

    // Initialize: prefer grid on wide screens, table on small
    setView(window.innerWidth >= 900 ? 'grid' : 'table');
  }

  /* ──────────────────────────────────────────────────────────
     LOADER
  ──────────────────────────────────────────────────────────*/
  _setupLoader() {
    const loader = document.getElementById('loader');
    if (!loader) return;
    const hide = () => {
      loader.classList.add('hidden');
      loader.addEventListener('transitionend', () => loader.remove(), { once: true });
    };
    if (document.readyState === 'complete') {
      setTimeout(hide, 400);
    } else {
      window.addEventListener('load', () => setTimeout(hide, 400), { once: true });
    }
  }

  /* ──────────────────────────────────────────────────────────
     NAVBAR
  ──────────────────────────────────────────────────────────*/
  _setupNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    const update = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
      this._updateActiveLink();
    };

    window.addEventListener('scroll', update, { passive: true });
    update();

    document.querySelectorAll('a.nav-link[href^="#"]').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        this._scrollTo(link.getAttribute('href'));
        document.getElementById('navMenu')?.classList.remove('open');
        document.getElementById('mobileToggle')?.classList.remove('active');
        document.getElementById('mobileToggle')?.setAttribute('aria-expanded', 'false');
      });
    });
  }

  _scrollTo(hash) {
    const target = document.querySelector(hash);
    if (!target) return;
    const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h')) || 80;
    window.scrollTo({ top: target.offsetTop - navH, behavior: 'smooth' });
  }

  _updateActiveLink() {
    const sections = document.querySelectorAll('section[id]');
    const links    = document.querySelectorAll('a.nav-link[href^="#"]');
    const scrollY  = window.scrollY + 120;
    let current    = '';
    sections.forEach(sec => { if (scrollY >= sec.offsetTop) current = sec.id; });
    links.forEach(link => {
      link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
    });
  }

  /* ──────────────────────────────────────────────────────────
     MOBILE MENU
  ──────────────────────────────────────────────────────────*/
  _setupMobileMenu() {
    const toggle = document.getElementById('mobileToggle');
    const menu   = document.getElementById('navMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
      const isOpen = menu.classList.toggle('open');
      toggle.classList.toggle('active', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', e => {
      if (!e.target.closest('.nav-container')) {
        menu.classList.remove('open');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        this.closeDropdowns();
      }
    });
  }

  /* ──────────────────────────────────────────────────────────
     DROPDOWN MENUS
  ──────────────────────────────────────────────────────────*/
  _setupDropdowns() {
    document.querySelectorAll('.nav-item-dropdown').forEach(parent => {
      const trigger  = parent.querySelector('.dropdown-trigger');
      const dropdown = parent.querySelector('.dropdown-menu');
      if (!trigger || !dropdown) return;

      trigger.addEventListener('click', e => {
        e.stopPropagation();
        const isOpen = parent.classList.toggle('open');
        trigger.setAttribute('aria-expanded', String(isOpen));
        document.querySelectorAll('.nav-item-dropdown').forEach(other => {
          if (other !== parent) {
            other.classList.remove('open');
            other.querySelector('.dropdown-trigger')?.setAttribute('aria-expanded', 'false');
          }
        });
      });

      parent.addEventListener('mouseleave', () => {
        if (window.innerWidth > 768) {
          parent.classList.remove('open');
          trigger.setAttribute('aria-expanded', 'false');
        }
      });

      trigger.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const isOpen = parent.classList.toggle('open');
          trigger.setAttribute('aria-expanded', String(isOpen));
          if (isOpen) dropdown.querySelector('.dropdown-item')?.focus();
        }
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          parent.classList.add('open');
          trigger.setAttribute('aria-expanded', 'true');
          dropdown.querySelector('.dropdown-item')?.focus();
        }
        if (e.key === 'Escape') {
          parent.classList.remove('open');
          trigger.setAttribute('aria-expanded', 'false');
        }
      });

      dropdown.addEventListener('keydown', e => {
        const items = [...dropdown.querySelectorAll('.dropdown-item')];
        const idx   = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); items[(idx + 1) % items.length]?.focus(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); idx <= 0 ? trigger.focus() : items[idx - 1]?.focus(); }
        if (e.key === 'Escape')    {
          parent.classList.remove('open');
          trigger.setAttribute('aria-expanded', 'false');
          trigger.focus();
        }
      });
    });
  }

  closeDropdowns() {
    document.querySelectorAll('.nav-item-dropdown.open').forEach(d => {
      d.classList.remove('open');
      d.querySelector('.dropdown-trigger')?.setAttribute('aria-expanded', 'false');
    });
  }

  _setupProjectSectionNav() {
    const prevBtn = document.getElementById('drivePrevSection');
    const nextBtn = document.getElementById('driveNextSection');

    prevBtn?.addEventListener('click', () => this.goToProjectSection(this._projectSectionIndex - 1));
    nextBtn?.addEventListener('click', () => this.goToProjectSection(this._projectSectionIndex + 1));
  }

  _uniqueProjectFolders(folders) {
    const folderIndexes = new Map();
    const uniqueFolders = [];

    folders.forEach(folder => {
      const key = (folder.name ?? '')
        .replace(/^proyecto\s+ijent\s*[·:—-]\s*/i, '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .replace(/\s+/g, ' ')
        .toLocaleLowerCase();

      if (!key) return;

      const existingIndex = folderIndexes.get(key);
      if (existingIndex === undefined) {
        folderIndexes.set(key, uniqueFolders.length);
        uniqueFolders.push(folder);
        return;
      }

      const existingFolder = uniqueFolders[existingIndex];
      if (folder.localSection && !existingFolder.localSection) {
        uniqueFolders[existingIndex] = { ...folder, name: existingFolder.name };
      } else if (!folder.localSection && existingFolder.localSection) {
        uniqueFolders[existingIndex] = { ...existingFolder, name: folder.name };
      }
    });

    return uniqueFolders;
  }

  _setProjectSections(files) {
    // Keep admin-uploaded sections reachable even when Drive has many folders.
    const allFolders = (files ?? []).filter(DriveService.isFolder);
    const localFolders = allFolders.filter(folder => (
      folder.localSection && folder.localSection !== 'Proyecto_IJENT'
    ));
    const driveFolders = allFolders.filter(folder => !folder.localSection).slice(0, 3);
    const folders = this._uniqueProjectFolders([...localFolders, ...driveFolders]);

    const vista = { id: '__vista__', name: 'Vista general', isVista: true };
    this._projectSections = [vista, ...folders];
    this._projectSectionIndex = this._projectSections.length ? 0 : -1;
    this._updateProjectSectionButtons();
  }

  _updateProjectSectionButtons() {
    const prevBtn = document.getElementById('drivePrevSection');
    const nextBtn = document.getElementById('driveNextSection');
    const hasSections = this._projectSections.length > 1;

    if (prevBtn) {
      prevBtn.style.display = hasSections ? 'inline-grid' : 'none';
      prevBtn.disabled = !hasSections;
    }
    if (nextBtn) {
      nextBtn.style.display = hasSections ? 'inline-grid' : 'none';
      nextBtn.disabled = !hasSections;
    }
  }

  async goToProjectSection(index) {
    if (!this._projectSections.length) return;
    const total = this._projectSections.length;
    const nextIndex = (index + total) % total;
    const section = this._projectSections[nextIndex];
    if (!section) return;

    this._projectSectionIndex = nextIndex;
    this._stack = [];

    // If the selected slide is the synthetic "Vista general",
    // keep/show the project highlight and render the root project files
    // in the drive area. For real folder sections, navigate into them.
    if (section.isVista) {
      // show highlight slide
      document.getElementById('projectSlide')?.classList.add('active-section');

      const files = await this._fetchLocalFiles('Proyecto_IJENT');
      const grid = document.getElementById('driveContent');
      if (grid) {
        grid.classList.remove('icons-only');
        if (files.length > 0) {
          grid.style.display = '';
          grid.removeAttribute('aria-hidden');
          this._renderDriveBrowser(files, 'Proyecto IJENT');
        } else {
          grid.innerHTML = '';
          grid.style.display = 'none';
          grid.setAttribute('aria-hidden', 'true');
        }
      }
      this._updateProjectSectionButtons();
      return;
    }

    // Non-vista sections: hide highlight and navigate into folder
    document.getElementById('projectSlide')?.classList.remove('active-section');
    // Ensure drive grid is visible for folder sections and remove icons-only state
    const gridShow = document.getElementById('driveContent');
    if (gridShow) {
      gridShow.style.display = '';
      gridShow.removeAttribute('aria-hidden');
      gridShow.classList.remove('icons-only');
    }
    await this.navigateFolder(section.id, section.name);
  }

  /* ──────────────────────────────────────────────────────────
     SCROLL ANIMATIONS
  ──────────────────────────────────────────────────────────*/
  _setupScrollAnimations() {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  }

  /* ──────────────────────────────────────────────────────────
     COUNTERS
  ──────────────────────────────────────────────────────────*/
  _setupCounters() {
    const statsBar = document.querySelector('.stats-bar');
    if (!statsBar) return;

    const run = () => {
      statsBar.querySelectorAll('.stat-number').forEach(el => {
        const target   = parseInt(el.dataset.target, 10) || 0;
        const duration = 2200;
        const step     = target / (duration / 16);
        let current    = 0;
        const tick = () => {
          current = Math.min(current + step, target);
          el.textContent = Math.floor(current);
          if (current < target) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      });
    };

    const observer = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting) { run(); observer.disconnect(); }
    }, { threshold: 0.5 });
    observer.observe(statsBar);
  }

  /* ──────────────────────────────────────────────────────────
     BACK TO TOP
  ──────────────────────────────────────────────────────────*/
  _setupBackToTop() {
    const btn = document.getElementById('backToTop');
    if (!btn) return;
    window.addEventListener('scroll', () => {
      btn.classList.toggle('visible', window.scrollY > 500);
    }, { passive: true });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* ──────────────────────────────────────────────────────────
     MODAL
  ──────────────────────────────────────────────────────────*/
  _setupModal() {
    const modal    = document.getElementById('fileModal');
    const overlay  = document.getElementById('modalOverlay');
    const closeBtn = document.getElementById('modalClose');
    if (!modal) return;

    const close = () => {
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      setTimeout(() => { const f = document.getElementById('modalFrame'); if (f) f.src = ''; }, 300);
    };

    closeBtn?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('open')) close();
    });
  }

  openFileModal(fileId, fileName, mimeType, webViewLink) {
    const modal   = document.getElementById('fileModal');
    const frame   = document.getElementById('modalFrame');
    const title   = document.getElementById('modalTitle');
    const extLink = document.getElementById('modalExternalLink');
    if (!modal || !frame) return;

    // Validar que el ID solo contiene caracteres permitidos por Drive
    if (!/^[\w-]+$/.test(fileId)) return;

    const previewUrl = DriveService.previewUrl(fileId);
    const viewUrl    = webViewLink || DriveService.viewUrl({ id: fileId });

    title.textContent = fileName;
    frame.src         = previewUrl;
    extLink.href      = viewUrl;

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  _setupVideoModal() {
    const modal = document.getElementById('videoPreviewModal');
    const video = document.getElementById('videoPreviewPlayer');
    const closeButton = document.getElementById('videoPreviewClose');
    if (!modal || !video || !closeButton) return;

    document.body.append(modal);
    closeButton.addEventListener('click', () => this.closeVideoModal());
    modal.addEventListener('click', event => {
      if (event.target === modal) this.closeVideoModal();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && modal.classList.contains('open')) {
        this.closeVideoModal();
      }
    });
  }

  openVideoModal(videoUrl, trigger = null) {
    const modal = document.getElementById('videoPreviewModal');
    const video = document.getElementById('videoPreviewPlayer');
    const title = document.getElementById('videoPreviewTitle');
    const closeButton = document.getElementById('videoPreviewClose');
    if (!modal || !video || !title || !closeButton) return;

    modal._returnFocusTo = trigger;
    title.textContent = 'Reproducción de video';
    video.src = videoUrl;
    video.load();
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    closeButton.focus();

    const playback = video.play();
    if (playback && typeof playback.catch === 'function') {
      playback.catch(error => {
        if (error.name !== 'NotAllowedError' && error.name !== 'AbortError') {
          console.warn('[SchoolApp] No se pudo iniciar la reproducción del video:', error);
        }
      });
    }
  }

  closeVideoModal() {
    const modal = document.getElementById('videoPreviewModal');
    const video = document.getElementById('videoPreviewPlayer');
    if (!modal || !video) return;

    video.pause();
    video.removeAttribute('src');
    video.load();
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    modal._returnFocusTo?.focus();
    modal._returnFocusTo = null;
  }

  _setupImageModal() {
    const modal = document.getElementById('imagePreviewModal');
    const closeButton = document.getElementById('imagePreviewClose');
    if (!modal || !closeButton) return;

    document.body.append(modal);
    closeButton.addEventListener('click', () => this.closeImageModal());
    modal.addEventListener('click', event => {
      if (event.target === modal) this.closeImageModal();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && modal.classList.contains('open')) {
        this.closeImageModal();
      }
    });
  }

  openImageModal(imageUrl, description = '', trigger = null) {
    const modal = document.getElementById('imagePreviewModal');
    const image = document.getElementById('imagePreviewPlayer');
    const title = document.getElementById('imagePreviewTitle');
    const descriptionElement = document.getElementById('imagePreviewDescription');
    const closeButton = document.getElementById('imagePreviewClose');
    if (!modal || !image || !title || !descriptionElement || !closeButton) return;

    modal._returnFocusTo = trigger;
    image.src = imageUrl;
    image.alt = 'Imagen ampliada';
    title.textContent = 'Vista previa de imagen';
    descriptionElement.textContent = description;
    descriptionElement.hidden = !description;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    closeButton.focus();
  }

  closeImageModal() {
    const modal = document.getElementById('imagePreviewModal');
    const image = document.getElementById('imagePreviewPlayer');
    if (!modal || !image) return;

    image.removeAttribute('src');
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    modal._returnFocusTo?.focus();
    modal._returnFocusTo = null;
  }

  _setupPdfModal() {
    const modal = document.getElementById('pdfPreviewModal');
    const closeButton = document.getElementById('pdfPreviewClose');
    if (!modal || !closeButton) return;

    document.body.append(modal);
    closeButton.addEventListener('click', () => this.closePdfModal());
    modal.addEventListener('click', event => {
      if (event.target === modal) this.closePdfModal();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && modal.classList.contains('open')) {
        this.closePdfModal();
      }
    });
  }

  openPdfModal(pdfUrl, description = '', trigger = null) {
    const modal = document.getElementById('pdfPreviewModal');
    const frame = document.getElementById('pdfPreviewFrame');
    const descriptionElement = document.getElementById('pdfPreviewDescription');
    const closeButton = document.getElementById('pdfPreviewClose');
    if (!modal || !frame || !descriptionElement || !closeButton) return;

    modal._returnFocusTo = trigger;
    frame.src = pdfUrl;
    descriptionElement.textContent = description;
    descriptionElement.hidden = !description;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    closeButton.focus();
  }

  closePdfModal() {
    const modal = document.getElementById('pdfPreviewModal');
    const frame = document.getElementById('pdfPreviewFrame');
    if (!modal || !frame) return;

    frame.removeAttribute('src');
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    modal._returnFocusTo?.focus();
    modal._returnFocusTo = null;
  }

  /* ──────────────────────────────────────────────────────────
     DRIVE CONTENT — carga desde el backend Laravel
  ──────────────────────────────────────────────────────────*/
  async _loadDriveContent() {
    if (this.cfg.demoMode) {
      const [localFolders, localNews, localPublications] = await Promise.all([
        this._fetchLocalFiles(),
        this._fetchLocalFiles('Novedades'),
        this._fetchLocalFiles('Publicaciones'),
      ]);
      const projectFiles = [...DEMO_DATA.proyecto, ...localFolders];
      this._setProjectSections(projectFiles);
      this._renderNavDropdown(projectFiles);
      await this.goToProjectSection(0);
      this._renderNovedades([...DEMO_DATA.novedades, ...localNews]);
      this._renderPublicaciones([...DEMO_DATA.publicaciones, ...localPublications]);
    } else {
      await this._loadFromApi();
    }
  }

  async _fetchLocalFiles(section = null, folder = null, course = null, category = null) {
    const url = new URL(this.cfg.apiRoutes.localProject, window.location.origin);
    if (section) url.searchParams.set('section', section);
    if (folder) url.searchParams.set('folder', folder);
    if (course) url.searchParams.set('course', course);
    if (category) url.searchParams.set('category', category);

    try {
      const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      return data.files ?? [];
    } catch (error) {
      console.warn('[SchoolApp] Local publication fetch error:', error);
      return [];
    }
  }

  /**
   * Obtiene datos del backend (DriveProxyController).
   * La API Key nunca se expone al cliente.
   */
  async _loadFromApi() {
    const routes = this.cfg.apiRoutes;

    const fetchFiles = async (url, fallback) => {
      try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        return data.files ?? fallback;
      } catch (err) {
        console.warn('[SchoolApp] Drive fetch error:', err);
        return fallback;
      }
    };

    const [proyectoFiles, novedadesFiles, publicacionesFiles, localFolders, localNews, localPublications] = await Promise.all([
      fetchFiles(routes.proyecto,      DEMO_DATA.proyecto),
      fetchFiles(routes.novedades,     DEMO_DATA.novedades),
      fetchFiles(routes.publicaciones, DEMO_DATA.publicaciones),
      this._fetchLocalFiles(),
      this._fetchLocalFiles('Novedades'),
      this._fetchLocalFiles('Publicaciones'),
    ]);

    const allProjectFiles = [...proyectoFiles, ...localFolders];
    this._setProjectSections(allProjectFiles);
    this._renderNavDropdown(allProjectFiles);
    await this.goToProjectSection(0);
    this._renderNovedades([...novedadesFiles, ...localNews]);
    this._renderPublicaciones([...publicacionesFiles, ...localPublications]);
  }

  /* ──────────────────────────────────────────────────────────
     RENDER: NAV DROPDOWN
  ──────────────────────────────────────────────────────────*/
  _renderNavDropdown(files) {
    const dropdown = document.getElementById('proyectoDropdown');
    if (!dropdown) return;

    const allFolders = files.filter(DriveService.isFolder);
    const folders = [
      ...allFolders.filter(folder => (
        folder.localSection && folder.localSection !== 'Proyecto_IJENT'
      )),
      ...allFolders.filter(folder => !folder.localSection).slice(0, 3),
    ];
    const uniqueFolders = this._uniqueProjectFolders(folders);

    if (uniqueFolders.length === 0) {
      dropdown.innerHTML = '<div class="dropdown-loading">Sin subcarpetas disponibles</div>';
      return;
    }

    // Compute project section indexes for each folder so dropdown items
    // can navigate the main project slide carousel (goToProjectSection).
    dropdown.innerHTML = uniqueFolders.map(f => {
      const idx = this._projectSections.findIndex(s => s.id === f.id);
      const safeIdx = idx >= 0 ? idx : 1; // fallback to first folder
      return `
      <div class="dropdown-item" role="menuitem" tabindex="0"
           data-id="${this._esc(f.id)}" data-name="${this._esc(f.name)}" data-index="${safeIdx}"
           onclick="app.goToProjectSection(${safeIdx})"
           onkeydown="if(event.key==='Enter')app.goToProjectSection(${safeIdx})">
        <i class="fas fa-folder" aria-hidden="true"></i>
        <span>${this._esc(f.name)}</span>
      </div>
    `}).join('');

    // After rendering, wire up click handlers to update the trigger label
    try {
      const parent = dropdown.closest('.nav-item-dropdown');
      const trigger = parent?.querySelector('.dropdown-trigger');
      dropdown.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', (e) => {
          // Update visible label of the trigger
          const name = item.dataset.name || item.textContent.trim();
          if (trigger) {
            const textSpan = trigger.querySelector('.nav-link-text');
            if (textSpan) {
              textSpan.textContent = name;
            } else {
              // fallback: replace first text node
              for (const node of Array.from(trigger.childNodes)) {
                if (node.nodeType === Node.TEXT_NODE) { node.textContent = name + ' '; break; }
              }
            }
            // Close dropdown
            parent.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
          }

          // Emit a custom event so other parts can react (filtering, analytics)
          try {
            const detail = { id: item.dataset.id, name };
            document.dispatchEvent(new CustomEvent('nav:dropdown:selected', { detail }));
          } catch (err) { /* ignore */ }
        });
      });
    } catch (err) {
      // fail silently; render still works via inline onclick handlers
      console.warn('[SchoolApp] _renderNavDropdown wiring failed', err);
    }
  }

  /* ──────────────────────────────────────────────────────────
     RENDER: DRIVE BROWSER
  ──────────────────────────────────────────────────────────*/
  _renderDriveBrowser(files, folderName = 'Proyecto IJENT') {
    const grid = document.getElementById('driveContent');
    const path = document.getElementById('drivePath');
    const back = document.getElementById('driveBack');
    if (!grid) return;

    this._renderDrivePath(path);

    if (back) back.style.display = this._stack.length > 0 ? 'inline-flex' : 'none';
    this._updateProjectSectionButtons();

    if (!files || files.length === 0) {
      grid.innerHTML = `
        <div class="drive-empty-state">
          <i class="fas fa-folder-open" style="font-size:2.5rem;color:var(--clr-gold);opacity:.6" aria-hidden="true"></i>
          <p>Esta carpeta está vacía.</p>
        </div>`;
      return;
    }

    grid.innerHTML = files.map(file => {
      const meta     = DriveService.getMeta(file.mimeType);
      const isFolder = DriveService.isFolder(file);
      if (!isFolder && file.localUrl) {
        const description = String(file.description ?? '').replace(/\s+/g, ' ').trim();
        const descriptionCharacters = Array.from(description);
        const descriptionExcerpt = descriptionCharacters.slice(0, 177).join('');
        const lastWordBoundary = descriptionExcerpt.lastIndexOf(' ');
        const summary = descriptionCharacters.length > 180
          ? `${descriptionExcerpt.slice(0, lastWordBoundary > 120 ? lastWordBoundary : descriptionExcerpt.length).trimEnd()}…`
          : description;
        const context = [
          file.grade ? ['Grado', file.grade] : null,
          file.courseName ? ['Curso', file.courseName] : null,
          file.week ? ['Unidad', file.week] : null,
          file.publishedAt ? ['Publicado', DriveService.formatDate(file.publishedAt)] : null,
        ].filter(Boolean);
        const details = summary || context.length
          ? `
            <div class="drive-file-details" aria-label="Información principal del archivo">
              ${summary ? `<div class="drive-file-summary"><strong>Descripción</strong><span>${this._esc(summary)}</span></div>` : ''}
              ${context.map(([label, value]) => `
                <div><strong>${this._esc(label)}</strong><span>${this._esc(value)}</span></div>
              `).join('')}
            </div>`
          : '';
        if (file.mimeType.startsWith('audio/')) {
          return `
            <article class="drive-card drive-audio-card" data-type="audio">
              <span class="drive-card-icon" aria-hidden="true">
                <i class="fas ${meta.icon}"></i>
              </span>
              <span class="drive-card-name">${this._esc(file.name)}</span>
              <span class="drive-card-type">${meta.label}</span>
              <audio controls preload="metadata" aria-label="Reproducir ${this._esc(file.name)}">
                <source src="${this._esc(file.localUrl)}" type="${this._esc(file.mimeType)}">
                Tu navegador no puede reproducir este audio.
              </audio>
              ${details}
            </article>`;
        }
        if (file.mimeType.startsWith('video/')) {
          return `
            <article class="drive-card drive-video-card" data-type="video" tabindex="0" role="button"
                     aria-label="Reproducir video: ${this._esc(file.name)}">
              <span class="drive-video-preview">
                <video muted preload="metadata" playsinline aria-hidden="true">
                  <source src="${this._esc(file.localUrl)}" type="${this._esc(file.mimeType)}">
                </video>
                <span class="drive-video-play" aria-hidden="true"><i class="fas fa-play"></i></span>
              </span>
              <span class="drive-card-name">${this._esc(file.name)}</span>
              <span class="drive-card-type">${meta.label} · Ver video</span>
              ${details}
            </article>`;
        }
        if (file.mimeType.startsWith('image/')) {
          return `
            <article class="drive-card drive-image-card" data-type="image" tabindex="0" role="button"
                     aria-label="Ver imagen: ${this._esc(file.name)}">
              <span class="drive-image-preview">
                <img src="${this._esc(file.localUrl)}" alt="${this._esc(file.name)}" loading="lazy">
              </span>
              <span class="drive-card-name">${this._esc(file.name)}</span>
              <span class="drive-card-type">${meta.label}</span>
              ${details}
            </article>`;
        }
        if (file.mimeType === 'application/pdf') {
          return `
            <article class="drive-card drive-pdf-card" data-type="pdf" data-pdf-url="${this._esc(file.localUrl)}" tabindex="0" role="button"
                     aria-label="Abrir documento PDF">
              <span class="drive-card-icon" aria-hidden="true">
                <i class="fas ${meta.icon}"></i>
              </span>
              <span class="drive-card-name">${this._esc(file.name)}</span>
              <span class="drive-card-type">PDF · Ver documento</span>
              ${details}
            </article>`;
        }
        return `
          <a class="drive-card" data-type="${meta.type}" href="${this._esc(file.localUrl)}"
             target="_blank" rel="noopener noreferrer"
             aria-label="Abrir archivo: ${this._esc(file.name)}">
            <span class="drive-card-icon" aria-hidden="true">
              <i class="fas ${meta.icon}"></i>
            </span>
            <span class="drive-card-name">${this._esc(file.name)}</span>
            <span class="drive-card-type">${meta.label}</span>
            ${details}
          </a>`;
      }
      const description = file.description?.trim()
        ? `<span class="drive-card-description">${this._esc(file.description)}</span>`
        : '';
      const action   = isFolder
        ? `app.navigateFolder('${this._esc(file.id)}','${this._esc(file.name)}')`
        : `app.openFileModal('${this._esc(file.id)}','${this._esc(file.name)}','${this._esc(file.mimeType)}','${this._esc(file.webViewLink||'')}')`;

      return `
        <div class="drive-card" data-type="${meta.type}"
             role="button" tabindex="0"
             aria-label="${isFolder ? 'Carpeta' : 'Archivo'}: ${this._esc(file.name)}"
             onclick="${action}"
             onkeydown="if(event.key==='Enter'){${action}}">
          <span class="drive-card-icon" aria-hidden="true">
            <i class="fas ${meta.icon}"></i>
          </span>
          <span class="drive-card-name">${this._esc(file.name)}</span>
          ${description}
          <span class="drive-card-type">${isFolder ? 'Carpeta' : meta.label}</span>
        </div>`;
    }).join('');

    grid.querySelectorAll('.drive-video-card').forEach(card => {
      const source = card.querySelector('video source');
      const videoUrl = source?.getAttribute('src');
      if (!videoUrl) return;

      const openVideo = () => this.openVideoModal(videoUrl, card);
      card.addEventListener('click', openVideo);
      card.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openVideo();
        }
      });
    });

    grid.querySelectorAll('.drive-image-card').forEach(card => {
      const image = card.querySelector('.drive-image-preview img');
      const imageUrl = image?.getAttribute('src');
      if (!imageUrl) return;

      const description = card.querySelector('.drive-card-description')?.textContent?.trim() ?? '';
      const openImage = () => this.openImageModal(imageUrl, description, card);
      card.addEventListener('click', openImage);
      card.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openImage();
        }
      });
    });

    grid.querySelectorAll('.drive-pdf-card').forEach(card => {
      const pdfUrl = card.dataset.pdfUrl;
      if (!pdfUrl) return;

      const description = card.querySelector('.drive-card-description')?.textContent?.trim() ?? '';
      const openPdf = () => this.openPdfModal(pdfUrl, description, card);
      card.addEventListener('click', openPdf);
      card.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openPdf();
        }
      });
    });
  }

  /* ──────────────────────────────────────────────────────────
     RENDER: ICON-ONLY GRID (para Vista general)
  ──────────────────────────────────────────────────────────*/
  _renderDriveIcons(files) {
    const grid = document.getElementById('driveContent');
    if (!grid) return;

    if (!files || files.length === 0) {
      grid.innerHTML = `
        <div class="drive-empty-state">
          <i class="fas fa-folder-open" style="font-size:2.5rem;color:var(--clr-gold);opacity:.6" aria-hidden="true"></i>
          <p>Sin recursos</p>
        </div>`;
      return;
    }

    grid.classList.add('icons-only');
    grid.style.display = '';
    grid.setAttribute('aria-hidden', 'false');

    grid.innerHTML = files.map(file => {
      const meta     = DriveService.getMeta(file.mimeType);
      const isFolder = DriveService.isFolder(file);
      const action   = isFolder
        ? `app.navigateFolder('${this._esc(file.id)}','${this._esc(file.name)}')`
        : `app.openFileModal('${this._esc(file.id)}','${this._esc(file.name)}','${this._esc(file.mimeType)}','${this._esc(file.webViewLink||'')}')`;

      return `
        <div class="drive-card" data-type="${meta.type}"
             role="button" tabindex="0"
             aria-label="${isFolder ? 'Carpeta' : 'Archivo'}: ${this._esc(file.name)}"
             onclick="${action}"
             onkeydown="if(event.key==='Enter'){${action}}">
          <span class="drive-card-icon icon-large" aria-hidden="true">
            <i class="fas ${meta.icon}"></i>
          </span>
        </div>`;
    }).join('');
  }

  /* ──────────────────────────────────────────────────────────
     NAVEGACIÓN DRIVE BROWSER
  ──────────────────────────────────────────────────────────*/
  _renderDrivePath(path) {
    if (!path) return;

    path.innerHTML = `
      <button class="path-segment path-root" type="button"
              onclick="app.resetDrive()" aria-label="Inicio proyecto IJENT">
        <i class="fas fa-folder" aria-hidden="true"></i> Proyecto IJENT
      </button>
      ${this._stack.map((folder, index) => `
        <span class="drive-sep" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
        <button class="path-segment" type="button" aria-current="${index === this._stack.length - 1 ? 'location' : 'false'}"
                onclick="app.navigateToBreadcrumb(${index})">
          ${this._esc(folder.name)}
        </button>
      `).join('')}`;
  }

  navigateToBreadcrumb(index) {
    const folder = this._stack[index];
    if (!folder) return this.resetDrive();

    this._stack = this._stack.slice(0, index);
    return this.navigateFolder(folder.id, folder.name);
  }

  async navigateFolder(folderId, folderName) {
    const grid = document.getElementById('driveContent');
    if (!grid) return;

    this.closeDropdowns();
    document.getElementById('navMenu')?.classList.remove('open');
    document.getElementById('mobileToggle')?.classList.remove('active');
    document.getElementById('mobileToggle')?.setAttribute('aria-expanded', 'false');

    this._stack.push({ id: folderId, name: folderName });
    this._scrollTo('#proyecto');

    grid.innerHTML = `<div class="drive-empty-state"><div class="spinner" aria-hidden="true"></div><p>Cargando…</p></div>`;

    const back = document.getElementById('driveBack');
    if (back) { back.style.display = 'inline-flex'; back.onclick = () => this.goBack(); }

    const path = document.getElementById('drivePath');
    this._renderDrivePath(path);

    const isExtracurricular = /actividades extracurriculares/i.test(folderName);
    const isGradeFolder = /^(1°|2°|3°|4°|5°) Secundaria$/i.test(folderName);

    if (folderId.startsWith('local_')) {
      const [section, ...folderParts] = folderId.slice('local_'.length).split('__');
      const folder = folderParts[0] ? decodeURIComponent(folderParts[0]) : null;
      const isCourseFolder = folderParts[1] === 'course';
      const course = isCourseFolder ? folderParts[2] || null : null;
      const category = isCourseFolder ? folderParts[3] || null : folderParts[1] || null;
      const files = await this._fetchLocalFiles(section, folder, course, category);
      this._renderDriveBrowser(files, folderName);
      return;
    }

    if (isExtracurricular) {
      const grados = DEMO_DATA.actividadesExtracurricularesGrados;
      this._renderDriveBrowser(grados, folderName);
      return;
    }

    if (isGradeFolder) {
      const items = DEMO_DATA.actividadesExtracurricularesContenido[folderName] ?? [];
      this._renderDriveBrowser(items, folderName);
      return;
    }

    const localSection = UPLOADABLE_PROJECT_SECTIONS.get(folderName);
    let files;
    if (this.cfg.demoMode) {
      files = localSection
        ? await this._fetchLocalFiles(localSection)
        : DEMO_DATA.proyectoSubFolder;
    } else {
      try {
        const url = `${this.cfg.apiRoutes.subfolder}?folder_id=${encodeURIComponent(folderId)}`;
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        files = data.files ?? DEMO_DATA.proyectoSubFolder;
      } catch {
        files = DEMO_DATA.proyectoSubFolder;
      }
      if (localSection) {
        files = [...files, ...await this._fetchLocalFiles(localSection)];
      }
    }

    this._renderDriveBrowser(files, folderName);
  }

  goBack() {
    this._stack.pop();
    if (this._stack.length === 0) {
      this.resetDrive();
    } else {
      const prev = this._stack[this._stack.length - 1];
      this._stack.pop();
      this.navigateFolder(prev.id, prev.name);
    }
  }

  async resetDrive() {
    this._stack = [];
    if (this._projectSections.length) {
      await this.goToProjectSection(0);
      return;
    }

    const grid = document.getElementById('driveContent');
    if (grid) {
      grid.innerHTML = `<div class="drive-empty-state"><div class="spinner" aria-hidden="true"></div><p>Cargando…</p></div>`;
    }

    let files;
    if (this.cfg.demoMode) {
      files = DEMO_DATA.proyecto;
    } else {
      try {
        const res = await fetch(this.cfg.apiRoutes.proyecto, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        files = data.files ?? DEMO_DATA.proyecto;
      } catch {
        files = DEMO_DATA.proyecto;
      }
    }

    this._setProjectSections(files);
    await this.goToProjectSection(0);
  }

  _isExtracurricularGradeFolder(folderName) {
    return /^(1°|2°|3°|4°|5°) Secundaria$/i.test(folderName);
  }

  _setupNovedadesCarousel() {
    const grid = document.getElementById('novedadesGrid');
    const previous = document.getElementById('novedadesPrev');
    const next = document.getElementById('novedadesNext');
    const status = document.getElementById('novedadesStatus');
    if (!grid || !previous || !next || !status) return;

    const update = () => {
      const cards = Array.from(grid.children);
      const style = getComputedStyle(grid);
      const gap = parseFloat(style.columnGap || style.gap) || 0;
      const padding = (parseFloat(style.paddingLeft) || 0) + (parseFloat(style.paddingRight) || 0);
      const firstCardWidth = cards[0]?.getBoundingClientRect().width || grid.clientWidth;
      const availableWidth = Math.max(1, grid.clientWidth - padding);
      const cardsPerPage = Math.max(1, Math.floor((availableWidth + gap) / (firstCardWidth + gap) + 0.01));
      const stride = cards[cardsPerPage]
        ? cards[cardsPerPage].getBoundingClientRect().left - cards[0].getBoundingClientRect().left
        : grid.clientWidth;
      const pages = Math.max(1, Math.ceil(cards.length / cardsPerPage));
      const currentPage = Math.min(pages, Math.max(1, Math.round(grid.scrollLeft / (stride || 1)) + 1));
      previous.disabled = currentPage <= 1;
      next.disabled = currentPage >= pages;
      status.textContent = `${String(currentPage).padStart(2, '0')} / ${String(pages).padStart(2, '0')}`;
      const hasMultiplePages = pages > 1;
      previous.parentElement.hidden = !hasMultiplePages;
      this._novedadesCarouselStride = stride;
    };

    previous.addEventListener('click', () => {
      grid.scrollBy({ left: -this._novedadesCarouselStride, behavior: 'smooth' });
    });
    next.addEventListener('click', () => {
      grid.scrollBy({ left: this._novedadesCarouselStride, behavior: 'smooth' });
    });
    grid.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    this._updateNovedadesCarousel = update;
    update();
  }

  /* ──────────────────────────────────────────────────────────
     RENDER: NOVEDADES
  ──────────────────────────────────────────────────────────*/
  _renderNovedades(files) {
    const grid = document.getElementById('novedadesGrid');
    if (!grid) return;

    const managedCards = Array.from(grid.querySelectorAll('[data-managed-news]'))
      .map(card => card.closest('.news-grid-item')?.outerHTML || card.outerHTML);

    if (!files || files.length === 0) {
      if (managedCards.length) {
        grid.innerHTML = managedCards.join('');
      } else {
        grid.innerHTML = '<p style="text-align:center;color:var(--clr-text-muted)">No hay novedades disponibles.</p>';
      }
      this._updateNovedadesCarousel?.();
      return;
    }

    // fallback images to use when Drive file has no thumbnail
    const FALLBACK_IMAGES = [
      '/images/1.jpeg','/images/2.jpeg','/images/3.jpeg','/images/4.jpeg','/images/logo.jpg','/images/LOGO.png'
    ];

    const managedTitles = new Set(
      Array.from(grid.querySelectorAll('[data-managed-news] h3'))
        .map(title => title.textContent.trim().toLocaleLowerCase())
    );
    const uniqueFiles = files.filter(file => {
      const title = (file.name || '').trim().toLocaleLowerCase();
      if (!title || managedTitles.has(title)) return false;
      managedTitles.add(title);
      return true;
    });

    const dynamicCards = uniqueFiles.map((file, idx) => {
      const meta    = DriveService.getMeta(file.mimeType);
      const date    = DriveService.formatDate(file.modifiedTime);
      const desc    = file.description || (file.localUrl
        ? 'Recurso institucional disponible en el sitio.'
        : 'Haga clic para acceder al documento completo en Google Drive.');
      const tag     = file.tag || 'Noticia';
      const tagIcon = NEWS_TAGS[tag] ?? NEWS_TAGS.default;
      const viewUrl = file.webViewLink || DriveService.viewUrl(file);

      // Prefer thumbnailLink from Drive; otherwise use a local fallback image (round-robin)
      const thumb = file.thumbnailLink || (file.localUrl && file.mimeType.startsWith('image/')
        ? file.localUrl
        : FALLBACK_IMAGES[idx % FALLBACK_IMAGES.length]);
      const fullImage = file.localUrl || file.webContentLink || thumb;
      const openAction = file.localUrl
        ? `window.open('${this._esc(file.localUrl)}','_blank','noopener,noreferrer')`
        : `app.openFileModal('${this._esc(file.id)}','${this._esc(file.name)}','${this._esc(file.mimeType)}','${this._esc(viewUrl)}')`;

      return `
        <article class="news-card reveal"
                 role="button" tabindex="0"
                 aria-label="Novedad: ${this._esc(file.name)}"
                 onclick="if (!event.target.closest('.news-cta')) { ${openAction} }"
                 onkeydown="if(event.key==='Enter'&&!event.target.closest('.news-cta'))${openAction}">
          <div class="news-card-cover" aria-hidden="true">
            <img src="${thumb}" data-full-src="${this._esc(fullImage)}" alt="${this._esc(file.name)}" />
          </div>
          <div class="news-body">
            <div class="news-date">
              <i class="fas fa-calendar-alt" aria-hidden="true"></i> ${date}
            </div>
            <h3>${this._esc(file.name)}</h3>
            <p>${this._esc(desc)}</p>
          </div>
          <div class="news-footer">
            <span class="news-tag">
              <i class="fas ${tagIcon}" aria-hidden="true"></i> ${this._esc(tag)}
            </span>
            <button class="news-cta" type="button">
              Ver más <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </article>`;
    }).join('');
    grid.innerHTML = [...managedCards, dynamicCards].join('');

    this._updateNovedadesCarousel?.();
    document.querySelectorAll('#novedadesGrid .reveal').forEach(el => this._observeReveal(el));
  }

  /* ──────────────────────────────────────────────────────────
     RENDER: PUBLICACIONES
  ──────────────────────────────────────────────────────────*/
  _renderPublicaciones(files) {
    const grid = document.getElementById('publicacionesGrid');
    if (!grid) return;

    if (!files || files.length === 0) {
      grid.innerHTML = '<p style="text-align:center;color:var(--clr-text-muted)">No hay publicaciones disponibles.</p>';
      return;
    }

    grid.innerHTML = files.map(file => {
      const meta    = DriveService.getMeta(file.mimeType);
      const viewUrl = file.localUrl || file.webViewLink || DriveService.viewUrl(file);
      const description = file.description?.trim()
        ? `<p class="pub-description">${this._esc(file.description)}</p>`
        : '';
      const openAction = file.localUrl
        ? `window.open('${this._esc(file.localUrl)}','_blank','noopener,noreferrer')`
        : `app.openFileModal('${this._esc(file.id)}','${this._esc(file.name)}','${this._esc(file.mimeType)}','${this._esc(viewUrl)}')`;

      return `
        <div class="pub-card reveal"
             role="button" tabindex="0"
             aria-label="Publicación: ${this._esc(file.name)}"
             onclick="${openAction}"
             onkeydown="if(event.key==='Enter')${openAction}">
          <div class="pub-icon ${meta.type}" aria-hidden="true">
            <i class="fas ${meta.icon}"></i>
          </div>
          <h3>${this._esc(file.name)}</h3>
          ${description}
          <div class="pub-meta">
            <span>${meta.label}</span>
            <a href="${this._esc(viewUrl)}" target="_blank" rel="noopener noreferrer"
               onclick="event.stopPropagation()"
               aria-label="Abrir ${this._esc(file.name)}">
              <i class="fas fa-external-link-alt" aria-hidden="true"></i> Abrir
            </a>
          </div>
        </div>`;
    }).join('');

    document.querySelectorAll('#publicacionesGrid .reveal').forEach(el => this._observeReveal(el));
  }

  /* ──────────────────────────────────────────────────────────
     HELPERS
  ──────────────────────────────────────────────────────────*/
  _observeReveal(el) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add('in-view'); observer.unobserve(entry.target); }
      });
    }, { threshold: 0.1 });
    observer.observe(el);
  }

  /**
   * Escapa un valor para uso seguro dentro de atributos HTML o cadenas JS.
   */
  _esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g,  '&amp;')
      .replace(/</g,  '&lt;')
      .replace(/>/g,  '&gt;')
      .replace(/"/g,  '&quot;')
      .replace(/'/g,  '&#39;')
      .replace(/\\/g, '&#92;');
  }
}

/* ═══════════════════════════════════════════════════════════════
   ARRANQUE
═══════════════════════════════════════════════════════════════ */
const app = new SchoolApp(window.SCHOOL_CONFIG);
