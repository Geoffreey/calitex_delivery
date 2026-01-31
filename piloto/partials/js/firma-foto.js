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