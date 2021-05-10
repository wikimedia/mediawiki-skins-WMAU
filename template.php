<?php
/**
 * This is the main template.
 * @file
 */
?>

<header>
	<div class="skin-wmau-container">
		<h1 id="sitename">
			<a href="<?php echo $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ] ?>"
				class="skin-wmau-image-logo-horizontal">
				<span><?php $this->text( 'sitename' ) ?></span>
			</a>
		</h1>
		<nav>
			<ul class="skin-wmau-list-horizontal">
				<?php echo $this->getMenuItem( [ 'page' => 'About us' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => "What's on", 'text' => 'What&rsquo;s on' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Projects & news' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Governance' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Contact' ] ); ?>
				<?php echo $this->getMenuItem( [
					'page' => 'Special:Search', 'icon' => 'search', 'class' => 'skin-wmau-search',
				] ); ?>
			</ul>
			<form action="<?php echo $this->get( 'wgScript' ) ?>" id="searchform">
				<?php
				echo Html::hidden( 'title', $this->get( 'searchtitle' ) );
				echo $this->makeSearchInput( [ 'id' => 'searchInput', 'size' => 50 ] );
				echo $this->makeSearchButton( 'go', [ 'id' => 'searchButton', 'class' => 'searchButton' ] );
				?>
			</form>
		</nav>
	</div>
</header>

<?php if ( $this->getSkin()->getUser()->isRegistered() ) { ?>
	<aside class="skin-wmau-action-drawer">
		<ul>
			<li class="skin-wmau-feathericons-tool"><span>Tools</span></li>
			<?php
			// @TODO Build this whole section better than this.
			$links = array_merge(
				$this->get( 'content_actions' ),
				$this->getNavUrls(),
				$this->get( 'sidebar' )['TOOLBOX'],
				$this->getSkin()->getPersonalToolsForMakeListItem( $this->get( 'personal_urls' ) )
			);

			unset(
				$links['nstab-main'],
				$links['talk'],
				$links['logout']
			);
			foreach ( $links as $url => $urlDetails ) {
				if ( is_array( $urlDetails ) ) {
					echo $this->makeListItem( $url, $urlDetails );
				}
			}
			?>
		</ul>
	</aside>
<?php } ?>

<article class="mw-body skin-wmau-container">
	<?php
	echo $this->articleHeader();
	$this->html( 'bodycontent' );
	$this->html( 'dataAfterContent' );
	echo $this->articleFooter();
	?>
</article>

<footer>
	<div class="skin-wmau-container">
		<section class="skin-wmau-footer-left">
			<p class="skin-wmau-image-logo-black-small"></p>
			<p class="skin-wmau-contact">
				<strong>Wikimedia Australia, Inc.</strong><br>
				<a href="mailto:contact@wikimedia.org.au">contact@wikimedia.org.au</a>
			</p>
		</section>
		<section class="skin-wmau-footer-right">
			<ul class="skin-wmau-list-horizontal">
				<?php echo $this->getMenuItem( [
					'icon' => 'facebook',
					'title' => 'Facebook',
					'url' => 'https://www.facebook.com/wikimedia.au',
					] ); ?>
				<?php echo $this->getMenuItem( [
					'icon' => 'twitter',
					'title' => 'Twitter',
					'url' => 'https://twitter.com/wma_au',
				] ); ?>
			</ul>
			<ul class="skin-wmau-list-horizontal">
				<?php echo $this->getMenuItem( [ 'page' => 'Donate' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Newsletter' ] ); ?>
				<?php
				if ( $this->getSkin()->getUser()->isRegistered() ) {
					echo $this->getMenuItem( [ 'page' => 'Special:UserLogout', 'text' => 'Log out' ] );
				} else {
					echo $this->getMenuItem( [ 'page' => 'Special:UserLogin', 'text' => 'Log in' ] );
				}
				?>
			</ul>
		</section>
	</div>
</footer>
