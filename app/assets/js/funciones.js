document.getElementById('departamento').addEventListener('change', function () {
  const departamento_id = this.value;
  fetch('../ajax/municipios_por_departamento.php?departamento_id=' + departamento_id)
    .then(res => res.json())
    .then(data => {
      const municipioSelect = document.getElementById('municipio');
      municipioSelect.innerHTML = '<option value="">Seleccione</option>';
      data.forEach(m => {
        municipioSelect.innerHTML += <option value="${m.id}">${m.nombre}</option>;
      });

      document.getElementById('zona').innerHTML = '<option value="">Seleccione</option>';
    });
});

document.getElementById('municipio').addEventListener('change', function () {
  const municipio_id = this.value;
  fetch('../ajax/zonas_por_municipio.php?municipio_id=' + municipio_id)
    .then(res => res.json())
    .then(data => {
      const zonaSelect = document.getElementById('zona');
      zonaSelect.innerHTML = '<option value="">Seleccione</option>';
      data.forEach(z => {
        zonaSelect.innerHTML += <option value="${z.id}">Zona ${z.numero}</option>;
      });
    });
});