(() => {
  const search = document.getElementById('manpowerSearch');
  const rows = [...document.querySelectorAll('.manpower-row')];
  if (!search) return;
  search.addEventListener('input', () => {
    const query = search.value.trim().toLocaleLowerCase('id-ID');
    rows.forEach((row) => {
      row.hidden = !row.dataset.search.includes(query);
    });
  });
})();
