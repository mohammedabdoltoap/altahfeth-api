<?php
include "../connect.php";



switch($_GET['mark'])
{

    case "addPlan":addPlan();
    break;
}


function addPlan(){

    global $con;

    $data=json_decode(file_get_contents("php://input"),true);
      insert($con,"monthly_plans",array(
    "id_student"=> $data["id_student"],
    "start_id_soura"=>  $data["start_id_soura"],
    "start_ayat"=>  $data["start_ayat"],
    "end_id_soura"=> $data["end_id_soura"],
    "end_ayat"=> $data["end_ayat"],
    "amount_value"=>$data["amount_value"],
    "id_user"=>$data["id_user"],
    "id_month"=>$data["id_month"]
      ));
    


   
}