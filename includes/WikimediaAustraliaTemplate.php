<?php

/**
 * @ingroup Skins
 */
class WikimediaAustraliaTemplate extends BaseTemplate {

	/** @var array */
	private $skinConfig;

	/**
	 * Outputs the entire contents of the page.
	 */
	public function execute() {
		$this->html( 'headelement' );
		require dirname( __DIR__ ) . '/template.php';
		$this->printTrail();
		echo '</body></html>';
	}

	/**
	 * Get HTML for the title, with wrappers for the namespace and text parts.
	 * @return string
	 */
	public function getTitle() {
		$title = $this->getSkin()->getRelevantTitle();
		$html = Html::element( 'span', [ 'class' => 'text' ], $title->getText() );
		// Prepend the namespace if it exists and is not the File namespace.
		if ( $title->getNsText() && $title->getNamespace() !== NS_FILE ) {
			$html = Html::element( 'span', [ 'class' => 'ns' ], $title->getNsText() . ':' ) . ' ' . $html;
		}
		return $html;
	}

	/**
	 * @param array $menuItem
	 * @return string
	 */
	public function getMenuItem( array $menuItem ): string {
		$aParams = [];
		// title
		if ( isset( $menuItem['title'] ) ) {
			$aParams['title'] = $menuItem['title'];
		}
		// href
		if ( isset( $menuItem['url'] ) ) {
			$aParams['href'] = $menuItem['url'];
		}
		if ( isset( $menuItem['page'] ) ) {
			$title = Title::newFromText( $menuItem['page'] );
			$aParams['href'] = $title->getLinkURL();
			if ( !isset( $menuItem['title'] ) ) {
				$aParams['title'] = $title->getFullText();
			}
		}
		// contents
		$contents = $menuItem['text'] ?? $aParams['title'] ?? '';
		// icon
		if ( isset( $menuItem['icon'] ) ) {
			$contents = $this->getFeatherIcon( $menuItem['icon'], $contents );
		}
		return Html::rawElement( 'li', [], Html::rawElement( 'a', $aParams, $contents ) );
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function getResourcesUrl( string $path ): string {
		return $this->getSkin()->getConfig()->get( 'StylePath' )
			. '/WikimediaAustralia/resources/'
			. ltrim( $path, '/' );
	}

	/**
	 * Get the nav_urls data.
	 * @return array
	 */
	protected function getNavUrls(): array {
		$out = [];
		foreach ( $this->get( 'nav_urls' ) as $name => $navUrl ) {
			if ( $name === 'mainpage' ) {
				continue;
			}
			if ( isset( $navUrl['href'] ) && !isset( $navUrl['id'] ) ) {
				$navUrl['id'] = 'nu-' . $name;
			}
			$out[ $name ] = $navUrl;
		}
		return $out;
	}

	/**
	 * @return string
	 */
	public function getLogo(): string {
		$params = [
			'src' => $this->getResourcesUrl( 'images/logo-black-small.svg' ),
			'alt' => 'Small black Wikimedia logo.',
			'width' => '48.97327',
			'height' => '49.080795',
		];
		$html = Html::element( 'img', $params );
		return $html;
	}

	/**
	 * @param string $name
	 * @param string $contents
	 * @return string
	 */
	public function getFeatherIcon( string $name, string $contents ) {
		$params = [
			'class' => 'skin-wmau-feathericons-' . $name,
		];
		$span = Html::rawElement( 'span', [], $contents );
		return Html::rawElement( 'abbr', $params, $span );
	}
}
