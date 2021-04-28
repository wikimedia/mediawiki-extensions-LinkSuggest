<?php
/**
 * LinkSuggest API module
 *
 * @file
 * @ingroup API
 * @date 27 September 2015
 * @see https://www.mediawiki.org/wiki/API:Extensions#ApiSampleApiExtension.php
 */
class ApiLinkSuggest extends ApiBase {

	/**
	 * Main entry point.
	 *
	 * @return bool true
	 */
	public function execute() {
		// Get the request parameters
		$params = $this->extractRequestParams();
		$this->requireAtLeastOneParameter( $params, 'query' );

		if ( $params['get'] === 'image' ) {
			$output = LinkSuggest::getImage( $params['query'] );
		} else {
			$output = LinkSuggest::get( $params['query'] );
		}

		// Top level
		$this->getResult()->addValue( null, $this->getModuleName(),
			[ 'result' => $output ]
		);

		return true;
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'get' => [
				ApiBase::PARAM_TYPE => [ 'image', 'suggestions' ],
				ApiBase::PARAM_REQUIRED => true
			],
			'query' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			]
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 *
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=linksuggest&get=suggestions&query=Ashley' => 'apihelp-linksuggest-example-1',
			'action=linksuggest&get=image&query=Whatever.jpg' => 'apihelp-linksuggest-example-2'
		];
	}
}
