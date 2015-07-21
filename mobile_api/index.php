<?php

require 'Slim/Slim.php';
require 'db-functions.php';
require_once '../qa-include/qa-base.php';
require_once '../qa-include/qa-db-users.php';
require_once '../qa-include/qa-db.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//Login and Authentication
$app->get('/getIdentity', 'getIdentity');
$app->get('/latestIdeas/:startIndex', 'latestIdeas');
$app->get('/myIdeas/:startIndex', 'myIdeas');
$app->get('/viewIdea/:postid', 'viewIdea');
$app->get('/categories/', 'categories');
$app->post('/postIdea','postIdea');



//loginAuthentication Callback
function getIdentity(){
	$username = $_SERVER['REMOTE_USER'];
	$username = explode('\\', $username);
	return $username[1];
};

//mostRecentIdeas Callback
function latestIdeas($startIndex){
	

	$result = qa_db_query_raw("Select q.postid,q.title from qa_posts q
							  inner join qa_latestactivity l on q.postid=l.postid
							  where q.tags <> '#test-challenge'
							  order by l.updated desc limit ".$startIndex.", 20;"
							);

	$ideas = getJsonFromResultObject($result);

	echo $ideas;
	
};


//myIdeas Callback
function myIdeas($startIndex){
	$username = getIdentity();
	$result = qa_db_query_raw("Select postid, type, parentid, upvotes, downvotes, netvotes, created, title, content, tags
									from qa_posts
									where userid = (select userid from qa_users where handle = '".$username.
									"' and email <> 'aicproddev') order by created desc
									 limit ".$startIndex.", 20");
	$ideas = getJsonFromResultObject($result);

	echo $ideas;
	
};


//viewIdea Callback
function viewIdea($postid){

	$result = qa_db_query_raw("Select postid, type, parentid, upvotes, downvotes, netvotes, created, title, content, tags
									from qa_posts
									where postid = '".$postid."'");
	$ideas = getJsonFromResultObject($result);

	echo $ideas;
	
};



//get Categories
function categories(){
	$result = qa_db_query_raw("Select * from qa_categories where parentid is null;");

	$categories = getJsonFromResultObject($result);

	echo $categories;
};


//Post an Idea
function postIdea(){
	echo "In Progress!";
};





$app->run();

?>