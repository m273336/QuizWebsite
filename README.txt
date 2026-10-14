MID Quiz: An Interactive Quiz Management System

1. Audience, Problem, and Benefit
Audience: Teachers and students in a classroom setting.
The Problem: Paper-based quizzes waste physical resources and demand significant manual grading time from instructors, causing delays in student feedback.
The Benefit: Our web application digitizes the assessment process. It eliminates paper waste, saves hours through automated grading, and provides students with immediate feedback to enhance their learning.

2. Application-Specific Features
Create Quiz (Teacher): An instructor enters a title, adds multiple-choice questions with an answer key, and publishes the assessment. This generates a unique quiz in the database that is instantly available to the class.
Take Quiz (Student): A student selects an active quiz from their dashboard, inputs their answers, and submits. The system records the submission and securely locks the quiz from retakes.
Auto-Grade (System): Upon submission, the application immediately compares the student's answers against the teacher's key. It instantly calculates the score, updates the teacher's gradebook, and displays the final result to the student.
Login (System): A student or instructor is able to login and interact with the webpage based on their role. Teachers have the ability to create quizzes and remove students from their online classroom. Students can take the quizzes made by the instructor. Both teacher and student can view grades for a quiz. 

3. Planned Pages, Database Structure & Action Sketch
Planned Pages:
Landing/Login Page (Registration and Authentication)
Teacher Dashboard (View active quizzes, access gradebook)
Quiz Builder Interface (Form to add questions/answers)
Student Dashboard (List of available and completed quizzes)
Quiz Interface (Where the student takes the test)
Results/Gradebook Page
Stored Fields and Relationships (Database Plan):
User Database
Grade database
Quiz Database
User Action Sketch (Taking a Quiz):
Student logs in successfully → Redirected to Student Dashboard → Clicks on "Week 1 Web Dev Quiz" → The Quiz Interface loads the questions from the database → Student fills out the radio buttons for their answers → Click "Submit Quiz" → The backend evaluates the answers, calculates the score, and saves it to the Results Table → Student is redirected to a "Success" page displaying their score of 90%.

4. Permissions, Search, and Ajax integration
Permissions:
Member (Student): Can view and take quizzes assigned to them, and view their own personal past grades. Cannot edit or view other students' data.
Administrator (Teacher): Ability to create quizzes for students. Can view all student grades and manage classroom rosters.
Visitor (Other): Only access to the login page, but can create an account to become a Member. Creating Administrators requires IT implementation. 
Search Feature: A search bar will be implemented on the Teacher Dashboard, allowing the teacher to quickly search for a specific student's name to pull up their grade history.
Ajax Integration: Ajax will be used on the Quiz Builder Interface. When a teacher clicks "Add Another Question," Ajax will seamlessly append new question fields to the form without requiring the page to reload. It will also be used during the quiz-taking process to auto-save student answers as they go, preventing data loss if they accidentally refresh the page.

5. Team Responsibilities and Collaboration Plan
Member 1 (Frontend & UI/UX): Responsible for designing the HTML/CSS templates, ensuring the site is responsive.
Member 2 (Backend & Database): Responsible for designing the database schema, setting up the server, handling user authentication (login/registration), and session management.
Member 3 (Backend Logic & Routing): Responsible for the core application logic, writing the auto-grading algorithm, writing database queries to fetch questions, and connecting the frontend forms to the backend database.
