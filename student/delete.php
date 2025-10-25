<?php
include "../connect.php";



switch($_GET['mark'])
{

    case "delet_student_skills":delet_student_skills();
    break;

}

function delet_student_skills(){
    global $con;
    $data=json_decode(file_get_contents("php://input"),true);
     
    delete($con,"student_skill",array(
        "id"=>$data["id"]
    ));
}