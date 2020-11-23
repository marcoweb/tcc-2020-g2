<?php
const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'applicationdb';
const DB_USER = 'root';
const DB_PASS = '';

$_dbConnection = null;

function getDbConnection() {
    global $_dbConnection;
    if(is_null($_dbConnection)){
        $_dbConnection = new PDO('mysql:host='. DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    }
    return $_dbConnection;
}

// $criteria_array = ['id' => ['LIKE', 'Marco']] ::: $fields_array = ['id', 'nome']
// $result = simpleSelect('produtos', ['nome' => ['LIKE', '%Geladeira%']], ['nome', 'preco'])
// ---> SELECT nome, preco FROM produtos WHERE nome LIKE %Geladeira%;
// $result = simpleSelect('produtos')
// ---> SELECT * FROM produtos;
function simpleSelect($tableName, $criteria_array = [], $fields_array = ['*']) {
    $sql = 'SELECT ' . implode(',', $fields_array) . ' FROM ' . $tableName;
    $sql_parameters = [];
    foreach($criteria_array as $name => $constraint) {
        $sql .= (empty($sql_parameters)) ? ' WHERE ' : ' AND ';
        $parameter = ':' . $name;
        $sql .= $name . ' ' . $constraint[0] . ' ' . $parameter;
        $sql_parameters[$parameter] = $constraint[1];
    }
    $command = getDbConnection()->prepare($sql);
    $command->execute($sql_parameters);
    return $command->fetchAll(PDO::FETCH_ASSOC);
}

// simpleInsert('usuario', ['nome' => 'teste, 'grupo' => 'admin'])
function simpleInsert($tableName, $values_array) {
    $sql = 'INSERT INTO ' . $tableName . '(' . implode(', ', array_keys($values_array)) . ') VALUES(';
    $sql_parameters = [];
    foreach($values_array as $name => $value) {
        $sql .= (empty($sql_parameters)) ? '' : ', ';
        $parameter = ':' . $name;
        $sql .= $parameter;
        $sql_parameters[$parameter] = $value;
    }
    $sql .= ')';
    $command = getDbConnection()->prepare($sql);
    $command->execute($sql_parameters);
}

function simpleUpdate($tableName, $values_array, $criteria_array) {
    $sql = 'UPDATE ' . $tableName . ' SET ';
    $sql_parameters = [];
    foreach($values_array as $name => $value) {
        $sql .= (empty($sql_parameters)) ? '' : ', ';
        $parameter = ':' . $name;
        $sql .= $name . ' = ' .  $parameter;
        $sql_parameters[$parameter] = $value;
    }
    $sql .= ' WHERE ';
    foreach($criteria_array as $name => $value) {
        $sql .= (count($values_array) == count($sql_parameters)) ? '' : ' AND ';
        $parameter = ':' . $name;
        $sql .= $name . ' = ' .  $parameter;
        $sql_parameters[$parameter] = $value;
    }
    $command = getDbConnection()->prepare($sql);
    $command->execute($sql_parameters);
}

function simpleDelete($tableName, $criteria_array) {
    $sql = 'DELETE FROM ' . $tableName . ' WHERE ';
    $sql_parameters = [];
    foreach($criteria_array as $name => $value) {
        $sql .= (empty($sql_parameters)) ? '' : ' AND ';
        $parameter = ':' . $name;
        $sql .= $name . ' = ' .  $parameter;
        $sql_parameters[$parameter] = $value;
    }
    $command = getDbConnection()->prepare($sql);
    $command->execute($sql_parameters);
}