// Static page previews: every value is local mock data; no API or database is used.
const manpowerRecords = [
  ['1','SWI-1001','Budi Santoso','OPERASIONAL KONTRAK','MEKANIK','JM2','1B','S','SURABAYA','BALIKPAPAN','M','2025-10-01','ON - SITE','K1'],
  ['2','SWI-1002','Rizky Ananda','OPERASIONAL KONTRAK','MEKANIK','MP1','1B','S','SURABAYA','BALIKPAPAN','M','2025-10-01','ON - SITE','K1'],
  ['3','SWI-1003','Dewi Puspita','OPERASIONAL KONTRAK','MEKANIK','JM1','1A','S','SURABAYA','BANJARMASIN','W','2025-10-01','ON - SITE','K1'],
  ['4','SWI-1004','Andi Firmansyah','OPERASIONAL KONTRAK','MEKANIK','JM1','1A','S','JOGJAKARTA','JAKARTA','S','2025-10-01','ON - SITE','K1'],
  ['5','SWI-1005','Nadia Putri','OPERASIONAL KONTRAK','MEKANIK','PM','1A','S','JOGJAKARTA','JAKARTA','M','2025-10-01','ON - SITE','K1'],
  ['6','SWI-1006','Fajar Pratama','OPERASIONAL KONTRAK','MEKANIK','MP1','1B','S','SURABAYA','MAKASSAR','M','2025-10-21','ON - SITE','K1'],
  ['7','SWI-1007','Dimas Saputra','OPERASIONAL KONTRAK','MEKANIK','PM','1A','S','SURABAYA','PALEMBANG','W','2025-11-03','PENDING','K1'],
  ['8','SWI-1008','Siti Rahma','OPERASIONAL KONTRAK','MEKANIK','PM','1A','S','BALIKPAPAN','BALIKPAPAN','M','2025-11-03','ON - SITE','K1']
];
const locations = [['BALIKPAPAN',18,17,1],['BANJARMASIN',12,10,2],['JAKARTA',8,7,1],['MAKASSAR',9,7,2],['PALEMBANG',13,7,2]];

