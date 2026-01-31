window.__ENVIO_PKGS_LOADED = true;
console.log("✅ envio-paquetes.js cargó");

(function () {
  const container = document.getElementById('itemsContainer');
  const tpl = document.getElementById('itemTemplate');
  const btnAdd = document.getElementById('btnAddItem');

  // ✅ Si el componente no existe en esta vista, salir sin romper nada
  if (!container || !tpl || !btnAdd) {
    console.warn('envio-paquetes.js: faltan itemsContainer / itemTemplate / btnAddItem');
    return;
  }

  const itemsBadge = document.getElementById('itemsBadge');
  const totalWeightEl = document.getElementById('totalWeight');
  const totalFeeEl = document.getElementById('totalFee');
  const totalAmountEl = document.getElementById('totalAmount');

  const payerRadios = document.querySelectorAll('input[name="pago_envio"]');

function getPayer() {
  const checked = document.querySelector('input[name="pago_envio"]:checked');
  return checked ? checked.value : 'cliente';
}


  payerRadios.forEach(r => r.addEventListener('change', recalcAll));

  function money(n) {
    return Number(n || 0).toFixed(2);
  }

  function renumberItems() {
    const cards = container.querySelectorAll('.itemCard');
    cards.forEach((card, idx) => {
      const t = card.querySelector('.itemTitle');
      if (t) t.textContent = `Item #${idx + 1}`;
    });
    if (itemsBadge) itemsBadge.textContent = `${cards.length} Items`;
  }

  function syncHiddenNames(card) {
    const select = card.querySelector('.pkgSelect');
    const hiddenPkg = card.querySelector('.hiddenPkgName');
    const hiddenCobro = card.querySelector('.hiddenCobroName');

    const pkgId = select ? select.value : '';

    if (!pkgId) {
      hiddenPkg?.removeAttribute('name');
      hiddenCobro?.removeAttribute('name');
      return;
    }

    hiddenPkg.name = paquete_ids[pkgId];
    hiddenCobro.name = monto_cobros[pkgId];
  }

  function calcCard(card) {
    const select = card.querySelector('.pkgSelect');
    const qty = Number(card.querySelector('.qtyInput')?.value || 0);
    const cobro = Number(card.querySelector('.cobroInput')?.value || 0);

    const opt = select ? select.options[select.selectedIndex] : null;
    const tarifa = opt ? Number(opt.dataset.tarifa || 0) : 0;

    const pesoInput = card.querySelector('.pesoInput');
    const pesoOpt = opt ? Number(opt.dataset.peso || 0) : 0;
    const pesoActual = Number(pesoInput?.value || 0);

    if (pesoInput && pesoActual === 0 && pesoOpt > 0) pesoInput.value = pesoOpt;

    const peso = Number(pesoInput?.value || 0);

    // ✅ regla: shipping solo si paga destinatario
    const payer = getPayer();
    const shipping = (payer === 'destinatario') ? (qty * tarifa) : 0;

    const subtotal = shipping + cobro;

    const subtotalEl = card.querySelector('.itemSubtotal');
    if (subtotalEl) subtotalEl.textContent = money(subtotal);

    const hiddenPkg = card.querySelector('.hiddenPkgName');
    const hiddenCobro = card.querySelector('.hiddenCobroName');
    if (hiddenPkg) hiddenPkg.value = qty;
    if (hiddenCobro) hiddenCobro.value = cobro;

    syncHiddenNames(card);

    return { pkgId: select ? select.value : '', qty, tarifa, cobro, peso, shipping };
  }

  function recalcAll() {
  const cards = [...container.querySelectorAll('.itemCard')];
  const payer = getPayer(); // "cliente" o "destinatario" (según tu HTML)

  // 1) Recolectar data por card y pintar subtotal por card (respetando payer)
  const agg = {}; // pkgId -> {qty, cobro, tarifa, pesoTotal}
  cards.forEach(card => {
    const select = card.querySelector('.pkgSelect');
    const pkgId = select.value;

    const qty = Number(card.querySelector('.qtyInput').value || 0);
    const cobro = Number(card.querySelector('.cobroInput').value || 0);

    const opt = select.options[select.selectedIndex];
    const tarifa = opt ? Number(opt.dataset.tarifa || 0) : 0;

    // peso auto
    const pesoInput = card.querySelector('.pesoInput');
    const pesoOpt = opt ? Number(opt.dataset.peso || 0) : 0;
    const pesoVal = Number(pesoInput.value || 0);
    if (pesoVal === 0 && pesoOpt > 0) pesoInput.value = pesoOpt;

    const peso = Number(pesoInput.value || 0);

    // ✅ subtotal visual por item: cobro + (tarifa*qty solo si paga destinatario)
    const shippingItem = (payer === 'destinatario') ? (qty * tarifa) : 0;
    const subtotalItem = cobro + shippingItem;

    card.querySelector('.itemSubtotal').textContent = money(subtotalItem);

    // hidden values (siempre)
    const hiddenPkg = card.querySelector('.hiddenPkgName');
    const hiddenCobro = card.querySelector('.hiddenCobroName');
    hiddenPkg.value = qty;
    hiddenCobro.value = cobro;

    // nombres de hidden (solo si hay pkg)
    if (!pkgId) {
      hiddenPkg.removeAttribute('name');
      hiddenCobro.removeAttribute('name');
      return;
    } else {
      hiddenPkg.name = `paquete_ids[${pkgId}]`;
      hiddenCobro.name = `monto_cobros[${pkgId}]`;
    }

    // agregación por paquete (para backend)
    if (!agg[pkgId]) agg[pkgId] = { qty: 0, cobro: 0, tarifa: tarifa, pesoTotal: 0 };
    agg[pkgId].qty += qty;
    agg[pkgId].cobro += cobro;
    agg[pkgId].tarifa = tarifa; // por si cambia
    agg[pkgId].pesoTotal += (peso * qty);
  });

  // 2) Dejar solo 1 hidden por paquete activo (evita duplicados al POST)
  const firstById = {};
  cards.forEach(card => {
    const pkgId = card.querySelector('.pkgSelect').value;
    if (!pkgId) return;

    const hiddenPkg = card.querySelector('.hiddenPkgName');
    const hiddenCobro = card.querySelector('.hiddenCobroName');

    if (!firstById[pkgId]) {
      firstById[pkgId] = true;

      // ✅ set agregados SOLO en el primer card de ese paquete
      hiddenPkg.value = agg[pkgId].qty;
      hiddenCobro.value = agg[pkgId].cobro;

      hiddenPkg.name = `paquete_ids[${pkgId}]`;
      hiddenCobro.name = `monto_cobros[${pkgId}]`;
    } else {
      // desactivar duplicados
      hiddenPkg.removeAttribute('name');
      hiddenCobro.removeAttribute('name');
    }
  });

  // 3) Totales
  let totalFee = 0;
  let totalAmount = 0;
  let totalWeight = 0;

  Object.values(agg).forEach(x => {
    const shipping = (payer === 'destinatario') ? (x.qty * x.tarifa) : 0;
    const extras = x.cobro;

    totalFee += shipping;
    totalAmount += (shipping + extras);
    totalWeight += x.pesoTotal;
  });

  totalAmountEl.textContent = money(totalAmount);
  totalFeeEl.textContent = money(totalFee);
  totalWeightEl.textContent = money(totalWeight);

  renumberItems();
}


  function addItem(prefill = {}) {
    const node = tpl.content.cloneNode(true);
    const card = node.querySelector('.itemCard');

    const select = card.querySelector('.pkgSelect');
    const qty = card.querySelector('.qtyInput');
    const cobro = card.querySelector('.cobroInput');
    const peso = card.querySelector('.pesoInput');

    if (prefill.pkgId && select) select.value = String(prefill.pkgId);
    if (prefill.qty != null && qty) qty.value = prefill.qty;
    if (prefill.cobro != null && cobro) cobro.value = prefill.cobro;
    if (prefill.peso != null && peso) peso.value = prefill.peso;

    card.addEventListener('input', (e) => {
      if (e.target.matches('.qtyInput,.cobroInput,.pesoInput')) recalcAll();
    });

    if (select) select.addEventListener('change', recalcAll);

    const btnDelete = card.querySelector('.btnDelete');
    if (btnDelete) {
      btnDelete.addEventListener('click', () => {
        card.remove();
        recalcAll();
      });
    }

    container.appendChild(node);
    recalcAll();
  }

  btnAdd.addEventListener('click', () => addItem({ qty: 1, cobro: 0, peso: 0 }));

  addItem({ qty: 1, cobro: 0, peso: 0 });

  const form = container.closest('form');
  if (form) {
    form.addEventListener('submit', (e) => {
      recalcAll();
      const hasAny = [...form.querySelectorAll('input[name^="paquete_ids["]')]
        .some(inp => Number(inp.value || 0) > 0);

      if (!hasAny) {
        e.preventDefault();
        alert('Seleccioná al menos un paquete con cantidad mayor a 0.');
      }
    });
  }
})();

