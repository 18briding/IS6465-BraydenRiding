
-- Part 1 creating the db tables

CREATE TABLE if not exists students (
  student_id INTEGER PRIMARY KEY,
  student_name TEXT NOT NULL,
  student_email TEXT NOT NULL UNIQUE
);

CREATE TABLE if not exists tutoring_requests (
  request_id INTEGER PRIMARY KEY,
  student_id INTEGER NOT NULL,
  course TEXT NOT NULL,
  topic TEXT NOT NULL,
  preferred_date TEXT NOT NULL,
  preferred_time TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'Open',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- *NOTE* the below section was used to follow along during the lecture video

INSERT OR ignore INTO students (student_name, student_email)
VALUES ('Maria Lopez', 'maria.lopez@example.com');

INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (1, 'IS 6465', 'PHP arrays and forms', '2026-11-03', '14:00');

  select * from students;

   SELECT
  r.request_id,
  s.student_name,
  s.student_email,
  r.course, r.topic,
  r.preferred_date, r.preferred_time, r.status
FROM tutoring_requests AS r
JOIN students AS s ON r.student_id = s.student_id
ORDER BY r.preferred_date;

UPDATE tutoring_requests
SET topic = 'PHP arrays and forms - updated',
    preferred_date = '2026-10-12',
    preferred_time = '15:00'
WHERE request_id = 1;

select * from tutoring_requests;


DELETE from tutoring_requests
WHERE request_id = 2;

select * from tutoring_requests;

UPDATE tutoring_requests
SET status = 'Cancelled'
WHERE request_id = 1;

select * from tutoring_requests;

-- *NOTE* the above code was used to follow along in the lecture video

-- Part 2 inserting new entries to the student table and the tutoring_request table


INSERT OR ignore INTO students (student_name, student_email)
VALUES ('Jordan Lee', 'jordan.lee@example.com');

INSERT OR ignore INTO students (student_name, student_email)
VALUES ('Avery Patel', 'avery.patel@example.com');

INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (2, 'IS 6465', 'Forms', '2026-11-09', '18:00');

INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (2, 'IS 6465', 'SQL SELECT', '2026-11-12', '16:00');

INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (3, 'IS 6465', 'SQLite CRUD', '2026-11-10', '12:00');


select * from students;
select * from tutoring_requests;

-- Part 3 Read the stored data

--3.1
select * from students;

--3.2 
select * from tutoring_requests;

--3.3
select * from tutoring_requests
where status = 'Open';

--3.4
select * from tutoring_requests
where request_id = 3;

--3.5
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


--4.1 (update one of Jordan's requests)

UPDATE tutoring_requests
SET topic = 'Talking about CATS now',
    preferred_date = '2030-10-12'
WHERE request_id = 12;

--4.2 (change Avery's request to cancelled)

UPDATE tutoring_requests
SET status = 'Cancelled'
WHERE request_id = 14;



--5.1 (creating a fake request for deletion)
INSERT OR ignore INTO tutoring_requests
  (student_id, course, topic, preferred_date, preferred_time)
VALUES
  (2, 'IS 6465', 'FAKE request for DELETION', '2026-11-15', '10:00');


--5.2 (deleting the fake request)

DELETE from tutoring_requests
WHERE request_id = 51;

-- verifying that fake request has been deleted
select * from tutoring_requests;