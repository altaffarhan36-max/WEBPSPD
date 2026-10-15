<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/api/database.php';
requireLogin();

$databaseError = false;
$isMaster = false;
$isGl = false;
$canAssess = false;
$mechanics = [];
$unassignedMechanics = [];
$criteria = [];
$assessments = [];
$mechanicAssessment = null;
$mechanicProfile = null;
$accountMessage = '';
$accountMessageType = '';
$assessmentMessage = '';
$assessmentMessageType = '';
$asOf = '';
$identity = null;
$passingGrade = 2.00;
$escape = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

try {
    $database = databaseConnection();
    $identityQuery = $database->prepare('SELECT id, username, role, mechanic_id, gl_name, gl_site FROM users WHERE id = :id LIMIT 1');
    $identityQuery->execute([':id' => (int) $_SESSION['user_id']]);
    $identity = $identityQuery->fetch();
    if (!$identity) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: login.php');
        exit;
    }
    $_SESSION['username'] = $identity['username'];
    $_SESSION['role'] = $identity['role'];
    $_SESSION['mechanic_id'] = $identity['mechanic_id'] === null ? null : (int) $identity['mechanic_id'];
    $isMaster = in_array($identity['role'], ['master', 'admin'], true);
    $isGl = $identity['role'] === 'gl';
    $canAssess = $isGl;
    $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

    if ($canAssess) {
        $criteria = $database->query(
            'SELECT id, category, criterion_name, behavior_indicator, display_order
             FROM behavior_criteria ORDER BY display_order'
        )->fetchAll();
    }

    $action = (string) ($_POST['action'] ?? '');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $action !== '') {
        $postAllowed = $isGl && $action === 'save_behavior_assessment';
        if (!$postAllowed) {
            http_response_code(403);
            exit('Akses tidak diizinkan.');
        }
        if (!hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
            if ($action === 'save_behavior_assessment') {
                $assessmentMessage = 'Sesi formulir berakhir. Muat ulang halaman dan coba lagi.';
            } else {
                $accountMessage = 'Sesi formulir berakhir. Muat ulang halaman dan coba lagi.';
            }
        } elseif ($action === 'create_mechanic_account') {
            $newUsername = trim((string) ($_POST['username'] ?? ''));
            $newPassword = (string) ($_POST['password'] ?? '');
            $mechanicId = filter_var($_POST['mechanic_id'] ?? null, FILTER_VALIDATE_INT);
            if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $newUsername)) {
                $accountMessage = 'Username harus 3–64 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
            } elseif (!hasMinimumPasswordLength($newPassword)) {
                $accountMessage = 'Password minimal 5 karakter.';
            } elseif ($mechanicId === false || $mechanicId === null) {
                $accountMessage = 'Pilih mekanik yang akan diberi akses.';
            } else {
                $available = $database->prepare(
                    'SELECT m.id FROM mechanics AS m
                     LEFT JOIN users AS u ON u.mechanic_id = m.id
                     WHERE m.id = :mechanic_id AND u.id IS NULL LIMIT 1'
                );
                $available->execute([':mechanic_id' => $mechanicId]);
                if (!$available->fetchColumn()) {
                    $accountMessage = 'Mekanik tidak ditemukan atau sudah memiliki akun.';
                } else {
                    try {
                        $insert = $database->prepare(
                            'INSERT INTO users (username, password_hash, role, mechanic_id)
                             VALUES (:username, :password_hash, \'mechanic\', :mechanic_id)'
                        );
                        $insert->execute([
                            ':username' => $newUsername,
                            ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                            ':mechanic_id' => $mechanicId,
                        ]);
                        $accountMessage = 'Akun mekanik berhasil dibuat. Sampaikan username dan password awal secara pribadi.';
                        $accountMessageType = 'success';
                    } catch (PDOException $exception) {
                        $accountMessage = 'Akun gagal dibuat. Username mungkin sudah digunakan.';
                    }
                }
            }
        } elseif ($action === 'create_gl_account' && $isMaster) {
            $glName = trim((string) ($_POST['gl_name'] ?? ''));
            $newUsername = trim((string) ($_POST['username'] ?? ''));
            $newPassword = (string) ($_POST['password'] ?? '');
            if ($glName === '' || mb_strlen($glName, 'UTF-8') > 120) {
                $accountMessage = 'Nama GL wajib diisi (maksimal 120 karakter).';
            } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $newUsername)) {
                $accountMessage = 'Username harus 3–64 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
            } elseif (!hasMinimumPasswordLength($newPassword)) {
                $accountMessage = 'Password minimal 5 karakter.';
            } else {
                $glSite = (string) ($_POST['gl_site'] ?? '');
                if (!in_array($glSite, ['ADRO', 'AMM'], true)) {
                    $accountMessage = 'Pilih site GL: ADRO atau AMM.';
                } else try {
                    $insert = $database->prepare(
                        'INSERT INTO users (username, password_hash, role, mechanic_id, gl_name, gl_site)
                         VALUES (:username, :password_hash, \'gl\', NULL, :gl_name, :gl_site)'
                    );
                    $insert->execute([
                        ':username' => $newUsername,
                        ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                        ':gl_name' => $glName,
                        ':gl_site' => $glSite,
                    ]);
                    $accountMessage = 'Akun GL berhasil dibuat. Sampaikan username dan password awal secara pribadi.';
                    $accountMessageType = 'success';
                } catch (PDOException $exception) {
                    $accountMessage = 'Akun GL gagal dibuat. Username mungkin sudah digunakan.';
                }
            }
        } elseif ($action === 'save_behavior_assessment') {
            $mechanicId = filter_var($_POST['mechanic_id'] ?? null, FILTER_VALIDATE_INT);
            $submittedScores = $_POST['scores'] ?? [];
            $normalizedScores = [];
            if ($mechanicId === false || $mechanicId === null) {
                $assessmentMessage = 'Pilih mekanik yang akan dinilai.';
            } elseif (!$criteria || !is_array($submittedScores) || count($submittedScores) !== count($criteria)) {
                $assessmentMessage = 'Beri nilai pada seluruh kriteria sebelum menyimpan.';
            } else {
                foreach ($criteria as $criterion) {
                    $criterionId = (int) $criterion['id'];
                    $score = filter_var($submittedScores[$criterionId] ?? null, FILTER_VALIDATE_INT);
                    if ($score === false || $score < 1 || $score > 4) {
                        $assessmentMessage = 'Nilai setiap kriteria harus berada pada skala 1 sampai 4.';
                        break;
                    }
                    $normalizedScores[$criterionId] = $score;
                }
            }

            if ($assessmentMessage === '') {
                $mechanicCheck = $database->prepare('SELECT id FROM mechanics WHERE id = :id AND site = :site LIMIT 1');
                $mechanicCheck->execute([':id' => $mechanicId, ':site' => (string) ($identity['gl_site'] ?? '')]);
                if (!$mechanicCheck->fetchColumn()) {
                    $assessmentMessage = 'Mekanik tidak ditemukan atau berada di luar site akun GL ini.';
                } else {
                    $average = array_sum($normalizedScores) / count($normalizedScores);
                    $result = $average >= $passingGrade ? 'LULUS' : 'TIDAK LULUS';
                    try {
                        $database->beginTransaction();
                        $insertAssessment = $database->prepare(
                            'INSERT INTO behavior_assessments (mechanic_id, master_user_id, average_score, passing_grade, result)
                             VALUES (:mechanic_id, :master_user_id, :average_score, :passing_grade, :result)'
                        );
                        $insertAssessment->execute([
                            ':mechanic_id' => $mechanicId,
                            ':master_user_id' => (int) $identity['id'],
                            ':average_score' => round($average, 2),
                            ':passing_grade' => $passingGrade,
                            ':result' => $result,
                        ]);
                        $assessmentId = (int) $database->lastInsertId();
                        $insertScore = $database->prepare(
                            'INSERT INTO behavior_assessment_scores (assessment_id, criterion_id, score)
                             VALUES (:assessment_id, :criterion_id, :score)'
                        );
                        foreach ($normalizedScores as $criterionId => $score) {
                            $insertScore->execute([
                                ':assessment_id' => $assessmentId,
                                ':criterion_id' => $criterionId,
                                ':score' => $score,
                            ]);
                        }
                        $database->commit();
                        $assessmentMessage = 'Penilaian tersimpan. Hasil: ' . $result . ' (rata-rata ' . number_format($average, 2, ',', '.') . ').';
                        $assessmentMessageType = 'success';
                    } catch (Throwable $exception) {
                        if ($database->inTransaction()) {
                            $database->rollBack();
                        }
                        error_log('Behavior assessment save failed: ' . $exception->getMessage());
                        $assessmentMessage = 'Penilaian gagal disimpan. Periksa database dan coba lagi.';
                    }
                }
            }
        }
    }

    if ($isMaster) {
        $mechanicQuery = $database->query(
            'SELECT m.id, m.technician_name, m.site, m.join_date,
                    ROUND(TIMESTAMPDIFF(SECOND, m.join_date, CURRENT_TIMESTAMP) / (365.2425 * 86400), 1) AS group_years,
                    COALESCE(m.grade_level, CASE
                      WHEN CURDATE() <= DATE_ADD(m.join_date, INTERVAL 24 MONTH) THEN "Baik Sekali"
                      WHEN CURDATE() <= DATE_ADD(m.join_date, INTERVAL 30 MONTH) THEN "Baik"
                      WHEN CURDATE() <= DATE_ADD(m.join_date, INTERVAL 36 MONTH) THEN "Cukup"
                      ELSE "Tidak Lulus"
                    END) AS grade_level,
                    CASE WHEN CURRENT_TIMESTAMP >= DATE_ADD(m.join_date, INTERVAL 2 YEAR)
                         THEN "Lulus" ELSE "Belum memenuhi: minimal 2 tahun" END AS status_group,
                    a.passing_duration_months, a.atr_percent, a.technical_service_reports_per_month,
                    a.stpr_count, a.training_score, a.individual_improvements, a.job_experience_count,
                    a.disciplinary_letters, a.tlu_competencies_passed,
                    a.status_duration, a.status_atr, a.status_service_report, a.status_stpr,
                    a.status_training, a.status_improvement, a.status_experience, a.status_sp, a.status_tlu,
                    (SELECT ba.result FROM behavior_assessments AS ba
                     WHERE ba.mechanic_id = m.id ORDER BY ba.assessed_at DESC, ba.id DESC LIMIT 1) AS behavior_result,
                    (SELECT ba.average_score FROM behavior_assessments AS ba
                     WHERE ba.mechanic_id = m.id ORDER BY ba.assessed_at DESC, ba.id DESC LIMIT 1) AS behavior_average,
                    CASE
                      WHEN a.mechanic_id IS NULL THEN "BELUM DINILAI"
                      WHEN CURRENT_TIMESTAMP < DATE_ADD(m.join_date, INTERVAL 2 YEAR)
                        OR CURDATE() > DATE_ADD(m.join_date, INTERVAL 36 MONTH)
                        OR a.status_duration <> "Lulus" OR a.status_atr <> "Lulus"
                        OR a.status_service_report <> "Lulus"
                        OR CASE
                             WHEN EXISTS (SELECT 1 FROM behavior_assessments AS ba WHERE ba.mechanic_id = m.id)
                               THEN (SELECT ba.result FROM behavior_assessments AS ba
                                     WHERE ba.mechanic_id = m.id ORDER BY ba.assessed_at DESC, ba.id DESC LIMIT 1) <> "LULUS"
                             ELSE a.status_stpr <> "Lulus"
                           END
                        OR a.status_training <> "Lulus" OR a.status_improvement <> "Lulus"
                        OR a.status_experience <> "Lulus" OR a.status_sp <> "Lulus"
                        OR a.status_tlu <> "Lulus" THEN "TIDAK LULUS"
                      ELSE "LULUS"
                    END AS final_result
             FROM mechanics AS m
             LEFT JOIN certification_assessments AS a ON a.mechanic_id = m.id
             ORDER BY m.technician_name'
        );
        $mechanics = $mechanicQuery->fetchAll();
        $unassignedMechanics = $database->query(
            'SELECT m.id, m.technician_name FROM mechanics AS m
             LEFT JOIN users AS u ON u.mechanic_id = m.id
             WHERE u.id IS NULL ORDER BY m.technician_name'
        )->fetchAll();
    } elseif ($isGl) {
        $mechanicListQuery = $database->prepare(
            'SELECT m.id, m.technician_name, m.site, m.grade_level, m.join_date,
                    a.atr_percent, a.training_score, a.status_tlu,
                    (SELECT ba.result FROM behavior_assessments AS ba
                     WHERE ba.mechanic_id = m.id ORDER BY ba.assessed_at DESC, ba.id DESC LIMIT 1) AS behavior_result,
                    (SELECT ba.average_score FROM behavior_assessments AS ba
                     WHERE ba.mechanic_id = m.id ORDER BY ba.assessed_at DESC, ba.id DESC LIMIT 1) AS behavior_average
             FROM mechanics AS m
             LEFT JOIN certification_assessments AS a ON a.mechanic_id = m.id
             WHERE m.site = :site ORDER BY m.technician_name'
        );
        $mechanicListQuery->execute([':site' => (string) $identity['gl_site']]);
        $mechanics = $mechanicListQuery->fetchAll();
        $assessmentQuery = $database->prepare(
            'SELECT ba.id, ba.average_score, ba.passing_grade, ba.result, ba.assessed_at, m.technician_name
             FROM behavior_assessments AS ba
             JOIN mechanics AS m ON m.id = ba.mechanic_id
             WHERE ba.master_user_id = :gl_user_id AND m.site = :site
             ORDER BY ba.assessed_at DESC, ba.id DESC'
        );
        $assessmentQuery->execute([':gl_user_id' => (int) $identity['id'], ':site' => (string) $identity['gl_site']]);
        $assessments = $assessmentQuery->fetchAll();
    } elseif ($identity['mechanic_id'] !== null) {
        $profileQuery = $database->prepare(
            'SELECT m.join_date,
                    ROUND(TIMESTAMPDIFF(SECOND, m.join_date, CURRENT_TIMESTAMP) / (365.2425 * 86400), 1) AS group_years,
                    a.passing_duration_months, a.atr_percent, a.training_score, a.individual_improvements
             FROM mechanics AS m
             LEFT JOIN certification_assessments AS a ON a.mechanic_id = m.id
             WHERE m.id = :mechanic_id LIMIT 1'
        );
        $profileQuery->execute([':mechanic_id' => (int) $identity['mechanic_id']]);
        $mechanicProfile = $profileQuery->fetch() ?: null;
        $latest = $database->prepare(
            'SELECT result, assessed_at FROM behavior_assessments
             WHERE mechanic_id = :mechanic_id ORDER BY assessed_at DESC, id DESC LIMIT 1'
        );
        $latest->execute([':mechanic_id' => (int) $identity['mechanic_id']]);
        $mechanicAssessment = $latest->fetch() ?: null;
    }
    $asOf = (string) $database->query('SELECT CURRENT_TIMESTAMP')->fetchColumn();
} catch (Throwable $exception) {
    error_log('Certification dashboard database error: ' . $exception->getMessage());
    $databaseError = true;
}

