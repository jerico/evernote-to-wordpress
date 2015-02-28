# Evernote to Wordpress plugin

Barebone plugin that imports Evernote notes to Wordpress depending on search term. It then deletes imported notes.

## Instructions
- `composer install`
- Replace $token on plugin.php with your token. You can get it at https://www.evernote.com/api/DeveloperToken.action 
- Replace $searchTerm with what notes you want to import. https://dev.evernote.com/doc/articles/search_grammar.php
- Upload as new a new plugin
- There will be a new menu named "Evernote to Wordpress", press "Fetch notes" button

## Change log
2015-02-28
- Ability to update $token and $searchTerm in wp-admin
- Ability to test $searchTerm
- Add $sandbox option
- Ability to set update interval in wp-admin
