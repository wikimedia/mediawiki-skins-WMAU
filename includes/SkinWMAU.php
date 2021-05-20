<?php

use MediaWiki\MediaWikiServices;

/**
 * @ingroup Skins
 */
class SkinWMAU extends SkinMustache {

	/**
	 * Initialise the page.
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		$version = $out->getConfig()->get( 'Version' );
		if ( version_compare( $version, '1.36', '<' ) ) {
			// @TODO Remove after support for 1.35 is dropped. This is replaced by the `responsive` option in skin.json.
			$out->addMeta( 'viewport', 'width=device-width, initial-scale=1.0' );
		}
	}

	/**
	 * Subclasses may extend this method to add additional
	 * template data.
	 *
	 * The data keys should be valid English words. Compound words should
	 * be hypenated except if they are normally written as one word. Each
	 * key should be prefixed with a type hint, this may be enforced by the
	 * class PHPUnit test.
	 *
	 * Plain strings are prefixed with 'html-', plain arrays with 'array-'
	 * and complex array data with 'data-'. 'is-' and 'has-' prefixes can
	 * be used for boolean variables.
	 *
	 * @return array Data for a mustache template
	 */
	public function getTemplateData() {
		$out = parent::getTemplateData();
		$services = MediaWikiServices::getInstance();
		$subjectPage = Title::castFromLinkTarget( $services
			->getNamespaceInfo()
			->getSubjectPage( $this->getTitle() ) );

		$out[ 'page-title' ] = [
			[
				'url' => $this->getTitle()->getLocalURL(),
				'text' => $this->getTitle()->getText(),
				'ns' => $this->getTitle()->getNamespace() !== NS_MAIN
					? $this->getTitle()->getNsText() . $this->msg( 'colon-separator' )->text()
					: false,
			]
		];
		$out[ 'data-subject-page' ] = $this->getTitle()->isTalkPage()
			? [
				'class' => 'skin-wmau-subject-link',
				'icon' => 'arrow-left',
				'text' => $this->msg( 'wmau-subject-link' )->text(),
				'url' => $subjectPage->getLocalURL(),
			]
			: false;
		$talkPage = $this->getTitle()->getTalkPageIfDefined();
		if ( !$this->getTitle()->isTalkPage() && $talkPage ) {
			$out[ 'data-talk-page' ] = [
				'text' => $this->msg( 'wmau-talk-link' )->text(),
				'icon' => 'message-square',
				'url' => $talkPage->getLocalURL(),
				'class' => 'skin-wmau-talk-link',
			];
		}
		$out[ 'is-talk-page' ] = $this->getTitle()->isTalkPage();
		$out[ 'url-mainpage' ] = Title::newMainPage()->getLocalUrl();
		$out[ 'array-header-menu' ] = [
			[ 'li' => $this->getMenuItem( [ 'page' => 'About us' ] ) ],
			[ 'li' => $this->getMenuItem( [ 'page' => "What's on", 'text' => 'What&rsquo;s on' ] ) ],
			[ 'li' => $this->getMenuItem( [ 'page' => 'Projects & news' ] ) ],
			[ 'li' => $this->getMenuItem( [ 'page' => 'Governance' ] ) ],
			[ 'li' => $this->getMenuItem( [ 'page' => 'Contact' ] ) ],
			[ 'li' => $this->getMenuItem( [
				'page' => 'Special:Search',
				'icon' => 'search',
				'class' => 'skin-wmau-search',
				] ) ],
		];
		$out['array-footer-menu-1'] = [
			[ 'li' => $this->getMenuItem( [
				'icon' => 'facebook',
				'title' => 'Facebook',
				'url' => 'https://www.facebook.com/wikimedia.au',
				] ) ],
			[ 'li' => $this->getMenuItem( [
				'icon' => 'twitter',
				'title' => 'Twitter',
				'url' => 'https://twitter.com/wma_au',
				] ) ],
		];
		$logInOut = $this->getUser()->isRegistered()
			? $this->getMenuItem( [ 'page' => 'Special:UserLogout', 'text' => 'Log out' ] )
			: $this->getMenuItem( [ 'page' => 'Special:UserLogin', 'text' => 'Log in' ] );
		$out['array-footer-menu-2'] = [
			[ 'li' => $this->getMenuItem( [ 'page' => 'Donate' ] ) ],
			[ 'li' => $this->getMenuItem( [ 'page' => 'Newsletter' ] ) ],
			[ 'li' => $logInOut ],
		];
		$out[ 'is-user-registered' ] = $this->getUser()->isRegistered();
		$out[ 'array-tools' ] = $this->getToolDrawerLinks();
		$out[ 'data-logos' ] = $this->getLogosData();
		foreach ( $this->options['messages'] ?? [] as $message ) {
			$out[ 'msg-' . $message ] = $this->msg( $message )->text();
		}
		return $out;
	}

