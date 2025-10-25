<?php

include "../connect.php";

switch($_GET['mark'])
{

   
    case "select_levels":select_levels();
    break;
    case "select_ids_month":select_ids_month();
    break;
    case "select_students":select_students();
    break;
    case "select_students_attendance":select_students_attendance();
    break;
    case "check_attendance":check_attendance();
    break;
    case "select_attendance":select_attendance();
    break;
    case "select_data_student":select_data_student();
    break;
    case "select_absence_report":select_absence_report();
    break;
   case "select_review_report":select_review_report();
    break;
    case "select_daily_report":select_daily_report();
    break;
    case "select_reders":select_reders();
    break;
    case "select_skill":select($con,"skill");
    break;
    case "select_student_skill":select_student_skill();
    break;


}

function select_levels(){
    global $con;
    $sql="SELECT stages.name_stages, `level`.* FROM level
    INNER JOIN stages
    ON stages.id_stages = level.id_stages;
 ";
 $result=$con->prepare($sql);   
    $result->execute(); 
    if ($result) {
        $r=$result->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array("stat" => "ok", "data" => $r));     
    } else {
        echo json_encode(array("stat" => "no"));
    }

}

function select_ids_month() {
    global $con;
    $data=json_decode(file_get_contents("php://input"), true);
    $id_student = $data['id_student'];
    $sql = "SELECT id_month FROM monthly_plans WHERE id_student = $id_student";
    $result = $con->prepare($sql);
    $result->execute();

    if ($result) {
        // هنا نجيب البيانات ونحولها مباشرة لقائمة من القيم
        $r = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        echo json_encode(array("stat" => "ok", "data" => $r));
    } else {
        echo json_encode(array("stat" => "no"));
    }
}

function select_students() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);
    $id_circle = $data['id_circle']; 
    
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
        echo json_encode([  "stat" => "ok","data"=>$students]); 
    else 
        echo json_encode(["stat"=>"no"]);
    
}
function select_students_attendance() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);
    
   $res= selectAndFilter($con, "students", ["id_circle" =>  $data['id_circle'] , "status" => 1], ["id_student", "name_student",]);
   if($res!=null)
   {for($i=0;$i<count($res);$i++){
       $res[$i]["status"]=true;
        $res[$i]["notes"]="";
    }
    echo json_encode([  "stat" => "ok","data"=>$res]);   
}
else
{
    echo json_encode(["stat"=>"no"]);   
}




}

function check_attendance() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);
    $id_circle = $data['id_circle']; 
    $date = $data['date']; 

     $stmt = $con->prepare("
            SELECT id_attendance FROM student_attendance
            WHERE id_circle = '$id_circle' AND DATE(date)= '$date' 
        ");
        $stmt->execute();
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($attendance)
        echo json_encode(["stat" => "no"]); 
    else 
        echo json_encode(["stat"=>"ok"]);
    
}

function select_attendance() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);
    $id_circle = $data['id_circle']; 
    $date = $data['date']; 

     $stmt = $con->prepare("
            SELECT sa.*, s.name_student
            FROM student_attendance sa
            JOIN students s ON sa.id_student = s.id_student
            WHERE sa.id_circle = '$id_circle' AND DATE(sa.date)= '$date' 
        ");
        $stmt->execute();
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        for($i=0;$i<count($attendance);$i++){
            $attendance[$i]["status"] = $attendance[$i]["status"] == 1 ? true : false;
        }
        if($attendance)
        echo json_encode([  "stat" => "ok","data"=>$attendance]); 
    else 
        echo json_encode(["stat"=>"no"]);
    
}
function select_data_student() {
    global $con;
    
    $data=json_decode(file_get_contents("php://input"), true);
    
    $name_student = $data['name_student'];
    $password = $data['password'];
 
    $student = selectAndFilter($con, "students", [
        "name_student" => $name_student,
        "password" => $password
    ]);
    if($student)
    {
        echo json_encode(array("stat" => "ok", "data" => $student[0])); 
    }
    else
    {
        echo json_encode(array("stat" => "no", "msg" => "User not found")); 
    }
}

// function select_report_students() {
//     global $con;

//     try{
//     $data = json_decode(file_get_contents("php://input"), true);
//     $id_student = $data['id_student'];