//modal guia 
document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('modalGuiaOverlay');
  const btnClose = document.getElementById('btnCerrarGuia');

  if (btnClose && overlay) {
    btnClose.addEventListener('click', () => overlay.classList.add('hidden'));
  }

  // cerrar al hacer click en el fondo oscuro
  if (overlay) {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) overlay.classList.add('hidden');
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('modalGuiaOverlay');
  const panel   = document.getElementById('modalGuiaPanel');
  const btnX    = document.getElementById('btnCerrarGuia');

  function closeGuia() {
    if (overlay) overlay.classList.add('hidden');
  }

  if (btnX) btnX.addEventListener('click', closeGuia);

  // Cerrar si se clickea fuera del panel
  if (overlay && panel) {
    overlay.addEventListener('click', (e) => {
      if (!panel.contains(e.target)) closeGuia();
    });
  }

  // Bonus: cerrar con ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay && !overlay.classList.contains('hidden')) {
      closeGuia();
    }
  });
});


//Imprimir guia
/**
 * Construye el texto de la guía en formato "ticket" (monospace).
 * NO usa innerHTML para evitar links/estilos raros.
 */
function buildGuiaText() {
  const id        = (document.getElementById('modalGuiaId')?.textContent || '').trim();
  const nombre    = (document.getElementById('modalGuiaNombre')?.textContent || '').trim();
  const telefono  = (document.getElementById('modalGuiaTelefono')?.textContent || '').trim();
  const direccion = (document.getElementById('modalGuiaDireccion')?.textContent || '').trim();
  const desc      = (document.getElementById('modalGuiaDescripcion')?.textContent || '').trim();
  const pago      = (document.getElementById('modalGuiaPagoEnvio')?.textContent || '').trim();
  const cobro     = (document.getElementById('modalGuiaCobro')?.textContent || '').trim();

  return `
----------------------------------------
          📨 GUÍA DE ENTREGA - ENVÍO
----------------------------------------
No. de Guía: ${id}
Nombre: ${nombre}
Teléfono: ${telefono}
Dirección: ${direccion}
Descripción: ${desc}
Forma de pago del envío: ${pago}
Cobro total al cliente: ${cobro}

📦 ¡Gracias por usar nuestro servicio!
----------------------------------------
`.trim();
}

