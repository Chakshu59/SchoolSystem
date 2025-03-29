create table account
	(email		varchar(50),
	 password	varchar(20) not null,
	 type		varchar(20),
	 primary key(email)
	);


create table department
	(dept_name	varchar(100), 
	 location	varchar(100), 
	 primary key (dept_name)
	);

create table instructor
	(instructor_id		varchar(10),
	 instructor_name	varchar(50) not null,
	 title 			varchar(30),
	 dept_name		varchar(100), 
	 email			varchar(50) not null,
	 primary key (instructor_id)
	);


create table student
	(student_id		varchar(10), 
	 name			varchar(20) not null, 
	 email			varchar(50) not null,
	 dept_name		varchar(100), 
	 primary key (student_id),
	 foreign key (dept_name) references department (dept_name)
		on delete set null
	);

create table PhD
	(student_id			varchar(10), 
	 qualifier			varchar(30), 
	 proposal_defence_date		date,
	 dissertation_defence_date	date, 
	 primary key (student_id),
	 foreign key (student_id) references student (student_id)
		on delete cascade
	);

create table master
	(student_id		varchar(10), 
	 total_credits		int,	
	 primary key (student_id),
	 foreign key (student_id) references student (student_id)
		on delete cascade
	);

create table undergraduate
	(student_id		varchar(10), 
	 total_credits		int,
	 class_standing		varchar(10)
		check (class_standing in ('Freshman', 'Sophomore', 'Junior', 'Senior')), 	
	 primary key (student_id),
	 foreign key (student_id) references student (student_id)
		on delete cascade
	);

create table classroom
	(classroom_id 		varchar(8),
	 building		varchar(15) not null,
	 room_number		varchar(7) not null,
	 capacity		numeric(4,0),
	 primary key (classroom_id)
	);

create table time_slot
	(time_slot_id		varchar(8),
	 day			varchar(10) not null,
	 start_time		time not null,
	 end_time		time not null,
	 primary key (time_slot_id)
	);

create table course
	(course_id		varchar(20), 
	 course_name		varchar(50) not null, 
	 credits		numeric(2,0) check (credits > 0),
	 primary key (course_id)
	);

create table section
	(course_id		varchar(20),
	 section_id		varchar(10), 
	 semester		varchar(6)
			check (semester in ('Fall', 'Winter', 'Spring', 'Summer')), 
	 year			numeric(4,0) check (year > 1990 and year < 2100), 
	 instructor_id		varchar(10),
	 classroom_id   	varchar(8),
	 time_slot_id		varchar(8),	
	 primary key (course_id, section_id, semester, year),
	 foreign key (course_id) references course (course_id)
		on delete cascade,
	 foreign key (instructor_id) references instructor (instructor_id)
		on delete set null,
	 foreign key (time_slot_id) references time_slot(time_slot_id)
		on delete set null
	);

create table prereq
	(course_id		varchar(20), 
	 prereq_id		varchar(20) not null,
	 primary key (course_id, prereq_id),
	 foreign key (course_id) references course (course_id)
		on delete cascade,
	 foreign key (prereq_id) references course (course_id)
	);

create table advise
	(instructor_id		varchar(8),
	 student_id		varchar(10),
	 start_date		date not null,
	 end_date		date,
	 primary key (instructor_id, student_id),
	 foreign key (instructor_id) references instructor (instructor_id)
		on delete  cascade,
	 foreign key (student_id) references PhD (student_id)
		on delete cascade
);

create table TA
	(student_id		varchar(10),
	 course_id		varchar(8),
	 section_id		varchar(10), 
	 semester		varchar(6),
	 year			numeric(4,0),
	 primary key (student_id, course_id, section_id, semester, year),
	 foreign key (student_id) references PhD (student_id)
		on delete cascade,
	 foreign key (course_id, section_id, semester, year) references 
	     section (course_id, section_id, semester, year)
		on delete cascade
);

create table masterGrader
	(student_id		varchar(10),
	 course_id		varchar(8),
	 section_id		varchar(10), 
	 semester		varchar(6),
	 year			numeric(4,0),
	 primary key (student_id, course_id, section_id, semester, year),
	 foreign key (student_id) references master (student_id)
		on delete cascade,
	 foreign key (course_id, section_id, semester, year) references 
	     section (course_id, section_id, semester, year)
		on delete cascade
);

