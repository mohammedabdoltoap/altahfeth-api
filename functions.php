<?php
define("MB",10246789);
function filterRequest($requestname){
return htmlspecialchars(strip_tags($_POST[$requestname]));
}

function select($conn,$table,$conditions=null,$columns='*',$max=null){
    if(is_array($columns)){
        $columns=implode(", ",$columns);
    }
    if(!empty($conditions)){
        $conditionPartArray =[];
        foreach($conditions as $key => $value){
           $conditionPartArray[]="$key = :$key";
        }
        $conditionPart=implode(" AND ",$conditionPartArray);
    }
    if($conditions==null){
        $sql="SELECT $columns FROM $table ";
    }else{
        $sql="SELECT $columns FROM $table WHERE $conditionPart ";
    }
try{
    $stmt=$conn->prepare($sql);
    if(!empty($conditions)){
        foreach($conditions as $key =>$value){
            $stmt->bindValue(":$key",$value);
        }
    }

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if($data){
        if($max!=null){
            return $data[0]['max(id_prodact)'];
        }
        echo json_encode(array("stat"=>"ok","data"=>$data));
    }else{
        echo json_encode(array("stat"=>"no"));
    }
        }catch(PDOException $e){
         
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }

}

function selectAndFilter($conn, $table, $conditions = null, $columns = '*') {
    // تحويل الأعمدة من array إلى string
    if (is_array($columns)) {
        $columns = implode(", ", $columns);
    }

    // تحضير شرط WHERE إذا موجود
    $conditionPart = '';
    if (!empty($conditions)) {
        $conditionPartsArray = [];
        foreach ($conditions as $key => $value) {
            $conditionPartsArray[] = "$key = :$key";
        }
        $conditionPart = "WHERE " . implode(" AND ", $conditionPartsArray);
    }

    $sql = "SELECT $columns FROM $table $conditionPart";

    try {
        $stmt = $conn->prepare($sql);

        // ربط القيم
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
        }

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // إرجاع البيانات أو null إذا لا يوجد
        if ($data) {
            return $data;
        } else {
            return null;
        }
    } catch(PDOException $e){
         
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
}

function insert($conn, $table, $data) {
    $keys = implode(", ", array_keys($data));               // أسماء الأعمدة
    $placeholders = ":" . implode(", :", array_keys($data)); // علامات الربط

    $sql = "INSERT INTO $table ($keys) VALUES ($placeholders)";

    try {
        $stmt = $conn->prepare($sql);

        // ربط القيم بالعلامات
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        $lastId = $conn->lastInsertId(); // آخر ID تم إدخاله

        $count = $stmt->rowCount();
        if ($count > 0) {
            echo json_encode([
                "stat" => "ok",
                "data" => $lastId
            ]);
        } else {
            echo json_encode([
                "stat" => "no",
                "data" => null
            ]);
        }

    } catch(PDOException $e){
         
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
}


function update($conn, $table, $data, $conditions) {
    $setPart = [];
    foreach ($data as $key => $value) {
        $setPart[] = "$key = :$key";
    }
    $setPart = implode(", ", $setPart);

    $conditionPart = [];
    foreach ($conditions as $key => $value) {
        $conditionPart[] = "$key = :cond_$key";
    }
    $conditionPart = implode(" AND ", $conditionPart);

    $sql = "UPDATE $table SET $setPart WHERE $conditionPart";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":cond_$key", $value);
        }

        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count > 0) {
            echo json_encode(["stat" => "ok", "data" => $count]);
        } else {
            echo json_encode(["stat" => "no"]);
        }
    } catch(PDOException $e){
         
         echo json_encode([
            "stat" => "error",
            "msg" => "حدث خطأ أثناء تنفيذ الاستعلام: " . $e->getMessage()
        ]);
        
        }
}
