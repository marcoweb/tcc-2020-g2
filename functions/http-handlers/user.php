<?php

function list_get() {
    authorize(['admin']);
    loadFunctions('core/database');
    $users = simpleSelect('users');
    return view(['users' => $users]);
}

function create_get() {
    $authorized_roles = ['admin'];
    if(USER_SELF_REGISTER) {
        array_push($authorized_roles, 'user');
    }
    authorize($authorized_roles);
    return view();
}

function create_post($email, $password, $confirm_password) {
    $authorized_roles = ['admin'];
    if(USER_SELF_REGISTER) {
        array_push($authorized_roles, 'user');
    }
    authorize($authorized_roles);
    if($password == $confirm_password) {
        loadFunctions('repositories/users');
        insertUser($email, $password);
        if(USER_SELF_REGISTER) {
            redirectTo(HOME_PAGE);
        } else {
            redirectTo('/user/list');
        }
    } else {
        redirectTo('/user/create');
    }
}

function update_get($id) {
    $authorized_roles = ['admin'];
    if(!USER_SELF_REGISTER && $id == getLoggedUserId()) {
        array_push($authorized_roles, 'user');
    }
    authorize($authorized_roles);
    loadFunctions('core/database');
    $user = simpleSelect('users', ['id' => ['=', $id]]);
    return view(['user' => ['id' => $user[0]['id'], 'email' => $user[0]['email']]]);
}

function update_post($id, $password, $confirm_password) {
    $authorized_roles = ['admin'];
    if(!USER_SELF_REGISTER && $id == getLoggedUserId()) {
        array_push($authorized_roles, 'user');
    }
    authorize($authorized_roles);
    if($password == $confirm_password){
        loadFunctions('repositories/users');
        updateUserPassword($id, $password);
        if(USER_SELF_REGISTER) {
            redirectTo(HOME_PAGE);
        } else {
            redirectTo('/user/list');
        }
    } else {
        redirectTo('/user/update/'.$id);
    }
}

function delete_get($id) {
    $authorized_roles = ['admin'];
    if(!USER_SELF_REGISTER && $id == getLoggedUserId()) {
        array_push($authorized_roles, 'user');
    }
    authorize($authorized_roles);
    loadFunctions('core/database');
    $user = simpleSelect('users', ['id' => ['=', $id]]);
    return view(['user' => ['id' => $user[0]['id'], 'email' => $user[0]['email']]]);
}

function delete_post($id) {
    $authorized_roles = ['admin'];
    if(!USER_SELF_REGISTER && $id == getLoggedUserId()) {
        array_push($authorized_roles, 'user');
    }
    authorize($authorized_roles);
    loadFunctions('repositories/users');
    deleteUser($id);
    if(USER_SELF_REGISTER) {
        redirectTo(HOME_PAGE);
    } else {
        redirectTo('/user/list');
    }
}