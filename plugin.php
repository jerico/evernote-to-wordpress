<?php
/*
Plugin Name: Evernote to Wordpress
Plugin URI: http://github.com/jerico/evernote-to-wordpress
Description: Imports Evernote notes to Wordpress as Posts
Version: 0.1
Author: Jerico Aragon
Author URI: http://www.jericoaragon.com/
*/


// Developer token. https://www.evernote.com/api/DeveloperToken.action
$token = "";

// Search notes with tag 'j'. https://dev.evernote.com/doc/articles/search_grammar.php
$searchTerm = "tag:j";

require('vendor/autoload.php');

add_action('admin_menu', 'e2w_menu');

function e2w_menu() {
	add_menu_page('Evernote to Wordpress', 'Evernote to Wordpress', 'manage_options', 'e2w', 'e2w_page', NULL, 30);
}

function e2w_page() {
	echo '<div class="wrap">';
	echo '<a href="admin.php?page=e2w&action=fetch" class="button">Fetch notes</a>';
	echo '</div>';

	if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
		e2w_fetch_notes();
		echo "<p>Imported!</p>";
	}
}

function e2w_fetch_notes() {

	global $token, $searchTerm;
	$client = new \Evernote\Client($token, false);

	$search = new \Evernote\Model\Search($searchTerm);

	$results = $client->findNotesWithSearch($search);

	$c = new \Evernote\Enml\Converter\EnmlToHtmlConverter();

	foreach ($results as $r) {
		$note = $client->getNote($r->guid)->getEdamNote();
		echo $c->convertToHtml($note->content);
		echo date('Y-m-d H:i:s', $note->created/1000);

		$post = array(
			'post_content' => $c->convertToHtml($note->content),
			'post_title' => $note->title,
			'post_date' => date('Y-m-d H:i:s', $note->created/1000),
			'post_status' => 'publish'
		);

		wp_insert_post($post);

		$client->deleteNote($client->getNote($r->guid));
	}

}


