let currentEnvioId = null;
  let signaturePad = null;

  const modalFirma = document.getElementById('modalFirma');
  const modalFoto  = document.getElementById('modalFoto');
  const canvas = document.getElementById('firmaCanvas');

  function openFirmaModal(envioId){
    currentEnvioId = envioId;
    document.getElementById('envioIdInput').value = envioId;

    modalFirma.classList.remove('hidden');

    // Ajustar canvas a tamaño real
    setTimeout(() => {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);

      signaturePad = new SignaturePad(canvas, { minWidth: 1, maxWidth: 2 });
    }, 50);
  }

  function closeFirmaModal(){
    modalFirma.classList.add('hidden');
    if (signaturePad) signaturePad.off();
    signaturePad = null;
    currentEnvioId = null;
  }

  function clearFirma(){
    if (signaturePad) signaturePad.clear();
  }

  function goFotoModal(){
    if (!signaturePad || signaturePad.isEmpty()){
      alert("La firma es obligatoria.");
      return;
    }
    const firmaBase64 = signaturePad.toDataURL("image/png");
    document.getElementById('firmaBase64Input').value = firmaBase64;

    modalFirma.classList.add('hidden');
    modalFoto.classList.remove('hidden');
  }

  function closeFotoModal(){
    modalFoto.classList.add('hidden');
    // no borramos firma automáticamente
  }

  function backToFirma(){
    modalFoto.classList.add('hidden');
    modalFirma.classList.remove('hidden');
  }

  // Foto -> base64
  const fotoInput = document.getElementById('fotoInput');
  const fotoPreview = document.getElementById('fotoPreview');

  fotoInput.addEventListener('change', () => {
    const file = fotoInput.files && fotoInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      const base64 = reader.result; // data:image/...
      document.getElementById('fotoBase64Input').value = base64;

      fotoPreview.src = base64;
      fotoPreview.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
  });

  // Validación antes de enviar
  document.getElementById('formEntregar').addEventListener('submit', (e) => {
    const firma = document.getElementById('firmaBase64Input').value;
    const foto  = document.getElementById('fotoBase64Input').value;
    if (!currentEnvioId || !firma || !foto){
      e.preventDefault();
      alert("Falta firma o foto.");
    }
  });


  (function () {
  const overlay = document.getElementById('modalGuiaOverlay');
  const btnCerrar = document.getElementById('btnCerrarGuia');

  const btnVerFirma = document.getElementById('btnVerFirma');
  const btnVerFoto  = document.getElementById('btnVerFoto');

  const previewOverlay = document.getElementById('modalPreviewOverlay');
  const previewImg = document.getElementById('previewImg');
  const previewTitle = document.getElementById('previewTitle');
  const btnCerrarPreview = document.getElementById('btnCerrarPreview');

  let current = { id: null, firma_url: null, foto_url: null, estado: null };

  function openGuia() {
    overlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeGuia() {
    overlay.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  function openPreview(title, url) {
    previewTitle.textContent = title;
    previewImg.src = url;
    previewOverlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closePreview() {
    previewOverlay.classList.add('hidden');
    previewImg.src = '';
    document.body.classList.remove('overflow-hidden');
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '';
  }

  function formatMoney(v) {
    const n = Number(v || 0);
    return n.toLocaleString('es-GT', { style: 'currency', currency: 'GTQ' });
  }

  function isFinalizado(estado) {
    // Ajustá a tu catálogo real
    return String(estado || '').toUpperCase() === 'FINALIZADO';
  }

  async function cargarGuia(envioId) {
    // Reset
    current = { id: envioId, firma_url: null, foto_url: null, estado: null };
    btnVerFirma.classList.add('hidden');
    btnVerFoto.classList.add('hidden');

    // Opcional: poné placeholders mientras carga
    setText('modalGuiaId', envioId);

    const baseUrl = window.APP_BASE_URL || '';
const url = `${baseUrl}/ajax/get_envio_detalle.php?id=${encodeURIComponent(envioId)}`;

const res = await fetch(url, {
  headers: { 'Accept': 'application/json' }
});


    const json = await res.json();
    if (!json.ok) {
      alert(json.msg || 'No se pudo cargar la guía');
      return;
    }

    const d = json.data;
    current.estado = d.estado;
    current.firma_url = d.firma_url;
    current.foto_url = d.foto_url;

    setText('modalGuiaId', d.id);
    setText('modalGuiaNombre', d.nombre);
    setText('modalGuiaTelefono', d.telefono);
    setText('modalGuiaDireccion', d.direccion);
    setText('modalGuiaDescripcion', d.descripcion);
    setText('modalGuiaPagoEnvio', d.pago_envio);
    setText('modalGuiaCobro', formatMoney(d.cobro));

    // Mostrar botones solo si está finalizado y hay evidencia
    if (isFinalizado(d.estado)) {
      if (d.firma_url) btnVerFirma.classList.remove('hidden');
      if (d.foto_url)  btnVerFoto.classList.remove('hidden');
    }
  }

  // Click en "Ver" desde la lista
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btnVerGuia');
    if (!btn) return;

    const envioId = btn.getAttribute('data-envio-id');
    openGuia();
    try {
      await cargarGuia(envioId);
    } catch (err) {
      console.error(err);
      alert('Error cargando la guía');
    }
  });

  // Cerrar modal guía
  if (btnCerrar) btnCerrar.addEventListener('click', closeGuia);
  overlay?.addEventListener('click', (e) => {
    if (e.target === overlay) closeGuia();
  });

  // Ver firma/foto
  btnVerFirma?.addEventListener('click', () => {
    if (current.firma_url) openPreview('Firma de entrega', current.firma_url);
  });

  btnVerFoto?.addEventListener('click', () => {
    if (current.foto_url) openPreview('Foto de entrega', current.foto_url);
  });

  // Cerrar preview
  btnCerrarPreview?.addEventListener('click', closePreview);
  previewOverlay?.addEventListener('click', (e) => {
    if (e.target === previewOverlay) closePreview();
  });

})();

document.getElementById('btnImprimirGuia')?.addEventListener('click', () => {
  window.print();
});

document.getElementById('btnDescargarPdf')?.addEventListener('click', () => {
  const id = document.getElementById('modalGuiaId')?.textContent?.trim();
  if (!id) return;
  window.open(`pdf_guia.php?id=${encodeURIComponent(id)}`, '_blank');
});