function manpowerTableMarkup() {
  return `<div class="table-scroll local-all-table"><table><thead><tr><th>No.</th><th>NIP/NRP</th><th>Nama</th><th>Divisi</th><th>Jabatan</th><th>Grade JIS</th><th>Grade SWI</th><th>Perusahaan</th><th>POH</th><th>Penempatan</th><th>Alokasi</th><th>Tanggal on-board</th><th>Status</th><th>Kontrak</th></tr></thead><tbody id="allManpowerRows">${manpowerRecords.map(row => `<tr data-search="${row.join(' ').toLowerCase()}">${row.map((cell,index) => `<td>${index === 12 ? `<span class="local-status ${cell === 'PENDING' ? 'pending' : 'onsite'}">${cell}</span>` : cell}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`;
}
function renderManpower() {
  const page = document.querySelector('[data-page="manpower"]');
  page.innerHTML = `<div class="local-intro manpower-intro"><p class="local-eyebrow">DATA TENAGA KERJA</p><h1>Monitoring Man Power</h1><p>Modul data dasar terpisah dari dashboard sertifikasi.</p></div><div class="local-tabs" role="tablist"><button class="local-tab active" data-manpower-tab="monitoring">Monitoring Man Power</button><button class="local-tab" data-manpower-tab="all">All</button></div><div class="local-mock-note">Data demonstrasi · Nama dan angka di halaman ini hanya contoh.</div><div class="manpower-view active" data-manpower-view="monitoring"><div class="local-stats"><article class="local-metric total"><span>Total tenaga kerja</span><strong>60</strong><small>Seluruh data karyawan</small></article><article class="local-metric onsite"><span>On-site</span><strong>48</strong><small>Sedang aktif di lokasi</small></article><article class="local-metric pending"><span>Pending</span><strong>8</strong><small>Menunggu penempatan</small></article><article class="local-metric location"><span>Penempatan</span><strong>5</strong><small>Lokasi kerja terdata</small></article></div><section class="local-panel"><div class="local-panel-heading"><div><h2>Rekap berdasarkan penempatan</h2><p>Ringkasan data manpower contoh.</p></div></div><div class="table-scroll"><table><thead><tr><th>Penempatan</th><th>Total</th><th>On-site</th><th>Pending</th></tr></thead><tbody>${locations.map(row => `<tr><td class="local-strong">${row[0]}</td><td><span class="local-count">${row[1]}</span></td><td><span class="local-status onsite">${row[2]} On-site</span></td><td><span class="local-status pending">${row[3]} Pending</span></td></tr>`).join('')}</tbody></table></div></section></div><div class="manpower-view" data-manpower-view="all"><section class="local-panel"><div class="local-panel-heading"><div><h2>All manpower</h2><p>${manpowerRecords.length} dari 60 data contoh</p></div><label class="local-search">⌕ <input id="localManpowerSearch" type="search" placeholder="Cari nama, NIP, atau penempatan"></label></div>${manpowerTableMarkup()}</section></div>`;
  page.querySelectorAll('[data-manpower-tab]').forEach(button => button.addEventListener('click', () => {
    page.querySelectorAll('[data-manpower-tab]').forEach(tab => tab.classList.toggle('active', tab === button));
    page.querySelectorAll('[data-manpower-view]').forEach(view => view.classList.toggle('active', view.dataset.manpowerView === button.dataset.manpowerTab));
  }));
  page.querySelector('#localManpowerSearch').addEventListener('input', event => {
    const query = event.target.value.toLocaleLowerCase('id');
    page.querySelectorAll('#allManpowerRows tr').forEach(row => row.hidden = !row.dataset.search.includes(query));
  });
}

const certifications = [
  {name:'Teknisi 01',site:'ADRO',join:'12 Feb 2024',grade:'1A',tenure:'1,8 th',atr:'99,2%',training:'88',stpr:'3,20',result:'LULUS',criteria:['Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 02',site:'ADRO',join:'22 Nov 2023',grade:'1B',tenure:'2,1 th',atr:'98,7%',training:'78',stpr:'2,85',result:'LULUS',criteria:['Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 03',site:'ADRO',join:'17 Mei 2023',grade:'1C',tenure:'2,6 th',atr:'97,9%',training:'72',stpr:'1,80',result:'TIDAK LULUS',criteria:['Lulus','Tidak Lulus','Lulus','Lulus','Tidak Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 04',site:'ADRO',join:'15 Okt 2023',grade:'1D',tenure:'3,0 th',atr:'96,8%',training:'70',stpr:'2,10',result:'TIDAK LULUS',criteria:['Lulus','Tidak Lulus','Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 05',site:'ADRO',join:'03 Mei 2024',grade:'1E',tenure:'2,4 th',atr:'98,5%',training:'82',stpr:'3,05',result:'LULUS',criteria:['Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 06',site:'AMM',join:'20 Apr 2025',grade:'F',tenure:'1,5 th',atr:'99,5%',training:'91',stpr:'3,55',result:'LULUS',criteria:['Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 07',site:'AMM',join:'15 Nov 2023',grade:'2A',tenure:'2,9 th',atr:'98,5%',training:'76',stpr:'2,75',result:'LULUS',criteria:['Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 08',site:'AMM',join:'01 Jul 2023',grade:'2B',tenure:'3,2 th',atr:'95,9%',training:'68',stpr:'1,65',result:'TIDAK LULUS',criteria:['Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Lulus']},
  {name:'Teknisi 09',site:'AMM',join:'15 Okt 2024',grade:'2C',tenure:'2,0 th',atr:'98,9%',training:'80',stpr:'2,90',result:'LULUS',criteria:['Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus']},
  {name:'Teknisi 10',site:'AMM',join:'02 Jan 2024',grade:'2D',tenure:'2,7 th',atr:'98,5%',training:'75',stpr:'—',result:'BELUM DINILAI',criteria:[]}
];
const criteriaNames = ['Masa golongan','ATR','Service report','STPR','Training','Improvement','Pengalaman kerja','Surat peringatan','Kompetensi TLU'];
function renderCertification() {
  const page = document.querySelector('[data-page="certification"]');
  page.innerHTML = `<div class="local-intro certification-intro"><p class="local-eyebrow">PT STAR WAGEN INDONESIA · PENILAIAN MEKANIK</p><h1>Dashboard Master</h1><p>Pantau rekap sertifikasi mekanik dan hasil STPR.</p><small>Data masa golongan dihitung per 15 Okt 2026</small></div><div class="local-stats cert-stats"><article class="local-metric total"><span>Total mekanik</span><strong>10</strong><small>Data contoh</small></article><article class="local-metric onsite"><span>Lulus TLU</span><strong>7</strong><small>Hasil sertifikasi</small></article><article class="local-metric failed"><span>Tidak lulus TLU</span><strong>2</strong><small>Perlu tindak lanjut</small></article><article class="local-metric pending"><span>Belum dinilai TLU</span><strong>1</strong><small>Menunggu penilaian</small></article></div><section class="local-panel certification-table"><div class="local-panel-heading cert-heading"><div><h2>Rekap data sertifikasi TLU</h2><p>Hasil penilaian perilaku terbaru ditampilkan pada kolom STPR.</p></div><div class="local-filters"><input id="localCertSearch" type="search" placeholder="Cari nama mekanik"><select id="localCertFilter"><option value="">Semua hasil TLU</option><option value="LULUS">Lulus TLU</option><option value="TIDAK LULUS">Tidak lulus TLU</option><option value="BELUM DINILAI">Belum dinilai TLU</option></select></div></div><p class="local-source-note" id="localCertCount">10 data mekanik contoh</p><div class="table-scroll"><table><thead><tr><th>Nama mekanik</th><th>Site / GL</th><th>Tanggal bergabung</th><th>Golongan</th><th>Masa golongan</th><th>ATR</th><th>Training</th><th>STPR</th><th>Hasil TLU</th><th>Detail</th></tr></thead><tbody>${certifications.map((row,index) => `<tr class="cert-main-row" data-name="${row.name.toLocaleLowerCase('id')}" data-result="${row.result}"><td class="local-strong">${row.name}</td><td>${row.site}</td><td>${row.join}</td><td>${row.grade}</td><td>${row.tenure}</td><td>${row.atr}</td><td>${row.training}</td><td>${row.stpr === '—' ? '<span class="local-status pending">BELUM DINILAI</span>' : `${row.stpr} poin`}</td><td><span class="local-status ${row.result === 'LULUS' ? 'onsite' : row.result === 'TIDAK LULUS' ? 'failed' : 'pending'}">${row.result}</span></td><td><button class="local-detail-button" data-cert-detail="${index}" aria-expanded="false">Detail</button></td></tr><tr class="cert-detail-row" data-detail-row="${index}" hidden><td colspan="10"><div class="criterion-cards">${criteriaNames.map((criterion,i) => `<article><span>${criterion}</span><strong>${row.criteria[i] || '—'}</strong><em>${row.criteria[i] ? row.criteria[i] === 'Lulus' ? 'Memenuhi standar' : 'Belum memenuhi' : 'Belum dinilai'}</em></article>`).join('')}</div></td></tr>`).join('')}</tbody></table></div></section>`;
  const applyCertFilters = () => {
    const query = page.querySelector('#localCertSearch').value.toLocaleLowerCase('id');
    const result = page.querySelector('#localCertFilter').value;
    let count = 0;
    page.querySelectorAll('.cert-main-row').forEach(row => {
      const visible = row.dataset.name.includes(query) && (!result || row.dataset.result === result);
      row.hidden = !visible;
      page.querySelector(`[data-detail-row="${row.querySelector('[data-cert-detail]').dataset.certDetail}"]`).hidden = !visible || row.querySelector('[data-cert-detail]').getAttribute('aria-expanded') !== 'true';
      if (visible) count++;
    });
    page.querySelector('#localCertCount').textContent = `${count} data mekanik ditampilkan`;
  };
  page.querySelector('#localCertSearch').addEventListener('input', applyCertFilters);
  page.querySelector('#localCertFilter').addEventListener('change', applyCertFilters);
  page.querySelectorAll('[data-cert-detail]').forEach(button => button.addEventListener('click', () => {
    const open = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(open));
    button.textContent = open ? 'Tutup' : 'Detail';
    page.querySelector(`[data-detail-row="${button.dataset.certDetail}"]`).hidden = !open;
  }));
}

const unitTypes = {
  ultra_large: {label:'Ultra Large Haul Truck & Excavator',description:'≥ 90 ton',models:'SANY SRT100S, SY600E',workload:1110},
  large: {label:'Large Haul Truck & Excavator',description:'60–89 ton',models:'SANY SKT105S, SY510A, SW966E',workload:943},
  medium: {label:'Medium Haul Truck & Excavator',description:'20–59 ton / kelas menengah',models:'SANY SY215E, AXOR AROCS, SW966E',workload:801},
  roller: {label:'Road maintenance',description:'Road maintenance',models:'SV700TF, HV80ST, TS206',workload:681},
  support: {label:'Tower lamp, Compressor, Generator',description:'Unit Support',models:'Atlas Copco HiLight B6+, XAHS38, QES30KD',workload:579}
};
const fleetDefaults = [{type:'ultra_large',units:0,after:0,breakdown:0}];
const parameterDefaults = {hours_per_day:11,calendar_days:365,onsite_days:70,site_leave_days:14,workdays_per_cycle:13,off_cycle_days:14,development_days:14,annual_leave_days:12,emergency_leave_days:7};
const parameterLabels = [['hours_per_day','Jam kerja per hari','jam/hari'],['calendar_days','Hari kalender','hari/tahun'],['onsite_days','Hari on-site per siklus','hari'],['site_leave_days','Cuti jobsite per siklus','hari'],['workdays_per_cycle','Hari kerja dalam siklus','hari'],['off_cycle_days','Panjang siklus roster','hari'],['development_days','Development / training','hari/tahun'],['annual_leave_days','Cuti tahunan','hari/tahun'],['emergency_leave_days','Emergency leave','hari/tahun']];
function renderCalculator() {
  const page = document.querySelector('[data-page="calculator"]');
  page.innerHTML = `<div class="local-intro mpp-intro"><p class="local-eyebrow">PERENCANAAN TENAGA KERJA</p><h1>Kalkulator MPP Mekanik</h1><p>Tambahkan jenis unit dan jumlahnya untuk menghitung kebutuhan manpower gabungan.</p></div><div class="local-mock-note">Simulasi browser · Hasil tidak disimpan dan Workload mengikuti kategori unit.</div><div class="mpp-local-layout"><section class="local-panel mpp-local-form"><p class="mpp-local-hint">Pilih kategori unit dan workload mengikuti pilihan</p><label class="mpp-local-toggle"><span>Pilih periode workload</span><input id="additionalWorkloadToggle" type="checkbox"><span class="toggle-switch"><i>Warranty</i><i>After Warranty</i><b></b></span></label><div id="localFleetRows"></div><button class="local-outline-button" id="addLocalFleet">＋ Tambah jenis unit</button><details class="local-parameters"><summary>Lihat dan ubah parameter roster</summary><p>Parameter ini digunakan dalam rumus MPP. Nilai awal mengikuti kalkulator lokal.</p><div class="local-parameter-grid">${parameterLabels.map(([id,label,unit]) => `<label>${label}<small>${unit}</small><input id="param_${id}" type="number" min="0" step="any" value="${parameterDefaults[id]}"></label>`).join('')}</div></details><div class="local-calc-actions"><button id="runMppCalculation" class="local-red-button">Hitung kebutuhan MPP</button><button id="resetMppCalculation" class="local-outline-button">Reset ke parameter default</button></div><div class="local-info-note"><strong>Catatan workload</strong><span>Workload tahunan ditentukan otomatis berdasarkan kategori unit.</span></div><div class="local-info-note"><strong>Parameter roster</strong><span>11 jam kerja/hari · 70 hari on-site dan 14 hari cuti.</span></div><p id="mppError" class="local-calc-error" hidden></p></section><section class="local-panel mpp-local-result" id="mppResultPanel"><div class="mpp-empty-icon">MPP</div><h2>Hasil perhitungan tampil di sini</h2><p>Masukkan jumlah unit, lalu hitung kebutuhan mekanik.</p></section></div>`;
  let fleet = structuredClone(fleetDefaults);
  const fleetHost = page.querySelector('#localFleetRows');
  const additionalToggle = page.querySelector('#additionalWorkloadToggle');
  const number = value => Number(value).toLocaleString('id-ID',{maximumFractionDigits:2});
  const renderFleet = () => {
    fleetHost.innerHTML = fleet.map((row,index) => { const type = unitTypes[row.type]; return `<div class="local-fleet-row" data-fleet-index="${index}"><label>Jenis unit<select data-fleet="type">${Object.entries(unitTypes).map(([key,value]) => `<option value="${key}" ${row.type === key ? 'selected' : ''}>${value.label}</option>`).join('')}</select><small>${type.description} · ${type.models}</small></label><label>Jumlah unit<input data-fleet="units" type="number" min="0" value="${row.units}"></label><label>Workload (jam/unit/tahun)<input data-fleet="workload" type="number" value="${type.workload}" readonly></label><div class="local-additional-fields" ${additionalToggle.checked ? '' : 'hidden'}><label>After Warranty (jam/unit/tahun)<input data-fleet="after" type="number" min="0" step="any" value="${row.after}"></label><label>Unscheduled Breakdown (jam/unit/tahun)<input data-fleet="breakdown" type="number" min="0" step="any" value="${row.breakdown}"></label></div><button class="local-remove-fleet" data-remove-fleet="${index}" ${fleet.length === 1 ? 'disabled' : ''}>Hapus</button></div>`; }).join('');
    fleetHost.querySelectorAll('[data-fleet]').forEach(input => input.addEventListener('change', event => {
      const rowElement = event.target.closest('.local-fleet-row');
      const rowIndex = Number(rowElement.dataset.fleetIndex);
      const key = event.target.dataset.fleet;
      if (key === 'type') { fleet[rowIndex].type = event.target.value; renderFleet(); }
      else fleet[rowIndex][key] = Number(event.target.value);
    }));
    fleetHost.querySelectorAll('[data-remove-fleet]').forEach(button => button.addEventListener('click', () => { fleet.splice(Number(button.dataset.removeFleet),1); renderFleet(); }));
  };
  additionalToggle.addEventListener('change', () => {
    fleetHost.querySelectorAll('.local-additional-fields').forEach(fields => fields.hidden = !additionalToggle.checked);
    page.querySelectorAll('.mpp-local-toggle .toggle-switch').forEach(toggle => toggle.classList.toggle('after-active', additionalToggle.checked));
  });
  page.querySelector('#addLocalFleet').addEventListener('click', () => { fleet.push({type:'ultra_large',units:0,after:0,breakdown:0}); renderFleet(); });
  page.querySelector('#resetMppCalculation').addEventListener('click', () => {
    fleet = structuredClone(fleetDefaults); additionalToggle.checked = false;
    page.querySelectorAll('.local-parameter-grid input').forEach(input => input.value = parameterDefaults[input.id.slice(6)]);
    page.querySelector('.toggle-switch').classList.remove('after-active');
    page.querySelector('#mppError').hidden = true;
    page.querySelector('#mppResultPanel').innerHTML = '<div class="mpp-empty-icon">MPP</div><h2>Hasil perhitungan tampil di sini</h2><p>Masukkan jumlah unit, lalu hitung kebutuhan mekanik.</p>';
    renderFleet();
  });
  page.querySelector('#runMppCalculation').addEventListener('click', () => {
    const params = Object.fromEntries(parameterLabels.map(([key]) => [key,Number(page.querySelector(`#param_${key}`).value)]));
    const invalid = Object.values(params).some(value => !Number.isFinite(value) || value < 0) || fleet.some(row => !unitTypes[row.type] || !Number.isFinite(row.units) || row.units < 0 || row.after < 0 || row.breakdown < 0);
    const onsiteCycle = params.onsite_days + params.site_leave_days;
    const onsiteDays = params.calendar_days * params.onsite_days / onsiteCycle;
    const rosterDays = onsiteDays * params.workdays_per_cycle / params.off_cycle_days;
    const netDays = rosterDays - params.development_days - params.annual_leave_days - params.emergency_leave_days;
    const effectiveHours = netDays * params.hours_per_day;
    const error = page.querySelector('#mppError');
    if (invalid || !onsiteCycle || !params.off_cycle_days || effectiveHours <= 0) { error.textContent = 'Periksa nilai parameter. Roster harus menghasilkan jam kerja efektif lebih dari 0.'; error.hidden = false; return; }
    error.hidden = true;
    const rows = fleet.map(row => { const workload = unitTypes[row.type].workload; const extra = additionalToggle.checked ? row.after + row.breakdown : 0; const total = row.units * (workload + extra); return {...row,workload,total,share:total/effectiveHours}; });
    const units = rows.reduce((sum,row) => sum + row.units,0);
    const base = rows.reduce((sum,row) => sum + row.units*row.workload,0);
    const after = additionalToggle.checked ? rows.reduce((sum,row) => sum + row.units*row.after,0) : 0;
    const breakdown = additionalToggle.checked ? rows.reduce((sum,row) => sum + row.units*row.breakdown,0) : 0;
    const totalWorkload = rows.reduce((sum,row) => sum + row.total,0);
    const decimal = totalWorkload/effectiveHours;
    const required = Math.ceil(decimal);
    const lsExact = required*.55;
    const ls = Math.round(lsExact);
    const organic = required-ls;
    const parts = [['Jumlah unit',`${number(units)} unit`],['Workload dasar',`${number(base)} jam/tahun`],...(additionalToggle.checked ? [['After Warranty',`${number(after)} jam/tahun`],['Unscheduled Breakdown',`${number(breakdown)} jam/tahun`]] : []),['Total annual workload',`${number(totalWorkload)} jam/tahun`],['Jam efektif per mekanik',`${number(effectiveHours)} jam/tahun`],['Kebutuhan sebelum pembulatan',`${number(decimal)} orang`],['Rasio unit per mekanik',`${number(required ? units/required : 0)} unit/orang`]];
    page.querySelector('#mppResultPanel').innerHTML = `<p class="mpp-result-label">Kebutuhan mekanik</p><p class="mpp-total"><strong>${number(required)}</strong><span>orang</span></p><p class="mpp-result-copy">Angka kebutuhan dibulatkan ke atas dari hasil perhitungan workload.</p><div class="mpp-split"><article class="mpp-split-card split-ls"><span>Mechanic LS · 55%</span><strong>${number(ls)} <small>orang</small></strong><em>${number(lsExact)} sebelum pembulatan</em></article><article class="mpp-split-card split-organik"><span>Mechanic Organik · 45%</span><strong>${number(organic)} <small>orang</small></strong><em>${number(required*.45)} sebelum pembulatan</em></article></div><p class="mpp-split-note">Alokasi orang dibulatkan agar total Mechanic LS dan Organik tetap sama dengan kebutuhan MPP.</p><div class="mpp-result-breakdown">${parts.map(([label,value]) => `<div><span>${label}</span><strong>${value}</strong></div>`).join('')}</div><h2>Rincian per jenis unit</h2><div class="mpp-result-breakdown">${rows.map(row => `<div><span>${row.type} · ${number(row.units)} unit × ${number(row.workload)} jam/unit${additionalToggle.checked ? ` + ${number(row.after)} After Warranty + ${number(row.breakdown)} Unscheduled Breakdown` : ''}</span><strong>${number(row.total)} jam/tahun · ${number(row.share)} orang ekuivalen</strong></div>`).join('')}</div>`;
  });
  renderFleet();
}

renderManpower();
renderCertification();
renderCalculator();
