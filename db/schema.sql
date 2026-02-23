-- SQL Server schema for trainee management system
-- Safe to run on a clean database. Adjust DB name before deployment.

CREATE TABLE governorates (
    id INT IDENTITY(1,1) PRIMARY KEY,
    code NVARCHAR(10) NOT NULL UNIQUE,
    name_ar NVARCHAR(100) NOT NULL,
    name_en NVARCHAR(100) NOT NULL
);

CREATE TABLE centers (
    id INT IDENTITY(1,1) PRIMARY KEY,
    governorate_id INT NOT NULL,
    name_ar NVARCHAR(120) NOT NULL,
    name_en NVARCHAR(120) NULL,
    CONSTRAINT FK_centers_governorates FOREIGN KEY (governorate_id) REFERENCES governorates(id)
);

CREATE TABLE universities (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name_ar NVARCHAR(150) NOT NULL,
    name_en NVARCHAR(150) NULL
);

CREATE TABLE colleges (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name_ar NVARCHAR(150) NOT NULL,
    name_en NVARCHAR(150) NULL
);

CREATE TABLE program_batches (
    id INT IDENTITY(1,1) PRIMARY KEY,
    code NVARCHAR(30) NOT NULL UNIQUE,
    name_ar NVARCHAR(150) NOT NULL,
    name_en NVARCHAR(150) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    supervisor_user_id INT NULL,
    is_active BIT NOT NULL DEFAULT 1
);

CREATE TABLE app_users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    full_name NVARCHAR(200) NOT NULL,
    email NVARCHAR(200) NOT NULL UNIQUE,
    password_hash NVARCHAR(255) NOT NULL,
    role_code NVARCHAR(30) NOT NULL, -- ITADMIN, ADMIN, SUPERVISOR, MENTOR, TRAINER, TRAINEE
    batch_id INT NULL,
    is_active BIT NOT NULL DEFAULT 1,
    created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    CONSTRAINT FK_app_users_batch FOREIGN KEY (batch_id) REFERENCES program_batches(id)
);

CREATE TABLE trainee_groups (
    id INT IDENTITY(1,1) PRIMARY KEY,
    batch_id INT NOT NULL,
    code NVARCHAR(50) NOT NULL,
    mentor_user_id INT NULL,
    max_size INT NOT NULL DEFAULT 25,
    CONSTRAINT UQ_trainee_groups_batch_code UNIQUE(batch_id, code),
    CONSTRAINT FK_trainee_groups_batch FOREIGN KEY (batch_id) REFERENCES program_batches(id),
    CONSTRAINT FK_trainee_groups_mentor FOREIGN KEY (mentor_user_id) REFERENCES app_users(id)
);

CREATE TABLE trainees (
    id INT IDENTITY(1,1) PRIMARY KEY,
    trainee_code NVARCHAR(30) NOT NULL UNIQUE,
    full_name NVARCHAR(250) NOT NULL,
    governorate_id INT NOT NULL,
    center_id INT NULL,
    national_id CHAR(14) NOT NULL UNIQUE,
    birth_date DATE NOT NULL,
    age AS DATEDIFF(YEAR, birth_date, CAST(GETDATE() AS DATE)),
    college_id INT NULL,
    university_id INT NULL,
    phone NVARCHAR(30) NULL,
    email NVARCHAR(200) NULL,
    is_sonaa_volunteer BIT NOT NULL DEFAULT 0,
    sonaa_volunteer_date DATE NULL,
    triful_join_date DATE NOT NULL,
    batch_id INT NOT NULL,
    group_id INT NULL,
    profile_image_url NVARCHAR(500) NULL,
    is_exited BIT NOT NULL DEFAULT 0,
    exit_reason NVARCHAR(1000) NULL,
    exit_reason_type NVARCHAR(50) NULL,
    exit_is_sensitive BIT NOT NULL DEFAULT 0,
    final_status NVARCHAR(30) NULL, -- PASSED, FAILED, MANUAL_PASS
    final_score DECIMAL(5,2) NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    CONSTRAINT FK_trainees_governorate FOREIGN KEY (governorate_id) REFERENCES governorates(id),
    CONSTRAINT FK_trainees_center FOREIGN KEY (center_id) REFERENCES centers(id),
    CONSTRAINT FK_trainees_college FOREIGN KEY (college_id) REFERENCES colleges(id),
    CONSTRAINT FK_trainees_university FOREIGN KEY (university_id) REFERENCES universities(id),
    CONSTRAINT FK_trainees_batch FOREIGN KEY (batch_id) REFERENCES program_batches(id),
    CONSTRAINT FK_trainees_group FOREIGN KEY (group_id) REFERENCES trainee_groups(id)
);

