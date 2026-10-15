<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
requireAdmin();

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$unitTypes = [
    'ultra_large' => ['label' => 'Ultra Large Haul Truck & Excavator', 'description' => '≥ 90 ton', 'models' => 'SANY SRT100S, SY600E', 'workload' => 1110],
    'large' => ['label' => 'Large Haul Truck & Excavator', 'description' => '60–89 ton', 'models' => 'SANY SKT105S, SY510A, SW966E', 'workload' => 943],
    'medium' => ['label' => 'Medium Haul Truck & Excavator', 'description' => '20–59 ton / kelas menengah', 'models' => 'SANY SY215E, AXOR AROCS, SW966E', 'workload' => 801],
    'roller' => ['label' => 'Roller / Compactor', 'description' => 'Road maintenance', 'models' => 'SV700TF, HV80ST, TS206', 'workload' => 681],
    'support' => ['label' => 'Tower lamp, Compressor, Generator', 'description' => 'Unit Support', 'models' => 'Atlas Copco HiLight B6+, XAHS38, QES30KD', 'workload' => 579],
];
$availableUnitTypes = array_keys($unitTypes);
$includeAdditional = ($_POST['include_additional'] ?? '') === '1';
$fleetRows = [];
$submittedFleet = $_POST['fleet'] ?? null;
if (is_array($submittedFleet)) {
    foreach ($submittedFleet as $row) {
        if (is_array($row)) {
            $rowType = (string) ($row['type'] ?? 'ultra_large');
            $defaultWorkload = (string) ($unitTypes[$rowType]['workload'] ?? $unitTypes['ultra_large']['workload']);
            $fleetRows[] = [
                'type' => isset($unitTypes[$rowType]) ? $rowType : 'ultra_large',
                'units' => trim((string) ($row['units'] ?? '0')),
                'workload' => trim((string) ($row['workload'] ?? $row['before_workload'] ?? $defaultWorkload)),
                'after_workload' => trim((string) ($row['after_workload'] ?? '0')),
                'breakdown_workload' => trim((string) ($row['breakdown_workload'] ?? '0')),
            ];
        }
    }
}
if ($fleetRows === []) {
    $fleetRows = [['type' => 'ultra_large', 'units' => '0', 'workload' => '1110', 'after_workload' => '0', 'breakdown_workload' => '0']];
}
$defaults = [
    'hours_per_day' => 11,
    'calendar_days' => 365,
    'onsite_days' => 70,
    'site_leave_days' => 14,
    'workdays_per_cycle' => 13,
    'off_cycle_days' => 14,
    'development_days' => 14,
    'annual_leave_days' => 12,
    'emergency_leave_days' => 7,
];
$parameterLabels = [
    'hours_per_day' => ['Jam kerja per hari', 'jam/hari'],
    'calendar_days' => ['Hari kalender', 'hari/tahun'],
    'onsite_days' => ['Hari on-site per siklus', 'hari'],
    'site_leave_days' => ['Cuti jobsite per siklus', 'hari'],
    'workdays_per_cycle' => ['Hari kerja dalam siklus', 'hari'],
    'off_cycle_days' => ['Panjang siklus roster', 'hari'],
    'development_days' => ['Development / training', 'hari/tahun'],
    'annual_leave_days' => ['Cuti tahunan', 'hari/tahun'],
    'emergency_leave_days' => ['Emergency leave', 'hari/tahun'],
];
$parameters = $defaults;
$action = (string) ($_POST['action'] ?? '');
if ($action === 'reset') { $includeAdditional = false; }
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $action !== 'reset') {
    foreach ($defaults as $key => $_default) {
        $rawValue = trim((string) ($_POST[$key] ?? (string) $_default));
        if (is_numeric($rawValue)) {
            $parameters[$key] = (float) $rawValue;
        } else {
            $parameters[$key] = NAN;
        }
    }
}
$error = '';
$result = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $action !== 'reset') {
    // Validasi dan akumulasi workload untuk setiap jenis unit.
    $validFleet = [];
    $totalUnits = 0;
    $totalWorkload = 0.0;
    $baseWorkload = 0.0;
    $afterWorkload = 0.0;
    $breakdownWorkload = 0.0;
    foreach ($fleetRows as $row) {
        if (!in_array($row['type'], $availableUnitTypes, true) || $row['units'] === '' || !ctype_digit($row['units']) || (float) $row['units'] > 1000000 || ($includeAdditional && (!is_numeric($row['after_workload']) || (float) $row['after_workload'] < 0 || (float) $row['after_workload'] > 1000000 || !is_numeric($row['breakdown_workload']) || (float) $row['breakdown_workload'] < 0 || (float) $row['breakdown_workload'] > 1000000))) {
            $error = 'Periksa kategori unit, jumlah unit bilangan bulat dari 0 sampai 1.000.000, dan nilai workload tambahan.';
            break;
        }
        $row['units'] = (int) $row['units'];
        $row['workload'] = (float) $unitTypes[$row['type']]['workload'];
        $row['after_workload'] = $includeAdditional ? (float) $row['after_workload'] : 0.0;
        $row['breakdown_workload'] = $includeAdditional ? (float) $row['breakdown_workload'] : 0.0;
        $row['base_total'] = $row['units'] * $row['workload'];
        $row['after_total'] = $row['units'] * $row['after_workload'];
        $row['breakdown_total'] = $row['units'] * $row['breakdown_workload'];
        $row['total_workload'] = $row['base_total'] + $row['after_total'] + $row['breakdown_total'];
        $validFleet[] = $row;
        $totalUnits += $row['units'];
        $baseWorkload += $row['base_total'];
        $afterWorkload += $row['after_total'];
        $breakdownWorkload += $row['breakdown_total'];
        $totalWorkload += $row['total_workload'];
    }
    if ($error === '' && $totalUnits > 1000000) {
        $error = 'Total unit tidak boleh lebih dari 1.000.000.';
    } elseif ($error === '' && (count(array_filter($parameters, static fn(float|int $value): bool => !is_finite((float) $value))) > 0 || min($parameters) < 0 || max($parameters) > 1000000)) {
        $error = 'Periksa kembali parameter. Semua nilai harus berupa angka dari 0 sampai 1.000.000.';
    } elseif ($error === '' && ($parameters['hours_per_day'] <= 0 || $parameters['calendar_days'] <= 0 || $parameters['onsite_days'] + $parameters['site_leave_days'] <= 0 || $parameters['off_cycle_days'] <= 0)) {
        $error = 'Jam kerja, hari kalender, total siklus on-site/cuti, dan panjang siklus harus lebih dari 0.';
    } elseif ($error === '') {
        // Hitung hari on-site efektif berdasarkan rasio roster on-site dan cuti jobsite.
        $onsiteEffectiveDays = $parameters['calendar_days'] * $parameters['onsite_days'] / ($parameters['onsite_days'] + $parameters['site_leave_days']);
        // Konversi hari on-site menjadi hari kerja sesuai siklus kerja dan off-cycle.
        $rosterWorkingDays = $onsiteEffectiveDays * $parameters['workdays_per_cycle'] / $parameters['off_cycle_days'];
        // Kurangi hari development, cuti tahunan, dan emergency leave.
        $nonProductiveDays = $parameters['development_days'] + $parameters['annual_leave_days'] + $parameters['emergency_leave_days'];
        $netWorkingDays = $rosterWorkingDays - $nonProductiveDays;
        // Kapasitas jam kerja efektif tahunan untuk satu mekanik.
        $effectiveHoursPerMechanic = $netWorkingDays * $parameters['hours_per_day'];
        if ($netWorkingDays <= 0 || $effectiveHoursPerMechanic <= 0) {
            $error = 'Parameter menghasilkan hari kerja efektif 0 atau kurang. Sesuaikan roster atau hari cuti.';
        }
    }

    if ($error === '') {
        $decimalManpower = $totalWorkload / $effectiveHoursPerMechanic;
        $requiredManpower = (int) ceil($decimalManpower);
        foreach ($validFleet as &$row) {
            $row['manpower_share'] = $row['total_workload'] / $effectiveHoursPerMechanic;
        }
        unset($row);
        $lsManpower = (int) round($requiredManpower * 0.55);
        $organicManpower = $requiredManpower - $lsManpower;
        $result = [
            'units' => $totalUnits,
            'fleet' => $validFleet,
            'total_workload' => $totalWorkload,
            'base_workload' => $baseWorkload,
            'after_workload' => $afterWorkload,
            'breakdown_workload' => $breakdownWorkload,
            'include_additional' => $includeAdditional,
            'effective_hours' => $effectiveHoursPerMechanic,
            'decimal' => $decimalManpower,
            'manpower' => $requiredManpower,
            'ls_manpower' => $lsManpower,
            'organic_manpower' => $organicManpower,
            'ls_exact' => $requiredManpower * 0.55,
            'organic_exact' => $requiredManpower * 0.45,
            'unit_ratio' => $totalUnits > 0 ? $totalUnits / max(1, $requiredManpower) : 0,
        ];
    }
}