$total = count($mechanics);
$passed = count(array_filter($mechanics, static fn(array $row): bool => ($row['final_result'] ?? '') === 'LULUS'));
$failed = count(array_filter($mechanics, static fn(array $row): bool => ($row['final_result'] ?? '') === 'TIDAK LULUS'));
$pending = $total - $passed - $failed;
$criteriaSummary = [
    ['status_group', 'Masa Golongan (minimal 2 tahun)', 'group_years', 'tahun'],
    ['status_duration', 'Passing Duration', 'passing_duration_months', 'bulan'],
    ['status_atr', 'ATR', 'atr_percent', '%'],
    ['status_service_report', 'Technical Service Report', 'technical_service_reports_per_month', '/bulan'],
    ['status_stpr', 'STPR', 'stpr_count', 'poin'],
    ['status_training', 'Nilai Training', 'training_score', 'poin'],
    ['status_improvement', 'Individual Improvement', 'individual_improvements', 'kali'],
    ['status_experience', 'Job Experience', 'job_experience_count', 'kali'],
    ['status_sp', 'SP selama TLU', 'disciplinary_letters', 'jumlah'],
    ['status_tlu', 'Kompetensi lulus TLU', 'tlu_competencies_passed', 'kompetensi'],
];
$displayRole = $identity['role'] ?? ($_SESSION['role'] ?? 'mechanic');
$toolbarTitle = 'Product Support · People Development';
$toolbarRoleLabel = in_array($displayRole, ['master', 'admin'], true)
    ? 'Master'
    : ($displayRole === 'gl' ? 'GL ' . ($identity['gl_name'] ?? '') . ' · ' . ($identity['gl_site'] ?? '') : 'Mekanik');