//     // 🔹 استعلام التسميع اليومي
//     $stmt = $con->prepare("
//         SELECT 
//             students.id_student, 
//             students.name_student,
//             students.date,
//             daily_report.*,
//             circles.name_circle,
//             users.username,
//             sq_from.soura_name AS from_soura_name,
//             sq_to.soura_name AS to_soura_name,
//             level.name_level,
//             stages.name_stages
//         FROM students
//         JOIN daily_report ON students.id_student = daily_report.id_student
//         JOIN circles ON daily_report.id_circle = circles.id_circle
//         JOIN users ON daily_report.id_user = users.id_user
//         JOIN sour_quran AS sq_from ON daily_report.from_id_soura = sq_from.id_soura
//         JOIN sour_quran AS sq_to   ON daily_report.to_id_soura   = sq_to.id_soura
//         JOIN level ON level.id_level = students.id_level
//         JOIN stages ON stages.id_stages = level.id_stages
//         WHERE students.id_student = :id_student
//     ");
//     $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
//     $stmt->execute();
//     $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     // 🔹 استعلام جدول المراجعة (review)
//     $stmt = $con->prepare("
//         SELECT 
//             students.id_student, 
//             students.name_student,
//             students.date,
//             review.*,
//             circles.name_circle,
//             users.username,
//             sq_from.soura_name AS from_soura_name,
//             sq_to.soura_name AS to_soura_name,
//             level.name_level,
//             stages.name_stages
//         FROM students
//         JOIN review ON students.id_student = review.id_student
//         JOIN circles ON review.id_circle = circles.id_circle
//         JOIN users ON review.id_user = users.id_user
//         JOIN sour_quran AS sq_from ON review.from_id_soura = sq_from.id_soura
//         JOIN sour_quran AS sq_to   ON review.to_id_soura   = sq_to.id_soura
//         JOIN level ON level.id_level = students.id_level
//         JOIN stages ON stages.id_stages = level.id_stages
//         WHERE students.id_student = :id_student
//     ");
//     $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
//     $stmt->execute();
//     $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     // 🔹 استعلام جدول الغياب
//     $stmt = $con->prepare("
//         SELECT id_attendance, date, notes, status 
//         FROM student_attendance 
//         WHERE id_student = :id_student AND status = 0
//     ");
//     $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
//     $stmt->execute();
//     $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     // 🔹 إرجاع كل البيانات
//     if ($students || $reviews || $attendance) {
//         echo json_encode([
//             "stat" => "ok",
//             "daily_report" => $students,   // تسميع يومي
//             "attendance" => $attendance, // الغياب
//             "reviews" => $reviews,    // المراجعة
//         ]);
//     } else {
//         echo json_encode(["stat" => "no"]);
//     }}catch(Exception $e){
//         echo json_encode(array("stat"=>"erorr","msg"=>$e->getMessage()));
//     }
// }




function select_daily_report() {
    global $con;

    try {
        $data = json_decode(file_get_contents("php://input"), true);
$id_student = $data['id_student'];

        $stmt = $con->prepare("
            SELECT 
                students.id_student, 
                students.name_student,
                students.date,
                daily_report.*,
                circles.name_circle,
                users.username,
                sq_from.soura_name AS from_soura_name,
                sq_to.soura_name AS to_soura_name,
                level.name_level,
                stages.name_stages
            FROM students
            JOIN daily_report ON students.id_student = daily_report.id_student
            JOIN circles ON daily_report.id_circle = circles.id_circle
            JOIN users ON daily_report.id_user = users.id_user
            JOIN sour_quran AS sq_from ON daily_report.from_id_soura = sq_from.id_soura
            JOIN sour_quran AS sq_to   ON daily_report.to_id_soura   = sq_to.id_soura
            JOIN level ON level.id_level = students.id_level
            JOIN stages ON stages.id_stages = level.id_stages
            WHERE students.id_student = :id_student
        ");
        $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["stat" => $result ? "ok" : "no", "daily_report" => $result]);
    } catch (Exception $e) {
        echo json_encode(["stat" => "error", "msg" => $e->getMessage()]);
    }
}

function select_review_report() {
    global $con;

    try {
        $data = json_decode(file_get_contents("php://input"), true);
$id_student = $data['id_student'];

        $stmt = $con->prepare("
            SELECT 
                students.id_student, 
                students.name_student,
                students.date,
                review.*,
                circles.name_circle,
                users.username,
                sq_from.soura_name AS from_soura_name,
                sq_to.soura_name AS to_soura_name,
                level.name_level,
                stages.name_stages
            FROM students
            JOIN review ON students.id_student = review.id_student
            JOIN circles ON review.id_circle = circles.id_circle
            JOIN users ON review.id_user = users.id_user
            JOIN sour_quran AS sq_from ON review.from_id_soura = sq_from.id_soura
            JOIN sour_quran AS sq_to   ON review.to_id_soura   = sq_to.id_soura
            JOIN level ON level.id_level = students.id_level
            JOIN stages ON stages.id_stages = level.id_stages
            WHERE students.id_student = :id_student
        ");
        $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["stat" => $result ? "ok" : "no", "reviews" => $result]);
    } catch (Exception $e) {
        echo json_encode(["stat" => "error", "msg" => $e->getMessage()]);
    }
}

function select_absence_report() {
    global $con;

    try {
$data = json_decode(file_get_contents("php://input"), true);
$id_student = $data['id_student'];


        $stmt = $con->prepare("
            SELECT id_attendance, date, notes, status 
            FROM student_attendance 
            WHERE id_student = :id_student AND status = 0
        ");
        $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["stat" => $result ? "ok" : "no", "attendance" => $result]);
    } catch (Exception $e) {
        echo json_encode(["stat" => "error", "msg" => $e->getMessage()]);
    }
}

function select_student_skill(){

    global $con;
    try{
    $data = json_decode(file_get_contents("php://input"), true);
    $stm=$con->prepare("select student_skill.*,skill.* 
    from student_skill
    join  skill on skill.id_skill=student_skill.id_skill
    where student_skill.id_student=? 
    ");
    $stm->execute([$data["id_student"]]);
    $skill=$stm->fetchAll(PDO::FETCH_ASSOC);
    if($skill){
        echo json_encode([
            "stat" => "ok",
            "data" => $skill,
        ]);
    } else {
        echo json_encode([
            "stat" => "no",
        ]);
    }}catch(Exception $e){
        echo json_encode([
            "stat" => "erorr",
            "msg" => $e->getMessage(),
        ]);
}
}






function select_reders(){

    global $con;
    
    select($con,"reders");
    
}

