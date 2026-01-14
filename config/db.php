<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tea_shop_db');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

function db_query($sql, $params = []) {
    global $mysqli;
    
    if (empty($params)) {
        return $mysqli->query($sql);
    }
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        error_log("MySQL Prepare Error: " . $mysqli->error);
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}


function db_fetch_one($sql, $params = []) {
    $result = db_query($sql, $params);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}


function db_fetch_all($sql, $params = []) {
    $result = db_query($sql, $params);
    if (!$result) return [];
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

if (!file_exists('uploads')) {
    mkdir('uploads', 0755, true);
}
?>