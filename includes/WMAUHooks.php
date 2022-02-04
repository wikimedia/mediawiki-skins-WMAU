<?php

use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;

class WMAUHooks implements SkinTemplateNavigation__UniversalHook, SidebarBeforeOutputHook {

	// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

	/**
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( $sktemplate->getSkinName() !== 'WMAU' ) {
			return;
		}
		// Remove log out link, which has been moved to the footer.
		unset( $links['user-menu']['logout'] );
	}

	/**
	 * @inheritDoc
	 */
	public function onSidebarBeforeOutput( $skin, &$sidebar ): void {
		if ( $skin->getSkinName() !== 'WMAU' ) {
			return;
		}
		unset(
			$sidebar['TOOLBOX']['specialpages'],
			$sidebar['TOOLBOX']['upload']
		);
	}
}
