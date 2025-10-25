<?php
include "../connect.php";



switch($_GET['mark'])
{

    case "insertStudents":insertStudents();
    break;
    case "insertAttendance":insertAttendance();
    break;
    case "insert_student_skills":insert_student_skills();
    break;

}
function insertStudents(){
    global $con;
        header('Content-Type: application/json; charset=utf-8');
    $data = json_decode(file_get_contents("php://input"), true);

    insert(
        $con,
        "students",
        array(
            "name_student" => $data['name_student'],
            "address_student" => $data['address_student'],
            "surname"=>$data['surname'],
            "place_of_birth"=>$data['place_of_birth'],
            "date_of_birth"=>$data['date_of_birth'],
            "phone"=>$data['phone'],
            "school_name"=>$data['school_name'],
            "classroom"=>$data['classroom'],
            "guardian"=>$data['guardian'],
            "jop"=> $data['jop'],
            "id_stages" => $data['id_stages'], 
            "id_level" => $data['id_level'],
            "status" => $data['status'],
            "id_circle"=> $data['id_circle'],
            "sex"=>$data["sex"],
            "qualification"=>$data["qualification"],
            "chronic_diseases"=>$data["chronic_diseases"],
            "id_reder" => $data["id_reder"],
            "password"=>$data["password"]
        )
    );
}


function insertAttendance(){
    global $con;
    // قراءة البيانات من JSON المرسل
    $data = json_decode(file_get_contents("php://input"), true);

   

    $failed = [];
     // لتخزين الطلاب الذين لم يُدخلوا

    foreach($data["students"] as $student) {
        $id_student = $student['id_student'];
        $id_circle  = $student['id_circle'];
        $id_user    = $student['id_user'];
        $status     = $student['status'];
        $notes      = $student['notes'];

        // بناء الاستعلام
        $stmt = $con->prepare("INSERT INTO student_attendance 
            (id_student, id_circle, id_user, `status`, notes) VALUES ('$id_student','$id_circle', '$id_user', '$status', '$notes')");
        
        if(!$stmt->execute()) {
            $failed[] = $id_student;
        }

    }

    if(empty($failed)) {
        echo json_encode(["stat" => "ok", "data" => "تم إدخال جميع الطلاب بنجاح"]);
    } else {
        echo json_encode([
            "stat" => "no",
            "failed_students" => $failed
        ]);
    }
}


function insert_student_skills(){

    global $con;

    // قراءة البيانات من JSON المرسل
    $data = json_decode(file_get_contents("php://input"), true);
    insert(
        $con,
        "student_skill",
        array(
            "id_student" => $data['id_student'],
            "id_skill"=>$data['id_skill'],
            "avaluation"=>$data['avaluation'],
            )
    );
 
}



