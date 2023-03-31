<?php
include 'connectDb.php';
include 'jwt_utils.php';
include 'requestDb.php';
error_reporting(E_ALL);
ini_set("display_errors", 1);
header("Content-Type:application/json");

//Verification connexion
$bearer_token = get_bearer_token();
if ($bearer_token !== null && is_jwt_valid($bearer_token)) {
    $payload = getPayload($bearer_token);
}

$http_method = $_SERVER['REQUEST_METHOD'];
switch($http_method){
    case 'GET':
        if (isset($payload)) {
            if($payload->type === "publisher"){
                if(isset($_GET["myown"])){
                    $listeArticlesAuteur = listArticleAuteur($payload->id_user);
                    if($listeArticlesAuteur) {
                        deliver_response(200, "Liste de vos articles", listArticleAuteur($payload->id_user));
                    }else{
                        deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                    }
                }else{
                    $listeArticles = listArticle($payload->id_user);
                    if($listeArticles != null) {
                        deliver_response(200, "Liste des articles", listArticle($payload->type));
                    }else{
                        deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                    }
                }
            }else{
                $listeArticles = listArticle($payload->id_user);
                if($listeArticles != null) {
                    deliver_response(200, "Liste des articles", listArticle($payload->type));
                }else{
                    deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                }
            }
        }else{
            $listeArticles = listArticle(null);
            if($listeArticles != null){
                deliver_response(200, "Liste des articles", $listeArticles);
            }else{
                deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
            }
        }
        break;
    case 'POST':
        $postedData = file_get_contents('php://input');
        $postedData = json_decode($postedData, TRUE);
        if(isset($payload)){
            if($payload->type === "publisher"){
                if(isset($postedData["content"])){
                    $addArticle = addArticle($postedData["content"], $payload->id_user);
                    if($addArticle){
                        deliver_response(201, "Article ajouté", listArticle($payload->type));
                    }else{
                        deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                    }
                }else if(isset($postedData["vote"])){
                    if(isset($postedData["id_article"])){
                        $articleVote = voteArticle($payload->id_user, $postedData["id_article"], $postedData["vote"]);
                        if($articleVote == 1){
                            $listeArticles = listArticle($payload->type);
                            if($listeArticles != null){
                                deliver_response(201, "Le vote a été appliqué", listArticle($payload->type));
                            }else{
                                deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                            }
                        }else if($articleVote == 0){
                            deliver_response(400, "L'article avec lequel vous essayez de liker/disliker n'a pas été trouvé", NULL);
                        }else if($articleVote == 2){
                            deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                        }else{
                            deliver_response(400, "La valeur du vote n'est pas bonne (1 ou -1)", NULL);
                        }
                    }else{
                        deliver_response(400, "Vous devez fournir un id d'article", NULL);
                    }
                } else{
                    deliver_response(400, "Vous n'avez pas fourni asser d'éléments", NULL);
                }
            }else{
                deliver_response(403, "Vous devez être un publisher", NULL);
            }
        }else{
            deliver_response(403, "Vous devez être connecté", NULL);
        }
        break;
    case 'PUT':
        $postedData = file_get_contents('php://input');
        $postedData = json_decode($postedData, TRUE);
        if(isset($payload)){
            if($payload->type === "publisher") {
                if (isset($postedData["id_article"])) {
                    $checkAutor = checkAutor($postedData["id_article"], $payload->id_user);
                    if ($checkAutor == 1) {
                        if (isset($postedData["content"])) {
                            $modifyArticle = modifyArticle($postedData["content"], $postedData["id_article"]);
                            if ($modifyArticle == 1) {
                                deliver_response(200, "Article modifié avec succès", listArticle($payload->type));
                            } else if ($modifyArticle == 0) {
                                deliver_response(400, "L'article que vous essayer de modifier n'a pas été trouvé", NULL);
                            } else {
                                deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                            }
                        } else {
                            deliver_response(400, "Vous devez renseigner le contenue de l'article", NULL);
                        }
                    } else if ($checkAutor == 0) {
                        deliver_response(403, "Vous ne pouvez pas modifier un article qui ne vous appartient pas", NULL);
                    } else {
                        deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                    }
                }else{
                    deliver_response(400, "Vous devez préciser l'article à modifier", NULL);
                }
            }else{
                deliver_response(403, "Vous devez être un publisher", NULL);
            }
        }else{
            deliver_response(403, "Vous devez être connecté", NULL);
        }
        break;
    case 'DELETE':
        $postedData = file_get_contents('php://input');
        $postedData = json_decode($postedData, TRUE);
        if(isset($payload)){
            if($payload->type === "publisher"){
                if(isset($postedData["id_article"])) {
                    $checkAutor = checkAutor($postedData["id_article"], $payload->id_user);
                    if ($checkAutor == 1) {
                        $deleteArticle = deleteArticle($postedData["id_article"]);
                        if($deleteArticle == 1){
                            deliver_response(200, "L'article a été supprimé avec succès", listArticle($payload->type));
                        }else if($deleteArticle == 0){
                            deliver_response(400, "L'article que vous essayer de supprimer n'a pas été trouvé", NULL);
                        }else{
                            deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                        }
                    } else if($checkAutor == 0) {
                        deliver_response(403, "Vous ne pouvez pas supprimer un article qui ne vous appartient pas", NULL);
                    }else{
                        deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                    }
                }else{
                    deliver_response(400, "Vous devez renseigner un article à supprimer", NULL);
                }
            }else if($payload->type === "moderator"){
                if(isset($postedData["id_article"])) {
                    $deleteArticle = deleteArticle($postedData["id_article"]);
                    if ($deleteArticle == 1) {
                        deliver_response(200, "L'article a été supprimé avec succès", listArticle($payload->type));
                    } else if($deleteArticle == 0){
                        deliver_response(400, "L'article que vous essayer de supprimer n'a pas été trouvé", $postedData["id_article"]);
                    }else{
                        deliver_response(502, "Erreur avec la requête envoyée à la base de données", NULL);
                    }
                }else{
                    deliver_response(400, "Vous devez renseigner un article à supprimer", NULL);
                }
            }else{
                deliver_response(403, "Vous n'avez pas la permission de supprimer cet article", NULL);
            }
        }else{
            deliver_response(403, "Vous devez être connecté", NULL);
        }
        break;
    default:
        deliver_response(401, "Requête non gérée par l'api", NULL);
        break;


}

?>