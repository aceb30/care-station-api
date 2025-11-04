CREATE TABLE Users (
    user_id SERIAL PRIMARY KEY,
    names VARCHAR(100) NOT NULL,
    surnames VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- NEEDS TO BE HASHED
    cellphone VARCHAR(20),
    photo_url VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE CareGroups (
    care_group_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    photo_url VARCHAR(255),
    admin_id INTEGER REFERENCES Users(user_id) ON DELETE SET NULL
);

CREATE TABLE GroupMembers (
    user_id INTEGER REFERENCES Users(user_id) ON DELETE CASCADE,
    care_group_id INTEGER REFERENCES CareGroups(care_group_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, care_group_id)
);

CREATE TABLE Patients (
    patient_id SERIAL PRIMARY KEY,
    care_group_id INTEGER REFERENCES CareGroups(care_group_id) ON DELETE CASCADE,
    names VARCHAR(100) NOT NULL,
    surnames VARCHAR(100),
    cellphone VARCHAR(20),
    telephone VARCHAR(20),
    address VARCHAR(255)
);

CREATE TABLE Tasks (
    task_id SERIAL PRIMARY KEY,
    care_group_id INTEGER REFERENCES CareGroups(care_group_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    frequency VARCHAR(100),
    category VARCHAR(100),
    begin_time TIMESTAMP WITH TIME ZONE,
    end_time TIMESTAMP WITH TIME ZONE,
    done BOOLEAN DEFAULT FALSE
);

CREATE TABLE TaskAssignments (
    task_id INTEGER REFERENCES Tasks(task_id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES Users(user_id) ON DELETE CASCADE,
    PRIMARY KEY (task_id, user_id)
);

CREATE TABLE HealthProblems (
    health_problem_id SERIAL PRIMARY KEY,
    patient_id INTEGER REFERENCES Patients(patient_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type INTEGER,
    -- 0=allergy, 1=disability, 2=chronic illness, 3=other
    CONSTRAINT chk_health_problem_type CHECK (type IN (0, 1, 2, 3))
);

CREATE TABLE MedicalContacts (
    medical_contact_id SERIAL PRIMARY KEY,
    patient_id INTEGER REFERENCES Patients(patient_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    cellphone VARCHAR(20),
    telephone VARCHAR(20),
    mail VARCHAR(255),
    address VARCHAR(255),
    description TEXT
);

CREATE TABLE Medications (
    medication_id SERIAL PRIMARY KEY,
    patient_id INTEGER REFERENCES Patients(patient_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT
);

CREATE TABLE Exams (
    exam_id SERIAL PRIMARY KEY,
    patient_id INTEGER REFERENCES Patients(patient_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    emission_date DATE,
    file_url VARCHAR(255)
);

CREATE TABLE Prescriptions (
    prescription_id SERIAL PRIMARY KEY,
    patient_id INTEGER REFERENCES Patients(patient_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    emission_date DATE,
    file_url VARCHAR(255)
);

CREATE TABLE MedicationPrescriptions (
    medication_id INTEGER REFERENCES Medications(medication_id) ON DELETE CASCADE,
    prescription_id INTEGER REFERENCES Prescriptions(prescription_id) ON DELETE CASCADE,
    PRIMARY KEY (medication_id, prescription_id)
);

CREATE TABLE Notes (
    note_id SERIAL PRIMARY KEY,
    patient_id INTEGER REFERENCES Patients(patient_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    urgent BOOLEAN DEFAULT FALSE,
    author_id INTEGER REFERENCES Users(user_id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);