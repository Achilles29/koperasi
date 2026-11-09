/* Selects are populated from the same active region hierarchy used by server validation. */
function regionCascade(root, endpoint) {
  const levels = ['province','regency','district','village'];
  const selects = levels.map(level => root.querySelector('[data-region='+level+']'));
  const saved = selects.map(select => select ? select.dataset.selected || '' : '');
  const parentCode = root.querySelector('[data-parent-code]');
  function clearAfter(index) {
    for (let i=index+1;i<selects.length;i++) if (selects[i]) {
      selects[i].innerHTML='<option value="">Pilih '+selects[i].dataset.label+'</option>';
      selects[i].disabled=true;
    }
  }
  async function load(index, id, selected) {
    const child=selects[index+1]; if (!child) return;
    clearAfter(index);
    if (!id) return;
    try {
      const response=await fetch(endpoint+'/'+encodeURIComponent(id),{credentials:'same-origin'});
      if (!response.ok) throw new Error('Wilayah gagal dimuat');
      const rows=await response.json();
      child.disabled=false;
      for (const row of rows) {
        const option=document.createElement('option');option.value=row.id;option.textContent=row.code+' · '+row.name;option.dataset.code=row.code;child.appendChild(option);
      }
      child.value=selected || '';
      if (selected && child.value) await load(index+1,selected,saved[index+2]);
    } catch (_) { child.disabled=false;child.innerHTML='<option value="">Gagal memuat; coba lagi</option>'; }
  }
  selects.forEach((select,index) => {
    if (!select) return;
    select.addEventListener('change', async () => {
      clearAfter(index);
      if (parentCode) {
        const chosen=select.selectedOptions[0];
        if (chosen && chosen.dataset.code) parentCode.value=chosen.dataset.code;
      }
      await load(index,select.value,'');
    });
  });
  if (selects[0] && selects[0].value) load(0,selects[0].value,saved[1]);
}
