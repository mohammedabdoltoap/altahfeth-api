<?php
include "../connect.php";

switch($_GET['mark'])
{

    case "updateDailyReport":updateDailyReport();
    break;
    case "updateReview":updateReview();
    break;
    case "update_visit_exam_result":update_visit_exam_result();
    break;
    case "update_notes_for_teacher":update_notes_for_teacher();
    break;

}

function updateDailyReport() {
    global $con;

    $data = json_decode(file_get_contents("php://input"), true);
    $id_daily_report = $data["id_daily_report"];

    // 🔹 نجيب القيم القديمة من قاعدة البيانات (بما في ذلك mark)
    $stmt = $con->prepare("SELECT to_id_soura, to_id_aya, mark,id_evaluation FROM daily_report WHERE id_daily_report = ?");
    $stmt->execute([$id_daily_report]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🔹 لو التقرير غير موجود
    if (!$old) {
        echo json_encode(["stat" => "not_found"]);
        return;
    }

    // 🔹 نتحقق إذا القيم الجديدة نفس القيم القديمة
    if (
        $old["to_id_soura"] == $data["to_id_soura"] &&
        $old["to_id_aya"] == $data["to_id_aya"] &&
        $old["mark"] == $data["mark"] && 
        $old["id_evaluation"]==$data["id_evaluation"]
    ) {
        // ✅ نفس البيانات، ما في داعي نقول فشل
        echo json_encode(["stat" => "ok", "msg" => "no_changes"]);
        return;
    }

    // 🔹 تنفيذ التحديث الفعلي
    $stmt = $con->prepare("UPDATE daily_report 
                           SET to_id_soura = ?, to_id_aya = ?, mark = ?,id_evaluation=?
                           WHERE id_daily_report = ?");
    $res = $stmt->execute([
        $data["to_id_soura"],
        $data["to_id_aya"],
        $data["mark"],
        $data["id_evaluation"],
        $id_daily_report
    ]);

    // 🔹 التحقق من نتيجة التنفيذ
    if ($res) {
        echo json_encode(["stat" => "ok"]);
    } else {
        echo json_encode(["stat" => "error"]);
    }
}


function updateReview() {
    global $con;

    $data = json_decode(file_get_contents("php://input"), true);
    $id_review = $data["id_review"];

    // 🔹 نجيب القيم القديمة من قاعدة البيانات
    $stmt = $con->prepare("SELECT to_id_soura, to_id_aya, mark,id_evaluation FROM review WHERE id_review = ?");
    $stmt->execute([$id_review]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$old) {
        echo json_encode(["stat" => "not_found"]);
        return;
    }

    // 🔹 نتحقق إذا القيم القديمة نفسها الجديدة
    if (
        $old["to_id_soura"] == $data["to_id_soura"] &&
        $old["to_id_aya"] == $data["to_id_aya"] &&
        $old["mark"] == $data["mark"] &&
        $old["id_evaluation"]==$data["id_evaluation"]
    ) {
        // ✅ القيم نفسها، نرجع ok عشان ما نحسس المستخدم انه خطأ
        echo json_encode(["stat" => "ok", "msg" => "no_changes"]);
        return;
    }

    // 🔹 التحديث الفعلي
    $stmt = $con->prepare("UPDATE review 
                           SET to_id_soura = ?, to_id_aya = ?, mark = ?,
                           id_evaluation=?
                           WHERE id_review = ?");
    $res = $stmt->execute([
        $data["to_id_soura"],
        $data["to_id_aya"],
        $data["mark"],
        $data["id_evaluation"],
        $id_review
    ]);

    if ($res) {
        echo json_encode(["stat" => "ok"]);
    } else {
        echo json_encode(["stat" => "error"]);
    }
}
function update_visit_exam_result() {
    global $con; // الاتصال بقاعدة البيانات
    try {
        // استلام البيانات من الـ JSON
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['id_result'])) {
            throw new Exception("لا توجد بيانات صالحة أو لم يتم تحديد نتيجة للتعديل");
        }

        // تجهيز الاستعلام للتحديث
        $stmt = $con->prepare("
            UPDATE visit_exam_result SET
                hifz_monthly = :hifz_monthly,
                tilawa_monthly = :tilawa_monthly,
                from_id_soura_monthly = :from_id_soura_monthly,
                to_id_soura_monthly = :to_id_soura_monthly,
                from_id_aya_monthly = :from_id_aya_monthly,
                to_id_aya_monthly = :to_id_aya_monthly,
                hifz_revision = :hifz_revision,
                tilawa_revision = :tilawa_revision,
                from_id_soura_revision = :from_id_soura_revision,
                to_id_soura_revision = :to_id_soura_revision,
                from_id_aya_revision = :from_id_aya_revision,
                to_id_aya_revision = :to_id_aya_revision,
                notes = :notes
            WHERE id_result = :id_result
        ");

        // ربط القيم
        $stmt->bindParam(":hifz_monthly", $data['hifz_monthly']);
        $stmt->bindParam(":tilawa_monthly", $data['tilawa_monthly']);
        $stmt->bindParam(":from_id_soura_monthly", $data['from_id_soura_monthly']);
        $stmt->bindParam(":to_id_soura_monthly", $data['to_id_soura_monthly']);
        $stmt->bindParam(":from_id_aya_monthly", $data['from_id_aya_monthly']);
        $stmt->bindParam(":to_id_aya_monthly", $data['to_id_aya_monthly']);
        $stmt->bindParam(":hifz_revision", $data['hifz_revision']);
        $stmt->bindParam(":tilawa_revision", $data['tilawa_revision']);
        $stmt->bindParam(":from_id_soura_revision", $data['from_id_soura_revision']);
        $stmt->bindParam(":to_id_soura_revision", $data['to_id_soura_revision']);
        $stmt->bindParam(":from_id_aya_revision", $data['from_id_aya_revision']);
        $stmt->bindParam(":to_id_aya_revision", $data['to_id_aya_revision']);
        $stmt->bindParam(":id_result", $data['id_result']);
        $stmt->bindParam(":notes", $data['notes']);

        // تنفيذ الاستعلام
        if ($stmt->execute()) {
            echo json_encode(array("stat" => "ok", "msg" => "تم تعديل النتائج بنجاح"));
        } else {
            echo json_encode(array("stat" => "no", "msg" => "فشل في تعديل النتائج"));
        }

    } catch (Exception $e) {
        echo json_encode(array("stat" => "error", "msg" => $e->getMessage()));
    }
}

function update_notes_for_teacher() {
    global $con; // الاتصال بقاعدة البيانات
    try {
        // استلام البيانات من الـ JSON
        $data = json_decode(file_get_contents("php://input"), true);

       

        // تجهيز الاستعلام للتحديث
        $stmt = $con->prepare("
            UPDATE notes_for_teacher SET
                notes = :notes
            WHERE id_notes = :id_notes
        ");

        // ربط القيم
        $stmt->bindParam(":notes", $data['notes']);
        $stmt->bindParam(":id_notes", $data['id_notes']);

        // تنفيذ الاستعلام
        if ($stmt->execute()) {
            echo json_encode(array("stat" => "ok", "msg" => "تم تعديل الملاحظة بنجاح"));
        } else {
            echo json_encode(array("stat" => "no", "msg" => "فشل في تعديل الملاحظة"));
        }

    } catch (Exception $e) {
        echo json_encode(array("stat" => "error", "msg" => $e->getMessage()));
    }
}
