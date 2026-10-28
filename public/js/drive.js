/* ═══════════════════════════════════════════════════════════════
   drive.js — Servicio de integración con Google Drive API v3
   ───────────────────────────────────────────────────────────────
   Expone: DriveService, DEMO_DATA
═══════════════════════════════════════════════════════════════ */
'use strict';

/* ─────────────────────────────────────────────────────────────
   TIPOS MIME → metadatos de presentación
───────────────────────────────────────────────────────────────*/
const MIME_MAP = {
  'application/vnd.google-apps.folder':       { icon: 'fa-folder',          label: 'Carpeta',        type: 'folder'  },
  'application/vnd.google-apps.document':     { icon: 'fa-file-word',        label: 'Documento',      type: 'doc'     },
  'application/vnd.google-apps.spreadsheet':  { icon: 'fa-file-excel',       label: 'Hoja de cálculo',type: 'sheet'   },
  'application/vnd.google-apps.presentation': { icon: 'fa-file-powerpoint',  label: 'Presentación',   type: 'slide'   },
  'application/vnd.google-apps.form':         { icon: 'fa-wpforms',          label: 'Formulario',     type: 'default' },
  'application/pdf':                          { icon: 'fa-file-pdf',         label: 'PDF',            type: 'pdf'     },
  'image/jpeg':                               { icon: 'fa-file-image',       label: 'Imagen',         type: 'image'   },
  'image/png':                                { icon: 'fa-file-image',       label: 'Imagen',         type: 'image'   },
  'image/gif':                                { icon: 'fa-file-image',       label: 'Imagen',         type: 'image'   },
  'image/webp':                               { icon: 'fa-file-image',       label: 'Imagen',         type: 'image'   },
  'video/mp4':                                { icon: 'fa-file-video',       label: 'Video',          type: 'video'   },
  'video/quicktime':                          { icon: 'fa-file-video',       label: 'Video',          type: 'video'   },
  'audio/mpeg':                               { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/mp4':                                { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/aac':                                { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/ogg':                                { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/wav':                                { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/webm':                               { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/flac':                               { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
  'audio/x-wav':                              { icon: 'fa-file-audio',       label: 'Audio',          type: 'audio'   },
};

const DEFAULT_META = { icon: 'fa-file', label: 'Archivo', type: 'default' };

/* ─────────────────────────────────────────────────────────────
   DATOS DE DEMOSTRACIÓN (se usan cuando demoMode: true)
───────────────────────────────────────────────────────────────*/
const DEMO_DATA = {

  proyecto: [
    { id: 'd1', name: 'Plan Estratégico IJENT',           mimeType: 'application/vnd.google-apps.folder'        },
    { id: 'd2', name: 'Recursos Pedagógicos',             mimeType: 'application/vnd.google-apps.folder'        },
    { id: 'd3', name: 'Actividades Extracurriculares',    mimeType: 'application/vnd.google-apps.folder'        },
    { id: 'd4', name: 'Evaluaciones y Seguimiento',       mimeType: 'application/vnd.google-apps.folder'        },
    { id: 'd5', name: 'Formación Docente 2026',           mimeType: 'application/vnd.google-apps.folder'        },
    { id: 'd6', name: 'Guía Metodológica IJENT',          mimeType: 'application/pdf'                           },
    { id: 'd7', name: 'Cronograma Institucional',         mimeType: 'application/vnd.google-apps.spreadsheet'   },
    { id: 'd8', name: 'Presentación del Proyecto',        mimeType: 'application/vnd.google-apps.presentation'  },
  ],

  proyectoSubFolder: [
    { id: 'ds1', name: 'Guía Didáctica Matemáticas',     mimeType: 'application/vnd.google-apps.document'    },
    { id: 'ds2', name: 'Material de Lectura Crítica',    mimeType: 'application/pdf'                          },
    { id: 'ds3', name: 'Rubrica de Evaluación',          mimeType: 'application/vnd.google-apps.spreadsheet'  },
    { id: 'ds4', name: 'Videos explicativos',            mimeType: 'application/vnd.google-apps.folder'       },
    { id: 'ds5', name: 'Presentación Ciencias Naturales',mimeType: 'application/vnd.google-apps.presentation' },
  ],

  actividadesExtracurricularesGrados: [
    { id: 'ae1', name: '1° Secundaria', mimeType: 'application/vnd.google-apps.folder' },
    { id: 'ae2', name: '2° Secundaria', mimeType: 'application/vnd.google-apps.folder' },
    { id: 'ae3', name: '3° Secundaria', mimeType: 'application/vnd.google-apps.folder' },
    { id: 'ae4', name: '4° Secundaria', mimeType: 'application/vnd.google-apps.folder' },
    { id: 'ae5', name: '5° Secundaria', mimeType: 'application/vnd.google-apps.folder' },
  ],

  actividadesExtracurricularesContenido: {
    '1° Secundaria': [
      { id: 'ae1a', name: 'Deportes y recreación', mimeType: 'application/pdf' },
      { id: 'ae1b', name: 'Arte y cultura', mimeType: 'application/vnd.google-apps.document' },
    ],
    '2° Secundaria': [
      { id: 'ae2a', name: 'Música y danza', mimeType: 'application/vnd.google-apps.document' },
      { id: 'ae2b', name: 'Ciencia y tecnología', mimeType: 'application/pdf' },
    ],
    '3° Secundaria': [
      { id: 'ae3a', name: 'Liderazgo estudiantil', mimeType: 'application/vnd.google-apps.document' },
      { id: 'ae3b', name: 'Concursos académicos', mimeType: 'application/pdf' },
    ],
    '4° Secundaria': [
      { id: 'ae4a', name: 'Voluntariado', mimeType: 'application/vnd.google-apps.document' },
      { id: 'ae4b', name: 'Emprendimiento', mimeType: 'application/pdf' },
    ],
    '5° Secundaria': [
      { id: 'ae5a', name: 'Proyecto de promoción', mimeType: 'application/vnd.google-apps.document' },
      { id: 'ae5b', name: 'Ensayos y ceremonia', mimeType: 'application/pdf' },
    ],
  },

  novedades: [],

  publicaciones: [
    { id: 'p1', name: 'Manual de Convivencia 2026',     mimeType: 'application/pdf'                          },
    { id: 'p2', name: 'PEI Institucional',              mimeType: 'application/vnd.google-apps.document'    },
    { id: 'p3', name: 'Plan de Estudios',               mimeType: 'application/vnd.google-apps.spreadsheet'  },
    { id: 'p4', name: 'Informe de Gestión 2025',        mimeType: 'application/pdf'                          },
    { id: 'p5', name: 'Boletín Informativo Padres',     mimeType: 'application/vnd.google-apps.document'    },
    { id: 'p6', name: 'Circular — Calendario Escolar',  mimeType: 'application/pdf'                          },
  ],
};

/* ─────────────────────────────────────────────────────────────
   DRIVE SERVICE CLASS
───────────────────────────────────────────────────────────────*/
class DriveService {
  /**
   * @param {string} apiKey — Google Drive API key (read-only)
   */
  constructor(apiKey) {
    this._apiKey  = apiKey;
    this._baseUrl = 'https://www.googleapis.com/drive/v3';
    this._cache   = new Map();
  }

  /* ── Public API ─────────────────────────────────────────── */

  /**
   * List files/folders inside a Drive folder.
   * @param {string} folderId
   * @param {Object} [opts]
   * @param {number} [opts.pageSize=30]
   * @returns {Promise<Array>}
   */
  async listFiles(folderId, opts = {}) {
    const cacheKey = `list_${folderId}`;
    if (this._cache.has(cacheKey)) return this._cache.get(cacheKey);

    const params = new URLSearchParams({
      q:       `'${folderId}' in parents and trashed = false`,
      // include thumbnailLink so we can show image previews when available
      fields:  'files(id,name,mimeType,modifiedTime,size,webViewLink,webContentLink,thumbnailLink)',
      orderBy: 'folder,name',
      pageSize: String(opts.pageSize ?? 30),
      key:     this._apiKey,
    });

    const url = `${this._baseUrl}/files?${params}`;

    let data;
    try {
      const res = await fetch(url, { mode: 'cors' });
      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err?.error?.message ?? `HTTP ${res.status}`);
      }
      data = await res.json();
    } catch (err) {
      console.error('[DriveService] listFiles error:', err);
      throw err;
    }

    const files = data.files ?? [];
    this._cache.set(cacheKey, files);
    return files;
  }

  /** Invalidate cached entry for a folder. */
  invalidate(folderId) {
    this._cache.delete(`list_${folderId}`);
  }

  /* ── Helpers ────────────────────────────────────────────── */

  /**
   * Returns display metadata for a given mimeType.
   * @param {string} mimeType
   * @returns {{ icon: string, label: string, type: string }}
   */
  static getMeta(mimeType) {
    return MIME_MAP[mimeType] ?? DEFAULT_META;
  }

  /**
   * Returns an embeddable preview URL for a Drive file.
   * @param {string} fileId
   * @returns {string}
   */
  static previewUrl(fileId) {
    return `https://drive.google.com/file/d/${encodeURIComponent(fileId)}/preview`;
  }

  /**
   * Returns the Google Drive view URL for a file.
   * @param {{ id: string, webViewLink?: string }} file
   * @returns {string}
   */
  static viewUrl(file) {
    return file.webViewLink
      ?? `https://drive.google.com/file/d/${encodeURIComponent(file.id)}/view`;
  }

  /**
   * Format an ISO date string to a human-readable Spanish date.
   * @param {string} iso
   * @returns {string}
   */
  static formatDate(iso) {
    if (!iso) return 'Fecha reciente';
    try {
      return new Date(iso).toLocaleDateString('es-CO', {
        year: 'numeric', month: 'long', day: 'numeric',
      });
    } catch {
      return iso;
    }
  }

  /** Determine if a file entry is a folder. */
  static isFolder(file) {
    return file.mimeType === 'application/vnd.google-apps.folder';
  }
}
