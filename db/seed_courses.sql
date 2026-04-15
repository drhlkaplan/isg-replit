-- ISG Eğitim Modülü — Kurs Seed Verisi
-- Bu dosya mevcut veritabanlarına kurs verilerini eklemek için kullanılır.
-- INSERT IGNORE kullanıldığı için güvenle tekrar çalıştırılabilir.

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

USE isg_lms;

-- course_categories refresh_period_years güncellemeleri
UPDATE course_categories SET refresh_period_years=3 WHERE id=1;
UPDATE course_categories SET refresh_period_years=2 WHERE id=2;
UPDATE course_categories SET refresh_period_years=1 WHERE id=3;

-- ============================================================
-- STANDART EĞİTİM PAKETLERİ (6 paket)
-- ============================================================
INSERT IGNORE INTO courses (id, category_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(1,  1, 'Az Tehlikeli İş Yerleri İçin Temel İSG Eğitim Paketi (8 Ders Saati)',    'Az tehlikeli işyerleri için 360 dakika temel İSG eğitim paketi.',       'temel',  'paket', 'online', 'genel', 1, 360, 'active'),
(2,  2, 'Tehlikeli İş Yerleri İçin Temel İSG Eğitim Paketi (12 Ders Saati)',      'Tehlikeli işyerleri için 540 dakika temel İSG eğitim paketi.',          'temel',  'paket', 'online', 'genel', 2, 540, 'active'),
(3,  3, 'Çok Tehlikeli İş Yerleri İçin Temel İSG Eğitim Paketi (16 Ders Saati)', 'Çok tehlikeli işyerleri için 720 dakika temel İSG eğitim paketi.',      'temel',  'paket', 'online', 'genel', 3, 720, 'active'),
(4,  1, 'Az Tehlikeli İş Yerleri İçin Tekrar İSG Eğitim Paketi (8 Ders Saati)',  'Az tehlikeli işyerleri için 360 dakika yenileme İSG eğitim paketi.',    'tekrar', 'paket', 'online', 'genel', 1, 360, 'active'),
(5,  2, 'Tehlikeli İş Yerleri İçin Tekrar İSG Eğitim Paketi (8 Ders Saati)',     'Tehlikeli işyerleri için 360 dakika yenileme İSG eğitim paketi.',       'tekrar', 'paket', 'online', 'genel', 2, 360, 'active'),
(6,  3, 'Çok Tehlikeli İş Yerleri İçin Tekrar İSG Eğitim Paketi (8 Ders Saati)','Çok tehlikeli işyerleri için 360 dakika yenileme İSG eğitim paketi.',  'tekrar', 'paket', 'online', 'genel', 3, 360, 'active');

-- ============================================================
-- İŞYERİNE ÖZGÜ VARYANT PAKETLERİ (8 paket)
-- ============================================================
INSERT IGNORE INTO courses (id, category_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(7,  3, 'Gratis Depo — Çok Tehlikeli Temel İSG Eğitim Paketi (16 Ders Saati)',   'Gratis Depo işyerine özgü çok tehlikeli temel İSG paketi.',             'temel',  'paket', 'online', 'gratis_depo',   4, 720, 'active'),
(8,  3, 'Gratis Depo — Çok Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)',   'Gratis Depo işyerine özgü çok tehlikeli tekrar İSG paketi.',            'tekrar', 'paket', 'online', 'gratis_depo',   4, 360, 'active'),
(9,  2, 'Beauty — Tehlikeli Temel İSG Eğitim Paketi (12 Ders Saati)',            'Beauty/Kozmetik işyerine özgü tehlikeli temel İSG paketi.',             'temel',  'paket', 'online', 'beauty',        5, 540, 'active'),
(10, 2, 'Beauty — Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)',            'Beauty/Kozmetik işyerine özgü tehlikeli tekrar İSG paketi.',            'tekrar', 'paket', 'online', 'beauty',        5, 360, 'active'),
(11, 1, 'Gratis Mağaza — Az Tehlikeli Temel İSG Eğitim Paketi (8 Ders Saati)',  'Gratis Mağaza işyerine özgü az tehlikeli temel İSG paketi.',            'temel',  'paket', 'online', 'gratis_magaza', 6, 360, 'active'),
(12, 1, 'Gratis Mağaza — Az Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)', 'Gratis Mağaza işyerine özgü az tehlikeli tekrar İSG paketi.',           'tekrar', 'paket', 'online', 'gratis_magaza', 6, 360, 'active'),
(13, 1, 'Mutfak — Az Tehlikeli Temel İSG Eğitim Paketi (8 Ders Saati)',         'Mutfak/Yemek servisi işyerine özgü az tehlikeli temel İSG paketi.',     'temel',  'paket', 'online', 'mutfak',        7, 360, 'active'),
(14, 1, 'Mutfak — Az Tehlikeli Tekrar İSG Eğitim Paketi (8 Ders Saati)',        'Mutfak/Yemek servisi işyerine özgü az tehlikeli tekrar İSG paketi.',    'tekrar', 'paket', 'online', 'mutfak',        7, 360, 'active');

-- ============================================================
-- MODÜLLER — Paket 1: Az Tehlikeli Temel (IDs 1001–1006)
-- NOTE: IDs 11-14 are taken by workplace packages; P1 modules use 1001+ range.
-- ============================================================
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(1001, 1, 1, 'Az Tehlikeli İSG Ön Değerlendirme Sınavı',           'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'temel', 'on_degerlendirme', 'online', 'genel', 1, 20,  'active'),
(1002, 1, 1, 'Az Tehlikeli İSG Genel Konular Eğitimi',             'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'temel', 'genel',            'online', 'genel', 2, 45,  'active'),
(1003, 1, 1, 'Az Tehlikeli İSG Sağlık Konuları Eğitimi',           'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'temel', 'saglik',           'online', 'genel', 3, 90,  'active'),
(1004, 1, 1, 'Az Tehlikeli İSG Teknik Konular Eğitimi',            'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD.',                'temel', 'teknik',           'online', 'genel', 4, 135, 'active'),
(1005, 1, 1, 'Az Tehlikeli İSG İşe ve İşyerine Özgü Konular',     'Risk değerlendirmesi, acil durum, işyeri tehlikeleri. (2 ders saati)',           'temel', 'ise_ozgu',         'online', 'genel', 5, 90,  'active'),
(1006, 1, 1, 'Az Tehlikeli İSG Eğitimi Final Sınavı',              'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'temel', 'final_sinav',      'online', 'genel', 6, 30,  'active');

-- Paket 2: Tehlikeli Temel
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(21, 2, 2, 'Tehlikeli İSG Ön Değerlendirme Sınavı',              'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'temel', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(22, 2, 2, 'Tehlikeli İSG Genel Konular Eğitimi',                'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'temel', 'genel',            'online',    'genel', 2, 90,  'active'),
(23, 2, 2, 'Tehlikeli İSG Sağlık Konuları Eğitimi',              'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'temel', 'saglik',           'online',    'genel', 3, 90,  'active'),
(24, 2, 2, 'Tehlikeli İSG Teknik Konular Eğitimi',               'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD, yüksek risk.',   'temel', 'teknik',           'online',    'genel', 4, 225, 'active'),
(25, 2, 2, 'Tehlikeli İSG İşe ve İşyerine Özgü Konular',        'Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (3 ders)', 'temel', 'ise_ozgu',         'yuz_yuze',  'genel', 5, 135, 'active'),
(26, 2, 2, 'Tehlikeli İSG Eğitimi Final Sınavı',                 'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'temel', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- Paket 3: Çok Tehlikeli Temel
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(31, 3, 3, 'Çok Tehlikeli İSG Ön Değerlendirme Sınavı',          'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'temel', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(32, 3, 3, 'Çok Tehlikeli İSG Genel Konular Eğitimi',            'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'temel', 'genel',            'online',    'genel', 2, 90,  'active'),
(33, 3, 3, 'Çok Tehlikeli İSG Sağlık Konuları Eğitimi',          'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'temel', 'saglik',           'online',    'genel', 3, 90,  'active'),
(34, 3, 3, 'Çok Tehlikeli İSG Teknik Konular Eğitimi',           'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD, yüksek risk.',   'temel', 'teknik',           'online',    'genel', 4, 360, 'active'),
(35, 3, 3, 'Çok Tehlikeli İSG İşe ve İşyerine Özgü Konular',    'Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (4 ders)', 'temel', 'ise_ozgu',         'yuz_yuze',  'genel', 5, 180, 'active'),
(36, 3, 3, 'Çok Tehlikeli İSG Eğitimi Final Sınavı',             'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'temel', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- Paket 4: Az Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(41, 1, 4, 'Az Tehlikeli İSG Ön Değerlendirme Sınavı (Tekrar)',  'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'tekrar', 'on_degerlendirme', 'online', 'genel', 1, 20,  'active'),
(42, 1, 4, 'Az Tehlikeli İSG Genel Konular Eğitimi (Tekrar)',    'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'tekrar', 'genel',            'online', 'genel', 2, 45,  'active'),
(43, 1, 4, 'Az Tehlikeli İSG Sağlık Konuları Eğitimi (Tekrar)', 'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'tekrar', 'saglik',           'online', 'genel', 3, 90,  'active'),
(44, 1, 4, 'Az Tehlikeli İSG Teknik Konular Eğitimi (Tekrar)',   'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik, KKD.',                'tekrar', 'teknik',           'online', 'genel', 4, 135, 'active'),
(45, 1, 4, 'Az Tehlikeli İSG İşe ve İşyerine Özgü Konular (Tekrar)', 'Risk değerlendirmesi, acil durum, işyeri tehlikeleri. (2 ders saati)',     'tekrar', 'ise_ozgu',         'online', 'genel', 5, 90,  'active'),
(46, 1, 4, 'Az Tehlikeli İSG Final Sınavı (Tekrar)',             'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'tekrar', 'final_sinav',      'online', 'genel', 6, 30,  'active');

-- Paket 5: Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(51, 2, 5, 'Tehlikeli İSG Ön Değerlendirme Sınavı (Tekrar)',     'Ön bilgi ölçümü — başarı barajı yoktur.',                                       'tekrar', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(52, 2, 5, 'Tehlikeli İSG Genel Konular Eğitimi (Tekrar)',       'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.', 'tekrar', 'genel',            'online',    'genel', 2, 45,  'active'),
(53, 2, 5, 'Tehlikeli İSG Sağlık Konuları Eğitimi (Tekrar)',     'Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                         'tekrar', 'saglik',           'online',    'genel', 3, 90,  'active'),
(54, 2, 5, 'Tehlikeli İSG Teknik Konular Eğitimi (Tekrar)',      'Kimyasal/fiziksel riskler, elle taşıma, yangın, elektrik.',                     'tekrar', 'teknik',           'online',    'genel', 4, 90,  'active'),
(55, 2, 5, 'Tehlikeli İSG İşe ve İşyerine Özgü Konular (Tekrar)','Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (3 ders)','tekrar', 'ise_ozgu',         'yuz_yuze',  'genel', 5, 135, 'active'),
(56, 2, 5, 'Tehlikeli İSG Final Sınavı (Tekrar)',                'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                              'tekrar', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- Paket 6: Çok Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(61, 3, 6, 'Çok Tehlikeli İSG Ön Değerlendirme Sınavı (Tekrar)','Ön bilgi ölçümü — başarı barajı yoktur.',                                        'tekrar', 'on_degerlendirme', 'online',    'genel', 1, 20,  'active'),
(62, 3, 6, 'Çok Tehlikeli İSG Genel Konular Eğitimi (Tekrar)',  'Çalışma mevzuatı, yasal haklar, işyeri temizliği, iş kazası hukuki sonuçları.',  'tekrar', 'genel',            'online',    'genel', 2, 45,  'active'),
(63, 3, 6, 'Çok Tehlikeli İSG Sağlık Konuları Eğitimi (Tekrar)','Meslek hastalıkları, korunma, ilk yardım, bağımlılık.',                          'tekrar', 'saglik',           'online',    'genel', 3, 90,  'active'),
(64, 3, 6, 'Çok Tehlikeli İSG Teknik Konular Eğitimi (Tekrar)', 'Temel teknik konular özeti.',                                                     'tekrar', 'teknik',           'online',    'genel', 4, 45,  'active'),
(65, 3, 6, 'Çok Tehlikeli İSG İşe ve İşyerine Özgü Konular (Tekrar)','Risk değerlendirmesi, işyeri tehlikeleri. ÖRGÜN — Yüz yüze zorunlu. (4 ders)','tekrar','ise_ozgu',       'yuz_yuze',  'genel', 5, 180, 'active'),
(66, 3, 6, 'Çok Tehlikeli İSG Final Sınavı (Tekrar)',           'Final sınavı — min. 60 puan, 2 ek deneme hakkı.',                               'tekrar', 'final_sinav',      'online',    'genel', 6, 30,  'active');

-- Paket 7: Gratis Depo Çok Tehlikeli Temel
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(71, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Ön Değerlendirme Sınavı',    'Ön bilgi ölçümü.',                                                       'temel', 'on_degerlendirme', 'online',    'gratis_depo', 1, 20,  'active'),
(72, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Genel Konular',              'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                       'temel', 'genel',            'online',    'gratis_depo', 2, 90,  'active'),
(73, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Sağlık Konuları',            'Meslek hastalıkları, korunma, ilk yardım.',                               'temel', 'saglik',           'online',    'gratis_depo', 3, 90,  'active'),
(74, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Teknik Konular',             'Kimyasal/fiziksel riskler, KKD, ekipman güvenliği.',                      'temel', 'teknik',           'online',    'gratis_depo', 4, 360, 'active'),
(75, 3, 7, 'Gratis Depo — İşe ve İşyerine Özgü Konular',                 'Depo güvenliği, yüksekte çalışma, yangın, ekipman. ÖRGÜN (4 ders).',      'temel', 'ise_ozgu',         'yuz_yuze',  'gratis_depo', 5, 180, 'active'),
(76, 3, 7, 'Gratis Depo — Çok Tehlikeli İSG Final Sınavı',               'Final sınavı — min. 60 puan.',                                            'temel', 'final_sinav',      'online',    'gratis_depo', 6, 30,  'active');

-- Paket 8: Gratis Depo Çok Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(81, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Ön Değerlendirme (Tekrar)',  'Ön bilgi ölçümü.',                                                       'tekrar', 'on_degerlendirme', 'online',    'gratis_depo', 1, 20,  'active'),
(82, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Genel Konular (Tekrar)',     'Çalışma mevzuatı, yasal haklar.',                                         'tekrar', 'genel',            'online',    'gratis_depo', 2, 45,  'active'),
(83, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Sağlık Konuları (Tekrar)',   'Meslek hastalıkları, korunma, ilk yardım.',                               'tekrar', 'saglik',           'online',    'gratis_depo', 3, 90,  'active'),
(84, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Teknik Konular (Tekrar)',    'Temel teknik konular özeti.',                                             'tekrar', 'teknik',           'online',    'gratis_depo', 4, 45,  'active'),
(85, 3, 8, 'Gratis Depo — İşe ve İşyerine Özgü Konular (Tekrar)',        'Depo güvenliği, yüksekte çalışma, yangın, ekipman. ÖRGÜN (4 ders).',      'tekrar', 'ise_ozgu',         'yuz_yuze',  'gratis_depo', 5, 180, 'active'),
(86, 3, 8, 'Gratis Depo — Çok Tehlikeli İSG Final Sınavı (Tekrar)',      'Final sınavı — min. 60 puan.',                                            'tekrar', 'final_sinav',      'online',    'gratis_depo', 6, 30,  'active');

-- Paket 9: Beauty Tehlikeli Temel
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(91, 2, 9, 'Beauty — Tehlikeli İSG Ön Değerlendirme Sınavı',             'Ön bilgi ölçümü.',                                                        'temel', 'on_degerlendirme', 'online',    'beauty', 1, 20,  'active'),
(92, 2, 9, 'Beauty — Tehlikeli İSG Genel Konular',                       'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                        'temel', 'genel',            'online',    'beauty', 2, 90,  'active'),
(93, 2, 9, 'Beauty — Tehlikeli İSG Sağlık Konuları',                     'Meslek hastalıkları, korunma, ilk yardım.',                                'temel', 'saglik',           'online',    'beauty', 3, 90,  'active'),
(94, 2, 9, 'Beauty — Tehlikeli İSG Teknik Konular',                      'Kimyasal/fiziksel riskler, KKD, ekipman güvenliği.',                       'temel', 'teknik',           'online',    'beauty', 4, 225, 'active'),
(95, 2, 9, 'Beauty — İşe ve İşyerine Özgü Konular',                     'Hijyen, yangın, kimyasal/biyolojik etkenler, ekipman. ÖRGÜN (3 ders).',    'temel', 'ise_ozgu',         'yuz_yuze',  'beauty', 5, 135, 'active'),
(96, 2, 9, 'Beauty — Tehlikeli İSG Final Sınavı',                        'Final sınavı — min. 60 puan.',                                             'temel', 'final_sinav',      'online',    'beauty', 6, 30,  'active');

-- Paket 10: Beauty Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(101, 2, 10, 'Beauty — Tehlikeli İSG Ön Değerlendirme (Tekrar)', 'Ön bilgi ölçümü.',                                                               'tekrar', 'on_degerlendirme', 'online',    'beauty', 1, 20,  'active'),
(102, 2, 10, 'Beauty — Tehlikeli İSG Genel Konular (Tekrar)',    'Çalışma mevzuatı, yasal haklar.',                                                 'tekrar', 'genel',            'online',    'beauty', 2, 45,  'active'),
(103, 2, 10, 'Beauty — Tehlikeli İSG Sağlık Konuları (Tekrar)', 'Meslek hastalıkları, korunma, ilk yardım.',                                        'tekrar', 'saglik',           'online',    'beauty', 3, 90,  'active'),
(104, 2, 10, 'Beauty — Tehlikeli İSG Teknik Konular (Tekrar)',  'Kimyasal/fiziksel riskler, KKD.',                                                  'tekrar', 'teknik',           'online',    'beauty', 4, 90,  'active'),
(105, 2, 10, 'Beauty — İşe ve İşyerine Özgü Konular (Tekrar)', 'Hijyen, yangın, kimyasal/biyolojik etkenler, ekipman. ÖRGÜN (3 ders).',             'tekrar', 'ise_ozgu',         'yuz_yuze',  'beauty', 5, 135, 'active'),
(106, 2, 10, 'Beauty — Tehlikeli İSG Final Sınavı (Tekrar)',    'Final sınavı — min. 60 puan.',                                                     'tekrar', 'final_sinav',      'online',    'beauty', 6, 30,  'active');

-- Paket 11: Gratis Mağaza Az Tehlikeli Temel
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(111, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Ön Değerlendirme',        'Ön bilgi ölçümü.',                                                        'temel', 'on_degerlendirme', 'online', 'gratis_magaza', 1, 20,  'active'),
(112, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Genel Konular',           'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                        'temel', 'genel',            'online', 'gratis_magaza', 2, 45,  'active'),
(113, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Sağlık Konuları',         'Meslek hastalıkları, korunma, ilk yardım.',                                'temel', 'saglik',           'online', 'gratis_magaza', 3, 90,  'active'),
(114, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Teknik Konular',          'Kimyasal/fiziksel riskler, elle taşıma, yangın.',                          'temel', 'teknik',           'online', 'gratis_magaza', 4, 135, 'active'),
(115, 1, 11, 'Gratis Mağaza — İşe ve İşyerine Özgü Konular',            'Mağaza güvenliği, yüksekte çalışma, yangın, depolama, ergonomi.',           'temel', 'ise_ozgu',         'online', 'gratis_magaza', 5, 90,  'active'),
(116, 1, 11, 'Gratis Mağaza — Az Tehlikeli İSG Final Sınavı',            'Final sınavı — min. 60 puan.',                                             'temel', 'final_sinav',      'online', 'gratis_magaza', 6, 30,  'active');

-- Paket 12: Gratis Mağaza Az Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(121, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Ön Değerlendirme (Tekrar)','Ön bilgi ölçümü.',                                                      'tekrar', 'on_degerlendirme', 'online', 'gratis_magaza', 1, 20,  'active'),
(122, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Genel Konular (Tekrar)',  'Çalışma mevzuatı, yasal haklar.',                                          'tekrar', 'genel',            'online', 'gratis_magaza', 2, 45,  'active'),
(123, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Sağlık Konuları (Tekrar)','Meslek hastalıkları, korunma, ilk yardım.',                               'tekrar', 'saglik',           'online', 'gratis_magaza', 3, 90,  'active'),
(124, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Teknik Konular (Tekrar)','Kimyasal/fiziksel riskler, elle taşıma, yangın.',                           'tekrar', 'teknik',           'online', 'gratis_magaza', 4, 135, 'active'),
(125, 1, 12, 'Gratis Mağaza — İşe ve İşyerine Özgü Konular (Tekrar)',   'Mağaza güvenliği, yüksekte çalışma, yangın, depolama, ergonomi.',           'tekrar', 'ise_ozgu',         'online', 'gratis_magaza', 5, 90,  'active'),
(126, 1, 12, 'Gratis Mağaza — Az Tehlikeli İSG Final Sınavı (Tekrar)',  'Final sınavı — min. 60 puan.',                                              'tekrar', 'final_sinav',      'online', 'gratis_magaza', 6, 30,  'active');

-- Paket 13: Mutfak Az Tehlikeli Temel
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(131, 1, 13, 'Mutfak — Az Tehlikeli İSG Ön Değerlendirme',               'Ön bilgi ölçümü.',                                                        'temel', 'on_degerlendirme', 'online', 'mutfak', 1, 20,  'active'),
(132, 1, 13, 'Mutfak — Az Tehlikeli İSG Genel Konular',                  'Çalışma mevzuatı, yasal haklar, işyeri temizliği.',                        'temel', 'genel',            'online', 'mutfak', 2, 45,  'active'),
(133, 1, 13, 'Mutfak — Az Tehlikeli İSG Sağlık Konuları',                'Meslek hastalıkları, korunma, ilk yardım.',                                'temel', 'saglik',           'online', 'mutfak', 3, 90,  'active'),
(134, 1, 13, 'Mutfak — Az Tehlikeli İSG Teknik Konular',                 'Kimyasal/fiziksel riskler, elle taşıma, yangın.',                          'temel', 'teknik',           'online', 'mutfak', 4, 135, 'active'),
(135, 1, 13, 'Mutfak — İşe ve İşyerine Özgü Konular',                   'Kişisel hijyen, mutfak yangınları, kimyasal etkenler, mutfak ekipmanları.', 'temel', 'ise_ozgu',         'online', 'mutfak', 5, 90,  'active'),
(136, 1, 13, 'Mutfak — Az Tehlikeli İSG Final Sınavı',                   'Final sınavı — min. 60 puan.',                                             'temel', 'final_sinav',      'online', 'mutfak', 6, 30,  'active');

-- Paket 14: Mutfak Az Tehlikeli Tekrar
INSERT IGNORE INTO courses (id, category_id, parent_course_id, title, description, training_type, topic_type, delivery_method, workplace_variant, sort_order, duration_minutes, status) VALUES
(141, 1, 14, 'Mutfak — Az Tehlikeli İSG Ön Değerlendirme (Tekrar)',      'Ön bilgi ölçümü.',                                                        'tekrar', 'on_degerlendirme', 'online', 'mutfak', 1, 20,  'active'),
(142, 1, 14, 'Mutfak — Az Tehlikeli İSG Genel Konular (Tekrar)',         'Çalışma mevzuatı, yasal haklar.',                                          'tekrar', 'genel',            'online', 'mutfak', 2, 45,  'active'),
(143, 1, 14, 'Mutfak — Az Tehlikeli İSG Sağlık Konuları (Tekrar)',       'Meslek hastalıkları, korunma, ilk yardım.',                                'tekrar', 'saglik',           'online', 'mutfak', 3, 90,  'active'),
(144, 1, 14, 'Mutfak — Az Tehlikeli İSG Teknik Konular (Tekrar)',        'Kimyasal/fiziksel riskler, elle taşıma, yangın.',                          'tekrar', 'teknik',           'online', 'mutfak', 4, 135, 'active'),
(145, 1, 14, 'Mutfak — İşe ve İşyerine Özgü Konular (Tekrar)',          'Kişisel hijyen, mutfak yangınları, kimyasal etkenler, mutfak ekipmanları.', 'tekrar', 'ise_ozgu',         'online', 'mutfak', 5, 90,  'active'),
(146, 1, 14, 'Mutfak — Az Tehlikeli İSG Final Sınavı (Tekrar)',         'Final sınavı — min. 60 puan.',                                              'tekrar', 'final_sinav',      'online', 'mutfak', 6, 30,  'active');

SET FOREIGN_KEY_CHECKS = 1;
