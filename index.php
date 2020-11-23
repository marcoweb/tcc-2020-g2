<?php
//-----------[ CONFIGURAÇÕES GERAIS DA APLICAÇÃO ]------------

const HOME_PAGE = '/page/index';
const PATH_ROOT = '.';
const PATH_PAGES = PATH_ROOT . '/pages';
const PATH_TEMPLATES = PATH_PAGES . '/_templates';
const PATH_FUNCTIONS = PATH_ROOT . '/functions';
const PATH_VIEWS = PATH_PAGES;
const PATH_HTTP_HANDLERS = PATH_FUNCTIONS . '/http-handlers';
const EXTENSION_OF_PAGES = '.phtml';
const USE_PAGE_TEMPLATE = TRUE;
const TEMPLATE_DEFAULT = 'default';
const USE_SIMPLE_AUTHENTICATION = TRUE;

date_default_timezone_set('America/Sao_Paulo');

//-------------------------------------------------------------

$_requestInfo = [];
$_response = '';

function loadFunctions($name) {
    $_functions_file_path = PATH_FUNCTIONS . '/' . $name . '.php';
    if(file_exists($_functions_file_path)) {
        require_once($_functions_file_path);
    }
}

function redirectTo($url) {
    header('Location:' . $url);
}

if(USE_SIMPLE_AUTHENTICATION) {
    loadFunctions('core/simple_authentication');
}

$_requestInfo['uri'] = ($_SERVER['REQUEST_URI'] == '/') ? trim(HOME_PAGE, '/') : trim($_SERVER['REQUEST_URI'], '/');
$_requestInfo['query_string'] = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : null;
$_requestInfo['uri_without_query_string'] = (is_null($_requestInfo['query_string'])) ?
    $_requestInfo['uri'] :
    str_replace('?'. $_requestInfo['query_string'], '', $_requestInfo['uri']);
$_requestInfo['uri_segments'] = explode('/', $_requestInfo['uri_without_query_string']);
$_requestInfo['method'] = $_SERVER['REQUEST_METHOD'];

if($_requestInfo['method'] == 'GET') {
    $_requestInfo['params'] = $_GET;
} else if ($_requestInfo['method'] == 'POST') {
    $_requestInfo['params'] = $_POST;
}
if(is_numeric(end($_requestInfo['uri_segments']))) {
    $_requestInfo['params']['id'] = end($_requestInfo['uri_segments']);
}

if($input_contents = file_get_contents('php://input')) {
    if($input_contents[0] == '{') {
        $_requestInfo['params'] = array_merge($_requestInfo['params'], json_decode($input_contents, TRUE));
    }
}

