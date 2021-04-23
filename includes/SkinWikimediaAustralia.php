<?php

/**
 * @ingroup Skins
 */
class SkinWikimediaAustralia extends SkinTemplate {

	/** @var string */
	public $skinname = 'WikimediaAustralia';

	/** @var string */
	public $template = WikimediaAustraliaTemplate::class;

	/**
	 * Initialise the page.
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1.0' );
		$out->addModuleStyles( 'skins.wikimediaaustralia' );
	}
}
