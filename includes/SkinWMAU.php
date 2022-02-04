<?php

use MediaWiki\MediaWikiServices;

/**
 * @ingroup Skins
 */
class SkinWMAU extends SkinMustache {

	/** @var mixed[] Skin config read from the wmau-config.json message. */
	private $skinConfig;

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

		// Only link the page title if we're not currently viewing the page.
		$out[ 'page-title-url' ] = Action::getActionName( $this->getContext() ) === 'view'
			? false
			: $this->getTitle()->getLocalURL();
		$out[ 'page-title-text' ] = $this->getTitle()->getText();
		$out[ 'page-title-ns' ] = $this->getTitle()->getNamespace() !== NS_MAIN
			? $this->getTitle()->getNsText() . $this->msg( 'colon-separator' )->text()
			: false;
		// @HACK There's no way to get the display title, so instead we check that it's different.
		$displayTitle = $this->getOutput()->getDisplayTitle();
		$out[ 'page-title-displaytitle' ] = $displayTitle !== $this->getTitle()->getPrefixedText()
			? $displayTitle
			: false;

		$out[ 'data-subject-page' ] = $this->getTitle()->isTalkPage()
			? [
				'class' => 'skin-wmau-subject-link',
				'icon' => 'arrow-left',
				'text' => $this->msg( 'wmau-subject-link' )->text(),
				'url' => $subjectPage->getLocalURL(),
			]
			: false;
		$talkPage = $this->getTitle()->getTalkPageIfDefined();
		if ( !$this->getTitle()->isTalkPage() && $talkPage && !$this->getTitle()->isMainPage() ) {
			$out[ 'data-talk-page' ] = [
				'text' => $this->msg( 'wmau-talk-link' )->text(),
				'icon' => 'message-square',
				'url' => $talkPage->getLocalURL(),
				'class' => 'skin-wmau-talk-link',
			];
		}
		$out[ 'is-talk-page' ] = $this->getTitle()->isTalkPage();
		$out[ 'url-mainpage' ] = Title::newMainPage()->getLocalUrl();
		$out[ 'array-header-menu' ] = [];
		foreach ( $this->getWmauConfig()['header_menu'] ?? [] as $menuConfig ) {
			$out['array-header-menu'][] = $this->getMenuItem( $menuConfig );
		}
		$out['array-header-menu'][] = $this->getMenuItem( [
			'page' => 'Special:Search',
			'icon' => 'search',
			'class' => 'skin-wmau-search',
		] );
		$out['array-footer-menu'] = $this->getMenu( 'footer_menu' );
		$logInOut = $this->getUser()->isRegistered()
			? $this->getMenuItem( [ 'page' => 'Special:UserLogout', 'text' => 'Log out' ] )
			: $this->getMenuItem( [ 'page' => 'Special:UserLogin', 'text' => 'Log in' ] );
		$out['array-footer-menu'][] = $logInOut;
		$out['html-footer-blurb'] = $this->msg( 'wmau-footer-blurb' )->parse();
		$out[ 'is-user-registered' ] = $this->getUser()->isRegistered();
		$out[ 'data-logos' ] = $this->getLogosData();
		$out[ 'html-retrievedfrom' ] = $this->printSource();
		foreach ( $this->options['messages'] ?? [] as $message ) {
			$out[ 'msg-' . $message ] = $this->msg( $message )->text();
		}
		return $out;
	}

	/**
	 * @param string $name
	 * @return string[] HTML of the menu list items.
	 */
	private function getMenu( $name ): array {
		$out = [];
		foreach ( $this->getWmauConfig()[ $name ] ?? [] as $menuConfig ) {
			$out[] = $this->getMenuItem( $menuConfig );
		}
		return $out;
	}

	/**
	 * @return mixed[]
	 */
	private function getWmauConfig(): array {
		if ( !$this->skinConfig ) {
			$this->skinConfig = json_decode( $this->msg( 'wmau-config.json' )->text(), true );
			if ( !is_array( $this->skinConfig ) ) {
				$this->skinConfig = [];
			}
		}
		return $this->skinConfig;
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
	 * @param array $menuItem
	 * @return string
	 */
	public function getMenuItem( array $menuItem ): string {
		$menuItem = array_filter( $menuItem );
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
			if ( $title ) {
				$aParams[ 'href' ] = $title->getLinkURL();
				if ( !isset( $menuItem[ 'title' ] ) ) {
					$aParams[ 'title' ] = $title->getFullText();
				}
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
