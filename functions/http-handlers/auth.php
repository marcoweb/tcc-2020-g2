<?php
function login_get() {
    return view();
}

function login_post($email = null, $password = null) {
    if(openSession($email, $password)) {
        redirectTo(HOME_PAGE);
    } else {
        redirectTo(LOGIN_FORM);
    }
}

function logout_get() {
    logout();
    redirectTo(LOGIN_FORM);
}