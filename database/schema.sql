CREATE DATABASE IF NOT EXISTS certification_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE certification_db;

CREATE TABLE IF NOT EXISTS mechanics (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  technician_name VARCHAR(120) NOT NULL,
  grade_level VARCHAR(8) NULL,
  site VARCHAR(16) NOT NULL DEFAULT 'ADRO',
  join_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mechanics_name (technician_name)
);

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(16) NOT NULL DEFAULT 'master',
  mechanic_id BIGINT UNSIGNED NULL,
  gl_name VARCHAR(120) NULL,
  gl_site VARCHAR(16) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_mechanic_id (mechanic_id),
  CONSTRAINT fk_users_mechanic FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS manpower_base (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  source_no VARCHAR(32) NULL,
  employee_no VARCHAR(32) NOT NULL,
  employee_name VARCHAR(160) NOT NULL,
  division VARCHAR(120) NULL,
  position_name VARCHAR(120) NULL,
  grade_jis VARCHAR(40) NULL,
  grade_swi VARCHAR(40) NULL,
  company VARCHAR(80) NULL,
  poh VARCHAR(120) NULL,
  placement VARCHAR(120) NULL,
  allocation VARCHAR(40) NULL,
  onboard_date DATE NULL,
  employment_status VARCHAR(80) NULL,
  contract_no VARCHAR(40) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_manpower_employee_no (employee_no),
  KEY idx_manpower_placement (placement),
  KEY idx_manpower_status (employment_status)
);

CREATE TABLE IF NOT EXISTS certification_assessments (
  mechanic_id BIGINT UNSIGNED NOT NULL,
  passing_duration_months DECIMAL(5,1) NOT NULL,
  atr_percent DECIMAL(5,2) NOT NULL,
  technical_service_reports_per_month DECIMAL(5,1) NOT NULL,
  stpr_count SMALLINT UNSIGNED NOT NULL,
  training_score DECIMAL(5,1) NOT NULL,
  individual_improvements SMALLINT UNSIGNED NOT NULL,
  job_experience_count SMALLINT UNSIGNED NOT NULL,
  disciplinary_letters SMALLINT UNSIGNED NOT NULL,
  tlu_competencies_passed SMALLINT UNSIGNED NOT NULL,
  status_duration VARCHAR(16) NOT NULL,
  status_atr VARCHAR(16) NOT NULL,
  status_service_report VARCHAR(16) NOT NULL,
  status_stpr VARCHAR(16) NOT NULL,
  status_training VARCHAR(16) NOT NULL,
  status_improvement VARCHAR(16) NOT NULL,
  status_experience VARCHAR(16) NOT NULL,
  status_sp VARCHAR(16) NOT NULL,
  status_tlu VARCHAR(16) NOT NULL,
  assessed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (mechanic_id),
  CONSTRAINT fk_assessment_mechanic FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE,
  CONSTRAINT chk_atr_range CHECK (atr_percent >= 0 AND atr_percent <= 100),
  CONSTRAINT chk_training_range CHECK (training_score >= 0 AND training_score <= 100)
);

CREATE TABLE IF NOT EXISTS certification_standards (
  criterion_key VARCHAR(40) NOT NULL,
  criterion_label VARCHAR(120) NOT NULL,
  baik_sekali_standard VARCHAR(120) NOT NULL,
  baik_standard VARCHAR(120) NOT NULL,
  cukup_standard VARCHAR(120) NOT NULL,
  data_owner VARCHAR(80) NOT NULL,
  collection_period VARCHAR(160) NOT NULL,
  PRIMARY KEY (criterion_key)
);

CREATE TABLE IF NOT EXISTS behavior_criteria (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(80) NOT NULL,
  criterion_name VARCHAR(120) NOT NULL,
  behavior_indicator TEXT NOT NULL,
  display_order TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_behavior_criterion_name (criterion_name),
  UNIQUE KEY uq_behavior_criterion_order (display_order)
);

