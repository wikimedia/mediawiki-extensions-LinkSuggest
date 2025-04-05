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
 * @author Jack Phoenix
 * @copyright Copyright © 2008-2009, Wikia Inc.
 * @copyright Copyright © 2011 Jesús Martínez Novo
 * @license GPL-2.0-or-later
 * @link https://www.mediawiki.org/wiki/Extension:LinkSuggest Documentation
 */

use MediaWiki\Hook\EditPage__showEditForm_initialHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\User\UserOptionsManager;

// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class LinkSuggest implements
	EditPage__showEditForm_initialHook,
	GetPreferencesHook
{
	private UserOptionsManager $userOptionsManager;

	public function __construct(
		UserOptionsManager $userOptionsManager
	) {
		$this->userOptionsManager = $userOptionsManager;
	}

	/**
	 * Adds the new toggle to Special:Preferences for disabling LinkSuggest
	 * extension on a per-user basis
	 *
	 * @param User $user
	 * @param mixed[] &$preferences
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['disablelinksuggest'] = [
			'type' => 'toggle',
			'section' => 'editing/advancedediting',
			'label-message' => 'tog-disablelinksuggest',
		];
	}

	/**
	 * Add HTML required by LinkSuggest and the appropriate CSS and JS files to the
	 * edit form to users who haven't disabled LinkSuggest in their preferences.
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $output
	 */
	public function onEditPage__showEditForm_initial( $editPage, $output ) {
		if ( !$this->userOptionsManager->getOption( $output->getUser(), 'disablelinksuggest' ) ) {
			// Load CSS and JS by using ResourceLoader
			$output->addModules( 'ext.LinkSuggest' );
		}
	}
}
