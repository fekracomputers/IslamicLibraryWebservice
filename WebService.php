<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebService
 *
 * @author softlock
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

        return json_encode($finalCategories, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function getAuthors($request, $input)
    {
        $startAfterID = 0; 
        $option = safeGetString($request[2], "");
        if($option=="more")$startAfterID = safeGetInt($request[3], 0);

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

        return json_encode($finalAuthors, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
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
        else if($of=="mybooks")
        {
            $of = "books";
            $ofData = UtilityDB::loadUserPreference("user@server.com");
            $ofData = str_replace("[", "(", $ofData);
            $ofData = str_replace("]", ")", $ofData);
        }
        else
        {
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));
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

        return json_encode($finalCategories, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
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

        return json_encode($finalSubjects, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function saveUserPreference($request, $input)
    {
        $userEmail = safeGetString($input["useremail"], false);
        if($userEmail===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $userPreferenceList = safeGetIntArray($input["userpreferencelist"], array());
        $userPreferenceList = json_encode($userPreferenceList);

        $result = UtilityDB::saveUserPreference($userEmail, $userPreferenceList);

        if($result===false)return json_encode(array("response"=>0, "reason"=>MSG_OPERATION_FAILED));
        return json_encode(array("response"=>1));
    }
    
    public static function loadUserPreference($request, $input)
    {
        $userEmail = safeGetString($input["useremail"], false);
        if($userEmail===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $result = UtilityDB::loadUserPreference($userEmail);
        
        return json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getBook($request, $input)
    {
        $bookID = safeGetInt($request[2], 0);
        
        $book = UtilityDB::getBookInfo($bookID);
        if($book===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
                        
        $finalBook = new stdClass();
        $finalBook->id = $book["id"];
        $finalBook->title = $book["title"];
        $finalBook->information = $book["information"];
        $finalBook->card = $book["card"];
        $finalBook->pagescount = UtilityDB::getBookPageCount($bookID);
        $finalBook->partsnumbers = UtilityDB::getPartsNumbers($bookID);
        $finalBook->authors = UtilityDB::getBookAuthors($bookID);
        $finalBook->categories = UtilityDB::getBookCategories($bookID);
        
        return json_encode($finalBook, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
        
    public static function getAuthor($request, $input)
    {
        $authorID = safeGetInt($request[2], 0);
        
        $author = UtilityDB::getAuthorInfo($authorID);
        if($author===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
        
        $finalAuthor = new stdClass();
        $finalAuthor->id = $author["id"];
        $finalAuthor->title = $author["name"];
        $finalAuthor->information = $author["information"];
        $finalAuthor->birthhigriyear = $author["birthhigriyear"];
        $finalAuthor->deathhigriyear = $author["deathhigriyear"];
        
        return json_encode($finalAuthor, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getPage($request, $input)
    {
        $bookID = safeGetInt($request[2], null);
        $pageID = safeGetInt($request[3], null);
        if($bookID===null)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));
        
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
                
        return json_encode($page, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getSimilarWords($request, $input)
    {
        $limit = safeGetInt($input["limit"], MAX_RESULT_COUNT);
        $word = safeGetString($input["word"], "");
        $word = mb_trim($word, " \r\n");
        if(mb_strlen($word)<4)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_INPUT));
        
        $words = UtilityDB::getSimilarWords($word, $limit);
        
        return json_encode($words, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
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
            return json_encode(array("response"=>0, "reason"=>MSG_FATURE_NOT_SUPPORTED));
        
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
        
        return json_encode($finalPages, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function processCommand($method, $request, $input)
    {
        if(count($request)<2 || strtolower($request[0])!="api")
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $command = strtolower($request[1]);
        
        if($command=="getcategories")
        {
            return WebService::getCategories($request, $input);
        }
        else if($command=="getauthors")
        {
            return WebService::getAuthors($request, $input);
        }
        else if($command=="getbooks")
        {
            return WebService::getBooks($request, $input);
        }
        else if($command=="getbooksubjects")
        {
            return WebService::getSubjects($request, $input);
        }
        else if($command=="getbook")
        {
            return WebService::getBook($request, $input);
        }
        else if($command=="getauthor")
        {
            return WebService::getAuthor($request, $input);
        }
        else if($command=="search")
        {
            return WebService::search($request, $input);
        }
        else if($command=="getpage")
        {
            return WebService::getPage($request, $input);
        }
        else if($command=="saveuserpreference")
        {
            return WebService::saveUserPreference($request, $input);
        }
        else if($command=="loaduserpreference")
        {
            return WebService::loadUserPreference($request, $input);
        }
        else if($command=="getsimilarwords")
        {
            return WebService::getSimilarWords($request, $input);
        }
    }
}
