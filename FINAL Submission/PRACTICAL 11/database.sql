CREATE DATABASE IF NOT EXISTS student_management;
USE student_management;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL
);

INSERT IGNORE INTO students (name, email, course) VALUES
('Aarav Sharma', 'aarav.sharma@example.com', 'Computer Science'),
('Aditi Patel', 'aditi.patel@example.com', 'Information Technology'),
('Akash Kumar', 'akash.kumar@example.com', 'Electronics'),
('Ananya Singh', 'ananya.singh@example.com', 'Computer Science'),
('Arjun Reddy', 'arjun.reddy@example.com', 'Mechanical Engineering'),
('Diya Nair', 'diya.nair@example.com', 'Information Technology'),
('Ishaan Gupta', 'ishaan.gupta@example.com', 'Civil Engineering'),
('Kavya Rao', 'kavya.rao@example.com', 'Computer Science'),
('Meera Iyer', 'meera.iyer@example.com', 'Electronics'),
('Nikhil Das', 'nikhil.das@example.com', 'Information Technology'),
('Pooja Verma', 'pooja.verma@example.com', 'Computer Science'),
('Rahul Joshi', 'rahul.joshi@example.com', 'Mechanical Engineering'),
('Riya Mehta', 'riya.mehta@example.com', 'Civil Engineering'),
('Rohan Shah', 'rohan.shah@example.com', 'Information Technology'),
('Sana Khan', 'sana.khan@example.com', 'Computer Science'),
('Shreya Bose', 'shreya.bose@example.com', 'Electronics'),
('Siddharth Jain', 'siddharth.jain@example.com', 'Mechanical Engineering'),
('Tanvi Kulkarni', 'tanvi.kulkarni@example.com', 'Information Technology'),
('Varun Menon', 'varun.menon@example.com', 'Civil Engineering'),
('Zoya Ali', 'zoya.ali@example.com', 'Computer Science');
