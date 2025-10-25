<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // إخفاء أخطاء PHP من الظهور

include "../connect.php";

switch($_GET['mark'])
{
    // ==================== USER & CIRCLE ====================
    case "select_users":
        select_users();
        break;
    
    case "getstudents":
        getstudents();
        break;
    
    case "get_circle":
        get_circle();
        break;
    
    case "select_circle_for_center":
        select_circle_for_center();
        break;

    // ==================== ATTENDANCE ====================
    case "select_users_attendance_today":
        select_users_attendance_today();
        break;
    
    case "select_all_users_attendance_by_date":
        select_all_users_attendance_by_date();
        break;

    // ==================== REPORTS ====================
    case "getLastDailyReport":
        getLastDailyReport();
        break;
    
    case "getLastReview":
        getLastReview();
        break;

    // ==================== QURAN ====================
    case "select_fromId_soura_with_to_soura":
        select_fromId_soura_with_to_soura();
        break;
    
    case "select_courses_typeWithSour_quran":
        select_courses_typeWithSour_quran();
        break;
    
    case "select_sour_quran":
        select($con,"sour_quran");
        break;

    // ==================== HOLIDAYS ====================
    case "select_Holiday_Days":
        select_Holiday_Days();
        break;

    // ==================== VISITS ====================
    case "select_visits_type_months_years":
        select_visits_type_months_years();
        break;
    
    case "select_years":
        select_years();
        break;
    
    case "select_visits":
        select_visits();
        break;
    
    case "select_visitsed":
        select_visitsed();
        break;
    
    case "select_previous_visits":
        select_previous_visits();
        break;
    
    case "select_data_visit_previous":
        select_data_visit_previous();
        break;

    // ==================== EXAMS & RESULTS ====================
    case "select_data_exam":
        select_data_exam();
        break;
    
    case "select_student_exam":
        select_student_exam();
        break;
    
    case "select_visit_results":
        select_visit_results();
        break;

    // ==================== EVALUATIONS ====================
    case "select_evaluations":
        select($con,"evaluations");
        break;

    // ==================== LEAVE REQUESTS ====================
    case "select_leave_requests":
        select_leave_requests();
        break;
    
    case "select_resignation_requests":
        select_resignation_requests();
        break;
    
    case "select_teacher_performance":
        select_teacher_performance();
        break;
    
    case "select_student_attendance":
        select_student_attendance();
        break;
    
    case "select_daily_recitation_report":
        select_daily_recitation_report();
        break;
    
    case "select_review_recitation_report":
        select_review_recitation_report();
        break;

    // ==================== NOTES ====================
    case "select_notes_for_teacher":
        select_notes_for_teacher();
        break;
    
    case "select_notes_for_teacher_by_circle":
        select_notes_for_teacher_by_circle();
        break;
    
    case "select_available_substitute_teachers":
        select_available_substitute_teachers();
        break;
    
    case "select_absence_report":
        select_absence_report();
        break;
    
    case "select_comprehensive_student_performance":
        select_comprehensive_student_performance();
        break;
    
    case "select_circle_statistics":
        select_circle_statistics();
        break;
    
    case "select_comprehensive_circle_report":
        select_comprehensive_circle_report();
        break;
    
    case "select_circle_comparison":
        select_circle_comparison();
        break;
    
    case "select_visit_results_report":
        select_visit_results_report();
        break;
    
    case "select_visit_notes_report":
        select_visit_notes_report();
        break;
    
    case "select_visit_statistics_report":
        select_visit_statistics_report();
        break;
    
    case "select_teacher_evaluation_criteria":
        select_teacher_evaluation_criteria();
        break;
    
    case "select_students_evaluation_criteria":
        select_students_evaluation_criteria();
        break;
    
    case "select_parents_contacts":
        select_parents_contacts();
        break;
}



// دالة تعيد بيانات المستخدم فقط
function select_users() {
    global $con;
    
    $data=json_decode(file_get_contents("php://input"), true);
    
    $username = $data['username'];
    $password = $data['password'];


    $user = selectAndFilter($con, "users", [
        "username" => $username,
        "password" => $password
    ]);
    if($user)
    {
        echo json_encode(array("stat" => "ok", "data" => $user[0])); 
    }
    else
    {
        echo json_encode(array("stat" => "no", "msg" => "User not found")); 
    }
}



