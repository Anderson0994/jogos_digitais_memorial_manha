<?php

# cabeçalho da página para receber requisições do tipo JSON
# Especificação CORS (AUTH0)
header("Access-Control-Allow-Origin: *"); // libera tudo

# tipo do cabeçalho
header("Content-Type: application/json; charset=utf-8");

# recupera as informações enviadas via serviço
$json = file_get_contents("php://input");

# decodifica os dados enviados no formato JSON para um objeto PHP
$obj = json_decode($json);

# tratar algum erro de requisição
# O nome do operador "=>" é: arrow
if ($obj === null && json_last_error() !== JSON_ERROR_NONE) {
    print(json_encode(["data" => "err", "value" => "Dados inválidos"]));
    die(); # para o processo 
}

# Incluindo/importando o arquivo do Modelo do Usuário
include("class/UserModel.php");

# criar um novo usuário - objeto UserModel()
# formato do payload:
# {"type":"new", "name":"Edson Melo de Souza", "user":"edson.melo", "password":"edson123"}

switch ($obj->type) {
    case 'new':
        try {
            $user = new UserModel();

            # verificar se os dados foram enviados corretamente
            if (empty($obj->name) == true || empty($obj->user) == true || empty($obj->password) == true) {
                print(json_encode(["data" => "err", "value" => "Dados inválidos"]));
                die();
            }

            # verifica se o usuário já existe (igual NULL não retornou nada)
            $result = $user->userExists($obj->user);
            if ($result != null) {
                print(json_encode(["data" => "err", "value" => "User [" . $obj->user . "] exists"]));
                die();
            }

            # chama o método que insere no banco
            $result = $user->new($obj->name, $obj->user, $obj->password);

            # verifica o retorno do método
            if ($result != null) {
                print(json_encode(["data" => "ok", "value" => "Usuário criado com sucesso"]));
            } else {
                print(json_encode(["data" => "err", "value" => "Falha na criação do usuário"]));
            }
        } catch (Exception $ex) {
            print(json_encode(["data" => "err", "value" => $ex->getMessage()]));
        }
        break;

    case 'login':
        try {
            $login = new UserModel();
            if (empty($obj->user) == true || empty($obj->password) == true) {
                print(json_encode(["data" => "err", "value" => "Dados inválidos"]));
                die();
            }
            $result = $login->login($obj->user, $obj->password);

            # verifica se foi retornado algum dado
            if ($result != null) {
                $result["data"] = "ok"; # incluído o campo data para controle
                print(json_encode($result)); # retorna os dados de login
            } else {
                print(json_encode(["data" => "err", "value" => "Usuário não localizado"]));
            }
        } catch (Exception $ex) {
            print(json_encode(["data" => "err", "value" => $ex->getMessage()]));
        }
        break;

    case 'users':
        try {
            $users = new UserModel();

            # retorna todos os usuários cadastrados
            $result = $users->users();

            if ($result != null) {
                $result["data"] = "ok";
                print(json_encode($result));
            } else {
                print(json_encode(["value" => "não há usuários para listar"]));
            }
        } catch (Exception $ex) {
            print(json_encode(["data" => "err", "value" => $ex->getMessage()]));
        }
        break;

    default:
        print(json_encode(["data" => "err", "value" => "Dados inválidos"]));
        break;
}
