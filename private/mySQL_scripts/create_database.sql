DROP DATABASE IF EXISTS team_generator;
CREATE DATABASE team_generator;
USE team_generator;

-- Create table for courses
DROP TABLE IF EXISTS course;
CREATE TABLE course (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(6) UNIQUE NOT NULL,
    name VARCHAR(40)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

-- Create table for students of course
DROP TABLE IF EXISTS student;
CREATE TABLE student (
    sid INT(9) PRIMARY KEY,
    first_name VARCHAR(40),
    last_name VARCHAR(40)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

-- Create linking table for students and courses
DROP TABLE IF EXISTS student_course;
CREATE TABLE student_course (
    sid INT(9),
    course_id INT,
    INDEX (sid, course_id),
    PRIMARY KEY (sid, course_id),
    FOREIGN KEY (sid)
        REFERENCES student(sid)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (course_id)
        REFERENCES course(course_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

-- Create table for teams
DROP TABLE IF EXISTS team;
CREATE TABLE team (
    team_id INT AUTO_INCREMENT PRIMARY KEY
) ENGINE=INNODB DEFAULT CHARSET=utf8;

-- Create table to link students in their course with teams they are in
DROP TABLE IF EXISTS student_team;
CREATE TABLE student_team (
    no INT NOT NULL AUTO_INCREMENT,
    sid INT(9) NOT NULL,
    course_id INT NOT NULL,
    team_id INT NOT NULL,
    
    PRIMARY KEY(no),
    INDEX (sid, course_id),
    INDEX team_id,
    
    FOREIGN KEY (sid, course_id)
        REFERENCES student_course(sid, course_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
        
    FOREIGN KEY (team_id)
        REFERENCES team(team_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
    
) ENGINE=INNODB DEFAULT CHARSET=utf8;