<?php require_once('../private/initialize.php');
$page_title = 'Student Added';
include(SHARED_PATH . '/header.php');

if(is_post_request()) {
    
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : 'fname';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : 'lname';
    $student_id = isset($_POST['studentID']) ? $_POST['studentID'] : 'sid';
    
    $result = add_student($student_id, $first_name, $last_name);
    
    //Announce addition of student
    $new_student = get_single_student($student_id);
    echo '<h3>Success! Student ' . $new_student['first_name'] . ' ' . 
    $new_student['last_name'] . ' has been added!</h3>';
    
    //Enroll student in courses
    foreach($_POST['enrolledIn'] as $course) { 
        $course_id = get_course_from_code($course)['course_id'];
        //echo $course_id . ', ';
        enroll_student($student_id, $course_id);
        echo $first_name . ' has been enrolled in ' . $course . '.<br>';
    }
    
    
    
} else {
    redirect_to(url_for('/index.php'));
}
/* echo '<p>You wish to add ' . $first_name . ' ' . $last_name .
', with SID = ' . $student_id . ', to the course.</p>'; */

?>
<?php include(SHARED_PATH . '/footer.php'); ?>