/**
 * Escapa texto para meterlo dentro de <pre> sin que el navegador lo interprete como HTML.
 */
function escapeHtml(s) {
  return String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;');
}

/**
 * Imprime SOLO el ticket (texto plano).
 * No imprime el modal bonito, ni botones, ni estilos del dashboard.
 */
function imprimirGuiaTicket() {
  const text = buildGuiaText();

  const win = window.open('', '_blank');
  if (!win) return;

  win.document.open();
  win.document.write(`
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Guía de Envío</title>
  <style>
    @page { size: A4 portrait; margin: 10mm; }
    body { margin: 0; padding: 0; }
    pre  { font-family: monospace; font-size: 11px; line-height: 1.4; white-space: pre-wrap; }
    a { display:none !important; } /* por si el navegador intenta auto-link */
  </style>
</head>
<body>
  <pre>${escapeHtml(text)}</pre>
</body>
</html>
  `);
  win.document.close();

  win.focus();
  win.print();
  win.close();
}

/**
 * Conecta botones del modal (Imprimir / Descargar PDF).
 * OJO: estos IDs deben existir en tu modal_guia.php:
 * - btnImprimirGuia
 * - btnDescargarPdf
 */
document.addEventListener('DOMContentLoaded', () => {
  const btnPrint = document.getElementById('btnImprimirGuia');
  if (btnPrint) btnPrint.addEventListener('click', imprimirGuiaTicket);

  const btnPdf = document.getElementById('btnDescargarPdf');
  if (btnPdf) {
    btnPdf.addEventListener('click', () => {
      // El envio_id lo tenemos en el span modalGuiaId
      const envioId = (document.getElementById('modalGuiaId')?.textContent || '').trim();
      if (!envioId) return alert('No se encontró el No. de guía.');

      // Descarga PDF REAL desde endpoint (TCPDF)
      // BASE_URL debe existir en PHP (ya lo definiste)
      window.open(`${window.BASE_URL || ''}/piloto/guia_pdf.php?id=${encodeURIComponent(envioId)}`, '_blank');
    });
  }
});

