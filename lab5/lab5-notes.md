Part 1
    1. part1.1 in images folder
    2. In the students table primary key = student_id
        In the tutoring_request table primary key= request_id
    3. The foreign is found in the tutoring_request table and it is the student_id. A foreign key is a column that originates from a different table but can also be found in a different table in a database. This helps link / connect two different tables together.
    4. The student_email column is marked as unique because we want to ensure that no two rows in the student table have the same email address. This would cause confusion in the application and when replying to students (manually or automated).

Part 2
    1. part2.1 in images folder

    2. select * from students;
        select * from tutoring_requests;

    3. Jordan has one student row and two request rows because she represents only 1 student. However every time she makes a request, a new row in the tutoring_request is created. This data entry includes her student_id (foreign key) which ties to the student_id (primary key) in the students table. Since multiple tutor request entries from a single student can be tied to a single student_id, there does not need to be two rows / entries for Jordan in the student table.


Part 3
    (query)
            select 
            r.request_id,
            s.student_name,
            s.student_email,
            r.course, 
            r.topic,
            r.preferred_date, 
            r.preferred_time, 
            r.status
            from tutoring_requests AS r
            join students AS s ON r.student_id = s.student_id
            order by r.preferred_date;

    (output) *NOTE* since the queries are run on the tutoring.sql page there are many duplicate tutoring request entires, Professor Chong Oh said not to worry about this in the lecture

            1|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms - updated|2026-10-12|15:00|Cancelled
            3|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            4|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            5|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            6|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            7|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            8|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            9|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            10|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            11|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            15|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            19|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            23|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            27|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            31|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            35|Maria Lopez|maria.lopez@example.com|IS 6465|PHP arrays and forms|2026-11-03|14:00|Open
            12|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            16|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            20|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            24|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            28|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            32|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            36|Jordan Lee|jordan.lee@example.com|IS 6465|Forms|2026-11-09|18:00|Open
            14|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            18|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            22|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            26|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            30|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            34|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            38|Avery Patel|avery.patel@example.com|IS 6465|SQLite CRUD|2026-11-10|12:00|Open
            13|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open
            17|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open
            21|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open
            25|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open
            29|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open
            33|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open
            37|Jordan Lee|jordan.lee@example.com|IS 6465|SQL SELECT|2026-11-12|16:00|Open

    1. The JOIN condition connects two tables together. In this case it was the connection between the students and the tutoring_request table. It specifically connected the tables by matching the student_id primary key in the students table to the student_id foreign key in the tutoring_request table.

    2.The JOIN and WHERE are completely different in function. The JOIN is used to connect to tables adding more results / structure to a query. The WHERE condition is used to filter a query to more finite results. For example we used the WHERE condition earlier to only show us rows that contained a one specific request_id.

    3.The reason to retreive a request based on request_id versus topic only simply comes down to specificity. If we used topic when trying to retreive a singluar request, potentially hundreds of results could populate. If we use request_id, only one tutoring request will populate because every request has a unique request_id.

Part 4
    1.
    2.
    3.
    4.
    5.

Part 5
    1.
    2.
    3.
    4.


Part 6
    Table:
    1.
    2.
    3.
    4.
    

