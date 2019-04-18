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
	 */
	public function execute() {
		$user = $this->getUser();

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
			array( 'result' => $output )
		);

		return true;
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		return array(
			'get' => array(
				ApiBase::PARAM_TYPE => array( 'image', 'suggestions' ),
				ApiBase::PARAM_REQUIRED => true
			),
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=linksuggest&get=suggestions&query=Ashley' => 'apihelp-linksuggest-example-1',
			'action=linksuggest&get=image&query=Whatever.jpg' => 'apihelp-linksuggest-example-2'
		);
	}
}