<?php
/**
 * This is the main template.
 * @file
 */
?>

<header>
	<div class="skin-wmau-container">
		<h1 id="sitename">
			<a href="<?php echo $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ] ?>" title="">
				<span><?php $this->text( 'sitename' ) ?></span>
			</a>
		</h1>
		<nav>
			<ul class="skin-wmau-list-horizontal">
				<?php echo $this->getMenuItem( [ 'page' => 'About us' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'What&rsquo;s on' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Projects & news' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Governance' ] ); ?>
				<?php echo $this->getMenuItem( [ 'page' => 'Contact' ] ); ?>
			</ul>
		</nav>
	</div>
</header>

<?php if ( $this->getSkin()->getUser()->isRegistered() ) { ?>
	<aside class="skin-wmau-action-drawer">
		<ul>
			<li class="skin-wmau-feathericons-tool"><span>Tools</span></li>
			<?php
			$links = array_merge(
				$this->get( 'content_actions' ),
				$this->getNavUrls(),
				$this->get( 'sidebar' )['TOOLBOX'],
				$this->getSkin()->getPersonalToolsForMakeListItem( $this->get( 'personal_urls' ) )
			);
			unset( $links['logout'] );
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
	<h1><?php echo $this->getTitle() ?></h1>

	<?php if ( $this->data[ 'subtitle' ] ) { ?>
		<p class="subtitle">
			<?php echo $this->get( 'subtitle' ) ?>
		</p>
	<?php } ?>

	<?php
	$this->html( 'bodycontent' );
	$this->html( 'catlinks' );
	$this->html( 'dataAfterContent' );
	?>
</article>

<footer>
	<div class="skin-wmau-container">
		<section class="skin-wmau-footer-left">
			<p><?php echo $this->getLogo(); ?></p>
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
