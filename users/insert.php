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
    case "insert_visits":insert_visits();
    break;
    case "insert_leave_requests":insert_leave_requests();
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
          insert($con,"resignation",array(
        "id_user"=>$data["id_user"], 
        "statuse"=>1
      ));     
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
  
function insert_visits(){

  global $con;
  try{
  $data=json_decode(file_get_contents("php://input"),true);
  insert($con,"visits",array(
            "id_visit_type"=> $data["id_visit_type"],
            "id_user"=> $data["id_user"],
            "id_year"=> $data["id_year"],
            "id_month"=> $data["id_month"],
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

        // 🔹 أولاً: نتحقق هل يوجد طلب بنفس التاريخ لنفس المستخدم
        $stmt = $con->prepare("SELECT COUNT(*) FROM leave_requests WHERE id_user = ? AND date_leave = ?");
        $stmt->execute([$id_user, $date_leave]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // ⚠️ وجدنا طلب سابق في نفس التاريخ
            echo json_encode(["stat" => "exist", "msg" => "لقد قمت بتقديم طلب إجازة في نفس التاريخ مسبقًا"]);
            return;
        }

        // 🔹 لو ما في طلب بنفس اليوم، نضيف الجديد
         insert($con, "leave_requests", [
            "id_user" => $id_user,
            "reason_leave" => $reason_leave,
            "date_leave" => $date_leave,
        ]);

    } catch (Exception $e) {
        echo json_encode(["stat" => "error", "msg" => $e->getMessage()]);
    }
}
