<?php
function open_database_con()
{
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "haha";
    $db_name = "racers";

    try {
          $link = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8", $db_user, $db_pass);
          $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
        echo $exception->getMessage();
    }
        return $link;
}

function close_database_con(&$link)
{
    $link = null;
}

function addRacer($name, $racerLink, $count, $email)
{
    $link = open_database_con();
    $sql = "insert into racers (name, link, count, email_facebook) values (:name, :link, :count, :email_facebook)";
    $query = $link->prepare($sql);
        $query->execute(array('name' => $name,
                              'link' => $racerLink,
                              'count' => $count,
                              'email_facebook' => $email));
        $_SESSION['messages'] = [
        'welcom' => "hello $name",
        ];

        $id = $link->lastInsertId();
        close_database_con($link);

        return $id;
}

function selectRacer($email)
{

        $link = open_database_con();
        $sql = "select * from racers where email_facebook = ?";
        $query = $link->prepare($sql);
        $query->execute([$email]);
        $racerResult = $query->fetch(PDO::FETCH_ASSOC);
        close_database_con($link);
        return $racerResult;
}

function getRacersInfo()
{

        $link = open_database_con();
        $sql = "select * from racers";
        $query = $link->prepare($sql);
        $query->execute();
        $racerResult = array();

    while ($result=$query->fetch(PDO::FETCH_ASSOC)) {
        $racerResult[] = $result;
    }
        close_database_con($link);

        return $racerResult;
}

function selectUserDetails($url)
{

        $link = open_database_con();
        $sql = "select * from racers where link = ?";
        $query = $link->prepare($sql);
        $query->execute([$url]);
        $racerResult = $query->fetch(PDO::FETCH_ASSOC);
        close_database_con($link);

        return $racerResult;
}

function updateCount($id)
{
    $link = open_database_con();
    $sql = "Update racers set count = count +1 where id = :id";
    $query = $link->prepare($sql);
    $query->execute(array('id'=> $id));
    close_database_con($link);
}


function addIpOfUser($ip, $userId)
{
    $link = open_database_con();
    $sql = "insert into clicks (ip, user_id) values (:ip, :user_id)";
    $query = $link->prepare($sql);
        $query->execute(array('ip' => $ip,
                              'user_id' => $userId,
                              ));
        //header("Location: http://localhost");
        close_database_con($link);
}


function checkIp($ip, $id)
{

        $link = open_database_con();
        $sql =  "Select racers.id, clicks.ip, clicks.user_id from racers  join clicks on racers.id = clicks.user_id where clicks.ip = ? and racers.id = ?";
        $query = $link->prepare($sql);
        $query->execute([$ip,$id]);
        $matchingResults = array();
    while ($result= $query->fetch(PDO::FETCH_ASSOC)) {
        $matchingResults[] = $result;
    }

        close_database_con($link);

        return $matchingResults;
}

function getRacerDataById($id)
{

    $link = open_database_con();
    $sql = "select * from racers where id = ?";
    $query = $link->prepare($sql);
    $query->execute([$id]);
    $racerData = $query->fetch(PDO::FETCH_ASSOC);
    close_database_con($link);

    return $racerData;
}