CREATE TABLE IF NOT EXISTS behavior_assessments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  mechanic_id BIGINT UNSIGNED NOT NULL,
  master_user_id BIGINT UNSIGNED NOT NULL,
  average_score DECIMAL(4,2) NOT NULL,
  passing_grade DECIMAL(3,2) NOT NULL DEFAULT 2.00,
  result VARCHAR(16) NOT NULL,
  assessed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_behavior_assessments_mechanic_date (mechanic_id, assessed_at),
  CONSTRAINT fk_behavior_assessment_mechanic FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE,
  CONSTRAINT fk_behavior_assessment_master FOREIGN KEY (master_user_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS behavior_assessment_scores (
  assessment_id BIGINT UNSIGNED NOT NULL,
  criterion_id TINYINT UNSIGNED NOT NULL,
  score TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (assessment_id, criterion_id),
  CONSTRAINT fk_behavior_score_assessment FOREIGN KEY (assessment_id) REFERENCES behavior_assessments(id) ON DELETE CASCADE,
  CONSTRAINT fk_behavior_score_criterion FOREIGN KEY (criterion_id) REFERENCES behavior_criteria(id) ON DELETE RESTRICT,
  CONSTRAINT chk_behavior_score_range CHECK (score BETWEEN 1 AND 4)
);

INSERT INTO behavior_criteria (id,category,criterion_name,behavior_indicator,display_order) VALUES
  (1,'Devotion to God Almighty','Ketakwaan kepada Tuhan Yang Maha Esa','Menunjukkan ketaatan untuk melaksanakan kewajiban kepada sang pencipta.',1),
  (2,'Open Mind','Keterbukaan','Mampu menerima masukan, saran dan kritik yang disampaikan orang lain, baik atasan, teman sejawat, ataupun bawahan.',2),
  (3,'Integrity','Integritas','Menunjukkan apa yang dikatakannya selalu sesuai dengan perbuatan, dan mengajak karyawan lain melakukan hal yang sama.',3),
  (4,'Team Work','Kerja Sama Tim','Menunjukkan kemauan yang positif untuk bekerja sama dengan karyawan lain untuk mencapai tujuan unit kerja.',4),
  (5,'Team Work','Komunikasi Tim','Menunjukkan kemampuan berkomunikasi dan koordinasi internal maupun eksternal departemen.',5),
  (6,'Serve','Etika Kerja','Memperlihatkan sikap hormat yang layak dalam berhubungan dengan orang lain.',6),
  (7,'Work Perfection','Kualitas Kerja','Menunjukkan hasil pekerjaan yang sesuai dengan target unit/departemen dengan tingkat kesalahan kerja yang rendah.',7),
  (8,'Work Perfection','Kuantitas Kerja','Menunjukkan jumlah pencapaian target kerja sesuai yang direncanakan.',8),
  (9,'Work Perfection','Profesionalisme','Cepat dan tanggap dalam menyelesaikan permasalahan kerja yang muncul (analisa pemecahan masalah).',9),
  (10,'Work Perfection','Kesehatan dan Keselamatan Kerja','Selalu peduli dan patuh terhadap ketentuan keselamatan, kesehatan dan kebersihan lingkungan kerjanya.',10),
  (11,'Innovative','Inisiatif','Memperlihatkan kemandirian dalam bekerja.',11),
  (12,'Innovative','Inovasi','Menunjukkan atau memberikan ide baru untuk mempermudah pekerjaan dengan tetap mengikuti SOP/standar keselamatan yang berlaku.',12)
ON DUPLICATE KEY UPDATE
  category=VALUES(category), behavior_indicator=VALUES(behavior_indicator), display_order=VALUES(display_order);

INSERT INTO certification_standards
  (criterion_key, criterion_label, baik_sekali_standard, baik_standard, cukup_standard, data_owner, collection_period)
VALUES
  ('group_years','Masa golongan minimal','≥ 2 tahun','≥ 2 tahun','≥ 2 tahun','SWI','-'),
  ('passing_duration','Passing duration minimum','≥ 22 bulan','≥ 22 bulan','≥ 22 bulan','SWI','-'),
  ('atr','ATR (attendance ratio)','≥ 98,5%','≥ 98,5%','≥ 98,5%','SWI','6 bulan sebelum periode TLU'),
  ('service_report','Technical Service Report / Workshop Report','≥ 2/bulan','≥ 2/bulan','≥ 2/bulan','SWI','6 bulan sebelum periode TLU'),
  ('stpr','STPR (Semester Technician Performance Report)','≥ 2','≥ 2','≥ 2','SWI','6 bulan sebelum periode TLU'),
  ('training','Nilai training','≥ 75','≥ 75','≥ 75','SWI','Sebelum periode TLU'),
  ('improvement','Individual Improvement','≥ 1','≥ 1','≥ 1','SWI','Sebelum periode TLU'),
  ('experience','Job experience','≥ 3 kali','≥ 3 kali','≥ 3 kali','SWI','3 tahun sebelum periode TLU di SWI'),
  ('sp','Tidak ada SP selama TLU','0','0','0','SWI','Sebelum periode TLU'),
  ('tlu','Lulus TLU','2 kompetensi / 1 MLU','2 kompetensi / 2 MLU','2 kompetensi / 3 MLU','SWI','-')
ON DUPLICATE KEY UPDATE
  criterion_label=VALUES(criterion_label), baik_sekali_standard=VALUES(baik_sekali_standard),
  baik_standard=VALUES(baik_standard), cukup_standard=VALUES(cukup_standard),
  data_owner=VALUES(data_owner), collection_period=VALUES(collection_period);

INSERT INTO mechanics (id, technician_name, grade_level, join_date, site) VALUES
  (1,'Teknisi 01','1a',DATE_SUB(CURDATE(), INTERVAL ROUND(1.8 * 365.2425) DAY),'ADRO'),
  (2,'Teknisi 02','1b',DATE_SUB(CURDATE(), INTERVAL ROUND(2.1 * 365.2425) DAY),'ADRO'),
  (3,'Teknisi 03','1c',DATE_SUB(CURDATE(), INTERVAL ROUND(2.6 * 365.2425) DAY),'ADRO'),
  (4,'Teknisi 04','1d',DATE_SUB(CURDATE(), INTERVAL ROUND(3.0 * 365.2425) DAY),'ADRO'),
  (5,'Teknisi 05','1e',DATE_SUB(CURDATE(), INTERVAL ROUND(2.4 * 365.2425) DAY),'ADRO'),
  (6,'Teknisi 06','f',DATE_SUB(CURDATE(), INTERVAL ROUND(1.5 * 365.2425) DAY),'AMM'),
  (7,'Teknisi 07','2a',DATE_SUB(CURDATE(), INTERVAL ROUND(2.9 * 365.2425) DAY),'AMM'),
  (8,'Teknisi 08','2b',DATE_SUB(CURDATE(), INTERVAL ROUND(3.2 * 365.2425) DAY),'AMM'),
  (9,'Teknisi 09','2c',DATE_SUB(CURDATE(), INTERVAL ROUND(2.0 * 365.2425) DAY),'AMM'),
  (10,'Teknisi 10','2d',DATE_SUB(CURDATE(), INTERVAL ROUND(2.7 * 365.2425) DAY),'AMM')
ON DUPLICATE KEY UPDATE
  technician_name=VALUES(technician_name), grade_level=VALUES(grade_level), join_date=VALUES(join_date), site=VALUES(site);

INSERT INTO certification_assessments
  (mechanic_id,passing_duration_months,atr_percent,technical_service_reports_per_month,stpr_count,
   training_score,individual_improvements,job_experience_count,disciplinary_letters,tlu_competencies_passed,
   status_duration,status_atr,status_service_report,status_stpr,status_training,
   status_improvement,status_experience,status_sp,status_tlu)
VALUES
  (1,24,99.2,3,3,88,2,4,0,3,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus'),
  (2,22,98.7,2,2,78,1,3,0,2,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus'),
  (3,22,97.9,2,2,72,1,3,0,2,'Lulus','Tidak Lulus','Lulus','Lulus','Tidak Lulus','Lulus','Lulus','Lulus','Lulus'),
  (4,22,96.8,2,1,70,0,3,0,2,'Lulus','Tidak Lulus','Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Lulus','Lulus','Lulus'),
  (5,23,98.5,2,2,82,1,3,0,2,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus'),
  (6,25,99.5,4,3,91,2,5,0,3,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus'),
  (7,22,98.5,2,2,76,1,3,0,2,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus'),
  (8,22,95.9,1,1,68,0,2,1,2,'Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Tidak Lulus','Lulus'),
  (9,24,98.9,3,2,80,1,4,0,2,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus'),
  (10,22,98.5,2,2,75,1,3,0,3,'Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus','Lulus')
ON DUPLICATE KEY UPDATE
  passing_duration_months=VALUES(passing_duration_months), atr_percent=VALUES(atr_percent),
  technical_service_reports_per_month=VALUES(technical_service_reports_per_month), stpr_count=VALUES(stpr_count),
  training_score=VALUES(training_score), individual_improvements=VALUES(individual_improvements),
  job_experience_count=VALUES(job_experience_count), disciplinary_letters=VALUES(disciplinary_letters),
  tlu_competencies_passed=VALUES(tlu_competencies_passed), status_duration=VALUES(status_duration),
  status_atr=VALUES(status_atr), status_service_report=VALUES(status_service_report),
  status_stpr=VALUES(status_stpr), status_training=VALUES(status_training),
  status_improvement=VALUES(status_improvement), status_experience=VALUES(status_experience),
  status_sp=VALUES(status_sp), status_tlu=VALUES(status_tlu);