function getstudents() {
    global $con;

    try{
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $id_circle=$data['id_circle'] ?? null;

    

    // جلب الحلقة
    $students = [];
        $stmt = $con->prepare("
            SELECT students.*, level.name_level, level.id_level, stages.name_stages, stages.id_stages
            FROM students
            JOIN level ON students.id_level = level.id_level
            JOIN stages ON level.id_stages = stages.id_stages
            WHERE students.id_circle = :circle_id AND students.status = 1
        ");
        $stmt->execute(['circle_id' => $id_circle]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
if($students)
    echo json_encode([
        "stat" => "ok",
        "data" => $students
    ]);
    else{
       echo json_encode([
        "stat" => "no",
    
    ]); 
    }}catch(PDOException $e){        
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
}


function select_fromId_soura_with_to_soura() {
    global $con;
    try{
    $data = json_decode(file_get_contents("php://input"), true);
    $id_level = $data['id_level'];
    $id_soura = $data['id_soura'];

    $stmt = $con->prepare("
        SELECT sq.*
        FROM level l
        JOIN sour_quran sq 
          ON sq.id_soura BETWEEN $id_soura AND l.to_id_soura
        WHERE l.id_level = :id_level
    ");
    $stmt->execute([
        'id_level' => $id_level
    ]);
    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($response){
         echo json_encode([
        "stat" => "ok",
        "data" => $response
    ]);
    }
    else {
          echo json_encode([
        "stat" => "no",
            ]); 
    }
}catch(PDOException $e){        
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
}




function get_circle(){
    global $con;
    try{
    $data = json_decode(file_get_contents("php://input"), true);
    $id_user = $data['id_user'];


   $stmt = $con->prepare("SELECT circles.*,centers.region_id from centers,circles where centers.center_id=circles.id_center and circles.id_user=?");
    $stmt->execute([$id_user]);
    $circle = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($circle)
    echo json_encode([
        "stat" => "ok",
        "data" => $circle
    ]);
    else{
       echo json_encode([
        "stat" => "no",
    
    ]); 
    }}catch(PDOException $e){        
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
  
}

function getLastDailyReport() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $con->prepare("
        SELECT dr.*,
       sq_from.soura_name AS from_soura_name,
       sq_to.soura_name AS to_soura_name
FROM daily_report dr
JOIN sour_quran sq_from ON dr.from_id_soura = sq_from.id_soura
JOIN sour_quran sq_to   ON dr.to_id_soura   = sq_to.id_soura
WHERE dr.id_student = ?
ORDER BY dr.id_daily_report DESC
LIMIT 1;

    
    ");
    $stmt->execute([$data["id_student"]]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            "stat" => "ok",
            "data" => $row
        ]);
    } else {
        $stmt = $con->prepare("SELECT l.from_id_soura as to_id_soura, l.from_id_aya AS to_id_aya, sq.soura_name AS to_soura_name
            FROM level AS l
            LEFT JOIN sour_quran AS sq ON l.from_id_soura = sq.id_soura
            WHERE l.id_level = ?
        ");
        $stmt->execute([$data["id_level"]]);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
         if($row){
         echo json_encode([
            "stat" => "ok",
            "data" => $row]);
         }else{ 
            echo json_encode([
                "stat" => "no"
            ]);
         } 
    }
}



function getLastReview() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $con->prepare("
SELECT r.*,
       sq_from.soura_name AS from_soura_name,
       sq_to.soura_name   AS to_soura_name
FROM review r
JOIN sour_quran sq_from ON r.from_id_soura = sq_from.id_soura
JOIN sour_quran sq_to   ON r.to_id_soura   = sq_to.id_soura
WHERE r.id_student = ?
ORDER BY r.id_review DESC
LIMIT 1;   
    ");
    $stmt->execute([$data["id_student"]]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            "stat" => "ok",
            "data" => $row
        ]);
    } else {
         $stmt=$con->prepare("select count(id_daily_report) from daily_report where id_student=?");
        $stmt->execute([$data["id_student"]]);
        $count=$stmt->fetchColumn();
        if($count>0) {
        $stmt = $con->prepare("SELECT l.from_id_soura as to_id_soura, l.from_id_aya AS to_id_aya, sq.soura_name AS to_soura_name
            FROM level AS l
            LEFT JOIN sour_quran AS sq ON l.from_id_soura = sq.id_soura
            WHERE l.id_level = ?
        ");
        $stmt->execute([$data["id_level"]]);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
         if($row){
         echo json_encode(value: [
            "stat" => "ok",
            "data" => $row]);
         } 
    }else{ 
            echo json_encode([
                "stat" => "NoBecauseNoDailyReport"
            ]);
         }
}

}

function select_Holiday_Days(){

    global $con;

$data = json_decode(file_get_contents("php://input"), true);
$region_id = $data['region_id'];
$today_date = $data['today_date'];
$today_name = $data['today_name'];
// ✅ ثانياً: التحقق من أيام الإجازة الأسبوعية
$stmt = $con->prepare("
    SELECT day_of_week
    FROM region_weekend_days
    WHERE region_id = :region_id
");
$stmt->execute(['region_id' => $region_id]);
$weekend_days = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($weekend_days && in_array($today_name, $weekend_days)) {
    echo json_encode([
        "is_holiday" => true,
        "reason" => "إجازة أسبوعية ($today_name)",
        "source" => "weekend"
    ]);
    exit;
}
// ✅ أولاً: التحقق من الإجازات السنوية أو المناسبات
$stmt = $con->prepare("
    SELECT *
    FROM region_special_days
    WHERE region_id = :region_id
      AND is_work_day = 0
      AND (
          :today_date BETWEEN day_date AND IFNULL(EndDate, day_date)
      )
    LIMIT 1
");
$stmt->execute([
    'region_id' => $region_id,
    'today_date' => $today_date
]);
$holiday = $stmt->fetch(PDO::FETCH_ASSOC);

if ($holiday) {
    echo json_encode([
        "is_holiday" => true,
        "reason" => $holiday["description"],
        "type" => $holiday["type"],
        "source" => "special_days"
    ]);
    exit;
}



// ✅ إذا لم يكن اليوم إجازة لا سنوية ولا أسبوعية
echo json_encode([
    "is_holiday" => false
]);


}


function select_users_attendance_today(){
    global $con;

    $data = json_decode(file_get_contents("php://input"), true);
    $id_user = $data['id_user'];
    $attendance_date = $data['attendance_date'];
    $id_circle=$data['id_circle'];

    
   $stm=$con->prepare("select * from users_attendance where 
   id_user=? and attendance_date=? and id_circle=?
   ");
   $stm->execute([$id_user,$attendance_date,$id_circle]);
   $attendance_today = $stm->fetchAll(PDO::FETCH_ASSOC);
      if($attendance_today==null){
      echo json_encode([
        "stat" => "No_record_today"
      ]);    
      }
      else if($attendance_today[0]["check_out_time"]==null){
        echo json_encode([
            "stat"=>"No_check_out_time",
             "data"=>$attendance_today[0],
        ]);
        }else{
            echo json_encode([
                "stat"=>"He_check_all",
                 "data"=>$attendance_today[0],
            ]);
        }
    
   
  
}

function select_all_users_attendance_by_date(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $date = $data['date'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        $id_user = $data['id_user'] ?? null; // المسؤول (الأدمن)
        
        error_log("Date: $date, Start: $start_date, End: $end_date, Responsible User ID: $id_user");
        
        // بناء شرط التاريخ
        $dateCondition = "";
        $params = [];
        
        if ($start_date && $end_date) {
            $dateCondition = "ua.attendance_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        } else if ($date) {
            $dateCondition = "ua.attendance_date = ?";
            $params[] = $date;
        } else {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد التاريخ"]);
            return;
        }
        
        // إذا كان هناك id_user، نجلب فقط الحضور للحلقات التابعة لمركزه
        if ($id_user) {
            $params[] = $id_user;
            $stmt = $con->prepare("
                SELECT ua.*, u.username, c.name_circle, cen.name as center_name, 
                       cen.responsible_user_id, c.id_center
                FROM users_attendance ua
                LEFT JOIN users u ON ua.id_user = u.id_user
                LEFT JOIN circles c ON ua.id_circle = c.id_circle
                LEFT JOIN centers cen ON c.id_center = cen.center_id
                WHERE $dateCondition
                  AND cen.responsible_user_id = ?
                ORDER BY ua.attendance_date DESC, c.name_circle ASC, ua.check_in_time ASC
            ");
            $stmt->execute($params);
        } else {
            // جلب جميع السجلات (للسوبر أدمن)
            $stmt = $con->prepare("
                SELECT ua.*, u.username, c.name_circle, cen.name as center_name
                FROM users_attendance ua
                LEFT JOIN users u ON ua.id_user = u.id_user
                LEFT JOIN circles c ON ua.id_circle = c.id_circle
                LEFT JOIN centers cen ON c.id_center = cen.center_id
                WHERE $dateCondition
                ORDER BY ua.attendance_date DESC, c.name_circle ASC, ua.check_in_time ASC
            ");
            $stmt->execute($params);
        }
        
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($attendance && count($attendance) > 0) {
            echo json_encode(["stat" => "ok", "data" => $attendance]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد سجلات حضور لهذا التاريخ"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}    

function select_visits_type_months_years() {
    global $con;
    
    try {
    $data=json_decode(file_get_contents("php://input"), true);

    $stm=$con->prepare("SELECT visits.*, years.name_year, visits_type.name_visit_type, months.month_name, users.username
FROM visits
JOIN years ON years.id_year = visits.id_year
JOIN visits_type ON visits_type.id_visit_type = visits.id_visit_type
JOIN months ON months.id_month = visits.id_month
JOIN users ON users.id_user = visits.id_user
WHERE visits.id_user = ?

     " );
    $stm->execute([$data['id_user']]);
    $visits = $stm->fetchAll(PDO::FETCH_ASSOC);
    
        // تنفيذ جميع الاستعلامات
        $stm = $con->prepare("SELECT * FROM visits_type");
        $stm->execute();
        $visits_type = $stm->fetchAll(PDO::FETCH_ASSOC);

        $stm = $con->prepare("SELECT months.id_month,months.month_name FROM months");
        $stm->execute();
        $months = $stm->fetchAll(PDO::FETCH_ASSOC);

        $stm = $con->prepare("SELECT * FROM years");
        $stm->execute();
        $years = $stm->fetchAll(PDO::FETCH_ASSOC);
        
       $stm = $con->prepare("
            SELECT circles.*
            FROM circles
            JOIN centers ON centers.center_id = circles.id_center
            WHERE centers.responsible_user_id = ?
        ");
        $stm->execute([$data['id_user']]);
        $circles = $stm->fetchAll(PDO::FETCH_ASSOC);

        // التحقق من النتائج قبل الإرسال
        if ($visits_type && $months && $years && $circles) {
            echo json_encode([
                "stat" => "ok",
                "visits_type" => $visits_type,
                "months" => $months,
                "years" => $years,
                "circles" => $circles,
                "visits" => $visits,
            ]);
        } else {
            echo json_encode([
                "stat" => "no",
                "msg" => "أحد الجداول فارغ أو لم يتم جلب البيانات بالكامل"
            ]);
        }
    } catch (PDOException $e) {
        // التقاط الأخطاء
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
    }
}


function select_circle_for_center(){
    global $con;
     try {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_user = $data['responsible_user_id'];

    $stm=$con->prepare("SELECT circles.* from centers,circles where centers.center_id=circles.id_center and centers.responsible_user_id=?");
    $stm->execute([$id_user]);
    $circle = $stm->fetchAll(PDO::FETCH_ASSOC);
    if($circle){
      echo  json_encode(["stat"=>"ok","data"=>$circle]);
    }else{
      echo  json_encode(["stat"=>"no",]);
    }
 } catch (PDOException $e) {
        // التقاط الأخطاء
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
    }


}



function select_years(){
    global $con;
    select($con,"years");
}

function select_visits(){
    global $con;
    try{
    $data=json_decode(file_get_contents("php://input"),true);
    $stm=$con->prepare("select visits.id_month,months.month_name,visits.date,visits.id_visit   
    from visits
    JOIN months ON months.id_month=visits.id_month
    where visits.id_year =? and 
   visits.id_visit_type=1
    ");
    $stm->execute([$data["id_year"]]);
    $months=$stm->fetchAll(PDO::FETCH_ASSOC);
    if($months){
        echo json_encode(["stat"=>"ok","data"=>$months]);
    }
    else{
        echo json_encode(["stat"=>"no"]);
    }}catch(PDOException $e){
         
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
}


function select_data_exam() {
    global $con;

    $dataCome=json_decode(file_get_contents("php://input"),true);
  
    $circle_id =$dataCome["circle_id"];
    $visit_id = $dataCome["visit_id"];

    $stm = $con->prepare("
        SELECT 
    s.id_student,
    s.name_student,
    l.id_level,
    l.name_level,
    st.id_stages,
    st.name_stages,
    c.id_course,
    c.name_course,
    ct.id_courses_type,
    ct.name_courses_type,
    se.mark,
    se.note

FROM students s
JOIN level l ON s.id_level = l.id_level
JOIN stages st ON l.id_stages = st.id_stages

-- نربط الكورس ونوعه
JOIN courses c ON c.id_stages = st.id_stages
JOIN courses_type ct ON ct.id_course = c.id_course

-- نربط بالامتحانات (لو موجود)
LEFT JOIN student_exam se 
    ON se.id_student = s.id_student
    AND se.id_courses_type = ct.id_courses_type
    AND se.id_visit = :visit_id

WHERE s.id_circle = :circle_id
  AND s.status = 1
ORDER BY s.name_student, c.id_course, ct.id_courses_type;

    ");

    $stm->bindParam(':circle_id', $circle_id, PDO::PARAM_INT);
    $stm->bindParam(':visit_id', $visit_id, PDO::PARAM_INT);

    $stm->execute();
    $data = $stm->fetchAll(PDO::FETCH_ASSOC);

    if($data){
        echo json_encode([
            "stat" => "ok",
            "data" => $data
        ]);
    }
    else {
        echo json_encode([
            "stat" => "no",
        ]);
    }
}

function select_student_exam() {
    global $con;
    // $dataCome = json_decode(file_get_contents("php://input"), true);

    // $student_id = $dataCome["student_id"];
    // $visit_id = $dataCome["visit_id"];

    $student_id = 18;
    $visit_id = 15;
// print_r( $student_id);
    $stm = $con->prepare("
        SELECT 
            s.id_student,
            s.name_student,
            l.id_level,
            l.name_level,
            st.id_stages,
            st.name_stages,
            MAX(CASE WHEN se.id_courses_type = 1 THEN se.mark END) AS monthly_mark,
            MAX(CASE WHEN se.id_courses_type = 1 THEN se.note END) AS monthly_note,
            MAX(CASE WHEN se.id_courses_type = 2 THEN se.mark END) AS review_mark,
            MAX(CASE WHEN se.id_courses_type = 2 THEN se.note END) AS review_note
        FROM students s
        JOIN level l ON s.id_level = l.id_level
        JOIN stages st ON l.id_stages = st.id_stages
        LEFT JOIN student_exam se 
            ON s.id_student = se.id_student 
            AND se.id_visit = :visit_id
        WHERE s.id_student = :student_id
          AND s.status = 1
        GROUP BY s.id_student, s.name_student, l.id_level, l.name_level, st.id_stages, st.name_stages
        LIMIT 1
    ");

    $stm->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stm->bindParam(':visit_id', $visit_id, PDO::PARAM_INT);
    $stm->execute();

    $data = $stm->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        // في حالة لم يوجد الطالب أو بياناته، نرسل null للحقول
        $data = [
            "id_student" => $student_id,
            "name_student" => null,
            "id_level" => null,
            "name_level" => null,
            "id_stages" => null,
            "name_stages" => null,
            "monthly_mark" => null,
            "monthly_note" => null,
            "review_mark" => null,
            "review_note" => null
        ];
    }

    echo json_encode([
        "stat" => "ok",
    ]);
}

function select_leave_requests(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_user = $data['id_user'] ?? null;
        
        // إذا كان id_user = "all" أو فارغ، نجلب جميع الطلبات مع أسماء المستخدمين
        if ($id_user == "all" || empty($id_user)) {
            $stmt = $con->prepare("
                SELECT lr.*, u.username 
                FROM leave_requests lr
                LEFT JOIN users u ON lr.id_user = u.id_user
                ORDER BY lr.date_request DESC
            ");
            $stmt->execute();
        } else {
            // جلب طلبات مستخدم معين
            $stmt = $con->prepare("
                SELECT lr.*, u.username 
                FROM leave_requests lr
                LEFT JOIN users u ON lr.id_user = u.id_user
                WHERE lr.id_user = ?
                ORDER BY lr.date_request DESC
            ");
            $stmt->execute([$id_user]);
        }
        
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($requests) {
            echo json_encode(["stat" => "ok", "data" => $requests]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد طلبات إجازة"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_previous_visits() {
    global $con;

    try {
        $data = json_decode(file_get_contents("php://input"), true);

        // التحقق من وجود id_circle أو id_user
        $useCircle = isset($data["id_circle"]);
        $whereClause = $useCircle ? "visits.id_circle = ?" : "visits.id_user = ?";
        $paramValue = $useCircle ? $data["id_circle"] : $data["id_user"];

        // جلب الزيارات الأساسية
        $stm = $con->prepare("
            SELECT 
                visits.*, 
                circles.name_circle, 
                visits_type.name_visit_type, 
                years.name_year, 
                months.month_name
            FROM visits 
            JOIN circles ON circles.id_circle = visits.id_circle
            JOIN visits_type ON visits_type.id_visit_type = visits.id_visit_type
            JOIN years ON years.id_year = visits.id_year
            JOIN months ON months.id_month = visits.id_month
            WHERE $whereClause
            ORDER BY visits.id_visit DESC
        ");
        $stm->execute([$paramValue]);
        $visits = $stm->fetchAll(PDO::FETCH_ASSOC);

        if (!$visits) {
            echo json_encode(["stat" => "no"]);
            return;
        }

        // ===== التحقق من حالة الزيارة (مكتملة أو لا) =====
        foreach ($visits as &$visit) {
            $visit["visit_status"] = "اداري"; // الافتراضي

            // إذا كانت الزيارة فنية
            if ($visit["id_visit_type"] == 1) {
                // عدد الطلاب في الحلقة
                $stmCount = $con->prepare("SELECT COUNT(*) FROM students WHERE id_circle = ? AND status = 1");
                $stmCount->execute([$visit["id_circle"]]);
                $total_students = (int) $stmCount->fetchColumn();

                // عدد الطلاب الذين لديهم نتائج صحيحة (نطاق حفظ ومراجعة مكتمل وليس null)
                $stmExam = $con->prepare("
                    SELECT COUNT(DISTINCT id_student)
                    FROM visit_exam_result
                    WHERE id_visit = ?
                      AND from_id_soura_monthly IS NOT NULL
                      AND to_id_soura_monthly IS NOT NULL
                      AND from_id_aya_monthly IS NOT NULL
                      AND to_id_aya_monthly IS NOT NULL
                      AND from_id_soura_revision IS NOT NULL
                      AND to_id_soura_revision IS NOT NULL
                      AND from_id_aya_revision IS NOT NULL
                      AND to_id_aya_revision IS NOT NULL
                ");
                $stmExam->execute([$visit["id_visit"]]);
                $fully_tested_students = (int) $stmExam->fetchColumn();

                // تحديد الحالة
                if ($fully_tested_students == 0) {
                    $visit["visit_status"] = 0; // لم يبدأ أي طالب
                } elseif ($fully_tested_students < $total_students) {
                    $visit["visit_status"] = 1; // لم يكتمل بعد
                } else {
                    $visit["visit_status"] = 2; // مكتملة تمامًا ✅
                }
            }
        }

        echo json_encode(["stat" => "ok", "data" => $visits]);

    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
    }
}


function select_data_visit_previous() {
    try {
        global $con;
        $data = json_decode(file_get_contents("php://input"), true);

        $stm = $con->prepare("
        SELECT
            s.id_student,
            s.name_student,
            r.id_visit,
            r.id_result,

            -- النطاق الشهري
            r.from_id_soura_monthly,
            r.to_id_soura_monthly,
            r.from_id_aya_monthly,
            r.to_id_aya_monthly,
            -- النطاق المراجعة
            r.from_id_soura_revision,
            r.to_id_soura_revision,
            r.from_id_aya_revision,
            r.to_id_aya_revision,
            r.hifz_monthly,
            r.tilawa_monthly,
            r.hifz_revision,
            r.tilawa_revision,
            r.notes,
            CASE
                WHEN r.id_result IS NULL THEN 0                   -- لم يختبر شيء
                WHEN (r.from_id_soura_monthly IS NOT NULL OR r.to_id_soura_monthly IS NOT NULL
                      OR r.from_id_aya_monthly IS NOT NULL OR r.to_id_aya_monthly IS NOT NULL)
                 AND (r.from_id_soura_revision IS NULL AND r.to_id_soura_revision IS NULL
                      AND r.from_id_aya_revision IS NULL AND r.to_id_aya_revision IS NULL)
                THEN 1                                         -- اختبر الحفظ فقط
                WHEN (r.from_id_soura_revision IS NOT NULL OR r.to_id_soura_revision IS NOT NULL
                      OR r.from_id_aya_revision IS NOT NULL OR r.to_id_aya_revision IS NOT NULL)
                 AND (r.from_id_soura_monthly IS NULL AND r.to_id_soura_monthly IS NULL
                      AND r.from_id_aya_monthly IS NULL AND r.to_id_aya_monthly IS NULL)
                THEN 2                                         -- اختبر المراجعة فقط
                WHEN (r.from_id_soura_monthly IS NOT NULL OR r.to_id_soura_monthly IS NOT NULL
                      OR r.from_id_aya_monthly IS NOT NULL OR r.to_id_aya_monthly IS NOT NULL)
                 AND (r.from_id_soura_revision IS NOT NULL OR r.to_id_soura_revision IS NOT NULL
                      OR r.from_id_aya_revision IS NOT NULL OR r.to_id_aya_revision IS NOT NULL)
                THEN 3                                         -- اختبر الاثنين
                ELSE 0                                         -- افتراضي
            END AS test_status
        FROM students s
        LEFT JOIN visit_exam_result r 
               ON s.id_student = r.id_student 
              AND r.id_visit = ?
        WHERE s.id_circle = ? 
          AND s.status = 1
        ORDER BY s.id_student;
        ");

        $stm->execute([$data["id_visit"], $data["id_circle"]]);
        $visit = $stm->fetchAll(PDO::FETCH_ASSOC);

        if ($visit) {
            echo json_encode(["stat" => "ok", "data" => $visit]);
        } else {
            echo json_encode(["stat" => "no"]);
        }

    } catch(PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
    }
}


function select_notes_for_teacher(){
    global $con;
    try{
    $data=json_decode(file_get_contents("php://input"),associative: true);
    $stm=$con->prepare("select * from notes_for_teacher  where id_visit=? ");
    $stm->execute([$data["id_visit"]]);
    $notes = $stm->fetch(PDO::FETCH_ASSOC);
    if($notes){
     echo   json_encode(["stat"=>"ok","data"=>$notes]);
    } else{
      echo  json_encode(["stat"=>"no"]);
    }}catch(PDOException $e){        
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }

    }


function select_visitsed() {
 global $con;    
    try {
    $data=json_decode(file_get_contents("php://input"), true);

    $stm=$con->prepare("SELECT visits.*, years.name_year, visits_type.name_visit_type, months.month_name, users.username
FROM visits
JOIN years ON years.id_year = visits.id_year
JOIN visits_type ON visits_type.id_visit_type = visits.id_visit_type
JOIN months ON months.id_month = visits.id_month
JOIN users ON users.id_user = visits.id_user
WHERE visits.id_user = ?

     " );
    $stm->execute([$data['id_user']]);
    $visits = $stm->fetchAll(PDO::FETCH_ASSOC);
    if($visits){
        echo json_encode([
            "stat" => "ok",
            "visits" => $visits,
        ]);
    } else {
        echo json_encode([
            "stat" => "no",
        ]);}
    }catch (PDOException $e) {
        // التقاط الأخطاء
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
    }
}

function select_notes_for_teacher_by_circle(){
    global $con;
    try{
        $data = json_decode(file_get_contents("php://input"), true);
        
        $stm = $con->prepare("
            SELECT 
                nft.*,
                v.date as visit_date,
                vt.name_visit_type,
                u.username as responsible_username
            FROM notes_for_teacher nft
            JOIN visits v ON nft.id_visit = v.id_visit
            JOIN visits_type vt ON v.id_visit_type = vt.id_visit_type
            JOIN users u ON nft.responsible_user_id = u.id_user
            WHERE nft.id_circle = ?
            ORDER BY v.date DESC

            ");
        
        $stm->execute([$data["id_circle"]]);
        $notes = $stm->fetchAll(PDO::FETCH_ASSOC);
        
        if($notes){
            echo json_encode(["stat" => "ok", "data" => $notes]);
        } else {
            echo json_encode(["stat" => "no"]);
        }
        
    } catch(PDOException $e){        
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
    }

}

function select_visit_results() {
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_visit = $data["id_visit"];
        
        $stmt = $con->prepare("
            SELECT 
                s.id_student,
                s.name_student,
                r.hifz_monthly,
                r.tilawa_monthly,
                r.from_id_soura_monthly,
                r.to_id_soura_monthly,
                r.from_id_aya_monthly,
                r.to_id_aya_monthly,
                r.hifz_revision,
                r.tilawa_revision,
                r.from_id_soura_revision,
                r.to_id_soura_revision,
                r.from_id_aya_revision,
                r.to_id_aya_revision,
                fs_monthly.soura_name AS from_soura_monthly_name,
                ts_monthly.soura_name AS to_soura_monthly_name,
                fs_revision.soura_name AS from_soura_revision_name,
                ts_revision.soura_name AS to_soura_revision_name
            FROM visit_exam_result r
            INNER JOIN students s ON r.id_student = s.id_student
            LEFT JOIN sour_quran fs_monthly ON r.from_id_soura_monthly = fs_monthly.id_soura
            LEFT JOIN sour_quran ts_monthly ON r.to_id_soura_monthly = ts_monthly.id_soura
            LEFT JOIN sour_quran fs_revision ON r.from_id_soura_revision = fs_revision.id_soura
            LEFT JOIN sour_quran ts_revision ON r.to_id_soura_revision = ts_revision.id_soura
            WHERE r.id_visit = ?
            ORDER BY s.name_student
        ");
        
        $stmt->execute([$id_visit]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($results && count($results) > 0) {
            echo json_encode(["stat" => "ok", "data" => $results]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد نتائج لهذه الزيارة"]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_resignation_requests(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_user = $data['id_user'] ?? null;
        
        // إذا كان id_user = "all" أو فارغ، نجلب جميع الطلبات مع أسماء المستخدمين
        if ($id_user == "all" || empty($id_user)) {
            $stmt = $con->prepare("
                SELECT r.*, u.username, c.name_circle,
                       r.date as request_date,
                       r.statuse as status
                FROM resignation r
                LEFT JOIN users u ON r.id_user = u.id_user
                LEFT JOIN circles c ON r.id_circle = c.id_circle
                ORDER BY r.date DESC
            ");
            $stmt->execute();
        } else {
            // جلب طلبات مستخدم معين
            $stmt = $con->prepare("
                SELECT r.*, u.username, c.name_circle,
                       r.date as request_date,
                       r.statuse as status
                FROM resignation r
                LEFT JOIN users u ON r.id_user = u.id_user
                LEFT JOIN circles c ON r.id_circle = c.id_circle
                WHERE r.id_user = ?
                ORDER BY r.date DESC
            ");
            $stmt->execute([$id_user]);
        }
        
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($requests) {
            echo json_encode(["stat" => "ok", "data" => $requests]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد طلبات استقالة"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_teacher_performance(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $month = $data['month'] ?? null;
        $year = $data['year'] ?? null;
        $id_user = $data['id_user'] ?? null;
        
        if (!$month || !$year) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الشهر والسنة"]);
            return;
        }
        
        // حساب عدد أيام الشهر
        $total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // جلب أداء الأساتذة (الذين لديهم حلقات)
        if ($id_user) {
            // للمسؤول: جلب أداء أساتذة المركز فقط
            $stmt = $con->prepare("
                SELECT 
                    u.id_user,
                    u.username,
                    c.name_circle,
                    COUNT(DISTINCT CASE WHEN ua.attendance_date IS NOT NULL THEN ua.attendance_date END) as attendance_count,
                    COUNT(DISTINCT CASE WHEN lr.date_leave IS NOT NULL AND lr.status = 1 THEN lr.date_leave END) as leave_count,
                    ? as total_days
                FROM users u
                INNER JOIN circles c ON u.id_user = c.id_user
                INNER JOIN centers cen ON c.id_center = cen.center_id
                LEFT JOIN users_attendance ua ON u.id_user = ua.id_user 
                    AND MONTH(ua.attendance_date) = ? 
                    AND YEAR(ua.attendance_date) = ?
                LEFT JOIN leave_requests lr ON u.id_user = lr.id_user 
                    AND MONTH(lr.date_leave) = ? 
                    AND YEAR(lr.date_leave) = ?
                WHERE cen.responsible_user_id = ?
                GROUP BY u.id_user, u.username, c.name_circle
                ORDER BY u.username
            ");
            $stmt->execute([$total_days, $month, $year, $month, $year, $id_user]);
        } else {
            // للسوبر أدمن: جلب جميع الأساتذة
            $stmt = $con->prepare("
                SELECT 
                    u.id_user,
                    u.username,
                    c.name_circle,
                    COUNT(DISTINCT CASE WHEN ua.attendance_date IS NOT NULL THEN ua.attendance_date END) as attendance_count,
                    COUNT(DISTINCT CASE WHEN lr.date_leave IS NOT NULL AND lr.status = 1 THEN lr.date_leave END) as leave_count,
                    ? as total_days
                FROM users u
                INNER JOIN circles c ON u.id_user = c.id_user
                LEFT JOIN users_attendance ua ON u.id_user = ua.id_user 
                    AND MONTH(ua.attendance_date) = ? 
                    AND YEAR(ua.attendance_date) = ?
                LEFT JOIN leave_requests lr ON u.id_user = lr.id_user 
                    AND MONTH(lr.date_leave) = ? 
                    AND YEAR(lr.date_leave) = ?
                GROUP BY u.id_user, u.username, c.name_circle
                ORDER BY u.username
            ");
            $stmt->execute([$total_days, $month, $year, $month, $year]);
        }
        
        $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // حساب أيام الغياب لكل أستاذ
        foreach ($performance as &$item) {
            $item['absence_count'] = $total_days - $item['attendance_count'] - $item['leave_count'];
            if ($item['absence_count'] < 0) $item['absence_count'] = 0;
        }
        
        if ($performance) {
            echo json_encode(["stat" => "ok", "data" => $performance]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات أداء"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_daily_recitation_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_circle = $data['id_circle'] ?? null;
        $date = $data['date'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$id_circle) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الحلقة"]);
            return;
        }
        
        // بناء شرط التاريخ
        $dateCondition = "";
        $params = [];
        
        if ($start_date && $end_date) {
            $dateCondition = "DATE(dr.date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        } else if ($date) {
            $dateCondition = "DATE(dr.date) = ?";
            $params[] = $date;
        } else {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد التاريخ"]);
            return;
        }
        
        // بناء الاستعلام حسب الحلقة
        if ($id_circle == 'all') {
            // جميع الحلقات
            if ($start_date && $end_date) {
                // فترة - تجميع
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        c.name_circle,
                        COUNT(dr.id_daily_report) as total_recitations,
                        AVG(dr.mark) as avg_mark
                    FROM students s
                    INNER JOIN circles c ON s.id_circle = c.id_circle
                    LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                        AND $dateCondition
                    WHERE s.status = 1
                    GROUP BY s.id_student, s.name_student, c.name_circle
                    HAVING total_recitations > 0
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute($params);
            } else {
                // يوم واحد - تفصيلي
                $stmt = $con->prepare("
                    SELECT 
                        dr.*,
                        s.name_student,
                        c.name_circle,
                        fs.soura_name as from_soura_name,
                        ts.soura_name as to_soura_name,
                        e.name_evaluation as evaluation_name
                    FROM daily_report dr
                    INNER JOIN students s ON dr.id_student = s.id_student
                    INNER JOIN circles c ON dr.id_circle = c.id_circle
                    LEFT JOIN sour_quran fs ON dr.from_id_soura = fs.id_soura
                    LEFT JOIN sour_quran ts ON dr.to_id_soura = ts.id_soura
                    LEFT JOIN evaluations e ON dr.id_evaluation = e.id_evaluation
                    WHERE $dateCondition
                      AND s.status = 1
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute($params);
            }
        } else {
            // حلقة محددة
            if ($start_date && $end_date) {
                // فترة - تجميع
                $params[] = $id_circle;
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        COUNT(dr.id_daily_report) as total_recitations,
                        AVG(dr.mark) as avg_mark
                    FROM students s
                    LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                        AND $dateCondition
                    WHERE s.id_circle = ?
                      AND s.status = 1
                    GROUP BY s.id_student, s.name_student
                    HAVING total_recitations > 0
                    ORDER BY s.name_student
                ");
                $stmt->execute($params);
            } else {
                // يوم واحد - تفصيلي
                $params[] = $id_circle;
                $stmt = $con->prepare("
                    SELECT 
                        dr.*,
                        s.name_student,
                        fs.soura_name as from_soura_name,
                        ts.soura_name as to_soura_name,
                        e.name_evaluation as evaluation_name
                    FROM daily_report dr
                    INNER JOIN students s ON dr.id_student = s.id_student
                    LEFT JOIN sour_quran fs ON dr.from_id_soura = fs.id_soura
                    LEFT JOIN sour_quran ts ON dr.to_id_soura = ts.id_soura
                    LEFT JOIN evaluations e ON dr.id_evaluation = e.id_evaluation
                    WHERE $dateCondition
                      AND dr.id_circle = ?
                      AND s.status = 1
                    ORDER BY s.name_student
                ");
                $stmt->execute($params);
            }
        }
        
        $recitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($recitations) {
            echo json_encode(["stat" => "ok", "data" => $recitations]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات تسميع"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_student_attendance(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_circle = $data['id_circle'] ?? null;
        $date = $data['date'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$id_circle) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الحلقة"]);
            return;
        }
        
        // بناء شرط التاريخ
        $dateCondition = "";
        $params = [];
        
        if ($start_date && $end_date) {
            $dateCondition = "DATE(sa.date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        } else if ($date) {
            $dateCondition = "DATE(sa.date) = ?";
            $params[] = $date;
        } else {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد التاريخ"]);
            return;
        }
        
        // بناء الاستعلام حسب الحلقة
        if ($id_circle == 'all') {
            // جميع الحلقات - تجميع الحضور والغياب
            if ($start_date && $end_date) {
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        c.name_circle,
                        COUNT(CASE WHEN sa.status = 1 THEN 1 END) as present_count,
                        COUNT(CASE WHEN sa.status = 0 THEN 1 END) as absent_count,
                        COUNT(sa.id_attendance) as total_days
                    FROM students s
                    INNER JOIN circles c ON s.id_circle = c.id_circle
                    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND DATE(sa.date) BETWEEN ? AND ?
                    WHERE s.status = 1
                    GROUP BY s.id_student, s.name_student, c.name_circle
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute([$start_date, $end_date]);
            } else {
                // يوم واحد - عرض تفصيلي
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        c.name_circle,
                        COALESCE(sa.status, '0') as status,
                        sa.notes
                    FROM students s
                    INNER JOIN circles c ON s.id_circle = c.id_circle
                    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND DATE(sa.date) = ?
                    WHERE s.status = 1
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute([$date]);
            }
        } else {
            // حلقة محددة
            if ($start_date && $end_date) {
                // فترة - تجميع الحضور والغياب
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        COUNT(CASE WHEN sa.status = 1 THEN 1 END) as present_count,
                        COUNT(CASE WHEN sa.status = 0 THEN 1 END) as absent_count,
                        COUNT(sa.id_attendance) as total_days
                    FROM students s
                    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND DATE(sa.date) BETWEEN ? AND ?
                    WHERE s.id_circle = ?
                      AND s.status = 1
                    GROUP BY s.id_student, s.name_student
                    ORDER BY s.name_student
                ");
                $stmt->execute([$start_date, $end_date, $id_circle]);
            } else {
                // يوم واحد - عرض تفصيلي
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        COALESCE(sa.status, '0') as status,
                        sa.notes
                    FROM students s
                    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND DATE(sa.date) = ?
                    WHERE s.id_circle = ?
                      AND s.status = 1
                    ORDER BY s.name_student
                ");
                $stmt->execute([$date, $id_circle]);
            }
        }
        
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($attendance) {
            echo json_encode(["stat" => "ok", "data" => $attendance]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات حضور"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_review_recitation_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_circle = $data['id_circle'] ?? null;
        $date = $data['date'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$id_circle) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الحلقة"]);
            return;
        }
        
        // بناء شرط التاريخ
        $dateCondition = "";
        $params = [];
        
        if ($start_date && $end_date) {
            $dateCondition = "DATE(r.date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        } else if ($date) {
            $dateCondition = "DATE(r.date) = ?";
            $params[] = $date;
        } else {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد التاريخ"]);
            return;
        }
        
        // بناء الاستعلام حسب الحلقة
        if ($id_circle == 'all') {
            // جميع الحلقات
            if ($start_date && $end_date) {
                // فترة - تجميع
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        c.name_circle,
                        COUNT(r.id_review) as total_reviews,
                        AVG(r.mark) as avg_mark
                    FROM students s
                    INNER JOIN circles c ON s.id_circle = c.id_circle
                    LEFT JOIN review r ON s.id_student = r.id_student 
                        AND $dateCondition
                    WHERE s.status = 1
                    GROUP BY s.id_student, s.name_student, c.name_circle
                    HAVING total_reviews > 0
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute($params);
            } else {
                // يوم واحد - تفصيلي
                $stmt = $con->prepare("
                    SELECT 
                        r.*,
                        s.name_student,
                        c.name_circle,
                        fs.soura_name as from_soura_name,
                        ts.soura_name as to_soura_name,
                        e.name_evaluation as evaluation_name
                    FROM review r
                    INNER JOIN students s ON r.id_student = s.id_student
                    INNER JOIN circles c ON r.id_circle = c.id_circle
                    LEFT JOIN sour_quran fs ON r.from_id_soura = fs.id_soura
                    LEFT JOIN sour_quran ts ON r.to_id_soura = ts.id_soura
                    LEFT JOIN evaluations e ON r.id_evaluation = e.id_evaluation
                    WHERE $dateCondition
                      AND s.status = 1
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute($params);
            }
        } else {
            // حلقة محددة
            if ($start_date && $end_date) {
                // فترة - تجميع
                $params[] = $id_circle;
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        COUNT(r.id_review) as total_reviews,
                        AVG(r.mark) as avg_mark
                    FROM students s
                    LEFT JOIN review r ON s.id_student = r.id_student 
                        AND $dateCondition
                    WHERE s.id_circle = ?
                      AND s.status = 1
                    GROUP BY s.id_student, s.name_student
                    HAVING total_reviews > 0
                    ORDER BY s.name_student
                ");
                $stmt->execute($params);
            } else {
                // يوم واحد - تفصيلي
                $params[] = $id_circle;
                $stmt = $con->prepare("
                    SELECT 
                        r.*,
                        s.name_student,
                        fs.soura_name as from_soura_name,
                        ts.soura_name as to_soura_name,
                        e.name_evaluation as evaluation_name
                    FROM review r
                    INNER JOIN students s ON r.id_student = s.id_student
                    LEFT JOIN sour_quran fs ON r.from_id_soura = fs.id_soura
                    LEFT JOIN sour_quran ts ON r.to_id_soura = ts.id_soura
                    LEFT JOIN evaluations e ON r.id_evaluation = e.id_evaluation
                    WHERE $dateCondition
                      AND r.id_circle = ?
                      AND s.status = 1
                    ORDER BY s.name_student
                ");
                $stmt->execute($params);
            }
        }
        
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($reviews) {
            echo json_encode(["stat" => "ok", "data" => $reviews]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات مراجعة"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_available_substitute_teachers(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_center = $data['id_center'] ?? null;
        
        if (!$id_center) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد المركز"]);
            return;
        }
        
        // جلب جميع الأساتذة في نفس المركز
        $stmt = $con->prepare("
            SELECT DISTINCT u.id_user, u.username, c.name_circle
            FROM users u
            INNER JOIN circles c ON u.id_user = c.id_user
            WHERE c.id_center = ?
            ORDER BY u.username
        ");
        $stmt->execute([$id_center]);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($teachers) {
            echo json_encode(["stat" => "ok", "data" => $teachers]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا يوجد أساتذة متاحين"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_absence_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_circle = $data['id_circle'] ?? null;
        $date = $data['date'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$id_circle) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الحلقة"]);
            return;
        }
        
        // بناء شرط التاريخ
        $dateCondition = "";
        $params = [];
        
        if ($start_date && $end_date) {
            $dateCondition = "DATE(sa.date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        } else if ($date) {
            $dateCondition = "DATE(sa.date) = ?";
            $params[] = $date;
        } else {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد التاريخ"]);
            return;
        }
        
        // بناء الاستعلام حسب الحلقة
        if ($id_circle == 'all') {
            // جميع الحلقات
            if ($start_date && $end_date) {
                // فترة - تجميع
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        c.name_circle,
                        COUNT(CASE WHEN sa.status = 0 THEN 1 END) as absent_count,
                        COUNT(sa.id_attendance) as total_days
                    FROM students s
                    INNER JOIN circles c ON s.id_circle = c.id_circle
                    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND $dateCondition
                    WHERE s.status = 1
                    GROUP BY s.id_student, s.name_student, c.name_circle
                    HAVING absent_count > 0
                    ORDER BY absent_count DESC, c.name_circle, s.name_student
                ");
                $stmt->execute($params);
            } else {
                // يوم واحد - تفصيلي (فقط الطلاب المسجلين كغائبين)
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        c.name_circle,
                        sa.date,
                        sa.status,
                        sa.notes
                    FROM students s
                    INNER JOIN circles c ON s.id_circle = c.id_circle
                    INNER JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND $dateCondition
                        AND sa.status = 0
                    WHERE s.status = 1
                    ORDER BY c.name_circle, s.name_student
                ");
                $stmt->execute($params);
            }
        } else {
            // حلقة محددة
            if ($start_date && $end_date) {
                // فترة - تجميع
                $params[] = $id_circle;
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        COUNT(CASE WHEN sa.status = 0 THEN 1 END) as absent_count,
                        COUNT(sa.id_attendance) as total_days
                    FROM students s
                    LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND $dateCondition
                    WHERE s.id_circle = ?
                      AND s.status = 1
                    GROUP BY s.id_student, s.name_student
                    HAVING absent_count > 0
                    ORDER BY absent_count DESC, s.name_student
                ");
                $stmt->execute($params);
            } else {
                // يوم واحد - تفصيلي (فقط الطلاب المسجلين كغائبين)
                $params[] = $id_circle;
                $stmt = $con->prepare("
                    SELECT 
                        s.id_student,
                        s.name_student,
                        sa.date,
                        sa.status,
                        sa.notes
                    FROM students s
                    INNER JOIN student_attendance sa ON s.id_student = sa.id_student 
                        AND $dateCondition
                        AND sa.status = 0
                    WHERE s.id_circle = ?
                      AND s.status = 1
                    ORDER BY s.name_student
                ");
                $stmt->execute($params);
            }
        }
        
        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($absences) {
            echo json_encode(["stat" => "ok", "data" => $absences]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات غياب"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_comprehensive_student_performance(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_student = $data['id_student'] ?? null;
        $id_circle = $data['id_circle'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        $result = [];
        
        // إذا تم تحديد طالب معين
        if ($id_student) {
            // معلومات الطالب
            $stmt = $con->prepare("
                SELECT s.*, c.name_circle
                FROM students s
                LEFT JOIN circles c ON s.id_circle = c.id_circle
                WHERE s.id_student = ?
            ");
            $stmt->execute([$id_student]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                echo json_encode(["stat" => "error", "msg" => "الطالب غير موجود"]);
                return;
            }
            
            // إحصائيات التسميع اليومي
            $stmt = $con->prepare("
                SELECT 
                    COUNT(*) as total_recitations,
                    AVG(mark) as avg_mark,
                    MAX(mark) as max_mark,
                    MIN(mark) as min_mark
                FROM daily_report
                WHERE id_student = ?
                  AND DATE(date) BETWEEN ? AND ?
            ");
            $stmt->execute([$id_student, $start_date, $end_date]);
            $recitation_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // إحصائيات المراجعة
            $stmt = $con->prepare("
                SELECT 
                    COUNT(*) as total_reviews,
                    AVG(mark) as avg_mark,
                    MAX(mark) as max_mark,
                    MIN(mark) as min_mark
                FROM review
                WHERE id_student = ?
                  AND DATE(date) BETWEEN ? AND ?
            ");
            $stmt->execute([$id_student, $start_date, $end_date]);
            $review_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // إحصائيات الحضور
            $stmt = $con->prepare("
                SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as absent_count
                FROM student_attendance
                WHERE id_student = ?
                  AND DATE(date) BETWEEN ? AND ?
            ");
            $stmt->execute([$id_student, $start_date, $end_date]);
            $attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // تفاصيل التسميع الأخيرة
            $stmt = $con->prepare("
                SELECT dr.*, fs.soura_name as from_soura, ts.soura_name as to_soura
                FROM daily_report dr
                LEFT JOIN sour_quran fs ON dr.from_id_soura = fs.id_soura
                LEFT JOIN sour_quran ts ON dr.to_id_soura = ts.id_soura
                WHERE dr.id_student = ?
                  AND DATE(dr.date) BETWEEN ? AND ?
                ORDER BY dr.date DESC
                LIMIT 10
            ");
            $stmt->execute([$id_student, $start_date, $end_date]);
            $recent_recitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = [
                "student_info" => $student,
                "recitation_stats" => $recitation_stats,
                "review_stats" => $review_stats,
                "attendance_stats" => $attendance_stats,
                "recent_recitations" => $recent_recitations
            ];
            
        } else if ($id_circle) {
            // تقرير لجميع طلاب الحلقة
            $stmt = $con->prepare("
                SELECT 
                    s.id_student,
                    s.name_student,
                    COUNT(DISTINCT dr.id_daily_report) as total_recitations,
                    AVG(dr.mark) as avg_recitation_mark,
                    COUNT(DISTINCT r.id_review) as total_reviews,
                    AVG(r.mark) as avg_review_mark,
                    COUNT(DISTINCT sa.id_attendance) as total_days,
                    SUM(CASE WHEN sa.status = 1 THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN sa.status = 0 THEN 1 ELSE 0 END) as absent_count
                FROM students s
                LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                    AND DATE(dr.date) BETWEEN ? AND ?
                LEFT JOIN review r ON s.id_student = r.id_student 
                    AND DATE(r.date) BETWEEN ? AND ?
                LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                    AND DATE(sa.date) BETWEEN ? AND ?
                WHERE s.id_circle = ?
                  AND s.status = 1
                GROUP BY s.id_student, s.name_student
                ORDER BY s.name_student
            ");
            $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $id_circle]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = ["students" => $students];
            
        } else {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الطالب أو الحلقة"]);
            return;
        }
        
        if ($result) {
            echo json_encode(["stat" => "ok", "data" => $result]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_circle_statistics(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_center = $data['id_center'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        // بناء الاستعلام
        if ($id_center) {
            // حلقات مركز محدد
            $stmt = $con->prepare("
                SELECT 
                    c.id_circle,
                    c.name_circle,
                    u.username as teacher_name,
                    cen.name as center_name,
                    COUNT(DISTINCT s.id_student) as total_students,
                    COUNT(DISTINCT CASE WHEN s.status = 1 THEN s.id_student END) as active_students,
                    COUNT(DISTINCT dr.id_daily_report) as total_recitations,
                    AVG(dr.mark) as avg_recitation_mark,
                    COUNT(DISTINCT r.id_review) as total_reviews,
                    AVG(r.mark) as avg_review_mark,
                    COUNT(DISTINCT sa.id_attendance) as total_attendance_records,
                    COUNT(DISTINCT CASE WHEN sa.status = 1 THEN sa.id_attendance END) as present_count,
                    COUNT(DISTINCT CASE WHEN sa.status = 0 THEN sa.id_attendance END) as absent_count
                FROM circles c
                LEFT JOIN users u ON c.id_user = u.id_user
                LEFT JOIN centers cen ON c.id_center = cen.center_id
                LEFT JOIN students s ON c.id_circle = s.id_circle
                LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                    AND DATE(dr.date) BETWEEN ? AND ?
                LEFT JOIN review r ON s.id_student = r.id_student 
                    AND DATE(r.date) BETWEEN ? AND ?
                LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                    AND DATE(sa.date) BETWEEN ? AND ?
                WHERE c.id_center = ?
                GROUP BY c.id_circle, c.name_circle, u.username, cen.name
                ORDER BY c.name_circle
            ");
            $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $id_center]);
        } else {
            // جميع الحلقات
            $stmt = $con->prepare("
                SELECT 
                    c.id_circle,
                    c.name_circle,
                    u.username as teacher_name,
                    cen.name as center_name,
                    COUNT(DISTINCT s.id_student) as total_students,
                    COUNT(DISTINCT CASE WHEN s.status = 1 THEN s.id_student END) as active_students,
                    COUNT(DISTINCT dr.id_daily_report) as total_recitations,
                    AVG(dr.mark) as avg_recitation_mark,
                    COUNT(DISTINCT r.id_review) as total_reviews,
                    AVG(r.mark) as avg_review_mark,
                    COUNT(DISTINCT sa.id_attendance) as total_attendance_records,
                    COUNT(DISTINCT CASE WHEN sa.status = 1 THEN sa.id_attendance END) as present_count,
                    COUNT(DISTINCT CASE WHEN sa.status = 0 THEN sa.id_attendance END) as absent_count
                FROM circles c
                LEFT JOIN users u ON c.id_user = u.id_user
                LEFT JOIN centers cen ON c.id_center = cen.center_id
                LEFT JOIN students s ON c.id_circle = s.id_circle
                LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                    AND DATE(dr.date) BETWEEN ? AND ?
                LEFT JOIN review r ON s.id_student = r.id_student 
                    AND DATE(r.date) BETWEEN ? AND ?
                LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                    AND DATE(sa.date) BETWEEN ? AND ?
                GROUP BY c.id_circle, c.name_circle, u.username, cen.name
                ORDER BY cen.name, c.name_circle
            ");
            $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
        }
        
        $circles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($circles) {
            echo json_encode(["stat" => "ok", "data" => $circles]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_comprehensive_circle_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_circle = $data['id_circle'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$id_circle) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الحلقة"]);
            return;
        }
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        // معلومات الحلقة
        $stmt = $con->prepare("
            SELECT c.*, u.username as teacher_name, cen.name as center_name
            FROM circles c
            LEFT JOIN users u ON c.id_user = u.id_user
            LEFT JOIN centers cen ON c.id_center = cen.center_id
            WHERE c.id_circle = ?
        ");
        $stmt->execute([$id_circle]);
        $circle_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$circle_info) {
            echo json_encode(["stat" => "error", "msg" => "الحلقة غير موجودة"]);
            return;
        }
        
        // قائمة الطلاب مع أدائهم
        $stmt = $con->prepare("
            SELECT 
                s.id_student,
                s.name_student,
                COUNT(DISTINCT dr.id_daily_report) as total_recitations,
                AVG(dr.mark) as avg_recitation_mark,
                MAX(dr.mark) as max_recitation_mark,
                MIN(dr.mark) as min_recitation_mark,
                COUNT(DISTINCT r.id_review) as total_reviews,
                AVG(r.mark) as avg_review_mark,
                COUNT(DISTINCT sa.id_attendance) as total_attendance_days,
                COUNT(DISTINCT CASE WHEN sa.status = 1 THEN sa.id_attendance END) as present_count,
                COUNT(DISTINCT CASE WHEN sa.status = 0 THEN sa.id_attendance END) as absent_count
            FROM students s
            LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                AND DATE(dr.date) BETWEEN ? AND ?
            LEFT JOIN review r ON s.id_student = r.id_student 
                AND DATE(r.date) BETWEEN ? AND ?
            LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                AND DATE(sa.date) BETWEEN ? AND ?
            WHERE s.id_circle = ?
              AND s.status = 1
            GROUP BY s.id_student, s.name_student
            ORDER BY s.name_student
        ");
        $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $id_circle]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [
            "circle_info" => $circle_info,
            "students" => $students
        ];
        
        echo json_encode(["stat" => "ok", "data" => $result]);
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_circle_comparison(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $circle_ids = $data['circle_ids'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$circle_ids || !is_array($circle_ids) || count($circle_ids) < 2) {
            echo json_encode(["stat" => "error", "msg" => "يجب اختيار حلقتين على الأقل للمقارنة"]);
            return;
        }
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        // بناء placeholders للحلقات
        $placeholders = implode(',', array_fill(0, count($circle_ids), '?'));
        
        // جلب بيانات الحلقات للمقارنة
        // ترتيب الـ parameters: التواريخ أولاً (6 parameters) ثم circle_ids
        $params = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
        $params = array_merge($params, $circle_ids);
        
        $stmt = $con->prepare("
            SELECT 
                c.id_circle,
                c.name_circle,
                u.username as teacher_name,
                COUNT(DISTINCT s.id_student) as total_students,
                COUNT(DISTINCT CASE WHEN s.status = 1 THEN s.id_student END) as active_students,
                COUNT(DISTINCT dr.id_daily_report) as total_recitations,
                AVG(dr.mark) as avg_recitation_mark,
                MAX(dr.mark) as max_recitation_mark,
                MIN(dr.mark) as min_recitation_mark,
                COUNT(DISTINCT r.id_review) as total_reviews,
                AVG(r.mark) as avg_review_mark,
                COUNT(DISTINCT sa.id_attendance) as total_attendance_records,
                COUNT(DISTINCT CASE WHEN sa.status = 1 THEN sa.id_attendance END) as present_count,
                COUNT(DISTINCT CASE WHEN sa.status = 0 THEN sa.id_attendance END) as absent_count
            FROM circles c
            LEFT JOIN users u ON c.id_user = u.id_user
            LEFT JOIN students s ON c.id_circle = s.id_circle
            LEFT JOIN daily_report dr ON s.id_student = dr.id_student 
                AND DATE(dr.date) BETWEEN ? AND ?
            LEFT JOIN review r ON s.id_student = r.id_student 
                AND DATE(r.date) BETWEEN ? AND ?
            LEFT JOIN student_attendance sa ON s.id_student = sa.id_student 
                AND DATE(sa.date) BETWEEN ? AND ?
            WHERE c.id_circle IN ($placeholders)
            GROUP BY c.id_circle, c.name_circle, u.username
            ORDER BY c.name_circle
        ");
        $stmt->execute($params);
        $circles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($circles) {
            echo json_encode(["stat" => "ok", "data" => $circles]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ: " . $e->getMessage()
        ]);
    }
}

function select_visit_results_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_center = $data['id_center'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        $whereClause = "";
        $params = [$start_date, $end_date];
        
        if ($id_center) {
            $whereClause = "AND c.id_center = ?";
            $params[] = $id_center;
        }
        
        $stmt = $con->prepare("
            SELECT 
                v.id_visit,
                v.date as visit_date,
                vt.name_visit_type as visit_type,
                c.name_circle,
                u.username as teacher_name,
                cen.name as center_name,
                COUNT(DISTINCT ver.id_result) as total_results,
                AVG(ver.hifz_monthly) as avg_hifz_monthly,
                AVG(ver.tilawa_monthly) as avg_tilawa_monthly,
                AVG(ver.hifz_revision) as avg_hifz_revision,
                AVG(ver.tilawa_revision) as avg_tilawa_revision
            FROM visits v
            LEFT JOIN visits_type vt ON v.id_visit_type = vt.id_visit_type
            LEFT JOIN circles c ON v.id_circle = c.id_circle
            LEFT JOIN users u ON c.id_user = u.id_user
            LEFT JOIN centers cen ON c.id_center = cen.center_id
            LEFT JOIN visit_exam_result ver ON v.id_visit = ver.id_visit
            WHERE DATE(v.date) BETWEEN ? AND ?
            $whereClause
            GROUP BY v.id_visit, v.date, vt.name_visit_type, c.name_circle, u.username, cen.name
            ORDER BY v.date DESC
        ");
        $stmt->execute($params);
        $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($visits) {
            echo json_encode(["stat" => "ok", "data" => $visits]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات زيارات"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["stat" => "error", "msg" => "حدث خطأ: " . $e->getMessage()]);
    }
}

function select_visit_notes_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_center = $data['id_center'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        $whereClause = "";
        $params = [$start_date, $end_date];
        
        if ($id_center) {
            $whereClause = "AND c.id_center = ?";
            $params[] = $id_center;
        }
        
        $stmt = $con->prepare("
            SELECT 
                nft.id_notes,
                nft.date as note_date,
                nft.notes as note,
                c.name_circle,
                u.username as teacher_name,
                cen.name as center_name,
                v.username as visitor_name
            FROM notes_for_teacher nft
            LEFT JOIN circles c ON nft.id_circle = c.id_circle
            LEFT JOIN users u ON c.id_user = u.id_user
            LEFT JOIN centers cen ON c.id_center = cen.center_id
            LEFT JOIN users v ON nft.responsible_user_id = v.id_user
            WHERE DATE(nft.date) BETWEEN ? AND ?
            $whereClause
            ORDER BY nft.date DESC
        ");
        $stmt->execute($params);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($notes) {
            echo json_encode(["stat" => "ok", "data" => $notes]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد ملاحظات"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["stat" => "error", "msg" => "حدث خطأ: " . $e->getMessage()]);
    }
}

function select_visit_statistics_report(){
    global $con;
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_center = $data['id_center'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        
        if (!$start_date || !$end_date) {
            echo json_encode(["stat" => "error", "msg" => "يجب تحديد الفترة الزمنية"]);
            return;
        }
        
        $whereClause = "";
        $params = [$start_date, $end_date, $start_date, $end_date];
        
        if ($id_center) {
            $whereClause = "AND c.id_center = ?";
            $params[] = $id_center;
        }
        
        $stmt = $con->prepare("
            SELECT 
                c.id_circle,
                c.name_circle,
                u.username as teacher_name,
                cen.name as center_name,
                COUNT(DISTINCT v.id_visit) as total_visits,
                COUNT(DISTINCT ver.id_result) as total_exam_results,
                AVG(ver.hifz_monthly) as avg_hifz_monthly,
                AVG(ver.tilawa_monthly) as avg_tilawa_monthly,
                COUNT(DISTINCT nft.id_notes) as total_notes
            FROM circles c
            LEFT JOIN users u ON c.id_user = u.id_user
            LEFT JOIN centers cen ON c.id_center = cen.center_id
            LEFT JOIN visits v ON c.id_circle = v.id_circle 
                AND DATE(v.date) BETWEEN ? AND ?
            LEFT JOIN visit_exam_result ver ON v.id_visit = ver.id_visit
            LEFT JOIN notes_for_teacher nft ON c.id_circle = nft.id_circle 
                AND DATE(nft.date) BETWEEN ? AND ?
            WHERE 1=1
            $whereClause
            GROUP BY c.id_circle, c.name_circle, u.username, cen.name
            HAVING total_visits > 0
            ORDER BY total_visits DESC, c.name_circle
        ");
        $stmt->execute($params);
        $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($statistics) {
            echo json_encode(["stat" => "ok", "data" => $statistics]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد بيانات"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["stat" => "error", "msg" => "حدث خطأ: " . $e->getMessage()]);
    }
}

function select_teacher_evaluation_criteria(){
    global $con;
    try {
        $stmt = $con->prepare("SELECT * FROM teacher_evaluation_criteria ORDER BY display_order");
        $stmt->execute();
        $criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($criteria) {
            echo json_encode(["stat" => "ok", "data" => $criteria]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد معايير"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["stat" => "error", "msg" => "حدث خطأ: " . $e->getMessage()]);
    }
}

function select_students_evaluation_criteria(){
    global $con;
    try {
        $stmt = $con->prepare("SELECT * FROM students_evaluation_criteria ORDER BY display_order");
        $stmt->execute();
        $criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($criteria) {
            echo json_encode(["stat" => "ok", "data" => $criteria]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد معايير"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["stat" => "error", "msg" => "حدث خطأ: " . $e->getMessage()]);
    }
}

function select_parents_contacts() {
    global $con;

    try {
        // استقبال البيانات القادمة من Flutter
        $data = json_decode(file_get_contents("php://input"), true);
        $id_circle = $data['id_circle'] ?? null;

        $whereClause = "";
        $params = [];

        // في حال تم إرسال رقم الحلقة
        if ($id_circle) {
            $whereClause = "WHERE s.id_circle = ? and s.status = 1";
            $params[] = $id_circle;
        }

        // ✅ الاستعلام المعدل
        $stmt = $con->prepare("
            SELECT 
                s.id_student,
                s.name_student AS student_name,
                s.guardian AS parent_phone,
                c.name_circle AS circle_name
            FROM students s
            LEFT JOIN circles c ON s.id_circle = c.id_circle
            $whereClause
            ORDER BY s.name_student
        ");

        $stmt->execute($params);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($contacts && count($contacts) > 0) {
            echo json_encode(["stat" => "ok", "data" => $contacts]);
        } else {
            echo json_encode(["stat" => "no", "msg" => "لا توجد جهات اتصال في هذه الحلقة"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["stat" => "error", "msg" => "حدث خطأ: " . $e->getMessage()]);
    }
}

