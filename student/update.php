<?php 

include "../connect.php";

switch($_GET['mark'])
{

    case "updateAttendance":updateAttendance();
    break;
    case "update_Student":update_Student();
    break;
    case "update_student_skill":update_student_skill();
    break;

}

function updateAttendance(){
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        foreach($data["students"] as $student) {
            $id_attendance = $student['id_attendance'];
            $id_circle     = $student['id_circle'];
            $id_user       = $student['id_user'];
            $status        = $student['status'];
            $notes         = $student['notes'];

            $sql = "UPDATE student_attendance 
                    SET `status` = :status,
                        `notes` = :notes
                    WHERE id_attendance = :id_attendance
                      AND id_circle = :id_circle 
                      AND id_user = :id_user";
            
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':id_attendance', $id_attendance);
            $stmt->bindParam(':id_circle', $id_circle);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
        }

        echo json_encode([
            "stat" => "ok", 
            "message" => "تم تحديث الحضور بنجاح"
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            "stat" => "no",
            "message" => "حصل خطأ أثناء التحديث",
            "error" => $e->getMessage()
        ]);
    }
}

function update_Student(){
    global $con;
    try{
    $data = json_decode(file_get_contents("php://input"), true);
    update($con, "students", [
        "name_student" => $data["name_student"],
       "age_student" => $data["age_student"],
       "address_student" => $data["address_student"],
       "surname"=>$data["surname"],
       "place_of_birth"=>$data["place_of_birth"],
       "date_of_birth"=>$data["date_of_birth"],
       "phone"=>$data["phone"],
       "school_name"=>$data["school_name"],
       "classroom"=>$data["classroom"],
       "guardian"=>$data["guardian"],
       "jop"=>$data["jop"],
       "sex"=>$data["sex"],
       "qualification"=>$data["qualification"],
       "chronic_diseases"=>$data["chronic_diseases"],
       "id_reder"=>$data["id_reder"],
       "password"=>$data["password"],
    ], [
        "id_student" => $data["id_student"]
    ]);
}catch(PDOException $e) {
    echo json_encode([
        "stat" => "error",
        "message" =>  $e->getMessage(),
    ]);
    return;
}

    

}

function update_student_skill(){
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    update($con, "student_skill", [
        "avaluation" => $data["avaluation"],
    ], [
        "id" => $data["id"]
    ]);

   
    }