$formatNumber = static fn(float|int $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
$formatInput = static fn(float|int $value): string => rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
$toolbarTitle = 'MANPOWER PLANNING CALCULATOR';
$toolbarUserLabel = (string) ($_SESSION['username'] ?? 'Administrator') . ' · Master';
$toolbarActions = [
    ['href' => 'modules.php', 'label' => 'Kembali ke Pilih Modul'],
    ['href' => 'manpower.php', 'label' => 'Monitoring Man Power'],
];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kalkulator MPP | PT Star Wagen Indonesia</title>
  <link rel="stylesheet" href="assets/css/dashboard.css?v=20261015-mpp-toolbar">
  <link rel="stylesheet" href="assets/css/toolbar.css?v=20261015">
</head>
<body class="mpp-page">
  <main class="shell">
    <?php require __DIR__ . '/views/topbar.php'; ?>

    <section class="mpp-hero">
      <p class="eyebrow">Perencanaan tenaga kerja</p>
      <h1>Kalkulator MPP Mekanik</h1>
      <p>Tambahkan kategori unit dan jumlahnya untuk menghitung kebutuhan manpower gabungan.</p>
    </section>

    <div class="mpp-notice" role="note">Simulasi browser · Hasil perhitungan tidak disimpan.</div>

    <section class="mpp-layout" aria-label="Kalkulator MPP beberapa jenis unit">
      <form class="panel mpp-form" method="post">
        <p class="mpp-hint">Pilih kategori unit dan workload mengikuti pilihan</p>
        <label class="mpp-toggle" for="include-additional">
          <span class="mpp-toggle-copy">Pilih periode workload</span>
          <input id="include-additional" type="checkbox" name="include_additional" value="1" <?= $includeAdditional ? 'checked' : '' ?>>
          <span class="mpp-switch" aria-hidden="true"><span class="mpp-switch-warranty">Warranty</span><span class="mpp-switch-after">After Warranty</span><span class="mpp-switch-thumb"></span></span>
        </label>
        <div id="fleetRows" data-next-index="<?= count($fleetRows) ?>">
          <?php foreach ($fleetRows as $index => $row): ?>
            <div class="mpp-fleet-row">
              <label>Jenis unit
                <select name="fleet[<?= (int) $index ?>][type]">
                  <?php foreach ($unitTypes as $unitTypeKey => $unitType): ?>
                    <option value="<?= $escape($unitTypeKey) ?>" data-default-workload="<?= $escape($unitType['workload']) ?>" data-description="<?= $escape($unitType['description']) ?>" data-models="<?= $escape($unitType['models']) ?>" <?= $row['type'] === $unitTypeKey ? 'selected' : '' ?>><?= $escape($unitType['label']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <p class="mpp-hint mpp-unit-details"><?= $unitTypes[$row['type']]['description'] !== '' ? $escape($unitTypes[$row['type']]['description']) . ' · ' : '' ?><?= $escape($unitTypes[$row['type']]['models']) ?></p>
              <label>Jumlah unit
                <input name="fleet[<?= (int) $index ?>][units]" type="number" min="0" max="1000000" step="1" value="<?= $escape($row['units']) ?>" required>
              </label>
              <label>Workload (jam/unit/tahun)
                <input name="fleet[<?= (int) $index ?>][workload]" type="number" value="<?= $escape($unitTypes[$row['type']]['workload']) ?>" readonly>
              </label>
              <div class="mpp-additional-workloads" <?= $includeAdditional ? '' : 'hidden' ?>>
                <label>After Warranty (jam/unit/tahun)
                  <input name="fleet[<?= (int) $index ?>][after_workload]" type="number" min="0" max="1000000" step="any" value="<?= $escape($row['after_workload']) ?>">
                </label>
                <label>Unscheduled Breakdown (jam/unit/tahun)
                  <input name="fleet[<?= (int) $index ?>][breakdown_workload]" type="number" min="0" max="1000000" step="any" value="<?= $escape($row['breakdown_workload']) ?>">
                </label>
              </div>
              <button class="mpp-remove-row" type="button" aria-label="Hapus baris unit">Hapus</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button class="mpp-reset" id="addFleetRow" type="button">+ Tambah jenis unit</button>
        <details class="mpp-parameters">
          <summary>Lihat dan ubah parameter roster</summary>
          <p>Parameter ini digunakan dalam rumus MPP. Nilai awal mengikuti Book1.xlsx.</p>
          <div class="mpp-parameter-grid">
            <?php foreach ($parameterLabels as $key => [$label, $unit]): ?>
              <label for="parameter-<?= $escape($key) ?>"><?= $escape($label) ?><span><?= $escape($unit) ?></span>
                <input id="parameter-<?= $escape($key) ?>" name="<?= $escape($key) ?>" type="number" min="0" max="1000000" step="any" value="<?= $escape($formatInput($parameters[$key])) ?>" required>
              </label>
            <?php endforeach; ?>
          </div>
        </details>
        <?php if ($error !== ''): ?><p class="mpp-error" role="alert"><?= $escape($error) ?></p><?php endif; ?>
        <div class="mpp-actions">
          <button class="mpp-submit" type="submit" name="action" value="calculate">Hitung kebutuhan MPP</button>
          <button class="mpp-reset" type="submit" name="action" value="reset" formnovalidate>Reset ke parameter default</button>
        </div>
        <div class="mpp-note"><strong>Catatan workload</strong><span>Isi workload tahunan setiap jenis unit sesuai data aktual.</span></div>
        <div class="mpp-note"><strong>Parameter roster</strong><span><?= $formatNumber($parameters['hours_per_day']) ?> jam kerja/hari · <?= $formatNumber($parameters['onsite_days']) ?> hari on-site dan <?= $formatNumber($parameters['site_leave_days']) ?> hari cuti.</span></div>
      </form>

      <section class="panel mpp-result<?= $result === null ? ' mpp-result-empty' : '' ?>" aria-live="polite">
        <?php if ($result !== null): ?>
          <p class="mpp-result-label">Kebutuhan mekanik</p>
          <p class="mpp-total"><strong><?= $formatNumber($result['manpower']) ?></strong><span>orang</span></p>
          <p class="mpp-result-copy">Angka kebutuhan dibulatkan ke atas dari hasil perhitungan workload.</p>
          <div class="mpp-split" aria-label="Pembagian kebutuhan mekanik berdasarkan rasio">
            <article class="mpp-split-card mpp-split-ls"><span>Mechanic LS � 55%</span><strong><?= $formatNumber($result['ls_manpower']) ?> <small>orang</small></strong><em><?= $formatNumber($result['ls_exact'], 2) ?> sebelum pembulatan</em></article>
            <article class="mpp-split-card mpp-split-organik"><span>Mechanic Organik � 45%</span><strong><?= $formatNumber($result['organic_manpower']) ?> <small>orang</small></strong><em><?= $formatNumber($result['organic_exact'], 2) ?> sebelum pembulatan</em></article>
          </div>
          <p class="mpp-split-note">Alokasi orang dibulatkan agar total Mechanic LS dan Organik tetap sama dengan kebutuhan MPP.</p>
          <div class="mpp-breakdown">
            <div><span>Jumlah unit</span><strong><?= $formatNumber($result['units']) ?> unit</strong></div>
            <div><span>Workload dasar</span><strong><?= $formatNumber($result['base_workload']) ?> jam/tahun</strong></div><?php if ($result['include_additional']): ?><div><span>After Warranty</span><strong><?= $formatNumber($result['after_workload']) ?> jam/tahun</strong></div><div><span>Unscheduled Breakdown</span><strong><?= $formatNumber($result['breakdown_workload']) ?> jam/tahun</strong></div><?php endif; ?><div><span>Total annual workload</span><strong><?= $formatNumber($result['total_workload']) ?> jam/tahun</strong></div>
            <div><span>Jam efektif per mekanik</span><strong><?= $formatNumber($result['effective_hours'], 2) ?> jam/tahun</strong></div>
            <div><span>Kebutuhan sebelum pembulatan</span><strong><?= $formatNumber($result['decimal'], 2) ?> orang</strong></div>
            <div><span>Rasio unit per mekanik</span><strong><?= $formatNumber($result['unit_ratio'], 2) ?> unit/orang</strong></div>
          </div>
          <h2>Rincian per jenis unit</h2>
          <div class="mpp-breakdown">
            <?php foreach ($result['fleet'] as $row): ?>
              <div><span><?= $escape($unitTypes[$row['type']]['label']) ?> &middot; <?= $formatNumber($row['units']) ?> unit &times; <?= $formatNumber($row['workload']) ?> jam/unit<?php if ($result['include_additional']): ?> + <?= $formatNumber($row['after_workload']) ?> After Warranty + <?= $formatNumber($row['breakdown_workload']) ?> Unscheduled Breakdown<?php endif; ?></span><strong><?= $formatNumber($row['total_workload']) ?> jam/tahun &middot; <?= $formatNumber($row['manpower_share'], 2) ?> orang ekuivalen</strong></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="mpp-empty-icon" aria-hidden="true">MPP</div>
          <h2>Hasil perhitungan tampil di sini</h2>
          <p>Pilih kategori unit, masukkan jumlah unit, atau tambahkan beberapa kategori sekaligus.</p>
        <?php endif; ?>
      </section>
    </section>
    <footer>Kalkulator MPP · Workload per unit dan parameter roster dapat disesuaikan.</footer>
  </main>
  <script>
    const fleetRows = document.getElementById('fleetRows');
    const additionalToggle = document.getElementById('include-additional');
    const syncAdditionalWorkloads = () => {
      document.querySelectorAll('.mpp-additional-workloads').forEach((fields) => { fields.hidden = !additionalToggle.checked; });
    };
    additionalToggle.addEventListener('change', syncAdditionalWorkloads);
    fleetRows.addEventListener('change', (event) => {
      if (event.target.matches('select[name$="[type]"]')) {
        const workloadInput = event.target.closest('.mpp-fleet-row').querySelector('input[name$="[workload]"]');
        const selectedUnit = event.target.selectedOptions[0];
        workloadInput.value = selectedUnit.dataset.defaultWorkload;
        const unitDetails = event.target.closest('.mpp-fleet-row').querySelector('.mpp-unit-details');
        unitDetails.classList.remove('is-changing');
        void unitDetails.offsetWidth;
        unitDetails.textContent = [selectedUnit.dataset.description, selectedUnit.dataset.models].filter(Boolean).join(' · ');
        unitDetails.classList.add('is-changing');
      }
    });
    document.getElementById('addFleetRow').addEventListener('click', () => {
      const index = Number(fleetRows.dataset.nextIndex++);
      const row = document.createElement('div');
      row.className = 'mpp-fleet-row';
      row.innerHTML = `<label>Jenis unit<select name="fleet[${index}][type]"><option value="ultra_large" data-default-workload="1110" data-description="≥ 90 ton" data-models="SANY SRT100S, SY600E">Ultra Large Haul Truck & Excavator</option><option value="large" data-default-workload="943" data-description="60–89 ton" data-models="SANY SKT105S, SY510A, SW966E">Large Haul Truck & Excavator</option><option value="medium" data-default-workload="801" data-description="20–59 ton / kelas menengah" data-models="SANY SY215E, AXOR AROCS, SW966E">Medium Haul Truck & Excavator</option><option value="roller" data-default-workload="681" data-description="Road maintenance" data-models="SV700TF, HV80ST, TS206">Roller / Compactor</option><option value="support" data-default-workload="579" data-description="Unit Support" data-models="Atlas Copco HiLight B6+, XAHS38, QES30KD">Tower lamp, Compressor, Generator</option></select></label><p class="mpp-hint mpp-unit-details">≥ 90 ton · SANY SRT100S, SY600E</p><label>Jumlah unit<input name="fleet[${index}][units]" type="number" min="0" max="1000000" step="1" value="0" required></label><label>Workload (jam/unit/tahun)<input name="fleet[${index}][workload]" type="number" value="1110" readonly></label><div class="mpp-additional-workloads" hidden><label>After Warranty (jam/unit/tahun)<input name="fleet[${index}][after_workload]" type="number" min="0" max="1000000" step="any" value="0"></label><label>Unscheduled Breakdown (jam/unit/tahun)<input name="fleet[${index}][breakdown_workload]" type="number" min="0" max="1000000" step="any" value="0"></label></div><button class="mpp-remove-row" type="button" aria-label="Hapus baris unit">Hapus</button>`;
      row.querySelector('.mpp-additional-workloads').hidden = !additionalToggle.checked;
      fleetRows.appendChild(row);
    });
    fleetRows.addEventListener('click', (event) => {
      if (event.target.matches('.mpp-remove-row') && fleetRows.children.length > 1) {
        event.target.closest('.mpp-fleet-row').remove();
      }
    });
  </script>
</body>
</html>
