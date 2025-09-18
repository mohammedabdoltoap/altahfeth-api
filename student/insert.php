<?php
include "../connect.php";



switch($_GET['mark'])
{

    case "insertStudents":insertStudents();
    break;
}
function insertStudents(){
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    insert(
        $con,
        "students",
        array(
            "name_student" => $data['name_student'],
            "age_student" => $data['age_student'],
            "address_student" => $data['address_student'],
            "id_stages" => $data['id_stages'],  // هنا صححت التنصيص
            "id_level" => $data['id_level'],
            "status" => $data['status'],
            "id_circle"=> $data['id_circle']
        )
    );
}