CREATE TABLE events (
    id INT IDENTITY(1,1) PRIMARY KEY,
    batch_id INT NOT NULL,
    event_type NVARCHAR(20) NOT NULL, -- SESSION, WEBINAR
    title NVARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    mentor_user_id INT NULL,
    attendance_mode NVARCHAR(20) NOT NULL, -- QR, MANUAL
    evidence_url NVARCHAR(500) NULL,
    created_by_user_id INT NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    CONSTRAINT FK_events_batch FOREIGN KEY (batch_id) REFERENCES program_batches(id),
    CONSTRAINT FK_events_mentor FOREIGN KEY (mentor_user_id) REFERENCES app_users(id),
    CONSTRAINT FK_events_creator FOREIGN KEY (created_by_user_id) REFERENCES app_users(id)
);

CREATE TABLE attendance_records (
    id INT IDENTITY(1,1) PRIMARY KEY,
    event_id INT NOT NULL,
    trainee_id INT NOT NULL,
    is_present BIT NOT NULL,
    absence_reason NVARCHAR(1000) NULL,
    excuse_status NVARCHAR(20) NULL, -- PENDING, ACCEPTED, REJECTED
    scanned_qr_token NVARCHAR(200) NULL,
    confirmed_by_user_id INT NULL,
    confirmed_at DATETIME2 NULL,
    CONSTRAINT UQ_attendance_event_trainee UNIQUE(event_id, trainee_id),
    CONSTRAINT FK_attendance_event FOREIGN KEY (event_id) REFERENCES events(id),
    CONSTRAINT FK_attendance_trainee FOREIGN KEY (trainee_id) REFERENCES trainees(id),
    CONSTRAINT FK_attendance_confirmer FOREIGN KEY (confirmed_by_user_id) REFERENCES app_users(id)
);

CREATE TABLE weekly_evaluations (
    id INT IDENTITY(1,1) PRIMARY KEY,
    trainee_id INT NOT NULL,
    batch_id INT NOT NULL,
    week_number INT NOT NULL,
    participation_score TINYINT NOT NULL,
    reflection_score TINYINT NOT NULL,
    tasks_score TINYINT NOT NULL,
    meetings_score TINYINT NOT NULL,
    communication_score TINYINT NOT NULL,
    notes NVARCHAR(1000) NULL,
    created_by_user_id INT NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    CONSTRAINT UQ_eval_trainee_week UNIQUE(trainee_id, week_number),
    CONSTRAINT FK_eval_trainee FOREIGN KEY (trainee_id) REFERENCES trainees(id),
    CONSTRAINT FK_eval_batch FOREIGN KEY (batch_id) REFERENCES program_batches(id),
    CONSTRAINT FK_eval_creator FOREIGN KEY (created_by_user_id) REFERENCES app_users(id)
);

CREATE TABLE trainee_tasks (
    id INT IDENTITY(1,1) PRIMARY KEY,
    trainee_id INT NOT NULL,
    title NVARCHAR(200) NOT NULL,
    task_text NVARCHAR(MAX) NULL,
    file_url NVARCHAR(500) NULL,
    submitted_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    CONSTRAINT FK_tasks_trainee FOREIGN KEY (trainee_id) REFERENCES trainees(id)
);

GO

CREATE VIEW vw_trainee_attendance_summary AS
SELECT
    t.id AS trainee_id,
    t.trainee_code,
    SUM(CASE WHEN e.event_type = 'SESSION' AND ar.is_present = 1 THEN 1 ELSE 0 END) AS session_present_count,
    SUM(CASE WHEN e.event_type = 'SESSION' AND ar.is_present = 0 THEN 1 ELSE 0 END) AS session_absent_count,
    SUM(CASE WHEN e.event_type = 'WEBINAR' AND ar.is_present = 1 THEN 1 ELSE 0 END) AS webinar_present_count,
    SUM(CASE WHEN e.event_type = 'WEBINAR' AND ar.is_present = 0 THEN 1 ELSE 0 END) AS webinar_absent_count
FROM trainees t
LEFT JOIN attendance_records ar ON t.id = ar.trainee_id
LEFT JOIN events e ON ar.event_id = e.id
GROUP BY t.id, t.trainee_code;
GO

CREATE VIEW vw_trainee_evaluation_percentage AS
SELECT
    trainee_id,
    CAST(AVG(((participation_score + reflection_score + tasks_score + meetings_score + communication_score) * 100.0) / 25.0) AS DECIMAL(5,2)) AS cumulative_percent
FROM weekly_evaluations
GROUP BY trainee_id;
GO
