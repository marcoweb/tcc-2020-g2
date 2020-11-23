INSERT INTO users(id, email, password) VALUES (1, 'admin@application.com', SHA2('admin', 256));
INSERT INTO users(id, email, password) VALUES (2, 'user@application.com', SHA2('user', 256));
INSERT INTO roles(id, name) VALUES (1, 'admin');
INSERT INTO roles(id, name) VALUES (2, 'user');
INSERT INTO users_has_roles(id_user, id_role) VALUES (1,1);
INSERT INTO users_has_roles(id_user, id_role) VALUES (1,2);
INSERT INTO users_has_roles(id_user, id_role) VALUES (2,2);