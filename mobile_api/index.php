<?php

require 'Slim/Slim.php';
require 'db-functions.php';
require_once '../qa-include/qa-base.php';
require_once '../qa-include/qa-db-users.php';
require_once '../qa-include/qa-db.php';
require_once '../qa-include/qa-app-posts.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//Login and Authentication

$app->post('/checkIdentity', 'checkIdentity');
$app->get('/getIdentity', 'getIdentities');
$app->get('/latestIdeas/:startIndex', 'latestIdeas');
$app->get('/myIdeas/:startIndex', 'myIdeas');
$app->get('/viewIdea/:postid', 'viewIdea');
$app->get('/categories/', 'categories');
$app->get('/categories/getFeed/:categoryId/:startIndex', 'categoryFeed');
$app->get('/notifications/:startIndex', 'notifications');
$app->get('/userVotes/:postid', 'userVotes');
$app->post('/submitPost','submitPost');

/*
	Function to return the Identity of the user in Post.
*/
function checkIdentity(){
	$username = $_SERVER['REMOTE_USER'];
	$username = explode('\\', $username);
	echo $username[1];
};

/*
	Function to return the Identity of the user.
*/
function getIdentities(){
	$username = $_SERVER['REMOTE_USER'];
	$username = explode('\\', $username);
	echo $username[1];
};

/*
	Internal Function to return Identity of the user.
*/
function getIdentity(){
	$username = $_SERVER['REMOTE_USER'];
	$username = explode('\\', $username);
	return $username[1];
};

/*
	Function to return the Identity of the user.
*/
function getUserId($handle){
	$result = qa_db_query_raw("Select userid from qa_users where handle = '".$handle."';");

	$userid = getValueFromResultObject($result, "userid");

	return $userid;
};

/*
	Function to return the latest Ideas of the application.
*/

function latestIdeas($startIndex){
	
	$result = qa_db_query_raw("Select q.* from qa_posts q
							  inner join qa_latestactivity l on q.postid=l.postid
							  where q.tags <> '#test-challenge'
							  order by l.updated desc limit ".$startIndex.", 20;"
							);

	$ideas = getJsonFromResultObject($result);

	echo $ideas;
	
};

/*
	Function to view myIdeas.
*/
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

/*
	Function to view an Idea Post.
*/
function viewIdea($postid){

	$result = qa_db_query_raw("Select *
									from qa_posts
									where postid = '".$postid."'");
	$ideas = getJsonFromResultObject($result);

	echo $ideas;
}

/*
	Function to return the Categories.
*/
function categories(){
	$result = qa_db_query_raw("Select * from qa_categories where parentid is null;");

	$categories = getJsonFromResultObject($result);

	echo $categories;
};

/*
	Function to return the Idea Feed based on the input category Id.
*/
function categoryFeed($catId, $startIndex){

	$result = qa_db_query_raw("Select * from qa_posts 
								where type = 'Q' and
								'".$catId."' in (categoryid, catidpath1, catidpath2, catidpath3)
								order by created desc
								limit ".$startIndex.", 20;");

	$categoryFeed = getJsonFromResultObject($result);

	echo $categoryFeed;

};

/*
	Function to get the notifications of a particular user
*/

function notifications($startIndex){
	$username = getIdentity();

	$result = qa_db_query_raw("Select p.postid, p.parentid, p.type, u.displayname, p.created, p.updated from 
									(Select p1.postid, p1.parentid, p1.userid, p1.type, p1.created, p1.updated from qa_posts as p1
										where p1.type <> 'Q' and p1.parentid in
										(select p2.postid from qa_posts as p2 inner join qa_users as u2 on p2.userid = u2.userid
										and u2.handle='".$username."' and p2.type = 'Q')
									order by 
									case when p1.updated is null then p1.created else p1.updated End
								desc
								limit ".$startIndex.", 20) as p , qa_users as u where u.userid = p.userid;"
							);

	/*
				select p.postid, p.type, u.displayname, p.created, p.updated from 
				(Select p1.postid, p1.userid, p1.type, p1.created, p1.updated from qa_posts as p1
					where p1.type <> 'Q' and p1.parentid in
					(select p2.postid from qa_posts as p2 inner join qa_users as u2 on p2.userid = u2.userid
					and u2.handle='t_angar' and p2.type = 'Q')
					order by 
					case when p1.updated is null then p1.created else p1.updated End
					desc
					limit 0, 20) as p , qa_users as u where u.userid = p.userid;
	*/

	$notifications = getJsonFromResultObject($result);

	echo $notifications;
	
};

/*
	Function to check if an Idea is voted
*/
function userVotes($postid){
	$handle = getIdentity();
	$userid = getUserId($handle);

	$result = qa_db_query_raw("Select * from qa_uservotes where postid='".$postid."' and userid = '".$userid."';");
	$uservotes = getJsonFromResultObject($result);

	echo $uservotes;
};

/*
	Function to Submit an Idea
*/
function submitPost(){
	echo "In Progress!";
};

$app->run();

?>