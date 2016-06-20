<?php
use \Psr\Http\Message\ServerRequestInterface ;
use \Psr\Http\Message\ResponseInterface ;



require '../vendor/autoload.php';

$dsn = 'mysql:host=localhost;dbname=visermort;charset=utf8';
$usr = 'root';
$pwd = '';


$app = new \Slim\App;

$pdo = new \Slim\PDO\Database($dsn, $usr, $pwd);

//pdo ищспользуем через use ()


//выдать таблицу  table , где param = value
$app->get('/{table}/{param}/{value}', function (ServerRequestInterface $request, ResponseInterface $response) use ($pdo) {

     $dbPrefix = 'shop_';

     $table = $request->getAttribute('table');
     $param = $request->getAttribute('param');
     $value = $request->getAttribute('value');

    // SELECT * FROM users WHERE id = ?
    $selectStatement = $pdo->select()
        ->from($dbPrefix.$table)
        ->where($param, '=', $value);

    $stmt = $selectStatement->execute();
    $data = $stmt->fetchAll();
    if ($data) {
        $result = array (
            'result' => 1,
            'response' => $data
        );
    } else {
        $result = array(
            'result' => 0
        );
    }
    $newResponse =  $response -> withJson($result) ->  withHeader('Content-type', 'application/json');
    return $newResponse;
});

//выюать все записи table
$app->get('/{table}/', function (ServerRequestInterface $request, ResponseInterface $response) use ($pdo) {

    $dbPrefix = 'shop_';

    $table = $request->getAttribute('table');

    // SELECT * FROM users WHERE id = ?
    $selectStatement = $pdo->select()
        ->from($dbPrefix.$table);

    $stmt = $selectStatement->execute();
    $data = $stmt->fetchAll();
    if ($data) {
        $result = array (
            'result' => 1,
            'response' => $data
        );
    } else {
        $result = array(
            'result' => 0
        );
    }
    $newResponse =  $response -> withJson($result) ->  withHeader('Content-type', 'application/json');
    return $newResponse;
});


//операция с table
$app->post('/action', function (ServerRequestInterface $request, ResponseInterface $response) use ($pdo) {
    $data = $_POST;
    $action = $data['action'];//название метода
    $table = $data['table']; //таблица

    $dbPrefix = 'shop_';
    $queryFields=[];
    $queryValues=[];

    //поля и данные для insert (action create)
    foreach ($data as $key => $value) {
        if ($key !='action' && $key !='action' && $key !='table') {
            $queryFields[] = $key;
            $queryValues[] = $value;
        }
    }

    $tableName = $dbPrefix.$table;
    try {
        switch ($action) {
            case 'create':
                $statement = $pdo->insert($queryFields)
                    ->into($tableName)
                    ->values($queryValues);
                $smtp = $statement->execute();
                break;
            case 'update':


                break;
        }
    } catch (exception $e) {
        $error = $e -> getMessage();
    }

    if (!empty($smtp)) {
        $result = array (
            'result' => 1,
            'table' => $table,
            'action' => $action
        );
    } else {
        $result = array(
            'result' => 0,
 //           'table' => $tableName,
 //           'action' => $action,
 //           'data' => $data,
 //           'querykeys' => $queryFields,
 //           'queryvalues' => $queryValues,
            'error' => (isset($error)? $error: '')
        );
    }
    $newResponse =  $response -> withJson($result) ->  withHeader('Content-type', 'application/json');
    return $newResponse;
});

$app->run();