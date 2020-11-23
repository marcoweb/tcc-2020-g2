<?php
function post($email, $password) {
    return json_encode(login($email, $password));
}