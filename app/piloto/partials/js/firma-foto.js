let currentEnvioId = null;
  let signaturePad = null;

  const modalFirma = document.getElementById('modalFirma');
  const modalFoto  = document.getElementById('modalFoto');
  const canvas = document.getElementById('firmaCanvas');

  function openRecibidoFlow(envioId) {
    if (REQUIERE_FIRMA_RECIBIDO) {
      openFirmaModal(envioId);
    } else {
      openFotoModal(envioId);
    }
  }

  function openFirmaModal(envioId){
    if (!modalFirma) return;

    currentEnvioId = envioId;

    const envioInput = document.getElementById('envioIdInput');
    if (envioInput) {
      envioInput.value = envioId;
    }

    modalFirma.classList.remove('hidden');

    //Ajusta tamaño del canvas para evitar que se vea borroso en pantallas retina
    setTimeout(() => {
      if (!canvas) return;

      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);

      signaturePad = new SignaturePad(canvas, { minWidth: 1, maxWidth: 2 });
    },  50);
  }

  // Si no se requiere firma, o después de firmar, se abre el modal de foto
  function openFotoModal(envioId){
    if (!modalFoto) return;

    currentEnvioId = envioId;

    const envioInput = document.getElementById('envioIdInput');
    if (envioInput) {
      envioInput.value = envioId;
    }

    modalFoto.classList.remove('hidden');
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
    if (!REQUIERE_FIRMA_RECIBIDO) {
      if (modalFirma) modalFirma.classList.add('hidden');
      if (modalFoto) modalFoto.classList.remove('hidden');
      return;
    }

    if (!signaturePad || signaturePad.isEmpty()){
      alert("La firma es obligatoria.");
      return;
    }

    const firmaBase64 = signaturePad.toDataURL("image/png");
    const firmaInput = document.getElementById('firmaBase64Input');
    if (firmaInput) {
      firmaInput.value = firmaBase64;
    }
  

    if (modalFirma) modalFirma.classList.add('hidden');
    if (modalFoto) modalFoto.classList.remove('hidden');
  }

  function closeFotoModal(){
    modalFoto.classList.add('hidden');
    // no borramos firma automáticamente
  }

  function backToFirma(){
    if (!REQUIERE_FIRMA_RECIBIDO) return;
    if (!modalFoto || !modalFirma) return;

    modalFoto.classList.add('hidden');
    modalFirma.classList.remove('hidden');
  }

  // Foto -> base64
  const fotoInput = document.getElementById('fotoInput');
  const fotoPreview = document.getElementById('fotoPreview');

  if (fotoInput) {
    fotoInput.addEventListener('change', () => {
      const file = fotoInput.files && fotoInput.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = () => {
        const base64 = reader.result;
        const fotoBase64Input = document.getElementById('fotoBase64Input');
        if (fotoBase64Input) {
          fotoBase64Input.value = base64;
        }

        if (fotoPreview) {
          fotoPreview.src = base64;
          fotoPreview.classList.remove('hidden');
        }
      };
      reader.readAsDataURL(file);
    });
  }

  // Validación antes de enviar
  document.getElementById('formEntregar').addEventListener('submit', (e) => {
  const firma = document.getElementById('firmaBase64Input')?.value || '';
  const foto  = document.getElementById('fotoBase64Input')?.value || '';

    if (!currentEnvioId) {
      e.preventDefault();
      alert("No se encontró el envío.");
      return;
    }

    if (REQUIERE_FIRMA_RECIBIDO) {
      if (!firma || !foto) {
        e.preventDefault();
        alert("Falta firma o foto.");
        return;
      }
    } else {
      if (!foto) {
        e.preventDefault();
        alert("La foto es obligatoria.");
        return;
      
      }
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
