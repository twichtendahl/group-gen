<?php require_once('../private/initialize.php');
$page_title = 'Course Added';
include(SHARED_PATH . '/header.php');
if(is_post_request()) {
    $code = $_POST['course_code'];
    $name = $_POST['course_name'];
    $result = add_course($code, $name);
    $new_course = get_single_course(mysqli_insert_id($db));
    echo '<h3>Success! Course ' . $new_course['code'] . ' has been added!</h3>';
} else {
    redirect_to(url_for('/index.php'));
}
?>
