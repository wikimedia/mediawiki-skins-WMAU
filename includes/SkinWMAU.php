<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader\SkinModule;

/**
 * @ingroup Skins
 */
class SkinWMAU extends SkinMustache {

	/** @var mixed[] Skin config read from the wmau-config.json message. */
	private $skinConfig;

	/** @var RepoGroup */
	private $repoGroup;

	/** @var WANObjectCache */
	private $cache;

	/**
	 * @inheritDoc
	 */
	public function __construct( $options = null ) {
		parent::__construct( $options );
		$services = MediaWikiServices::getInstance();
		$this->repoGroup = $services->getRepoGroup();
		$this->cache = $services->getMainWANObjectCache();
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

		// Only link the page title if we're not currently viewing the page.
		$isView = Action::getActionName( $this->getContext() ) === 'view';
		$diff = $this->getContext()->getRequest()->getVal( 'diff' );
		$oldid = $this->getContext()->getRequest()->getVal( 'oldid' );
		$out['page-title-url'] = $isView && !$diff && !$oldid
			? false
			: $this->getTitle()->getLocalURL();
		$out['page-title-text'] = $this->getTitle()->getText();
		$out['page-title-ns'] = $this->getTitle()->getNamespace() !== NS_MAIN
			? str_replace( '_', ' ', $this->getTitle()->getNsText() ) . $this->msg( 'colon-separator' )->text()
			: false;
		// @HACK There's no way to get the display title, so instead we check that it's different.
		$displayTitle = $this->getOutput()->getDisplayTitle();
		$out['page-title-displaytitle'] = $displayTitle !== $this->getTitle()->getPrefixedText()
			? $displayTitle
			: false;

		$out['data-subject-page'] = $this->getTitle()->isTalkPage()
			? [
				'class' => 'skin-wmau-subject-link',
				'icon' => 'arrow-left',
				'text' => $this->msg( 'wmau-subject-link' )->text(),
				'url' => $subjectPage->getLocalURL(),
			]
			: false;
		$talkPage = $this->getTitle()->getTalkPageIfDefined();
		if ( !$this->getTitle()->isTalkPage() && $talkPage && !$this->getTitle()->isMainPage() ) {
			$out['data-talk-page'] = [
				'text' => $this->msg( 'wmau-talk-link' )->text(),
				'icon' => 'message-square',
				'url' => $talkPage->getLocalURL(),
				'class' => 'skin-wmau-talk-link',
			];
		}
		$out['is-talk-page'] = $this->getTitle()->isTalkPage();
		$out['url-mainpage'] = Title::newMainPage()->getLocalURL();
		$out['array-header-menu'] = [];
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
			? $this->getMenuItem( [ 'page' => 'Special:UserLogout', 'text' => $this->msg( 'logout' )->plain() ] )
			: $this->getMenuItem( [ 'page' => 'Special:UserLogin', 'text' => $this->msg( 'login' )->plain() ] );
		$out['array-footer-menu'][] = $logInOut;
		$out['html-footer-blurb'] = $this->msg( 'wmau-footer-blurb' )->parse();
		$out['is-user-registered'] = $this->getUser()->isRegistered();
		$out['data-logos'] = $this->getLogosData();
		$out['html-retrievedfrom'] = $this->printSource();
		foreach ( $this->options['messages'] ?? [] as $message ) {
			$out['msg-' . $message] = $this->msg( $message )->text();
		}
		$out['html-recentchanges'] = $services->getLinkRenderer()->makeKnownLink(
			SpecialPage::getTitleFor( 'Recentchanges' ),
			$this->msg( 'recentchanges' ),
			Linker::tooltipAndAccesskeyAttribs( 'n-recentchanges' )
		);
		return $out;
	}

	/**
	 * @param string $name
	 * @return string[] HTML of the menu list items.
	 */
	private function getMenu( $name ): array {
		$out = [];
		foreach ( $this->getWmauConfig()[$name] ?? [] as $menuConfig ) {
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
		$logoData = SkinModule::getAvailableLogos( $this->getConfig() );
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
		if ( isset( $menuItem['title'] ) ) {
			$aParams['title'] = $menuItem['title'];
		}
		// href
		if ( isset( $menuItem['url'] ) ) {
			$aParams['href'] = $menuItem['url'];
		}
		if ( isset( $menuItem['page'] ) ) {
			$title = Title::newFromText( $menuItem['page'] );
			if ( $title ) {
				$aParams['href'] = $title->getLinkURL();
				if ( !isset( $menuItem['title'] ) ) {
					$aParams['title'] = $title->getFullText();
				}
			}
		}
		// contents
		$contents = $menuItem['text'] ?? $aParams['title'] ?? '';
		// icon
		if ( isset( $menuItem['icon'] ) ) {
			$iconUrl = $this->getWikiFileIcon( $menuItem );
			if ( $iconUrl ) {
				$contents = Html::element( 'img', [ 'src' => $iconUrl ] );
			} else {
				$contents = $this->getFeatherIcon( $menuItem['icon'], $contents );
			}
		}
		$liParams = [
			'class' => $menuItem['class'] ?? '',
		];
		return Html::rawElement( 'li', $liParams, Html::rawElement( 'a', $aParams, $contents ) );
	}

	/**
	 * @param array $menuItem The menu item details array.
	 * @return string|null The URL to the icon thumbnail, or null if the file could not be found.
	 */
	private function getWikiFileIcon( array $menuItem ): ?string {
		if ( !isset( $menuItem['icon'] ) ) {
			return null;
		}
		$repoGroup = $this->repoGroup;
		$filename = $menuItem['icon'];
		$iconWidth = $menuItem['icon_width'] ?? 24;
		return $this->cache->getWithSetCallback(
			$this->cache->makeKey( 'skin-WMAU-icon', $filename, $iconWidth ),
			WANObjectCache::TTL_MONTH,
			static function () use ( $repoGroup, $filename, $iconWidth ) {
				$iconTitle = Title::newFromText( $filename, NS_FILE );
				$iconFile = $repoGroup->findFile( $iconTitle );
				if ( !$iconFile ) {
					return null;
				}
				return $iconFile->transform( [ 'width' => $iconWidth ] )->getUrl();
			}
		);
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
