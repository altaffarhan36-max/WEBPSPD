
      <section class="intro">
        <p class="eyebrow">PT Star Wagen Indonesia · Penilaian mekanik</p>
        <h1>Dashboard Master</h1>
        <p>Pantau rekap sertifikasi mekanik dan hasil STPR.</p>
        <p class="as-of">Data masa golongan dihitung per <?= $escape($asOf) ?></p>
      </section>

      <?php if ($accountMessage !== ''): ?><div class="account-message <?= $accountMessageType ?>" role="status"><?= $escape($accountMessage) ?></div><?php endif; ?>

      <section id="certification" class="summary" aria-label="Ringkasan sertifikasi TLU">
        <article class="summary-card"><span>Total mekanik</span><strong><?= $total ?></strong></article>
        <article class="summary-card passed"><span>Lulus TLU</span><strong><?= $passed ?></strong></article>
        <article class="summary-card failed"><span>Tidak lulus TLU</span><strong><?= $failed ?></strong></article>
        <article class="summary-card pending"><span>Belum dinilai TLU</span><strong><?= $pending ?></strong></article>
      </section>

      <section class="menu-view active" data-menu-view="overview">
      <section class="panel legacy-panel initial-recap">
        <div class="panel-heading"><div><h2>Rekap data sertifikasi TLU</h2><p>Hasil penilaian perilaku terbaru ditampilkan pada kolom STPR.</p></div>
          <div class="filters"><input id="mechanicSearch" type="search" placeholder="Cari nama mekanik…" aria-label="Cari nama mekanik"><select id="resultFilter" aria-label="Filter hasil"><option value="">Semua hasil TLU</option><option value="LULUS">Lulus TLU</option><option value="TIDAK LULUS">Tidak lulus TLU</option><option value="BELUM DINILAI">Belum dinilai TLU</option></select></div>
        </div>
        <p id="resultCount" class="source-note">Memuat data mekanik</p>
        <div class="table-wrap"><table>
          <thead><tr><th>Nama mekanik</th><th>Site / GL</th><th>Tanggal bergabung</th><th>Golongan saat ini</th><th>Masa golongan</th><th>ATR</th><th id="training">Training</th><th>STPR</th><th>Hasil TLU</th><th>Detail</th></tr></thead>
          <tbody>
          <?php foreach ($mechanics as $row): ?>
            <?php $result = $row['final_result'] ?? 'BELUM DINILAI'; $stprResult = $row['behavior_result'] ?? 'BELUM DINILAI'; ?>
            <tr class="mechanic-row" data-name="<?= $escape(mb_strtolower($row['technician_name'], 'UTF-8')) ?>" data-result="<?= $escape($result) ?>"><td class="mechanic-name"><?= $escape($row['technician_name']) ?></td><td><?= $escape($row['site']) ?></td><td><?= $escape($row['join_date']) ?></td><td><?= $escape($row['grade_level']) ?></td><td><?= $row['group_years'] === null ? '—' : $escape($row['group_years']) . ' th' ?></td><td><?= $row['atr_percent'] === null ? '—' : $escape($row['atr_percent']) . '%' ?></td><td><?= $escape($row['training_score']) ?></td><td><?php if ($row['behavior_average'] !== null): ?><span class="badge <?= $stprResult === 'LULUS' ? 'badge-pass' : 'badge-fail' ?>"><?= number_format((float) $row['behavior_average'], 2, ',', '.') ?> poin</span><?php elseif ($row['stpr_count'] !== null): ?><span class="badge <?= $row['status_stpr'] === 'Lulus' ? 'badge-pass' : 'badge-fail' ?>"><?= $escape($row['stpr_count']) ?> poin</span><?php else: ?><span class="badge badge-pending">BELUM DINILAI</span><?php endif; ?></td><td><?= $escape($result) ?></td><td><button type="button" class="detail-toggle" aria-expanded="false">Detail</button></td></tr>
            <tr class="detail-row" hidden><td colspan="10"><div class="detail-grid"><?php foreach ($criteriaSummary as [$statusKey,$label,$valueKey,$unit]): $status=(string)($row[$statusKey]??'Belum dinilai'); $value=$row[$valueKey]??null; if ($label === 'STPR' && $row['behavior_average'] !== null) { $status = $stprResult === 'LULUS' ? 'Lulus' : 'Tidak Lulus'; $value = number_format((float) $row['behavior_average'], 2, ',', '.'); $unit = 'poin / 4'; } ?><article class="criterion"><span><?= $escape($label) ?></span><strong><?= $value===null?'—':$escape($value).' '.$escape($unit) ?></strong><em><?= $escape($status) ?></em></article><?php endforeach; ?></div></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table><p id="emptyState" class="empty-state" hidden>Tidak ada mekanik yang cocok dengan pencarian.</p></div>
      </section>
      </section>