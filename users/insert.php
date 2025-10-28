<?php
include "../connect.php";



switch($_GET['mark'])
{

    case "addDailyReport":addDailyReport();
    break;
    case "addResignation":addResignation();
    break;
    case "addReview":addReview();
    break;
    case "add_check_in_time_usersAttendance":add_check_in_time_usersAttendance();
    break;
    case "add_check_out_time_usersAttendance":add_check_out_time_usersAttendance();
    break;
    case "add_substitute_attendance":
        add_substitute_attendance();
        break;
    
    case "add_administrative_visit":
        add_administrative_visit();
        break;
    case "insert_visits":insert_visits();
    break;
    case "insert_leave_requests":insert_leave_requests();
    break;
    case "insert_visit_exam_result":insert_visit_exam_result();
    break;
    case "insert_notes_for_teacher":insert_notes_for_teacher();
    break;



}




function addDailyReport(){
    global $con;
    $data=json_decode(file_get_contents("php://input"),true);
      insert($con,"daily_report",array(
        "id_student"=> $data["id_student"],
        "from_id_soura"=>$data["from_id_soura"],
        "from_id_aya"=>$data["from_id_aya"],
        "to_id_soura"=>$data["to_id_soura"],
        "to_id_aya"=>$data["to_id_aya"],
        "id_user"=>$data["id_user"],
        "id_circle"=>$data["id_circle"],
        "mark"=>$data["mark"],
        "id_evaluation"=>$data["id_evaluation"]
      ));   
    }
    
function addResignation(){
    global $con;  
    $data=json_decode(file_get_contents("php://input"),true);
     
    $res= selectAndFilter($con,"resignation",array(
        "id_user"=>$data["id_user"], 
        "statuse"=>1
      ));
      if($res==null){
          // تحضير البيانات للإدخال
          $resignationData = array(
              "id_user"=>$data["id_user"], 
             
          );
          
          // إضافة الحقول الاختيارية إذا كانت موجودة
          if(isset($data["resignation_date"]) && !empty($data["resignation_date"])){
              $resignationData["resignation_date"] = $data["resignation_date"];
          }
          
          if(isset($data["reason"]) && !empty($data["reason"])){
              $resignationData["reason"] = $data["reason"];
          }
          
          if(isset($data["notes"]) && !empty($data["notes"])){
              $resignationData["notes"] = $data["notes"];
          }
          
          if(isset($data["id_circle"]) && !empty($data["id_circle"])){
              $resignationData["id_circle"] = $data["id_circle"];
          }
          
          insert($con,"resignation", $resignationData);     
      }
      else {
          echo json_encode(array("stat"=>"no","message"=>"يوجد استقالة مقدمة مسبقا بالفعل"));
      }
  
}


function addReview(){
    global $con;
    $data=json_decode(file_get_contents("php://input"),true);
      insert($con,"review",array(
        "id_student"=> $data["id_student"],
        "from_id_soura"=>$data["from_id_soura"],
        "from_id_aya"=>$data["from_id_aya"],
        "to_id_soura"=>$data["to_id_soura"],
        "to_id_aya"=>$data["to_id_aya"],
        "id_user"=>$data["id_user"],
        "id_circle"=>$data["id_circle"],
        "mark"=>$data["mark"],
        "id_evaluation"=>$data["id_evaluation"]
      ));   
    }

    function add_check_in_time_usersAttendance(){
        global $con;
        $data=json_decode(file_get_contents("php://input"),true);
          insert($con,"users_attendance",array(
            "id_user"=> $data["id_user"],
            "id_circle"=> $data["id_circle"],
          ));   
        }
    function add_check_out_time_usersAttendance(){
        global $con;
        $data=json_decode(file_get_contents("php://input"),true);
          update($con,"users_attendance",array(
            "check_out_time"=> $data["check_out_time"],
          ),array(
            "id"=> $data["id"],
          ));
        }

    function add_substitute_attendance(){
        global $con;
        $data=json_decode(file_get_contents("php://input"),true);
        
        // تسجيل تغطية (إذا لم يكن العمود attendance_status موجود، نستخدم حقل آخر)
        try {
            // محاولة استخدام attendance_status
            insert($con,"users_attendance",array(
                "id_user"=> $data["id_user"],
                "id_circle"=> $data["id_circle"],
                "attendance_status"=> 3, // 3 = تغطية
            ));
        } catch (Exception $e) {
            // إذا فشل، نستخدم الحقول الموجودة فقط
            insert($con,"users_attendance",array(
                "id_user"=> $data["id_user"],
                "id_circle"=> $data["id_circle"],
            ));
        }
    }
  