	/**
	 * @return array of logo data localised to the current language variant
	 */
	private function getLogosData(): array {
		$logoData = ResourceLoaderSkinModule::getAvailableLogos( $this->getConfig() );
		// check if the logo supports variants
		$variantsLogos = $logoData['variants'] ?? null;
		if ( $variantsLogos ) {
			$preferred = $this->getOutput()->getTitle()
				->getPageViewLanguage()->getCode();
			$variantOverrides = $variantsLogos[$preferred] ?? null;
			// Overrides the logo
			if ( $variantOverrides ) {
				foreach ( $variantOverrides as $key => $val ) {
					$logoData[$key] = $val;
				}
			}
		}
		return $logoData;
	}

	/**
	 * @return array
	 */
	private function getToolDrawerLinks() {
		$links = [];
		foreach ( $this->buildContentNavigationUrls() as $x => $contentNavigationUrls ) {
			if ( $x === 'namespaces' ) {
				// Talk and article links are handled separately.
				continue;
			}
			$links = array_merge( $links, $contentNavigationUrls );
		}
		$links = array_merge(
			$links,
			$this->buildNavUrls(),
			$this->getPersonalToolsForMakeListItem( $this->buildPersonalUrls() )
		);
		unset(
			$links['logout'],
			$links['mainpage'],
			$links['specialpages'],
			$links['upload']
		);
		$out = [];
		foreach ( $links as $url => $urlDetails ) {
			if ( is_array( $urlDetails ) ) {
				$out[] = [ 'li' => $this->makeListItem( $url, $urlDetails ) ];
			}
		}
		return $out;
	}

	/**
	 * @param array $menuItem
	 * @return string
	 */
	public function getMenuItem( array $menuItem ): string {
		$aParams = [];
		// title
		if ( isset( $menuItem[ 'title' ] ) ) {
			$aParams[ 'title' ] = $menuItem[ 'title' ];
		}
		// href
		if ( isset( $menuItem[ 'url' ] ) ) {
			$aParams[ 'href' ] = $menuItem[ 'url' ];
		}
		if ( isset( $menuItem[ 'page' ] ) ) {
			$title = Title::newFromText( $menuItem[ 'page' ] );
			$aParams[ 'href' ] = $title->getLinkURL();
			if ( !isset( $menuItem[ 'title' ] ) ) {
				$aParams[ 'title' ] = $title->getFullText();
			}
		}
		// contents
		$contents = $menuItem[ 'text' ] ?? $aParams[ 'title' ] ?? '';
		// icon
		if ( isset( $menuItem[ 'icon' ] ) ) {
			$contents = $this->getFeatherIcon( $menuItem[ 'icon' ], $contents );
		}
		$liParams = [
			'class' => $menuItem[ 'class' ] ?? '',
		];
		return Html::rawElement( 'li', $liParams, Html::rawElement( 'a', $aParams, $contents ) );
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
