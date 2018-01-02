<?php
include_once './common.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$input = json_decode(file_get_contents('php://input'), true);

//compressBooks();die();

if(file_exists($baseDataFolder."/main.sqlite")===false) {
    UtilityDB::generateMain();
} else {
//    UtilityDB::syncMain();
}

WebService::processCommand($method, $request, $input);
//echo WebService::processCommand("post", array("api", "getcategories", 0, "more", 0), array("keywords"=>""));
//echo WebService::processCommand("post", array("api", "getauthors", "more", 184), array("keywords"=>""));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 0), array("keywords"=>"", "of"=>"category", "id"=>2));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 0), array("keywords"=>"", "of"=>"author", "id"=>218));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 0), array("keywords"=>"", "of"=>"books", "ids"=>array(119, 137)));
//echo WebService::processCommand("post", array("api", "saveuserpreference"), array("useremail"=>"user@server.com", "userpreferencelist"=>array(array(119, 1), array(137, 1))));
//echo WebService::processCommand("post", array("api", "loaduserpreference"), array("useremail"=>"user@server.com"));
//echo WebService::processCommand("post", array("api", "getbooks", "more", 0), array("keywords"=>"", "of"=>"mybooks", "id"=>"user@server.com"));
//echo WebService::processCommand("post", array("api", "getbooksubjects", 119, 0, "more", 0), array("keywords"=>""));
//echo WebService::processCommand("post", array("api", "getbook", 119), array());
//echo WebService::processCommand("post", array("api", "getauthor", 218), array());
//echo WebService::processCommand("post", array("api", "getpage", 119, 1), array());
//$keyWords = "انما الاعمال بالنيات وانما لكل امرئ ما نوى فمن كانت هجرته الى الله ورسوله فهجرته الى الله ورسوله ومن كانت هجرته لدنيا يصيبها او امراة يتزوجها فهجرته الى ما هاجر اليه";
//echo WebService::processCommand("post", array("api", "search", 0, "more", 0), array("keywords"=>$keyWords, "option"=>"exact"));
//echo WebService::processCommand("post", array("api", "getsimilarwords"), array("word"=>"الني", "limit"=>10));
?>
