(function () {
  const container = document.getElementById('itemsContainer');
  const tpl = document.getElementById('itemTemplate');
  const btnAdd = document.getElementById('btnAddItem');

  const itemsBadge = document.getElementById('itemsBadge');
  const totalWeightEl = document.getElementById('totalWeight');
  const totalFeeEl = document.getElementById('totalFee');
  const totalAmountEl = document.getElementById('totalAmount');

  // Radios de "¿Quién paga el envío?"
const payerRadios = document.querySelectorAll('input[name="pago_envio"]');

/**
 * Retorna quién paga el envío:
 * - "sender" => Remitente
 * - "recipient" => Destinatario
 * Default: "sender"
 */
function getPayer() {
  const checked = document.querySelector('input[name="pago_envio"]:checked');
  return checked ? checked.value : 'cleinte';
}

// Recalcula totales cuando el usuario cambia quién paga el envío
payerRadios.forEach(r => r.addEventListener('change', recalcAll));

  function money(n) {
    n = Number(n || 0);
    return n.toFixed(2);
  }

  function renumberItems() {
    const cards = container.querySelectorAll('.itemCard');
    cards.forEach((card, idx) => {
      card.querySelector('.itemTitle').textContent = `Item #${idx + 1}`;
    });
    itemsBadge.textContent = `${cards.length} Items`;
  }

  function syncHiddenNames(card) {
    const select = card.querySelector('.pkgSelect');
    const hiddenPkg = card.querySelector('.hiddenPkgName');
    const hiddenCobro = card.querySelector('.hiddenCobroName');

    const pkgId = select.value;

    // Si no hay paquete seleccionado, no mandamos nada
    if (!pkgId) {
      hiddenPkg.removeAttribute('name');
      hiddenCobro.removeAttribute('name');
      return;
    }

    // Mantener EXACTAMENTE la estructura anterior:
    hiddenPkg.name = `paquete_ids[${pkgId}]`;     // qty
    hiddenCobro.name = `monto_cobros[${pkgId}]`;  // cobro
  }

  function calcCard(card) {
    const select = card.querySelector('.pkgSelect');
    const qty = Number(card.querySelector('.qtyInput').value || 0);
    const cobro = Number(card.querySelector('.cobroInput').value || 0);

    const opt = select.options[select.selectedIndex];
    const tarifa = opt ? Number(opt.dataset.tarifa || 0) : 0;

    // peso: si el usuario no lo toca, lo llenamos del paquete seleccionado
    const pesoInput = card.querySelector('.pesoInput');
    const pesoOpt = opt ? Number(opt.dataset.peso || 0) : 0;
    const peso = Number(pesoInput.value || 0);

    // Si el peso está en 0 y hay paquete, lo setea automático
    if (peso === 0 && pesoOpt > 0) pesoInput.value = pesoOpt;

    const subtotal = (qty * tarifa) + cobro;
    card.querySelector('.itemSubtotal').textContent = money(subtotal);

    // Los hidden inputs deben llevar el valor correcto:
    const hiddenPkg = card.querySelector('.hiddenPkgName');
    const hiddenCobro = card.querySelector('.hiddenCobroName');
    hiddenPkg.value = qty;
    hiddenCobro.value = cobro;

    syncHiddenNames(card);

    return {
      pkgId: select.value,
      qty,
      tarifa,
      cobro,
      peso: Number(pesoInput.value || 0),
      subtotal
    };
  }

  function recalcAll() {
    const cards = [...container.querySelectorAll('.itemCard')];

    // Normalización por ID (si repiten paquete en 2 items)
    const agg = {}; // pkgId -> {qty, cobro, tarifa, peso, subtotal}
    cards.forEach(card => {
      const r = calcCard(card);
      if (!r.pkgId) return;
      if (!agg[r.pkgId]) agg[r.pkgId] = {qty:0, cobro:0, tarifa:r.tarifa, peso:0, subtotal:0};
      agg[r.pkgId].qty += r.qty;
      agg[r.pkgId].cobro += r.cobro;
      agg[r.pkgId].peso += (r.peso * r.qty); // peso total por qty
      agg[r.pkgId].subtotal += r.subtotal;
      agg[r.pkgId].tarifa = r.tarifa; // por si cambia
    });

    // Forzar que SOLO 1 hidden por ID quede activo: desactivamos y reactivamos por el primero encontrado
    // (esto evita enviar paquete_ids[5] dos veces)
    const firstById = {};
    cards.forEach(card => {
      const select = card.querySelector('.pkgSelect');
      const pkgId = select.value;
      const hiddenPkg = card.querySelector('.hiddenPkgName');
      const hiddenCobro = card.querySelector('.hiddenCobroName');

      if (!pkgId) return;

      if (!firstById[pkgId]) {
        firstById[pkgId] = { hiddenPkg, hiddenCobro };
        // set values agregados
        hiddenPkg.value = agg[pkgId].qty;
        hiddenCobro.value = agg[pkgId].cobro;
        syncHiddenNames(card);
      } else {
        // desactivar duplicados
        hiddenPkg.removeAttribute('name');
        hiddenCobro.removeAttribute('name');
      }
    });

    let totalAmount = 0;
    let totalFee = 0;
    let totalWeight = 0;

const payer = getPayer(); // "sender" o "recipient"

Object.values(agg).forEach(x => {
  // x = {qty, cobro, tarifa, peso, subtotal}

  // Shipping (tarifa) SOLO si paga el destinatario
  const shipping = (payer === 'destinatario') ? (x.qty * x.tarifa) : 0;

  // Extras (cobros contra entrega) siempre se suman (como antes)
  const extras = x.cobro;

  totalFee += shipping;
  totalAmount += (shipping + extras);

  // En agg guardamos peso total ya acumulado (peso * qty)
  totalWeight += x.peso;
});

    totalAmountEl.textContent = money(totalAmount);
    totalFeeEl.textContent = money(totalFee);
    totalWeightEl.textContent = money(totalWeight);

    renumberItems();
  }

  function addItem(prefill = {}) {
    const node = tpl.content.cloneNode(true);
    const card = node.querySelector('.itemCard');

    // prefill
    const select = card.querySelector('.pkgSelect');
    const qty = card.querySelector('.qtyInput');
    const cobro = card.querySelector('.cobroInput');
    const peso = card.querySelector('.pesoInput');

    if (prefill.pkgId) select.value = String(prefill.pkgId);
    if (prefill.qty != null) qty.value = prefill.qty;
    if (prefill.cobro != null) cobro.value = prefill.cobro;
    if (prefill.peso != null) peso.value = prefill.peso;

    // events
    card.addEventListener('input', (e) => {
      if (e.target.matches('.qtyInput,.cobroInput,.pesoInput')) recalcAll();
    });
    select.addEventListener('change', recalcAll);

    card.querySelector('.btnDelete').addEventListener('click', () => {
      card.remove();
      recalcAll();
    });

    container.appendChild(node);
    recalcAll();
  }

  btnAdd.addEventListener('click', () => addItem({ qty: 1, cobro: 0, peso: 0 }));

  // Arranca con 1 item vacío
  addItem({ qty: 1, cobro: 0, peso: 0 });

  // Validación: no permitir enviar si no hay ningún paquete con qty > 0
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