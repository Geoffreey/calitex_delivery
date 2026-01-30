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
    // ✅ tus values reales
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

    const agg = {};
    cards.forEach(card => {
      const r = calcCard(card);
      if (!r.pkgId) return;

      if (!agg[r.pkgId]) {
        agg[r.pkgId] = { qty: 0, cobro: 0, tarifa: r.tarifa, pesoTotal: 0, shippingTotal: 0 };
      }

      agg[r.pkgId].qty += r.qty;
      agg[r.pkgId].cobro += r.cobro;
      agg[r.pkgId].tarifa = r.tarifa;
      agg[r.pkgId].pesoTotal += (r.peso * r.qty);
      agg[r.pkgId].shippingTotal += r.shipping;
    });

    // Normalizar hidden inputs (solo 1 por paquete)
    const firstById = {};
    cards.forEach(card => {
      const select = card.querySelector('.pkgSelect');
      const pkgId = select ? select.value : '';
      const hiddenPkg = card.querySelector('.hiddenPkgName');
      const hiddenCobro = card.querySelector('.hiddenCobroName');

      if (!pkgId) return;

      if (!firstById[pkgId]) {
        firstById[pkgId] = true;
        if (hiddenPkg) hiddenPkg.value = agg[pkgId].qty;
        if (hiddenCobro) hiddenCobro.value = agg[pkgId].cobro;
        syncHiddenNames(card);
      } else {
        hiddenPkg?.removeAttribute('name');
        hiddenCobro?.removeAttribute('name');
      }
    });

    let totalAmount = 0;
    let totalFee = 0;
    let totalWeight = 0;

    Object.values(agg).forEach(x => {
      totalFee += x.shippingTotal;
      totalAmount += x.shippingTotal + x.cobro;
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