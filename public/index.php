<?php require_once('../private/initialize.php'); ?>
<?php $page_title='Team Generator'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>
<?php
if(is_post_request()) {
    $course = get_course_from_code($_POST['selectCourse']); //is an assoc array
    $students = get_students($course['course_id']); //is a result object
    
    //construct an array of SIDs of students in course
    $enrollment = array();
    while($student = mysqli_fetch_assoc($students)) {
        $enrollment[] = $student['sid'];
    }
    mysqli_data_seek($students, 0);
    
    
    $team_size = 4; //avoids devision by 0 later on
    
    //set team size to desired number if it was specified, otherwise it's 4
    if(isset($_POST['teamSize']) && $_POST['teamSize'] > 0) {
        $team_size = $_POST['teamSize'];
    }
    $num_teams = floor($students->num_rows/$team_size);
    echo "<h4>Number of students: $students->num_rows</h4>";
    echo "<h4>Number of students per team: $team_size</h4>";
    echo "<h4>Number of teams: $num_teams</h4>";
    
    //get all student-team associations in the course's history (result object)
    $team_history = past_teams($course['course_id']);
    
    //get the team id of the most recently created team, or 0 if no teams yet
    $most_recent_team_id = 0;
    while($row = mysqli_fetch_assoc($team_history)) {
        if($row['team_id'] > $most_recent_team_id) {
            $most_recent_team_id = $row['team_id'];
        }
    }
    mysqli_data_seek($team_history, 0);
    
    /* Create arrays of SIDs to represent new teams. Initialize each team array
    with either a student from most recent team or next student in enrollment 
    array. Enrollment array represents students remaining to be added to teams,
    and when a student is assigned to a team they are removed from enrollment.
    */
    $teams = array();
    if($most_recent_team_id > 0) { // true if prior teams exist
        $most_recent_team = get_students_by_team($most_recent_team_id);
        for($i = 1; $i <= $num_teams; $i++) {
            /* Get the sid of each student in the most recently created team,
             * then 'seed' a new team with that student's sid. Student will
             * be removed from enrollment, since that array has un-teamed
             * students, using the unset() and array_values() methods. */
            $student_in_recent_team = mysqli_fetch_assoc($most_recent_team);
            $next_sid = $student_in_recent_team['sid'];
            $teams[] = array($next_sid);
            array_splice($enrollment, array_search($next_sid, $enrollment), 1);
        }
        mysqli_free_result($most_recent_team);
    } else { // no prior teams exist; seed teams with first few students
        for($i = 0; $i < $num_teams; $i++) {
            $teams[] = array($enrollment[0]);
            echo "Future team $i has been started by student " . 
            implode($teams[$i]) . '<br/>';
            array_splice($enrollment, 0, 1);
        }
        reset($enrollment);
    }
    
    /* For each student left in enrollment, test their suitability for inclusion
     * in each group. If they've been in a group with N students once previously
     * and with K students twice previously, then they are scored against that 
     * group as N + 2K. It works analogously for "more-than-twice" overlap. */
    foreach($enrollment as $sid) {
        // Get list of team_ids for teams this student has been on
        $my_teams_result = teams_ive_been_in($sid);
        $my_teams = array();
        while($this_team = mysqli_fetch_assoc($my_teams_result)) {
            $my_teams[] = $this_team['team_id'];
        }
        mysqli_free_result($my_teams_result);
        
        //Reporting & Debugging
        echo "Student $sid has been on teams ";
        foreach($my_teams as $past_team) {
            echo "$past_team, ";
        }
        echo "</br>";
        reset($my_teams);
        
        // Array will have the form sid => count
        $students_ive_worked_with = array();
        foreach($my_teams as $past_team) {
            $teammates = get_students_by_team($past_team);
            while($teammate = mysqli_fetch_assoc($teammates)) {
                if($teammate['sid'] != $sid) {
                    $students_ive_worked_with[$teammate['sid']]++;
                }
            }
            mysqli_free_result($teammates);
        }
        
        //Reporting & Debugging
        echo "Student $sid has worked with: <ul>";
        foreach($students_ive_worked_with as $prior_teammate => $this_many) {
            echo "<li>$prior_teammate $this_many time(s)</li>";
        }
        echo "</ul>";
        
        $compatibility_scores = array();
        foreach($teams as $team) {
            $compatibility_score = 0;
            foreach($team as $teammate) {
                $compatibility_score += $students_ive_worked_with[$teammate];
            }
            $compatibility_scores[] = $compatibility_score;
        }
        
        //Reporting & Debugging
        echo "Compatibility scores for student $sid:<ul>";
        foreach($compatibility_scores as $key => $score) {
            echo "<li>Team $key: $score</li>";
        }
        reset($compatibility_scores);
        echo "</ul>";
        
        $best_score = min($compatibility_scores);
        echo "Best compatibility score for student $sid: $best_score <br/>";
        
        foreach($teams as $key => &$team) {
            echo "Team $key has " . count($team) . " students already.<br/>" . 
                 "The compatibility score for student $sid with this team is" . 
                 " $compatibility_scores[$key].<br/>";
            if(count($team) < $team_size + 1 && 
            $compatibility_scores[$key] == $best_score) {
                $team[] = $sid;
                print_r($team);
                unset($team);
                break;
            }
        }
    }

    echo "<br/><h4>Teams (prior to database insertion)</h4>";
    foreach($teams as $team) {
        print_r($team);
        echo "<br/>";
    }
    
    // Now we use our array of teams to add the teams to the database
    echo "Teams to add to database:<ul>";
    foreach($teams as $key => $team) {
        create_team($team, $course['course_id']);
        echo "<li>Team $key:";
        foreach($team as $student) {
            echo "<li>Student $student has been added.</li>";
        }
        echo "</li>";
    }
    echo "</ul>";
}

