<?php
function get_courses() {
    global $db;
    $sql = 'SELECT * FROM course;';
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return($result);
}

function get_single_course($id) {
    global $db;
    $sql = 'SELECT * FROM course WHERE course_id=\'' . $id . '\';';
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $course = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $course;
}

function get_course_from_code($code) {
    global $db;
    $sql = 'SELECT * FROM course WHERE code=\'' . $code . '\';';
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $course = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $course;
}

function get_students($course_id = NULL) {
    global $db;
    $sql = 'SELECT * FROM student';
    /* If course is specified, add to query
    so that only students in that course
    are selected. Then close query with a
    semicolon. */
    if(isset($course_id)) {
        $sql .= ' INNER JOIN student_course ';
        $sql .= 'ON student.sid=student_course.sid ';
        $sql .= 'WHERE student_course.course_id=' . $course_id;
    }
    $sql .= ';';
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function get_single_student($sid) {
    global $db;
    $sql = 'SELECT * FROM student WHERE sid=\'' . $sid . '\';';
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $student = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $student;
}

function get_students_by_team($team_id = NULL) {
    global $db;
    $sql = 'SELECT sid FROM student_team';
    if(isset($team_id)) {
        $sql .= ' WHERE team_id=' . $team_id;
    }
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function past_teams($course_id) {
    global $db;
    $sql = 'SELECT * FROM student_team';
    $sql .= ' WHERE course_id=' . $course_id;
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function teams_ive_been_in($sid) {
    global $db;
    $sql = 'SELECT team_id from student_team';
    $sql .= ' WHERE sid=' . $sid;
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function add_course($code, $name) {
    global $db;
    $sql = 'INSERT INTO course (code, name) VALUES (';
    $sql .= '\'' . $code . '\', \'' . $name . '\');';
    $result = mysqli_query($db, $sql);
    if($result) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function add_student($sid, $f_name, $l_name) {
    global $db;
    $sql = 'INSERT INTO student (sid, first_name, last_name) VALUES (';
    $sql .= '\'' . $sid . '\', ';
    $sql .= '\'' . $f_name . '\', ';
    $sql .= '\'' . $l_name . '\')';
    $result = mysqli_query($db, $sql);
    if($result) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function enroll_student($sid, $course_id) {
    global $db;
    if(!strlen($sid)==9) {
        echo 'SID must be a nine digit number. Return to the main form and try'
        . ' again';
        exit;
    }
    $sql = 'INSERT INTO student_course (sid, course_id) VALUES (';
    $sql .= '\'' . $sid . '\', ';
    $sql .= '\'' . $course_id . '\')';
    $result = mysqli_query($db, $sql);
    if($result) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function create_team($students, $course_id) {
    global $db;
    $sql = 'INSERT INTO team (team_id) VALUES (0)';
    $result = mysqli_query($db, $sql);
    $new_team = mysqli_insert_id($db);
    echo "New team: $new_team";
    foreach($students as $student) {
        $sql = 'INSERT INTO student_team (sid, course_id, team_id) VALUES(';
        $sql .= '\'' . $student . '\', ';
        $sql .= '\'' . $course_id . '\', ';
        $sql .= '\'' . $new_team . '\')';
        $student_added = mysqli_query($db, $sql);
    }
    if($student_added) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}
?>