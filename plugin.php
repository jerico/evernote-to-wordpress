<?php
/*
Plugin Name: Evernote to Wordpress
Plugin URI: http://github.com/jerico/evernote-to-wordpress
Description: Imports Evernote notes to Wordpress as Posts
Version: 0.1
Author: Jerico Aragon
Author URI: http://www.jericoaragon.com/
*/


require('vendor/autoload.php');

add_action('admin_menu', 'e2w_menu');

function e2w_menu() {
	add_menu_page('Evernote to Wordpress', 'Evernote to Wordpress', 'manage_options', 'e2w', 'e2w_page', NULL, 30);
}

function e2w_page() {

	$options = unserialize(get_option('e2w_options'));

	echo '<div class="wrap">';
	echo '<a href="admin.php?page=e2w&action=fetch" class="button">Fetch notes</a>';
	echo '
		<h2>Settings</h2>
		<form method="POST" action="admin.php?page=e2w">
			<table class="form-table">
				<tr>
					<th><label for="developerToken">Developer Token</label></th>
					<td>
						<input type="text" name="developerToken" style="width:25em" value="' . $options['developerToken'] . '"/>
						<p class="description"><a href="https://www.evernote.com/api/DeveloperToken.action" target="_blank">Get yours here</a></p>
					</td>
				</tr>
				<tr>
					<th><label for="searchTerm">Search Term.</label></th>
					<td>
						<input type="text" name="searchTerm" style="width:25em" value="' . $options['searchTerm'] . '"/>
						<p class="description"><a href="https://dev.evernote.com/doc/articles/search_grammar.php" target="_blank">Evernote Search Grammar</a></p>					</td>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
		</form>
	';
	echo '</div>';

	if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
		e2w_fetch_notes();
		echo "<p>Imported!</p>";
	}

	if (isset($_POST['developerToken']) && $_POST['searchTerm']) {
		$options = array(
			'developerToken' => $_POST['developerToken'],
			'searchTerm' => $_POST['searchTerm']
		);

		update_option('e2w_options', serialize($options));
	}
}

function e2w_fetch_notes() {

	$options = unserialize(get_option('e2w_options'));

	$token = $options['developerToken'];
	$searchTerm = $options['searchTerm'];
	$sandbox = true;

	$client = new \Evernote\Client($token, $sandbox);

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


