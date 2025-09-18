<?php   

include "../connect.php";

switch($_GET['mark'])
{

    case "select_users":select_users();
    break;
    case "get_circle_and_students":get_circle_and_students();
    break;
    case "select_fromId_soura_with_to_soura":select_fromId_soura_with_to_soura();
    break;
    
    case 'selectYearsWithMonths':selectYearsWithMonths();
    
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



function get_circle_and_students() {
    global $con;
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $id_user = $data['id_user'] ?? null;

    if (!$id_user) {
        echo json_encode(["stat"=>"error", "msg"=>"id_user missing"]);
        return;
    }

    // جلب الحلقة
    $circle = selectAndFilter($con, "circles", ["id_user" => $id_user]);
    $circle = $circle ? $circle[0] : null;

    $students = [];
    if ($circle) {
        $stmt = $con->prepare("
            SELECT students.*, level.name_level, level.id_level, stages.name_stages, stages.id_stages
            FROM students
            JOIN level ON students.id_level = level.id_level
            JOIN stages ON level.id_stages = stages.id_stages
            WHERE students.id_circle = :circle_id AND students.status = 1
        ");
        $stmt->execute(['circle_id' => $circle['id_circle']]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "stat" => "ok",
        "circle" => $circle,
        "students" => $students
    ]);
}


function select_fromId_soura_with_to_soura() {
    global $con;

    $data = json_decode(file_get_contents("php://input"), true);
    $id_level = $data['id_level'];

    $stmt = $con->prepare("
        SELECT sq.*
        FROM level l
        JOIN sour_quran sq 
          ON sq.id_soura BETWEEN l.from_id_soura AND l.to_id_soura
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
    
}

function selectYearsWithMonths(){
    global $con;

$sql="select y.*,m.*  from years y, months m where y.id_year=m.id_year and m.status=1 ";
    $stm=$con->prepare($sql);
    $stm->execute();
    $data=$stm->fetchAll(PDO::FETCH_ASSOC);
    if($data){
        echo json_encode([
            "stat"=>"ok",
            "data"=>$data
        ]);
    }
    else {
         echo json_encode([
            "stat"=>"no",
                ]);
    }


}



