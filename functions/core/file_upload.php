<?php
const UPLOAD_PATH = PATH_ROOT . '/assets/images/uploaded';

function uploadFiles() {
    $_uploaded_files = [];
    foreach($_FILES as $file_id => $file_info) {
        $_uploaded_file_path = UPLOAD_PATH . '/' . md5(uniqid(rand(), true)) . substr($file_info['name'], strrpos($file_info['name'], '.'));
        if(move_uploaded_file($file_info['tmp_name'], $_uploaded_file_path)) {
            $_uploaded_files[$file_id] = $_uploaded_file_path;
        }
    }
    return $_uploaded_files;
}