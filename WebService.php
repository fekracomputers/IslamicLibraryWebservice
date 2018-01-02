<?php

/*
 * released under GPL-3
 *
 */

/**
 * Web servise for shamela books
 *
 * @author fekra computers
 */
class WebService {

    public static function getCategories($request, $input)
    {
        $parentCategoryID = safeGetInt($request[2], null);

        $startAfterID = 0;
        $option = safeGetString($request[3], "");
        if($option=="more")$startAfterID = safeGetInt($request[4], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);

        if(mb_strlen($keywords)>0 && $parentCategoryID==0)$parentCategoryID = null;

        $categories = UtilityDB::getCategories($keywords, $parentCategoryID, $startAfterID, $limit);

        $finalCategories = array();
        foreach ($categories as $id=>$title)
        {
            //echo "<p>$id:$title";

            $finalCategory = new stdClass();
            $finalCategory->id = intval($id);
            $finalCategory->title = $title;

            array_push($finalCategories, $finalCategory);
        }

        return self::successResponse($finalCategories, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getAuthors($request, $input)
    {
        $option="";
        $startAfterID = 0;
        if(array_key_exists(2, $request))
          $option = safeGetString($request[2], "");
        if($option=="more") $startAfterID = safeGetInt($request[3], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);

        $authors = UtilityDB::getAuthors($keywords, $startAfterID, $limit);

        $finalAuthors = array();
        foreach ($authors as $id=>$name)
        {
            //echo "<p>$id:$name";

            $finalAuthor = new stdClass();
            $finalAuthor->id = intval($id);
            $finalAuthor->name = $name;

            array_push($finalAuthors, $finalAuthor);
        }

        return self::successResponse($finalAuthors, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getBooks($request, $input)
    {
        $startAfterID = 0;
        $option = safeGetString($request[2], "");
        if($option=="more")$startAfterID = safeGetInt($request[3], 0);

        $of = safeGetString($input["of"], null);
        if($of == "author" || $of == "category")
        {
            $ofData = safeGetInt($input["id"], 0);
        }
        else if($of=="books")
        {
            $ofData = safeGetIntArray($input["ids"], array());
            $ofData = json_encode($ofData);
            $ofData = str_replace("[", "(", $ofData);
            $ofData = str_replace("]", ")", $ofData);
        }
        else
        {
           return self::errorResponse(400,
           MSG_INVALID_REQUEST_FORMAT,
           400,
           "of must be author ,cateory or book");
        }

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);

        $categories = UtilityDB::getBooks($keywords, $of, $ofData, $startAfterID, $limit);

        $finalCategories = array();
        foreach ($categories as $id=>$title)
        {
            //echo "<p>$id:$title";

            $finalCategory = new stdClass();
            $finalCategory->id = intval($id);
            $finalCategory->title = $title;

            array_push($finalCategories, $finalCategory);
        }

        return self::successResponse($finalCategories, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getSubjects($request, $input)
    {
        $bookID = safeGetInt($request[2], null);

        $parentSubjectID = safeGetInt($request[3], null);

        $startAfterID = 0;
        $option = safeGetString($request[4], "");
        if($option=="more")$startAfterID = safeGetInt($request[5], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], 1000000);

        if(mb_strlen($keywords)>0 && $parentSubjectID==0)$parentSubjectID = null;

        $subjects = UtilityDB::getSubjects($bookID, $keywords, $parentSubjectID, $startAfterID, $limit);

        $finalSubjects = array();
        foreach ($subjects as $item)
        {
            //echo "<p>$item->id ($item->hasChilds):$item->title:$item->firsthadithid";
            //echo getSearchText($item->title);

            $finalSubject = new stdClass();
            $finalSubject->id = intval($item->id);
            $finalSubject->title = $item->title;
            $finalSubject->haschilds = $item->hasChilds;

            array_push($finalSubjects, $finalSubject);
        }

        return self::successResponse($finalSubjects, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function saveUserPreference($request, $input)
    {
        $userEmail = safeGetString($input["useremail"], false);
        if($userEmail===false)
           return self::errorResponse(400,MSG_INVALID_REQUEST_FORMAT,400,"missing useremail");


        $userPreferenceList = $input["userpreferencelist"];
        $userPreferenceList = json_encode($userPreferenceList, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

        $result = UtilityDB::saveUserPreference($userEmail, $userPreferenceList);

        if($result===false)
          return self::errorResponse(500 ,MSG_OPERATION_FAILED,400);
        return self::successResponse(array("response"=>1));
    }

    public static function loadUserPreference($request, $input)
    {
        $userEmail = safeGetString($input["useremail"], false);
        if($userEmail===false)
            return self::errorResponse(400,MSG_INVALID_REQUEST_FORMAT,400,"missing useremail");

        return UtilityDB::loadUserPreference($userEmail);
    }

    public static function getBook($request, $input)
    {
        $bookID = safeGetInt($request[2], 0);

        $book = UtilityDB::getBookInfo($bookID);
        if($book===false)
          return self::errorResponse(500 ,MSG_OPERATION_FAILED,500);

        $finalBook = new stdClass();
        $finalBook->id = $book["id"];
        $finalBook->title = $book["title"];
        $finalBook->information = $book["information"];
        $finalBook->card = $book["card"];
        $finalBook->pagescount = UtilityDB::getBookPageCount($bookID);
        $finalBook->partsnumbers = UtilityDB::getPartsNumbers($bookID);
        $finalBook->authors = UtilityDB::getBookAuthors($bookID);
        $finalBook->categories = UtilityDB::getBookCategories($bookID);

        return self::successResponse($finalBook, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getAuthor($request, $input)
    {
        $authorID = safeGetInt($request[2], 0);

        $author = UtilityDB::getAuthorInfo($authorID);
        if($author===false)
          return self::errorResponse(500 ,MSG_OPERATION_FAILED,500);

        $finalAuthor = new stdClass();
        $finalAuthor->id = $author["id"];
        $finalAuthor->title = $author["name"];
        $finalAuthor->information = $author["information"];
        $finalAuthor->birthhigriyear = $author["birthhigriyear"];
        $finalAuthor->deathhigriyear = $author["deathhigriyear"];

        return self::successResponse($finalAuthor, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getPage($request, $input)
    {
        $bookID = safeGetInt($request[2], null);
        $pageID = safeGetInt($request[3], null);
        if($bookID===null)
            return self::successResponse(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        if($pageID===null){
            $pageID = intval(UtilityDB::getBookFirstPageID($bookID));
        }

        $pageInfo = UtilityDB::getPageInfo($bookID, $pageID);

        $page = new stdClass();
        $page->page = UtilityDB::getPage($bookID, $pageID);
        $page->pageid = $pageID;
        $page->partnumber = $pageInfo["partnumber"];
        $page->pagenumber = $pageInfo["pagenumber"];
        $page->firstpagenumber = UtilityDB::getBookFirstPageID($bookID);
        $page->previouspagenumber = UtilityDB::getBookPreviousPageID($bookID, $pageID);
        $page->nextpagenumber = UtilityDB::getBookNextPageID($bookID, $pageID);
        $page->lastpagenumber = UtilityDB::getBookLastPageID($bookID);

        return self::successResponse($page, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getSimilarWords($request, $input)
    {
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        $word = safeGetString($input["word"], "");
        $word = mb_trim($word, " \r\n");
        if(mb_strlen($word)<4)
            return self::errorResponse(400,MSG_INVALID_INPUT,400,"at least 4 words");

        $words = UtilityDB::getSimilarWords($word, $limit);

        return self::successResponse($words, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function search($request, $input)
    {
        $bookID = safeGetInt($request[2], 0);

        $startAfterID = 0;
        $option = safeGetString($request[3], "");
        if($option=="more")$startAfterID = safeGetInt($request[4], 0);

        $keywords = safeGetString($input["keywords"], "");
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        $option = safeGetString($input["option"], "");

        $pages = UtilityDB::search($bookID, $keywords, $option, $startAfterID, $limit);
        if($pages===false)
          return self::errorResponse(501 ,MSG_FATURE_NOT_SUPPORTED,501);

        $booksIDs = array();
        foreach ($pages as $page)
        {
            array_push($booksIDs, $page->bookid);
        }
        $booksIDs = json_encode($booksIDs);
        $booksIDs = str_replace("[", "(", $booksIDs);
        $booksIDs = str_replace("]", ")", $booksIDs);
        $books = UtilityDB::getBooks("", "books", $booksIDs);

        $finalPages = array();
        foreach ($pages as $page)
        {
            $finalPage = new stdClass();
            $finalPage->id = $page->docid;
            $finalPage->bookid = $page->bookid;
            $finalPage->booktitle = $books[$page->bookid];
            $finalPage->pageid = $page->pageid;
            $finalPage->searchtext = $page->searchtext;

            //echo "<p>$finalPage->id, $finalPage->bookid, $finalPage->pageid, $finalPage->booktitle:<br/> $finalPage->searchtext</p>";

            array_push($finalPages, $finalPage);
        }

        return self::successResponse($finalPages, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function processCommand($method, $request, $input)
    {
        if(count($request)<2 || strtolower($request[0])!="api")
             return self::errorResponse(400,MSG_INVALID_REQUEST_FORMAT,400,"all requests must tart with api/");

        $command = strtolower($request[1]);

        if($command=="getcategories")
        {
            return self::getCategories($request, $input);
        }
        else if($command=="getauthors")
        {
            return self::getAuthors($request, $input);
        }
        else if($command=="getbooks")
        {
            return self::getBooks($request, $input);
        }
        else if($command=="getbooksubjects")
        {
            return self::getSubjects($request, $input);
        }
        else if($command=="getbook")
        {
            return self::getBook($request, $input);
        }
        else if($command=="getauthor")
        {
            return self::getAuthor($request, $input);
        }
        else if($command=="search")
        {
            return self::search($request, $input);
        }
        else if($command=="getpage")
        {
            return self::getPage($request, $input);
        }
        else if($command=="saveuserpreference")
        {
            return self::saveUserPreference($request, $input);
        }
        else if($command=="loaduserpreference")
        {
            return self::loadUserPreference($request, $input);
        }
        else if($command=="getsimilarwords")
        {
            return self::getSimilarWords($request, $input);
        }
        else {
           return self::errorResponse(400,MSG_INVALID_REQUEST_FORMAT,400,"unkown request name : (".$command.")");
        }
    }
  private static function successResponse($data, $options = 0, $code=200)
	{
		http_response_code ($code);
		header("Content-Type: application/json; charset=UTF-8");
		echo json_encode($data,$options);
	}

	protected static function errorResponse($HttpStatusCode,$title,$errorCode,$detail="")
	{
		http_response_code ($HttpStatusCode);
		header("Content-Type: application/json; charset=UTF-8",$HttpStatusCode);
		echo json_encode([
			"code"=>$errorCode,
			"title"=>$title,
			"status"=>$HttpStatusCode,
			"detail"=>$detail,
		]);
	}
}
