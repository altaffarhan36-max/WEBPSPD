const navButtons = [...document.querySelectorAll('.nav-item')];
const pages = [...document.querySelectorAll('.page-view')];
const pageNames = { overview: 'Ringkasan', manpower: 'Man Power', certification: 'Certification', training: 'Training', calculator: 'Kalkulator MPP' };
const appShell = document.querySelector('#appShell');
function openModule(name) {
  document.querySelector('#loginScreen').hidden = true;
  document.querySelector('#loginScreen').classList.remove('active');
  document.querySelector('#modulesScreen').hidden = true;
  document.querySelector('#modulesScreen').classList.remove('active');
  appShell.hidden = false;
  showPage(name);
}
document.querySelector('#demoLoginForm').addEventListener('submit', event => {
  event.preventDefault();
  const username = document.querySelector('#demoUsername').value.trim() || 'Admin Demo';
  document.querySelector('#moduleUsername').textContent = username;
  appShell.hidden = true;
  document.querySelector('#loginScreen').hidden = true;
  document.querySelector('#loginScreen').classList.remove('active');
  document.querySelector('#modulesScreen').hidden = false;
  document.querySelector('#modulesScreen').classList.add('active');
});
document.querySelector('#togglePassword').addEventListener('click', event => {
  const input = document.querySelector('#demoPassword');
  input.type = input.type === 'password' ? 'text' : 'password';
  event.currentTarget.textContent = input.type === 'password' ? 'Tampilkan' : 'Sembunyikan';
});
document.querySelectorAll('[data-open-page]').forEach(button => button.addEventListener('click', () => openModule(button.dataset.openPage)));
document.querySelector('#returnToModules').addEventListener('click', () => {
  appShell.hidden = true;
  document.querySelector('#modulesScreen').hidden = false;
  document.querySelector('#modulesScreen').classList.add('active');
});
document.querySelector('#backToModules').addEventListener('click', () => {
  appShell.hidden = true;
  document.querySelector('#modulesScreen').hidden = false;
  document.querySelector('#modulesScreen').classList.add('active');
});
function returnToLogin() {
  appShell.hidden = true;
  document.querySelector('#modulesScreen').hidden = true;
  document.querySelector('#modulesScreen').classList.remove('active');
  document.querySelector('#loginScreen').hidden = false;
  document.querySelector('#loginScreen').classList.add('active');
}
document.querySelector('#logoutFromModules').addEventListener('click', returnToLogin);
function showPage(name) {
  pages.forEach(page => {
    const active = page.dataset.page === name;
    page.hidden = !active;
    page.classList.toggle('visible', active);
  });
  document.querySelector('#modulesScreen').hidden = true;
  document.querySelector('#modulesScreen').classList.remove('active');
  document.querySelector('#loginScreen').hidden = true;
  document.querySelector('#loginScreen').classList.remove('active');
  appShell.hidden = false;
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