create table undergraduateGrader
	(student_id		varchar(10),
	 course_id		varchar(8),
	 section_id		varchar(10), 
	 semester		varchar(6),
	 year			numeric(4,0),
	 primary key (student_id, course_id, section_id, semester, year),
	 foreign key (student_id) references undergraduate (student_id)
		on delete cascade,
	 foreign key (course_id, section_id, semester, year) references 
	     section (course_id, section_id, semester, year)
		on delete cascade
);

create table take
	(student_id		varchar(10), 
	 course_id		varchar(8),
	 section_id		varchar(10), 
	 semester		varchar(6),
	 year			numeric(4,0),
	 grade		    	varchar(2)
		check (grade in ('A+', 'A', 'A-','B+', 'B', 'B-','C+', 'C', 'C-','D+', 'D', 'D-','F')), 
	 primary key (student_id, course_id, section_id, semester, year),
	 foreign key (course_id, section_id, semester, year) references 
	     section (course_id, section_id, semester, year)
		on delete cascade,
	 foreign key (student_id) references student (student_id)
		on delete cascade
	);

create table alerts
	(student_id		varchar(24),
	 alert_type	varchar(24),
	 alert		varchar(1024),
	 primary key(student_id, alert_type),
	 foreign key (student_id) references student (student_id)
	);

insert into department (dept_name, location) value ('Miner School of Computer & Information Sciences', 'Dandeneau Hall, 1 University Avenue, Lowell, MA 01854');

insert into account (email, password, type) values ('admin@uml.edu', '123456', 'admin');
insert into account (email, password, type) values ('dbadams@cs.uml.edu', '123456', 'instructor');
insert into account (email, password, type) values ('slin@cs.uml.edu', '123456', 'instructor');
insert into account (email, password, type) values ('Yelena_Rykalova@uml.edu', '123456', 'instructor');
insert into account (email, password, type) values ('Johannes_Weis@uml.edu', '123456', 'instructor');
insert into account (email, password, type) values ('Charles_Wilkes@uml.edu', '123456', 'instructor');
insert into account (email, password, type) values ('Hugo_akitaya@uml.edu', '123456', 'instructor');

insert into account (email, password, type) values ('Chak_Pat@student.uml.edu', '654321', 'student');
insert into account (email, password, type) values ('Prath_Kot@student.uml.edu', '654321', 'student');
insert into account (email, password, type) values ('Rus_John@student.uml.edu', '654321', 'student');
insert into account (email, password, type) values ('Peter_Griffin@student.uml.edu', '654321', 'student');
insert into account (email, password, type) values ('Bob_Smith@student.uml.edu', '654321', 'student');
insert into account (email, password, type) values ('John_Doe@student.uml.edu', '654321', 'student');

insert into student (student_id, name, email, dept_name) values ('12345678','Chak Pat','Chak_Pat@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345679','Prath Kot','Prath_Kot@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345680','Rus John','Rus_John@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345681','Peter Griffin','Peter_Griffin@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345682','Bob Smith','Bob_Smith@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345683','John Doe','John_Doe@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345684','Jane Doe','Jane_Doe@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345685','John Doe','John_Doe1@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345686','Billy James','Billy_James@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345687','Abe Lincoln','Abe_Lincoln@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345688','George Wash','George_Wash@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345689','Jack Andrews','Jack_Andrews@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345690','Tony Stark','Tony_Stark@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345691','Bruce Banner','Bruce_Banner@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345692','Steve Rogers','Steve_Rogers@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345693','Steven Johnson','Steven_Johnson@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345694','Clark Kent','Clark_Kent@student.uml.edu','Miner School of Computer & Information Sciences');
insert into student (student_id, name, email, dept_name) values ('12345695','Bruce Wayne','Bruce_Wayne@student.uml.edu','Miner School of Computer & Information Sciences');

