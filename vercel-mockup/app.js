const navButtons = [...document.querySelectorAll('.nav-item')];
const pages = [...document.querySelectorAll('.page-view')];
const pageNames = { overview: 'Ringkasan', manpower: 'Man Power', certification: 'Certification', training: 'Training', calculator: 'Kalkulator MPP' };
function showPage(name) {
  pages.forEach(page => page.classList.toggle('visible', page.dataset.page === name));
  navButtons.forEach(button => button.classList.toggle('active', button.dataset.view === name));
  document.querySelector('#breadcrumbCurrent').textContent = pageNames[name] || 'Ringkasan';
  document.querySelector('#sidebar').classList.remove('open');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
navButtons.forEach(button => button.addEventListener('click', () => showPage(button.dataset.view)));
document.querySelectorAll('[data-goto]').forEach(button => button.addEventListener('click', () => showPage(button.dataset.goto)));
document.querySelector('#menuToggle').addEventListener('click', () => document.querySelector('#sidebar').classList.toggle('open'));
document.querySelector('#dismissBanner').addEventListener('click', () => document.querySelector('.demo-banner').remove());

let toastTimer;
function toast(message) {
  const element = document.querySelector('#toast');
  element.textContent = message;
  element.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => element.classList.remove('show'), 2600);
}
function filterTable(inputId, tableId) {
  const input = document.querySelector(`#${inputId}`);
  const table = document.querySelector(`#${tableId}`);
  if (!input || !table) return;
  input.addEventListener('input', () => {
    const query = input.value.toLocaleLowerCase('id');
    table.querySelectorAll('tbody tr').forEach(row => row.hidden = !row.textContent.toLocaleLowerCase('id').includes(query));
  });
}
filterTable('manpowerSearch', 'manpowerTable');
filterTable('certSearch', 'certTable');

document.querySelector('#calculateButton').addEventListener('click', () => {
  const units = Number(document.querySelector('#unitCount').value);
  const hours = Number(document.querySelector('#hoursDay').value);
  const days = Number(document.querySelector('#workDays').value);
  const workload = Number(document.querySelector('#workload').value);
  if (![units, hours, days, workload].every(Number.isFinite) || units < 0 || hours <= 0 || days <= 0 || workload <= 0) {
    toast('Periksa kembali nilai parameter simulasi.');
    return;
  }
  const productiveHours = hours * days;
  const totalWorkload = units * workload;
  const people = productiveHours ? Math.ceil(totalWorkload / productiveHours) : 0;
  document.querySelector('#mppResult').innerHTML = `${people} <small>orang</small>`;
  document.querySelector('#mppDescription').textContent = `${units} unit ${document.querySelector('#unitType').value} membutuhkan estimasi ${people} orang.`;
  document.querySelector('#totalWorkload').textContent = `${totalWorkload.toLocaleString('id-ID')} jam/tahun`;
  document.querySelector('#productiveHours').textContent = `${productiveHours.toLocaleString('id-ID')} jam/tahun`;
});
document.querySelector('#exportButton').addEventListener('click', () => toast('Unduhan tidak tersedia pada mockup statis.'));
document.querySelector('#printButton').addEventListener('click', () => window.print());
document.querySelector('#trainingToast').addEventListener('click', () => toast('Fitur ini hanya contoh pada mockup.'));
