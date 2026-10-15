-- Jalankan satu kali di certification_db untuk menyiapkan matriks penilaian.
USE certification_db;

UPDATE users SET role = 'master' WHERE role = 'admin';

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
