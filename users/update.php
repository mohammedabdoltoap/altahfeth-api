<?php
include "../connect.php";

switch($_GET['mark'])
{

    case "updateDailyReport":updateDailyReport();
    break;
    case "updateReview":updateReview();
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
