<?php   

include "../connect.php";

switch($_GET['mark'])
{

    case "select_users":select_users();
    break;
    case "getstudents":getstudents();
    break;
    case "select_fromId_soura_with_to_soura":select_fromId_soura_with_to_soura();
    break;
    case "get_circle":get_circle();
    break;
    case "getLastDailyReport":getLastDailyReport();
    break;
    case "getLastReview":getLastReview();
    break;
    case "select_Holiday_Days":select_Holiday_Days();
    break;
    case "select_users_attendance_today":select_users_attendance_today();
    break;
    case "select_visits_type_months_years":select_visits_type_months_years();
    break;
    case "select_circle_for_center":select_circle_for_center();
    break;
    case "select_report_visits":select_report_visits();
    break;
    case "select_years":select_years();
    break;
    case "select_visits":select_visits();
    break;
    case "select_data_exam":select_data_exam();
    break;
    case "select_student_exam":select_student_exam();
    break;
    case "select_evaluations":select($con,"evaluations");
    break;
    case "select_leave_requests":select_leave_requests();
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

        // التحقق من النتائج قبل الإرسال
        if ($visits_type && $months && $years) {
            echo json_encode([
                "stat" => "ok",
                "visits_type" => $visits_type,
                "months" => $months,
                "years" => $years,
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
    $id_user = $data['id_user'];

    $stm=$con->prepare("SELECT circles.* from centers,circles where centers.center_id=circles.id_center and centers.id_user=?");
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

function select_report_visits(){

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
            
            -- نجمع بيانات الاختبارات للحفظ والمراجعة
            MAX(CASE WHEN se.id_courses_type = 1 THEN 1 ELSE 0 END) AS has_monthly_exam,
            MAX(CASE WHEN se.id_courses_type = 2 THEN 1 ELSE 0 END) AS has_review_exam,
            
            MAX(CASE WHEN se.id_courses_type = 1 THEN se.mark END) AS monthly_mark,
            MAX(CASE WHEN se.id_courses_type = 2 THEN se.mark END) AS review_mark,
            MAX(CASE WHEN se.id_courses_type = 1 THEN se.note END) AS monthly_note,
            MAX(CASE WHEN se.id_courses_type = 2 THEN se.note END) AS review_note

        FROM students s
        JOIN level l ON s.id_level = l.id_level
        JOIN stages st ON l.id_stages = st.id_stages
        LEFT JOIN student_exam se 
            ON s.id_student = se.id_student 
            AND se.id_visit = :visit_id
        WHERE s.id_circle = :circle_id
          AND s.status = 1
        GROUP BY 
            s.id_student, s.name_student,
            l.id_level, l.name_level,
            st.id_stages, st.name_stages
        ORDER BY s.name_student
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
    $dataCome = json_decode(file_get_contents("php://input"), true);

    $student_id = $dataCome["student_id"];
    $visit_id = $dataCome["visit_id"];
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
        "data" => $data
    ]);
}


function select_leave_requests(){

    global $con;
$data = json_decode(file_get_contents("php://input"), true);
$id_user = $data['id_user'];
select($con,"leave_requests",array("id_user"=>$id_user));

}