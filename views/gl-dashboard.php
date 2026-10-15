
      <section class="intro">
        <p class="eyebrow">Group Leader · <?= $escape($identity['gl_site'] ?? '—') ?></p>
        <h1><?= $escape($identity['gl_name'] ?? 'Group Leader') ?></h1>
        <p>Site <?= $escape($identity['gl_site'] ?? '—') ?> · pilih hanya mekanik yang terdaftar pada site ini, isi nilai 1 sampai 4, lalu simpan.</p>
      </section>
      <?php if ($assessmentMessage !== ''): ?><div class="account-message <?= $assessmentMessageType ?>" role="status"><?= $escape($assessmentMessage) ?></div><?php endif; ?>
      <section id="certification" class="panel results-panel">
        <div class="panel-heading"><div><h2>Monitoring mechanic wilayah <?= $escape($identity['gl_site'] ?? '—') ?></h2><p>Menampilkan seluruh mechanic di site ini, termasuk yang belum memiliki penilaian.</p></div></div>
        <div class="table-wrap"><table>
          <thead><tr><th>Nama mechanic</th><th>Site</th><th>Golongan</th><th>Tanggal bergabung</th><th>ATR</th><th id="training">Training</th><th>Penilaian perilaku</th><th>Hasil TLU</th></tr></thead>
          <tbody>
          <?php foreach ($mechanics as $mechanic): ?>
            <?php $behaviorResult = $mechanic['behavior_result'] ?? null; $tluResult = $mechanic['status_tlu'] ?? null; ?>
            <tr>
              <td class="mechanic-name"><?= $escape($mechanic['technician_name']) ?></td>
              <td><?= $escape($mechanic['site']) ?></td>
              <td><?= $escape($mechanic['grade_level']) ?></td>
              <td><?= $escape($mechanic['join_date']) ?></td>
              <td><?= $mechanic['atr_percent'] === null ? '—' : $escape($mechanic['atr_percent']) . '%' ?></td>
              <td><?= $escape($mechanic['training_score']) ?></td>
              <td><?php if ($mechanic['behavior_average'] !== null): ?><span class="badge <?= $behaviorResult === 'LULUS' ? 'badge-pass' : 'badge-fail' ?>"><?= number_format((float) $mechanic['behavior_average'], 2, ',', '.') ?> / 4 · <?= $escape($behaviorResult) ?></span><?php else: ?><span class="badge badge-pending">BELUM DINILAI</span><?php endif; ?></td>
              <td><?php if ($tluResult === null): ?><span class="badge badge-pending">BELUM DINILAI</span><?php else: ?><span class="badge <?= $tluResult === 'Lulus' ? 'badge-pass' : 'badge-fail' ?>"><?= $escape($tluResult) ?></span><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$mechanics): ?><tr><td colspan="8" class="empty-cell">Belum ada mechanic pada site ini.</td></tr><?php endif; ?>
          </tbody>
        </table></div>
      </section>
      <section class="panel assessment-panel">
        <div class="panel-heading"><div><h2>Penilaian perilaku dan kinerja</h2><p>Nilai 1 Kurang · 2 Cukup · 3 Baik · 4 Sangat Baik</p></div></div>
        <form method="post" class="assessment-form">
          <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="save_behavior_assessment">
          <div class="assessment-select"><label for="glAssessedMechanic">Pilih mekanik</label>
            <select id="glAssessedMechanic" name="mechanic_id" required>
              <option value="">Pilih mekanik yang dinilai</option>
              <?php foreach ($mechanics as $mechanic): ?><option value="<?= (int) $mechanic['id'] ?>"><?= $escape($mechanic['technician_name']) ?></option><?php endforeach; ?>
            </select>
            <span>Passing grade: rata-rata nilai minimal 2,00 / 4,00.</span>
          </div>
          <div class="matrix-wrap"><table class="matrix-table">
            <thead><tr><th>No.</th><th>Item penilaian</th><th>Indikator perilaku/kinerja</th><th>1<br>Kurang</th><th>2<br>Cukup</th><th>3<br>Baik</th><th>4<br>Sangat baik</th></tr></thead>
            <tbody><?php foreach ($criteria as $criterion): ?><tr><td><?= (int) $criterion['display_order'] ?></td><td><strong><?= $escape($criterion['category']) ?></strong><br><?= $escape($criterion['criterion_name']) ?></td><td><?= $escape($criterion['behavior_indicator']) ?></td><?php for ($score = 1; $score <= 4; $score++): ?><td class="score-cell"><label class="score-choice"><input type="radio" name="scores[<?= (int) $criterion['id'] ?>]" value="<?= $score ?>" required><span class="sr-only">Nilai <?= $score ?></span></label></td><?php endfor; ?></tr><?php endforeach; ?></tbody>
          </table></div>
          <button type="submit" class="create-account save-assessment">Simpan penilaian</button>
        </form>
      </section>
      ication" class="panel results-panel">
        <div class="panel-heading"><div><h2>Rekap penilaian saya</h2><p>Daftar ini hanya berisi penilaian yang disimpan dari akun GL Anda.</p></div></div>
        <div class="table-wrap"><table>
          <thead><tr><th>Mekanik</th><th>Waktu penilaian</th><th>Rata-rata</th><th>Passing grade</th><th>Hasil</th></tr></thead>
          <tbody>
          <?php foreach ($assessments as $assessment): ?>
            <tr>
              <td class="mechanic-name"><?= $escape($assessment['technician_name']) ?></td>
              <td><?= $escape($assessment['assessed_at']) ?></td>
              <td><?= number_format((float) $assessment['average_score'], 2, ',', '.') ?> / 4</td>
              <td><?= number_format((float) $assessment['passing_grade'], 2, ',', '.') ?></td>
              <td><span class="badge <?= $assessment['result'] === 'LULUS' ? 'badge-pass' : 'badge-fail' ?>"><?= $escape($assessment['result']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$assessments): ?><tr><td colspan="5" class="empty-cell">Belum ada penilaian yang disimpan dari akun GL ini.</td></tr><?php endif; ?>
          </tbody>
        </table></div>
      </section>
    
