<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
//var type doit être 1 ou -1 (vote positif ou négatif)
function fetchNbVote($idArticle, $type){
    include 'connectDb.php';
    $total = 0;
    $result = $db->prepare("SELECT Voter.vote FROM Voter,articles WHERE Voter.Id_articles = articles.Id_articles AND articles.Id_articles =?");
    $result->execute(array($idArticle));
    $allVote = $result->fetchAll(PDO::FETCH_ASSOC);
    for($i=0;$i<count($allVote);$i++){
        if($allVote[$i]["vote"] == $type){
            $total+=1;
        }
    }
    return $total;
}
//var type doit être 1 ou -1 (vote positif ou négatif)
function listUserVote($idArticle,$type){
    include 'connectDb.php';
    $listUser = array();
    $result = $db->prepare("SELECT users.username FROM Voter,articles,users WHERE Voter.Id_articles = articles.Id_articles AND articles.Id_articles = ? AND Voter.Id_user = users.Id_user AND Voter.vote = ?");
    $result->execute(array($idArticle,$type));
    $allVote = $result->fetchAll(PDO::FETCH_ASSOC);
    for($i=0;$i<count($allVote);$i++){
        $listUser[] = $allVote[$i]["username"];
    }
    return $listUser;
}

function listArticle($role){
    include 'connectDb.php';
    $resultat = array();
    try {
        $result = $db->prepare("SELECT a.Id_articles, a.content, a.date_publi, u.username, u.Id_user FROM articles a, users u WHERE a.Id_user= u.Id_user");
        $result->execute();
    }catch (Exception $e){
        return NULL;
    }
    $allArticle = $result->fetchAll(PDO::FETCH_ASSOC);
    for($i=0;$i<count($allArticle);$i++){
        $art = array();
        if($role == "publisher" || $role == "moderator"){
            $art["votePos"] = fetchNbVote($allArticle[$i]["Id_articles"],1);
            $art["voteNeg"] = fetchNbVote($allArticle[$i]["Id_articles"],-1);
        }
        if($role =="moderator"){
            $art["votePosUser"] = listUserVote($allArticle[$i]["Id_articles"],1);
            $art["voteNegUser"] = listUserVote($allArticle[$i]["Id_articles"],-1);
        }
        $art["auteur"] = $allArticle[$i]["username"];
        $art["date"] = $allArticle[$i]["date_publi"];
        $art["content"] = $allArticle[$i]["content"];
        $resultat[] = $art;
    }
    return $resultat;
}

function listArticleAuteur($id){
    include 'connectDb.php';
    $resultat = array();
    $result = $db->prepare("SELECT a.Id_articles, a.content, a.date_publi, u.username, u.Id_user FROM articles a, users u WHERE a.Id_user= u.Id_user AND u.Id_user = ?");
    $result->execute(array($id));
    $allArticle = $result->fetchAll(PDO::FETCH_ASSOC);
    return $allArticle;
}

function addArticle($content, $id){
    include "connectDb.php";
    try {
        $request = $db->prepare("INSERT INTO `articles`(`content`, `Id_user`) VALUES (?,?)");
        $request->execute(array($content, $id));
    }catch (Exception $e){
        return false;
    }

    return $request->rowCount() > 0;
}

function modifyArticle($content, $id){
    include "connectDb.php";
    try {
        $request = $db->prepare("UPDATE `articles` SET `content`=? WHERE `Id_articles`=?");
        $request->execute(array($content, $id));
    }catch (Exception $e){
        return -1;
    }

    return $request->rowCount() > 0 ? 1 : 0;
}

function checkAutor($idArticle,$idAuteur){
    include "connectDb.php";
    try {
        $request = $db->prepare("SELECT * FROM `articles` WHERE `Id_articles`=? AND `Id_user`=?");
        $request->execute(array($idArticle, $idAuteur));
    }catch (Exception $e){
        return -1;
    }

    return $request->rowCount() > 0 ? 1 : 0;
}

function deleteArticle($idArticle){
    include "connectDb.php";
    try {
        $request = $db->prepare("DELETE FROM `articles` WHERE `Id_articles`=?");
        $request->execute(array($idArticle));
    }catch (Exception $e){
        return -1;
    }

    return $request->rowCount() > 0 ? 1 : 0;
}

function voteArticle($idUser, $idArticle, $value){
    include "connectDb.php";

    if($value != "1" && $value != "-1"){
        return -1;
    }

    try {
        $request = $db->prepare("SELECT * FROM Voter WHERE Id_user = ? AND Id_articles = ?");
        $request->execute(array($idUser, $idArticle));
    }catch (Exception $e){
        return 2;
    }

    if($request->rowCount() == 0){
        try {
            $request = $db->prepare("INSERT INTO `Voter`(`Id_user`, `Id_articles`, `vote`) VALUES (?,?,?)");
            $request->execute(array($idUser, $idArticle, $value));
        }catch (Exception $e){
            return 2;
        }

        if($request->rowCount() > 0){
            return 1;
        }else{
            return 0;
        }
    }else{
        //L'user à déjà voté pour cette article
        try {
            $request = $db->prepare("DELETE FROM Voter WHERE Id_user = ? AND Id_articles = ?");
            $request->execute(array($idUser, $idArticle));
        }catch (Exception $e){
            return 2;
        }

        if($request->rowCount() > 0){
            return voteArticle($idUser, $idArticle, $value);
        }else{
            return 0;
        }
    }
}

?>