function insert_visits(){

  global $con;
  try{
  $data=json_decode(file_get_contents("php://input"),true);
  insert($con,"visits",array(
            "id_visit_type"=> $data["id_visit_type"],
            "id_user"=> $data["id_user"],
            "id_year"=> $data["id_year"],
            "id_month"=> $data["id_month"],
            "id_circle"=> $data["id_circle"],
            "notes"=> $data["notes"],

          ));

      }catch(Exception $e){
        echo json_encode(array("stat"=>"erorr","msg"=>$e->getMessage()));
      }
    
    }

    function insert_leave_requests() {
    global $con;

    try {
        $data = json_decode(file_get_contents("php://input"), true);

        $id_user = $data["id_user"];
        $date_leave = $data["date_leave"];
        $reason_leave = $data["reason_leave"];
        $leave_type = isset($data["leave_type"]) ? $data["leave_type"] : "single";
        $end_date = isset($data["end_date"]) ? $data["end_date"] : null;

        // 🔹 التحقق من وجود تداخل في التواريخ
        if ($leave_type == "period" && $end_date) {
            // للفترة: نتحقق من أي تداخل
            $stmt = $con->prepare("
                SELECT COUNT(*) FROM leave_requests 
                WHERE id_user = ? 
                AND (
                    (date_leave BETWEEN ? AND ?) 
                    OR (end_date BETWEEN ? AND ?)
                    OR (? BETWEEN date_leave AND IFNULL(end_date, date_leave))
                    OR (? BETWEEN date_leave AND IFNULL(end_date, date_leave))
                )
            ");
            $stmt->execute([$id_user, $date_leave, $end_date, $date_leave, $end_date, $date_leave, $end_date]);
        } else {
            // ليوم واحد: نتحقق من نفس التاريخ
            $stmt = $con->prepare("
                SELECT COUNT(*) FROM leave_requests 
                WHERE id_user = ? 
                AND (
                    date_leave = ? 
                    OR (? BETWEEN date_leave AND IFNULL(end_date, date_leave))
                )
            ");
            $stmt->execute([$id_user, $date_leave, $date_leave]);
        }
        
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(["stat" => "exist", "msg" => "يوجد تداخل مع طلب إجازة سابق في هذه الفترة"]);
            return;
        }

        // 🔹 إضافة الطلب الجديد
        $leaveData = [
            "id_user" => $id_user,
            "reason_leave" => $reason_leave,
            "date_leave" => $date_leave,
            "leave_type" => $leave_type,
        ];
        
        if ($leave_type == "period" && $end_date) {
            $leaveData["end_date"] = $end_date;
        }
        
        insert($con, "leave_requests", $leaveData);

    } catch (Exception $e) {
        echo json_encode(["stat" => "error", "msg" => $e->getMessage()]);
    }
}

function insert_visit_exam_result() {
    global $con; // الاتصال بقاعدة البيانات
    try {
        // استلام البيانات من الـ JSON
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            throw new Exception("لا توجد بيانات صالحة");
        }

        // تجهيز الاستعلام
        $stmt = $con->prepare("
            INSERT INTO visit_exam_result (
                id_visit,
                id_student,
                hifz_monthly,
                tilawa_monthly,
                from_id_soura_monthly,
                to_id_soura_monthly,
                from_id_aya_monthly,
                to_id_aya_monthly,
                hifz_revision,
                tilawa_revision,
                from_id_soura_revision,
                to_id_soura_revision,
                from_id_aya_revision,
                to_id_aya_revision,
                notes
            ) VALUES (
                :id_visit,
                :id_student,
                :hifz_monthly,
                :tilawa_monthly,
                :from_id_soura_monthly,
                :to_id_soura_monthly,
                :from_id_aya_monthly,
                :to_id_aya_monthly,
                :hifz_revision,
                :tilawa_revision,
                :from_id_soura_revision,
                :to_id_soura_revision,
                :from_id_aya_revision,
                :to_id_aya_revision,
                :notes
            )
        ");

        // ربط القيم
        $stmt->bindParam(":id_visit", $data['id_visit']);
        $stmt->bindParam(":id_student", $data['id_student']);
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
        $stmt->bindParam(":notes", $data['notes']);

        // تنفيذ الاستعلام
        if ($stmt->execute()) {
            echo json_encode(array("stat" => "ok", "msg" => "تم حفظ النتائج بنجاح"));
        } else {
            echo json_encode(array("stat" => "no", "msg" => "فشل في حفظ النتائج"));
        }

    } catch (Exception $e) {
        echo json_encode(array("stat" => "error", "msg" => $e->getMessage()));
    }
}

function insert_notes_for_teacher() {
    global $con; // الاتصال بقاعدة البيانات
        // استلام البيانات من الـ JSON
        $data = json_decode(file_get_contents("php://input"), true);

        insert($con,"notes_for_teacher",array(
          "responsible_user_id"=>$data["responsible_user_id"],
          "id_circle"=>$data["id_circle"],
          "id_visit"=>$data["id_visit"],
          "notes"=>$data["notes"],
          ));
}

function add_administrative_visit(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // بدء معاملة
        $con->beginTransaction();
        
        // إضافة الزيارة
        $stmt = $con->prepare("
            INSERT INTO visits (id_circle, id_visit_type, id_user, id_year, id_month, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data["id_circle"],
            $data["id_visit_type"],
            $data["id_user"],
            $data["id_year"],
            $data["id_month"],
            $data["notes"] ?? ""
        ]);
        
        $visitId = $con->lastInsertId();
        
        // إضافة تقييمات المدرس (10 معايير)
        if (isset($data["teacher_evaluations"]) && is_array($data["teacher_evaluations"])) {
            $stmt = $con->prepare("
                INSERT INTO administrative_visit_evaluations (id_visit, evaluation_type, criteria_id, rating, notes) 
                VALUES (?, 'teacher', ?, ?, ?)
            ");
            foreach ($data["teacher_evaluations"] as $evaluation) {
                $stmt->execute([
                    $visitId,
                    $evaluation["criteria_id"],
                    $evaluation["rating"],
                    $evaluation["notes"] ?? ""
                ]);
            }
        }
        
        // إضافة تقييمات الطلاب (9 معايير)
        if (isset($data["students_evaluations"]) && is_array($data["students_evaluations"])) {
            $stmt = $con->prepare("
                INSERT INTO administrative_visit_evaluations (id_visit, evaluation_type, criteria_id, rating, notes) 
                VALUES (?, 'students', ?, ?, ?)
            ");
            foreach ($data["students_evaluations"] as $evaluation) {
                $stmt->execute([
                    $visitId,
                    $evaluation["criteria_id"],
                    $evaluation["rating"],
                    $evaluation["notes"] ?? ""
                ]);
            }
        }
        
        // تأكيد المعاملة
        $con->commit();
        
        echo json_encode(array("stat" => "ok", "msg" => "تم إضافة الزيارة الإدارية بنجاح", "visit_id" => $visitId));
        
    } catch (Exception $e) {
        // التراجع عن المعاملة في حالة الخطأ
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        echo json_encode(array("stat" => "error", "msg" => $e->getMessage()));
    }
}
