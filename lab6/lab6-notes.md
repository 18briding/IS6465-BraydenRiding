PART 1

    1)To create the tables in the tutoring.db database we ran the command "sqlite3 database/tutoring.db < database/setup-database.sql"
    which runs the setup-database.sql command against the tutoring.db database. 
    
    Then to confirm that tables were created we ran the .tables command in the terminal. The output contained "students" and "tutoring_request." This indicates that there are two tables in the database present with those names. Then to confirm there was information in each table I ran "Select * from students;" and "select * from tutoring_requests;"

        This was the output:
        1|Maria Lopez|maria.lopez@example.com
        1|1|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open|2026-11-11 05:18:53

    This indicates that there is one student row/entry in the students tables and one rown/entry in the tutoring_requests table. 

PART 2

    1) https://zany-memory-gxrp4g5r4r75hwvv6-8000.app.github.dev/php/request-details.php?request_id=2

    2) After pressing submit I was taken to a page with a title of "Request 2." There is also an option to edit or cancel this request. To confirm that the request submitted, I clicked on the "Requests" link in the header which took me to a list of all requests. Two requests were present, the first one from Maria Lopez in the setup code we ran and this one. 

    3)part2.3.png in the images folder
![Submission Proof](images/part2.3.png)

PART 3

    1)part3.1.png in the images folder
![Submission Proof](images/part3.1.png)

PART 4
    1) request-form.php
    2) process-request.php
    3) db.php 
    4)requestService.php
    5) studentPDO.php / requestPDO.php
    6) students
    7) tutoring_requests
    8) A tutoring request connects to the student who submitted it via the student_id column. This is a primary key in the students table and a foreign key in the tutoring_request table. 
    9) ** 2-4 sentences at the end **

PART 5

PART 6

PART 7