// Gather repeated-use data sets
$course_set = get_courses();
?>
        
        <form id='addCourseForm' action='<?php echo 
        url_for('/add_course.php'); ?>' method='post'>
            <fieldset>
                <legend>Add Course</legend>
               
                <label for = 'course_code'>Course Code</label>
                <input name = 'course_code' type = 'text'/>

                <label for = 'course_name'>Course Name</label>
                <input name = 'course_name' type = 'text'/>

               <button id='createCourse' type='submit'>Create New Course</button>
           </fieldset>
       </form>

        <form id='addStudentForm' action='<?php echo 
        url_for('/add_student.php'); ?>' method='post'>
            <fieldset>
                <legend>Add Student</legend>

                <label for = 'first_name'>First Name</label>
                <input name = 'first_name' type = 'text'/>
               
                <label for = 'last_name'>Last Name</label>
                <input name = 'last_name' type = 'text'/>

                <label for = 'studentID'>Student ID</label>
                <input name = 'studentID' type = 'number'/>
               
                <label for = 'enrolledIn'>Enrolled In:</label>
                <?php
                while($course = mysqli_fetch_assoc($course_set)) {
                    $course_code = $course['code'];
                    $i = $course['course_id']; ?>
                    <input type='checkbox' name='enrolledIn[]' value='<?php
                    echo $course_code; ?>' />
                    <?php echo $course_code;
                    if($i%4 == 0) { echo '<br>'; }
                } ?>

               <button id='createStudent' type='submit'>Create New Student</button>
           </fieldset>
       </form>
      
       <form id='dropStudentForm' action='drop_student.php' method='post'>
          <fieldset>
             <legend>Drop Student</legend>
             
             <label for='dropID'>Student ID</label>
             <input name='dropID' type='number'/>
             
             <button id='dropStudent' type='submit'>Drop Student</button>
          </fieldset>
       </form>

       <form id='createTeamForm' action='index.php' method='post'>
           <fieldset>
               <legend>Create New Teams</legend>
               <label for='selectCourse'>Which course would you like
               to set teams on?</label>
               <select name='selectCourse'>
                    <?php
                    mysqli_data_seek($course_set, 0);
                    while($course = mysqli_fetch_assoc($course_set)) {
                        $course_code = $course['code']; ?>
                        <option value='<?php echo $course_code; ?>'>
                            <?php echo $course_code; ?>
                        </option>
                    <?php } ?>
                </select>

               <label for = 'teamSize'>Number of students in each team:</label>
               <input name = 'teamSize' type = 'number'/>

               <button type='submit'>Create Teams</button>
           </fieldset>
       </form>

       <div id='teamList'></div>
<?php 
mysqli_free_result($course_set);
include(SHARED_PATH . '/footer.php');
?>