if($_requestInfo['uri_segments'][0] == 'page') {
    $_requestInfo['current_page'] = (is_numeric(end($_requestInfo['uri_segments']))) ?
        implode('/', array_slice($_requestInfo['uri_segments'], 1, -1)) :
        implode('/', array_slice($_requestInfo['uri_segments'], 1));
    $_page_file_path = PATH_PAGES . '/' . $_requestInfo['current_page'] . EXTENSION_OF_PAGES;
    if(file_exists($_page_file_path)) {
        ob_start();
        foreach($_requestInfo['params'] as $name => $value) {
            $$name = $value;
        }
        require_once($_page_file_path);
        $_contents = ob_get_clean();
        $_response = $_contents;
        if(USE_PAGE_TEMPLATE) {
            $_template_file_path = (isset($_template)) ?
                PATH_TEMPLATES . '/' . $_template . EXTENSION_OF_PAGES :
                PATH_TEMPLATES . '/' . TEMPLATE_DEFAULT . EXTENSION_OF_PAGES;
            if(file_exists($_template_file_path)) {
                require_once($_template_file_path);
                $_response = ob_get_clean();
            }
        }
    } else {
        http_response_code(404);
        $_response = 'Página Não Encontrada : ' . $_requestInfo['current_page'];
    }
} else if($_requestInfo['uri_segments'][0] == 'api') {
    $_requestInfo['current_http_handler'] = (is_numeric(end($_requestInfo['uri_segments']))) ?
        implode('/', array_slice($_requestInfo['uri_segments'], 1, -1)) :
        implode('/', array_slice($_requestInfo['uri_segments'], 1));
    $_handler_file_path = PATH_HTTP_HANDLERS . '/' . $_requestInfo['current_http_handler'] . '.php';
    if(file_exists($_handler_file_path)) {
        require_once($_handler_file_path);
        if(function_exists(strtolower($_requestInfo['method']))) {
            $_refFunction = new ReflectionFunction(strtolower($_requestInfo['method']));
            $_functionParameters = [];
            foreach($_refFunction->getParameters() as $p) {
                $_functionParameters[$p->name] = $_requestInfo['params'][$p->name];
            }
            $_response = json_encode(call_user_func_array(strtolower($_requestInfo['method']), $_functionParameters));
        } else {
            http_response_code(404);
            $_response = json_encode(['mensagem' => 'Método HTTP Não Implementado : ' . $_requestInfo['method']]);
        }
    } else {
        http_response_code(404);
        $_response = json_encode(['mensagem' => 'Recurso Não Encontrado : ' . $_requestInfo['current_http_handler']]);
    }
    header('Content-Type: application/json');
} else {
    $_function_name = '';
    if(is_numeric(end($_requestInfo['uri_segments']))) {
        $_requestInfo['current_http_handler'] = implode('/', array_slice($_requestInfo['uri_segments'], 0, -2));
        $_requestInfo['function_name'] = $_requestInfo['uri_segments'][count($_requestInfo['uri_segments']) -2];
    } else {
        $_requestInfo['current_http_handler'] = implode('/', array_slice($_requestInfo['uri_segments'], 0, -1));
        $_requestInfo['function_name'] = $_requestInfo['uri_segments'][count($_requestInfo['uri_segments']) -1];
    }
    $_handler_file_path = PATH_HTTP_HANDLERS . '/' . $_requestInfo['current_http_handler'] . '.php';
    if(file_exists($_handler_file_path)) {
        require_once($_handler_file_path);
        if(function_exists($_requestInfo['function_name'] . '_' . strtolower($_requestInfo['method']))) {
            $_requestInfo['function_name'] = $_requestInfo['function_name'] . '_' . strtolower($_requestInfo['method']);
        }
        if(function_exists($_requestInfo['function_name'])){
            $_refFunction = new ReflectionFunction($_requestInfo['function_name']);
            $_functionParameters = [];
            foreach($_refFunction->getParameters() as $p) {
                $_functionParameters[$p->name] = $_requestInfo['params'][$p->name];
            }

            function view($parameters = [], $view_name = null) {
                global $_requestInfo;
                $_response = '';
                $_view_path = PATH_VIEWS . '/' . ((is_null($view_name)) ? ($_requestInfo['current_http_handler'] . '/' . $_requestInfo['function_name']) : $view_name) . '.phtml';
                if(file_exists($_view_path)) {
                    ob_start();
                    foreach($parameters as $name => $value) {
                        $$name = $value;
                    }
                    include_once($_view_path);
                    $_contents = ob_get_clean();
                    $_response = $_contents;
                    if(USE_PAGE_TEMPLATE) {
                        $_template_file_path = (isset($_template)) ?
                            PATH_TEMPLATES . '/' . $_template . EXTENSION_OF_PAGES :
                            PATH_TEMPLATES . '/' . TEMPLATE_DEFAULT . EXTENSION_OF_PAGES;
                        if(file_exists($_template_file_path)) {
                            require_once($_template_file_path);
                            $_response = ob_get_clean();
                        }
                    }
                }
                return $_response;
            }
            $_response = call_user_func_array($_requestInfo['function_name'], $_functionParameters);
        }
    }
}

echo $_response;
