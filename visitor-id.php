<?php

session_start();
if (!isset($_GET['id'])) {
    var_dump(http_response_code(403));
    echo 'Error 403: Forbidden';
} else {
    // $_SESSION['visitor_id'] = $_GET['id'];
    $gen_time = date();
    $_SESSION['visitor_gen_time'] = $gen_time;
    $fnz_id = hash('sha256', $_GET['id'] + $gen_time);

    setcookie('v_id', $_GET['id'], time() + 3600);
    setcookie('fnz_id', $fnz_id, time() + 3600);
    var_dump(http_response_code(200));
    echo 'Success';
}
?>