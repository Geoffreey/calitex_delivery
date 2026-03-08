(function () {
  const baseUrl = window.APP_BASE_URL || '';

  const overlay = document.getElementById('modalGuiaOverlay');
  const btnCerrar = document.getElementById('btnCerrarGuia');

  const btnVerFirma = document.getElementById('btnVerFirma');
  const btnVerFoto  = document.getElementById('btnVerFoto');

  // Preview modal
  const previewOverlay = document.getElementById('modalPreviewOverlay');
  const previewImg = document.getElementById('previewImg');
  const previewTitle = document.getElementById('previewTitle');
  const btnCerrarPreview = document.getElementById('btnCerrarPreview');

  // Guardamos URLs actuales para cuando el usuario le de click
  let current = { firma_url: null, foto_url: null };

  function openGuia() {
    overlay?.classList.remove('hidden');
  }

  function closeGuia() {
    overlay?.classList.add('hidden');
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '';
  }

  function formatMoney(v) {
    const n = Number(v || 0);
    return n.toLocaleString('es-GT', { style: 'currency', currency: 'GTQ' });
  }

  function formatFechaGT(fechaStr) {
    if (!fechaStr) return '—';
    const dt = new Date(String(fechaStr).replace(' ', 'T'));
    if (isNaN(dt.getTime())) return String(fechaStr);
    return dt.toLocaleDateString('es-GT', { year:'numeric', month:'long', day:'2-digit' });
  }

  function hideEvidenciaButtons() {
    btnVerFirma?.classList.add('hidden');
    btnVerFoto?.classList.add('hidden');
    current.firma_url = null;
    current.foto_url = null;
  }

  function openPreview(title, url) {
    if (!previewOverlay || !previewImg) return;
    previewTitle && (previewTitle.textContent = title);
    previewImg.src = url;
    previewOverlay.classList.remove('hidden');
  }

  function closePreview() {
    if (!previewOverlay || !previewImg) return;
    previewOverlay.classList.add('hidden');
    previewImg.src = '';
  }

  async function cargarGuia(envioId) {
    // reset evidencia
    hideEvidenciaButtons();

    // poner el id de una
    setText('modalGuiaId', envioId);

    const url = `${baseUrl}/ajax/get_envio_detalle.php?id=${encodeURIComponent(envioId)}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

    const raw = await res.text();
    let json;
    try { json = JSON.parse(raw); }
    catch (e) {
      console.error("Respuesta no es JSON:", raw);
      throw new Error('El endpoint no devolvió JSON (revisá consola).');
    }

    if (!res.ok || !json.ok) {
      throw new Error(json.msg || ('No se pudo cargar la guía'));
    }

    const d = json.data || {};

    setText('modalGuiaId', d.id ?? envioId);
    setText('modalGuiaNombre', d.nombre ?? '');
    setText('modalGuiaTelefono', d.telefono ?? '');
    setText('modalGuiaDireccion', d.direccion ?? '');
    setText('modalGuiaDescripcion', d.descripcion ?? '');
    setText('modalGuiaPagoEnvio', d.pago_envio ?? '');
    setText('modalGuiaCobro', formatMoney(d.cobro ?? 0));

    const fechaEl = document.getElementById('modalGuiaFecha');
    if (fechaEl) setText('modalGuiaFecha', formatFechaGT(d.fecha_creacion));

    // ✅ Mostrar firma/foto SOLO si está recibido
    const estado = String(d.estado || '').toLowerCase();
    const esRecibido = (estado === 'recibido');

    if (esRecibido) {
      current.firma_url = d.firma_url || null;
      current.foto_url  = d.foto_url || null;

      if (current.firma_url) btnVerFirma?.classList.remove('hidden');
      if (current.foto_url)  btnVerFoto?.classList.remove('hidden');
    }
  }

  // Click en "Ver" (delegación)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btnVerGuia');
    if (!btn) return;

    const envioId = btn.getAttribute('data-envio-id');
    if (!envioId) return;

    openGuia();
    try {
      await cargarGuia(envioId);
    } catch (err) {
      console.error(err);
      alert(err.message || 'No se pudo cargar la guía');
    }
  });

  // cerrar guía
  btnCerrar?.addEventListener('click', closeGuia);
  overlay?.addEventListener('click', (e) => {
    if (e.target === overlay) closeGuia();
  });

  // botones evidencia
  btnVerFirma?.addEventListener('click', () => {
    if (current.firma_url) openPreview('Firma de entrega', current.firma_url);
  });

  btnVerFoto?.addEventListener('click', () => {
    if (current.foto_url) openPreview('Foto de entrega', current.foto_url);
  });

  // cerrar preview
  btnCerrarPreview?.addEventListener('click', closePreview);
  previewOverlay?.addEventListener('click', (e) => {
    if (e.target === previewOverlay) closePreview();
  });

})();
