(() => {
  const menuTabs = [...document.querySelectorAll('.menu-tab')];
  const menuViews = [...document.querySelectorAll('[data-menu-view]')];
  menuTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.menuTarget;
      menuTabs.forEach((item) => item.classList.toggle('active', item === tab));
      menuViews.forEach((view) => {
        const active = view.dataset.menuView === target;
        view.classList.toggle('active', active);
        view.hidden = !active;
      });
    });
  });

  const search = document.getElementById('mechanicSearch');
  const filter = document.getElementById('resultFilter');
  const rows = [...document.querySelectorAll('.mechanic-row')];
  const count = document.getElementById('resultCount');
  const empty = document.getElementById('emptyState');

  function applyFilters() {
    if (!search || !filter) return;
    const query = search.value.trim().toLocaleLowerCase('id-ID');
    const result = filter.value;
    let visible = 0;

    rows.forEach((row) => {
      const matches = row.dataset.name.includes(query) && (!result || row.dataset.result === result);
      row.hidden = !matches;
      const detail = row.nextElementSibling;
      if (!matches) {
        detail.classList.remove('detail-open');
        const button = row.querySelector('.detail-toggle');
        button.setAttribute('aria-expanded', 'false');
        button.textContent = 'Detail';
      }
      detail.hidden = !matches || !detail.classList.contains('detail-open');
      visible += Number(matches);
    });

    count.textContent = `${visible} mekanik ditampilkan`;
    empty.hidden = visible > 0;
  }

  search?.addEventListener('input', applyFilters);
  filter?.addEventListener('change', applyFilters);
  applyFilters();

  document.querySelectorAll('.detail-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const row = button.closest('.mechanic-row');
      const detail = row.nextElementSibling;
      const open = !detail.classList.contains('detail-open');
      detail.classList.toggle('detail-open', open);
      detail.hidden = !open;
      button.setAttribute('aria-expanded', String(open));
      button.textContent = open ? 'Tutup' : 'Detail';
    });
  });
})();
