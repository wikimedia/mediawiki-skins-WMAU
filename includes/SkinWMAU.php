<?php

/**
 * @ingroup Skins
 */
class SkinWMAU extends SkinTemplate {

	/** @var string */
	public $skinname = 'WMAU';

	/** @var string */
	public $template = WMAUTemplate::class;

	/**
	 * Initialise the page.
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1.0' );
		$out->addModuleStyles( [ 'skins.wmau', 'skins.wmau.images' ] );
		$out->addModules( [ 'skins.wmau.js' ] );
	}
}
