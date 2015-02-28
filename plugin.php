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

	if ($options['environment']) {
		$options['environment'] = 'checked';
	}

	echo '<div class="wrap">';
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
					<th><label for="searchTerm">Search Term</label></th>
					<td>
						<input type="text" name="searchTerm" style="width:25em" value="' . $options['searchTerm'] . '"/>
						<p class="description"><a href="https://dev.evernote.com/doc/articles/search_grammar.php" target="_blank">Evernote Search Grammar</a></p>					</td>
					</td>
				</tr>
				<tr>
					<th>Environment</th>
					<td>
						<fieldset><legend class="screen-reader-text"><span>Sandbox</span></legend><label for="environment">
							<input name="environment" type="checkbox" id="environment" value="true" ' . $options['environment'] . '>
							Sandbox</label>
						</fieldset>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
		</form>
	';
	echo '</div>';

	echo '<p><a href="admin.php?page=e2w&action=test" class="button">Test Search Term</a></p>';

	echo '<p><a href="admin.php?page=e2w&action=fetch" class="button">Import notes</a></p>';

	if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
		e2w_fetch_notes();
		echo "<p>Imported!</p>";
	}

	if (isset($_GET['action']) && $_GET['action'] == 'test') {
		e2w_test_search_term();
	}

	if (isset($_POST['developerToken']) && $_POST['searchTerm']) {
		if ($options['environment']) {
			$options['environment'] = true;
		}
		$options = array(
			'developerToken' => $_POST['developerToken'],
			'searchTerm' => $_POST['searchTerm'],
			'environment' => $t_POST['environment']
		);

		update_option('e2w_options', serialize($options));
	}
}

function e2w_fetch_notes() {

	$options = unserialize(get_option('e2w_options'));
	$sandbox = false;

	if ($options['environment']) {
		$sandbox = true;
	}

	$token = $options['developerToken'];
	$searchTerm = $options['searchTerm'];

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


function e2w_test_search_term() {

	$options = unserialize(get_option('e2w_options'));

	$token = $options['developerToken'];
	$searchTerm = $options['searchTerm'];
	$sandbox = false;

	if ($options['environment']) {
		$sandbox = true;
	}

	$client = new \Evernote\Client($token, $sandbox);

	$search = new \Evernote\Model\Search($searchTerm);

	$results = $client->findNotesWithSearch($search);

	$c = new \Evernote\Enml\Converter\EnmlToHtmlConverter();

	echo '<h3>Search Term Results</h3>';
	echo '<pre>';
	foreach ($results as $r) {
		$note = $client->getNote($r->guid)->getEdamNote();
		echo $note->title;
		echo '<br>';
		echo date('Y-m-d H:i:s', $note->created/1000);
		echo '<br>';
		echo '<br>';

		$post = array(
			'post_content' => $c->convertToHtml($note->content),
			'post_title' => $note->title,
			'post_date' => date('Y-m-d H:i:s', $note->created/1000),
			'post_status' => 'publish'
		);

	}
	echo '</pre>';

}
