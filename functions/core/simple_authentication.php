<?php
loadFunctions('core/database');

const SECRET_KEY = '4{?b/nvmFxjWf/Lo4+)VR~n4ME(.i,JHw&2a~<vfGhF5?*|eNKqjv?ab`EGyVxHr';
const LOGIN_FORM = '/auth/login';
const USER_SELF_REGISTER = FALSE;

function generateToken($email) {
    $date = new Datetime();
    $header = json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]);
    $payload = json_encode([
        'email' => $email,
        'date' => $date->format('Y-m-d\TH:i:s.u')
    ]);
    $header_encoded = base64_encode($header);
    $payload_encoded = base64_encode($payload);
    $token = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, SECRET_KEY, true);
    return base64_encode($token) . $date->format('Y:m:d:H:i:s:u');
}

function validateToken($token) {
    $command_token = getDbConnection()->prepare('SELECT * FROM tokens WHERE token = :token AND revoked = FALSE AND expiration_date >= :date');
    $date = new Datetime();
    $command_token->execute([':token' => $token, ':date' => $date->format('Y-m-d\TH:i:s.u')]);
    $result_token = $command_token->fetch(PDO::FETCH_ASSOC);
    if(empty($result_token)) {
        return [
            'status' => FALSE
        ];
    } else {
        $command_token_update = getDbConnection()->prepare('UPDATE tokens SET expiration_date = DATE_ADD(now(), INTERVAL 30 MINUTE) WHERE id = :id');
        $command_token_update->execute([':id' => $result_token['id']]);
        return [
            'status' => TRUE,
            'id_user' => $result_token['id_user']
        ];
    }
}

function login($email, $password) {
    $command_user = getDbConnection()->prepare('SELECT * FROM users WHERE email = :email AND password = :password');
    $command_user->execute([':email' => $email, ':password' => hash('SHA256', $password)]);
    $result_user = $command_user->fetch(PDO::FETCH_ASSOC);
    if(!empty($result_user)) {
        $token = generateToken($result_user['email']);
        $date = DateTime::createFromFormat('Y:m:d:H:i:s:u', explode('=', $token)[1]);
        $generation = $date->format('Y-m-d\TH:i:s');
        $expiration = $date->add(new DateInterval('PT30M'))->format('Y-m-d\TH:i:s');
        $command_token_insert = getDbConnection()->prepare('INSERT INTO tokens(token, id_user, generation_date, expiration_date) VALUES(:token, :id_user, :generation, :expiration)');
        $command_token_insert->execute([':token' => $token, ':id_user' => $result_user['id'], ':generation' => $generation, ':expiration' => $expiration]);
        return [
            'logged' => true,
            'token' => $token
        ];
    } else {
        return [
            'logged' => false,
            'message' => 'Nome de usuário ou senha inválido(s)'
        ];
    }
}

function isAuthorized($token, $authorized_roles) {
    $tokenInfo = validateToken($token);
    return $tokenInfo;
    if($tokenInfo['status']) {
        $command_roles = getDbConnection()->prepare('SELECT r.name FROM tokens AS t
                LEFT JOIN users_has_roles AS uhr ON t.id_user = uhr.id_user
                LEFT JOIN roles AS r ON uhr.id_role = r.id
            WHERE t.token = :token');
        $command_roles->execute([':token' => $token]);
        $result_roles = $command_roles->fetchAll(PDO::FETCH_ASSOC);
        foreach($result_roles as $role) {
            if(in_array($role['name'], $authorized_roles)) {
                return TRUE;
            }
        }
        return false;
    }
}

function hasAuthorization($authorized_roles = ['user']) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['auth_token'])) {
        if(!isAuthorized($_SESSION['auth_token'], $authorized_roles)) {
            return true;
        }
    }
    return false;
}

function openSession($email, $password) {
    $loginInfo = login($email, $password);
    if($loginInfo['logged']) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['auth_token'] = $loginInfo['token'];
        return true;
    } else {
        if (session_status() != PHP_SESSION_NONE) {
            session_destroy();
        }
        return false;
    }
}

function getLoggedUserId() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['auth_token'])) {
        return validateToken($_SESSION['auth_token'])['id_user'];
    }
    return false;
}

function logout() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
}

function authorize($authorized_roles = ['user']) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['auth_token'])) {
        if(!isAuthorized($_SESSION['auth_token'], $authorized_roles)) {
            redirectTo(LOGIN_FORM);
        }
    } else {
        redirectTo(LOGIN_FORM);
    }
}