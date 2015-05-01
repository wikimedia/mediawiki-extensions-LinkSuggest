<?php
/**
 * @file
 */

class LinkSuggest {

	/**
	 * Adds the new toggle to Special:Preferences for disabling LinkSuggest
	 * extension on a per-user basis
	 *
	 * @param User $user
	 * @param Preferences $preferences
	 * @return bool
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['disablelinksuggest'] = array(
			'type' => 'toggle',
			'section' => 'editing/advancedediting',
			'label-message' => 'tog-disablelinksuggest',
		);
		return true;
	}

	/**
	 * Add HTML required by LinkSuggest and the appropriate CSS and JS files to the
	 * edit form to users who haven't disabled LinkSuggest in their preferences.
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $output
	 * @return bool
	 */
	public static function onEditPage( EditPage $editPage, OutputPage $output ) {
		global $wgUser;
		if ( $wgUser->getOption( 'disablelinksuggest' ) != true ) {
			// Load CSS and JS by using ResourceLoader
			$output->addModules( 'ext.LinkSuggest' );
		}
		return true;
	}

	/**
	 * Creates a thumbnail from an image name.
	 *
	 * @return AjaxResponse containing the thumbnail image
	 */
	public static function getImage() {
		global $wgRequest;

		$imageName = $wgRequest->getText( 'imageName' );

		$out = 'N/A';
		try {
			$img = wfFindFile( $imageName );
			if ( $img ) {
				$out = $img->createThumb( 180 );
			}
		} catch ( Exception $e ) {
			$out = 'N/A';
		}

		$ar = new AjaxResponse( $out );
		$ar->setCacheDuration( 60 * 60 );

		return $ar;
	}

	/**
	 * AJAX callback function
	 *
	 * @return array $ar Link suggestions
	 */
	public static function get() {
		global $wgRequest, $wgContLang, $wgContentNamespaces;

		// trim passed query and replace spaces by underscores
		// - this is how MediaWiki stores article titles in database
		$query = urldecode( trim( $wgRequest->getText( 'query' ) ) );
		$query = str_replace( ' ', '_', $query );

		// explode passed query by ':' to get namespace and article title
		$queryParts = explode( ':', $query, 2 );

		if ( count( $queryParts ) == 2 ) {
			$query = $queryParts[1];

			$namespaceName = $queryParts[0];

			// try to get the index by canonical name first
			$namespace = MWNamespace::getCanonicalIndex( strtolower( $namespaceName ) );
			if ( $namespace == null ) {
				// if we failed, try looking through localized namespace names
				$namespace = array_search(
					ucfirst( $namespaceName ),
					$wgContLang->getNamespaces()
				);
				if ( empty( $namespace ) ) {
					// getting here means our "namespace" is not real and can only
					// be a part of the title
					$query = $namespaceName . ':' . $query;
				}
			}
		}

		// list of namespaces to search in
		if ( empty( $namespace ) ) {
			// search only within content namespaces - default behaviour
			$namespaces = $wgContentNamespaces;
		} else {
			// search only within a namespace from query
			$namespaces = $namespace;
		}

		$results = array();

		$dbr = wfGetDB( DB_SLAVE );
		$query = mb_strtolower( $query );

		$res = $dbr->select(
			array( 'querycache', 'page' ),
			array( 'qc_namespace', 'qc_title' ),
			array(
				'qc_title = page_title',
				'qc_namespace = page_namespace',
				'page_is_redirect' => 0,
				'qc_type' => 'Mostlinked',
				'LOWER(qc_title)' . $dbr->buildLike( $query, $dbr->anyString() ),
				'qc_namespace' => $namespaces
			),
			__METHOD__,
			array( 'ORDER BY' => 'qc_value DESC', 'LIMIT' => 10 )
		);

		foreach ( $res as $row ) {
			$results[] = self::formatTitle( $row->qc_namespace, $row->qc_title );
		}

		$res = $dbr->select(
			'page',
			array( 'page_namespace', 'page_title' ),
			array(
				'LOWER(page_title)' . $dbr->buildLike( $query, $dbr->anyString() ),
				'page_is_redirect' => 0,
				'page_namespace' => $namespaces
			),
			__METHOD__,
			array(
				'ORDER BY' => 'page_title ASC',
				'LIMIT' => ( 15 - count( $results ) )
			)
		);

		foreach ( $res as $row ) {
			$results[] = self::formatTitle( $row->page_namespace, $row->page_title );
		}

		$results = array_unique( $results );
		$format = $wgRequest->getText( 'format' );

		if ( $format == 'json' ) {
			$out = json_encode( array(
				'query' => $wgRequest->getText( 'query' ),
				'suggestions' => array_values( $results )
			) );
		} else {
			$out = implode( "\n", $results );
		}

		$ar = new AjaxResponse( $out );
		$ar->setCacheDuration( 60 * 60 ); // cache results for one hour

		// set proper content type to ease development
		if ( $format == 'json' ) {
			$ar->setContentType( 'application/json; charset=utf-8' );
		} else {
			$ar->setContentType( 'text/plain; charset=utf-8' );
		}

		return $ar;
	}

	/**
	 * Returns formatted title based on given namespace and title
	 *
	 * @param int $namespace Page namespace ID
	 * @param string $title Page title
	 * @return string Formatted title (prefixed with localised namespace)
	 */
	public static function formatTitle( $namespace, $title ) {
		global $wgContLang;

		if ( $namespace != NS_MAIN ) {
			$title = $wgContLang->getNsText( $namespace ) . ':' . $title;
		}

		return str_replace( '_', ' ', $title );
	}
}