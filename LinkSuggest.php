<?php
/**
 * LinkSuggest
 * This extension provides the user with article title suggestions as he types
 * a link in wikitext.
 *
 * @file
 * @ingroup Extensions
 * @author Inez Korczyński <korczynski at gmail dot com>
 * @author Bartek Łapiński <bartek at wikia-inc dot com>
 * @author Łukasz Garczewski (TOR) <tor at wikia-inc dot com>
 * @author Maciej Brencz <macbre@wikia-inc.com>
 * @author Jesús Martínez Novo <martineznovo at gmail dot com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @copyright Copyright © 2008-2009, Wikia Inc.
 * @copyright Copyright © 2011 Jesús Martínez Novo
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:LinkSuggest Documentation
 */

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'LinkSuggest',
	'version' => '1.8.1',
	'author' => array(
		'Inez Korczyński', 'Bartek Łapiński', 'Łukasz Garczewski', 'Maciej Brencz',
		'Jesús Martínez Novo', 'Jack Phoenix'
	),
	'descriptionmsg' => 'linksuggest-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:LinkSuggest',
);

// Internationalization stuff
$wgMessagesDirs['LinkSuggest'] = __DIR__ . '/i18n';

// ResourceLoader support (MW 1.17+)
$wgResourceModules['ext.LinkSuggest'] = array(
	'scripts' => 'jquery.mw.linksuggest.js',
	'dependencies' => array( 'jquery.ui.autocomplete' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'LinkSuggest'
);

// Autoload the class file which contains everything (from the PHP side, that is)
$wgAutoloadClasses['LinkSuggest'] = __DIR__ . '/LinkSuggest.class.php';

// Hooked functions
$wgHooks['EditPage::showEditForm:initial'][] = 'LinkSuggest::onEditPage';
$wgHooks['GetPreferences'][] = 'LinkSuggest::onGetPreferences';

// AJAX callback functions
$wgAjaxExportList[] = 'LinkSuggest::get';
$wgAjaxExportList[] = 'LinkSuggest::getImage';