$toolbarUserLabel = (string) ($_SESSION['username'] ?? 'Pengguna') . ' · ' . $toolbarRoleLabel;
$toolbarActions = [['href' => 'modules.php', 'label' => 'Kembali ke Pilih Modul']];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sertifikasi Mekanik | PT Star Wagen Indonesia</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/toolbar.css?v=20261015">
</head>
<body>
  <main class="shell">
    <?php require __DIR__ . '/views/topbar.php'; ?>

    <?php if ($databaseError): ?>
      <div class="notice error" role="alert">Data sertifikasi belum dapat dimuat. Pastikan seluruh migrasi database, termasuk migrasi site mechanic dan GL, sudah dijalankan.</div>
    <?php elseif ($isMaster): ?>
      <?php require __DIR__ . '/views/master-dashboard.php'; ?>
    <?php elseif ($isGl): ?>
      <?php require __DIR__ . '/views/gl-dashboard.php'; ?>
    <?php else: ?>
      <?php require __DIR__ . '/views/mechanic-dashboard.php'; ?><?php endif; ?>
    <footer>Dashboard Sertifikasi Mekanik · TLU</footer>
  </main>
  <?php if ($isMaster): ?><script src="assets/js/dashboard.js" defer></script><?php endif; ?>
</body>
</html>
