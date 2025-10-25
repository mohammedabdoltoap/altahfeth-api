-- ✅ تعديل جدول users_attendance لإضافة ميزة التغطية

-- 1️⃣ إضافة عمود attendance_status (حالة الحضور)
ALTER TABLE users_attendance 
ADD COLUMN attendance_status TINYINT DEFAULT 1 COMMENT '1=حضور عادي, 3=تغطية';

-- 2️⃣ إضافة Index لتحسين الأداء
CREATE INDEX idx_attendance_status ON users_attendance(attendance_status);

-- ✅ ملاحظات:
-- - attendance_status = 1: حضور عادي (الأستاذ حضر بنفسه)
-- - attendance_status = 3: تغطية (تم تغطية الحلقة بدون تحديد من يغطي)
