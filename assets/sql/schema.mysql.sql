CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(322) NOT NULL UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE roles(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE users_has_roles(
    id_user INT NOT NULL,
    id_role INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_role) REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (id_user, id_role)
);

CREATE TABLE tokens(
    id INT AUTO_INCREMENT PRIMARY KEY,
    token TEXT NOT NULL,
    id_user INT NOT NULL,
    generation_date DATETIME NOT NULL,
    expiration_date DATETIME NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE Habilitacao (
    habId int PRIMARY KEY AUTO_INCREMENT,
    habNome varchar(250) NOT NULL, 
    habOrgaoClasse varchar(250) NOT NULL
);

CREATE TABLE Profissional (
    proId int PRIMARY KEY AUTO_INCREMENT,
    proNome varchar(250) NOT NULL,
    proDataNas date NOT NULL,
    proTelefone char(14) NOT NULL, 
    proCPF char(11) NOT NULL,
    proRG varchar(250) NOT NULL,
    proEndNumero int NOT NULL,           
    proEndCidade varchar(250) NOT NULL,  
    proEndLogradouro varchar(250) NOT NULL,
    proEndCEP char(8) NOT NULL,
    proFoto varchar(250) NOT NULL,
    proEmail varchar(250) NOT NULL, 
    proRegistroProfissional int NOT NULL,
    proHabId int NOT NULL,
    FOREIGN KEY (proHabId) REFERENCES Habilitacao(habId)   
);

CREATE TABLE Disponibilidade (
    disId int PRIMARY KEY AUTO_INCREMENT, 
    disInicio datetime NOT NULL, 
    disFim datetime NOT NULL, 
    disProId int NOT NULL, 
    FOREIGN KEY (disProid) REFERENCES Profissional(proId)
);

CREATE TABLE Paciente (
    pacid int PRIMARY KEY AUTO_INCREMENT,
    pacNome varchar(250) NOT NULL,
    pacDataNas date NOT NULL,
    pacTelefone char(14) NOT NULL,
    pacCPF char(11) NOT NULL,
    pacRG varchar(250) NOT NULL,
    pacEndNumero int NOT NULL,
    pacEndCidade varchar(250) NOT NULL,
    pacEndLogradouro varchar(250) NOT NULL,
    pacEndCep char(8) NOT NULL,
    pacFoto varchar(250) NOT NULL, 
    pacEmail varchar(250) NOT NULL,
    pacPlanoSaude  varchar(250) NOT NULL
);

CREATE TABLE Agendamento (
    ageId int PRIMARY KEY AUTO_INCREMENT, 
    ageData date NOT NULL, 
    ageHorario time NOT NULL, 
    ageObservacoes varchar(250) NOT NULL, 
    agePacId int NOT NULL, 
    ageProId int NOT NULL,
    FOREIGN KEY (agePacId) REFERENCES Paciente (pacId),
    FOREIGN KEY(ageProId) REFERENCES Profissional (proId)
);
