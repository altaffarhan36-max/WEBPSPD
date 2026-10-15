
      <section class="intro">
        <p class="eyebrow">Rekap pribadi</p>
        <h1>Hasil penilaian saya</h1>
        <p>Informasi masa bergabung dan rekap performa pribadi.</p>
      </section>
      <?php if ($identity['mechanic_id'] === null): ?>
        <div class="notice error" role="alert">Akun ini belum ditautkan ke data mekanik. Hubungi Master.</div>
      <?php else: ?>
        <?php if ($mechanicProfile): ?>
          <section class="mechanic-metrics" aria-label="Data performa pribadi">
            <article class="summary-card"><span>Tanggal bergabung</span><strong class="metric-text"><?= $escape($mechanicProfile['join_date']) ?></strong></article>
            <article class="summary-card"><span>Masa bergabung</span><strong><?= $mechanicProfile['group_years'] === null ? '—' : $escape($mechanicProfile['group_years']) . ' th' ?></strong></article>
            <article class="summary-card"><span>Passing duration</span><strong><?= $mechanicProfile['passing_duration_months'] === null ? '—' : $escape($mechanicProfile['passing_duration_months']) . ' bln' ?></strong></article>
            <article id="training" class="summary-card"><span>Training</span><strong><?= $mechanicProfile['training_score'] === null ? '—' : $escape($mechanicProfile['training_score']) . ' poin' ?></strong></article>
            <article class="summary-card"><span>ATR</span><strong><?= $mechanicProfile['atr_percent'] === null ? '—' : $escape($mechanicProfile['atr_percent']) . '%' ?></strong></article>
            <article class="summary-card"><span>Individual Improvement</span><strong><?= $mechanicProfile['individual_improvements'] === null ? '—' : $escape($mechanicProfile['individual_improvements']) . ' kali' ?></strong></article>
          </section>
        <?php else: ?>
          <div class="notice error" role="alert">Data mekanik untuk akun ini tidak ditemukan. Hubungi Master.</div>
        <?php endif; ?>
      <?php if (!$mechanicAssessment): ?>
        <section id="certification" class="mechanic-result pending-result"><span>Status penilaian</span><strong>BELUM DINILAI</strong><p>Master belum menyimpan penilaian untuk akun ini.</p></section>
      <?php else: ?>
        <?php $passedAssessment = $mechanicAssessment['result'] === 'LULUS'; ?>
        <section id="certification" class="mechanic-result <?= $passedAssessment ? 'pass-result' : 'fail-result' ?>">
          <span>Hasil penilaian terbaru</span>
          <strong><?= $passedAssessment ? 'LOLOS' : 'TIDAK LOLOS' ?></strong>
          <p>Dinilai pada <?= $escape($mechanicAssessment['assessed_at']) ?>.</p>
        </section>
      <?php endif; ?>
      <?php endif; ?>
      <p class="source-note mechanic-note">Halaman ini hanya menampilkan rekap hasil penilaian Anda.</p>
    