insert into instructor (instructor_id, instructor_name, title, dept_name, email) value ('1', 'David Adams', 'Teaching Professor', 'Miner School of Computer & Information Sciences','dbadams@cs.uml.edu');
insert into instructor (instructor_id, instructor_name, title, dept_name, email) value ('2', 'Sirong Lin', 'Associate Teaching Professor', 'Miner School of Computer & Information Sciences','slin@cs.uml.edu');
insert into instructor (instructor_id, instructor_name, title, dept_name, email) value ('3', 'Yelena Rykalova', 'Associate Teaching Professor', 'Miner School of Computer & Information Sciences', 'Yelena_Rykalova@uml.edu');
insert into instructor (instructor_id, instructor_name, title, dept_name, email) value ('4', 'Johannes Weis', 'Assistant Teaching Professor', 'Miner School of Computer & Information Sciences','Johannes_Weis@uml.edu');
insert into instructor (instructor_id, instructor_name, title, dept_name, email) value ('5', 'Tom Wilkes', 'Assistant Teaching Professor', 'Miner School of Computer & Information Sciences','Charles_Wilkes@uml.edu');
insert into instructor (instructor_id, instructor_name, title, dept_name, email) value ('6', 'Hugo Akitaya', 'Assistant Professor', 'Miner School of Computer & Information Sciences','hugo_akitaya@uml.edu');

insert into undergraduate (student_id, total_credits) values ('12345678', 90);
insert into undergraduate (student_id, total_credits) values ('12345679', 90);
insert into undergraduate (student_id, total_credits) values ('12345680', 90);
insert into undergraduate (student_id, total_credits) values ('12345681', 60);
insert into undergraduate (student_id, total_credits) values ('12345682', 64);
insert into undergraduate (student_id, total_credits) values ('12345683', 0);

insert into master (student_id, total_credits) values ('12345684', 120);
insert into master (student_id, total_credits) values ('12345685', 128);
insert into master (student_id, total_credits) values ('12345686', 129);
insert into master (student_id, total_credits) values ('12345687', 131);
insert into master (student_id, total_credits) values ('12345688', 140);
insert into master (student_id, total_credits) values ('12345689', 120);

insert into PhD (student_id, qualifier, proposal_defence_date, dissertation_defence_date) values ('12345690', 'Yes', '2025-01-01', '2025-01-01');
insert into PhD (student_id, qualifier, proposal_defence_date, dissertation_defence_date) values ('12345691', 'Yes', '2025-01-01', '2025-01-01');
insert into PhD (student_id, qualifier, proposal_defence_date, dissertation_defence_date) values ('12345692', 'Yes', '2025-01-01', '2025-01-01');
insert into PhD (student_id, qualifier, proposal_defence_date, dissertation_defence_date) values ('12345693', 'Yes', '2025-01-01', '2025-01-01');
insert into PhD (student_id, qualifier, proposal_defence_date, dissertation_defence_date) values ('12345694', 'Yes', '2025-01-01', '2025-01-01');

insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS1', 'MoWeFr', '8:00:00', '8:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS2', 'MoWeFr', '9:00:00', '9:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS3', 'MoWeFr', '10:00:00', '10:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS4', 'MoWeFr', '11:00:00', '11:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS5', 'MoWeFr', '12:00:00', '12:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS6', 'MoWeFr', '13:00:00', '13:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS7', 'MoWeFr', '14:00:00', '14:50:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS8', 'TuTh', '11:00:00', '12:15:00');
insert into time_slot (time_slot_id, day, start_time, end_time) value ('TS9', 'TuTh', '12:30:00', '13:45:00');

insert into classroom (classroom_id,building,room_number,capacity) values ('DAN401','Dandeneu','401',30);
insert into classroom (classroom_id,building,room_number,capacity) values ('DAN415','Dandeneu','415',45);
insert into classroom (classroom_id,building,room_number,capacity) values ('OLS415','Olsen Hall','303',40);
insert into classroom (classroom_id,building,room_number,capacity) values ('OLN150','Olney Hall','150',200);
insert into classroom (classroom_id,building,room_number,capacity) values ('BALL213','Ball Hall','213',30);

insert into course (course_id, course_name, credits) values ('COMP1010', 'Computing I', 3);
insert into course (course_id, course_name, credits) values ('COMP1020', 'Computing II', 3);
insert into course (course_id, course_name, credits) values ('COMP2010', 'Computing III', 3);
insert into course (course_id, course_name, credits) values ('COMP2040', 'Computing IV', 3);
insert into course (course_id, course_name, credits) values ('COMP3040', 'Operating Systems', 3);

insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP1010', 'Section101', 'Fall', 2023, '1','DAN401' ,'TS1');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP1010', 'Section102', 'Fall', 2023, '1', 'DAN401', 'TS2');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP2010', 'Section101', 'Fall', 2023,'5','DAN401','TS3');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP1010', 'Section103', 'Fall', 2023,'3','DAN415','TS3');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP1020', 'Section101', 'Spring', 2024,'3','DAN415','TS2');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP1020', 'Section102', 'Spring', 2024,'2','OLS415','TS2');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP1010', 'Section104', 'Fall', 2023,'2','OLS415','TS3');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP2010', 'Section102', 'Fall', 2023,'4','OLN150','TS1');
insert into section (course_id, section_id, semester, year, instructor_id, classroom_id, time_slot_id) values ('COMP2040', 'Section201', 'Spring', 2024,'4','BALL213','TS2');

insert into prereq (course_id, prereq_id) values ('COMP1020','COMP1010');
insert into prereq (course_id, prereq_id) values ('COMP2010','COMP1020');
insert into prereq (course_id, prereq_id) values ('COMP2040','COMP2010');
insert into prereq (course_id, prereq_id) values ('COMP3040','COMP2010');

insert into advise (instructor_id, student_id) values ('1','12345690');
insert into advise (instructor_id, student_id) values ('1','12345691');
insert into advise (instructor_id, student_id) values ('1','12345692');
insert into advise (instructor_id, student_id) values ('1','12345693');
insert into advise (instructor_id, student_id) values ('1','12345694');

insert into undergraduategrader (student_id, course_id, section_id, semester, year) values ('12345681', 'COMP1010', 'Section101','Fall',2023);
insert into undergraduategrader (student_id, course_id, section_id, semester, year) values ('12345682', 'COMP1010', 'Section102', 'Fall',2023);
insert into undergraduategrader (student_id, course_id, section_id, semester, year) values ('12345678', 'COMP1020', 'Section101', 'Spring',2024);
insert into undergraduategrader (student_id, course_id, section_id, semester, year) values ('12345679', 'COMP1020', 'Section102','Spring' ,2024);
insert into undergraduategrader (student_id, course_id, section_id, semester, year) values ('12345680', 'COMP2010', 'Section101','Fall' ,2023);

insert into mastergrader (student_id, course_id, section_id, year, semester) values ('12345684', 'COMP2010', 'Section102', 2023, 'Fall');
insert into mastergrader (student_id, course_id, section_id, year, semester) values ('12345685', 'COMP1010', 'Section103', 2023, 'Fall');
insert into mastergrader (student_id, course_id, section_id, year, semester) values ('12345686', 'COMP1010', 'Section103', 2023, 'Fall');
insert into mastergrader (student_id, course_id, section_id, year, semester) values ('12345687', 'COMP2010', 'Section102', 2023, 'Fall');
insert into mastergrader (student_id, course_id, section_id, year, semester) values ('12345688', 'COMP2040', 'Section201', 2024, 'Spring');

insert into TA (student_id, course_id, section_id, semester, year) values ('12345690', 'COMP2040', 'Section201', 'Spring', 2024);
insert into TA (student_id, course_id, section_id, semester, year) values ('12345691', 'COMP2010', 'Section101', 'Fall', 2023);
insert into TA (student_id, course_id, section_id, semester, year) values ('12345692', 'COMP2010', 'Section102', 'Fall', 2023);
insert into TA (student_id, course_id, section_id, semester, year) values ('12345693', 'COMP1010', 'Section101', 'Fall', 2023);
insert into TA (student_id, course_id, section_id, semester, year) values ('12345694', 'COMP1010', 'Section102', 'Fall', 2023);

insert into take (student_id, course_id, section_id, semester, year) values ('12345678','COMP2010','Section101','Fall',2023);
insert into take (student_id, course_id, section_id, semester, year) values ('12345679','COMP2010','Section102','Fall',2023);
insert into take (student_id, course_id, section_id, semester, year) values ('12345680','COMP1020','Section101','Spring',2024);
insert into take (student_id, course_id, section_id, semester, year) values ('12345678','COMP1020','Section101','Spring',2024);
insert into take (student_id, course_id, section_id, semester, year) values ('12345684','COMP1010','Section101','Fall',2023);


