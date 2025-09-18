<?php

include "../connect.php";



switch($_GET['mark'])
{

    // case "students":select($con,"students");
    // break;
    case "select_levels":select_levels();
    break;

   case "select_ids_month":select_ids_month();
    
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
