<?php
loadFunctions('core/database');

function insertUser($email, $password) {
    simpleInsert('users', ['email' => $email, 'password' => hash('sha256', $password)]);
    $user = simpleSelect('users', ['email' => ['=', $email], 'password' => ['=', hash('sha256', $password)]]);
    $role = simpleSelect('roles', ['name' => ['=', 'user']]);
    simpleInsert('users_has_roles', ['id_user' => $user[0]['id'], 'id_role' => $role[0]['id']]);
}

function updateUserPassword($id_user, $password) {
    simpleUpdate('users', ['password' => hash('sha256', $password)], ['id' => $id_user]);
}

function deleteUser($id) {
    simpleDelete('users_has_roles', ['id_user' => $id]);
    simpleDelete('users', ['id' => $id]);
}