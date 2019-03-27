-- Create table for assignments
DROP TABLE IF EXISTS assignment;
CREATE TABLE assignment (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_name VARCHAR(40)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

-- Create table for teams
DROP TABLE IF EXISTS team;
CREATE TABLE team (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT,
    FOREIGN KEY (assignment_id)
        REFERENCES assignment(assignment_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

-- Create linking table for representing students in groups
DROP TABLE IF EXISTS team_student;
CREATE TABLE team_student (
    team_id INT,
    sid INT(9),
    course_id INT,
    PRIMARY KEY (team_id, sid, course_id),
    FOREIGN KEY (team_id)
        REFERENCES team(team_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (sid)
        REFERENCES student_course(sid)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (course_id)
        REFERENCES student_course(course_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;