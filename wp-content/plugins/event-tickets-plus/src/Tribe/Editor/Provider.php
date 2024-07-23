<?php
/**
 * Register Event Tickets provider
 *
 * @since 4.9
 */

// Tribe__Tickets_Plus__APM
class Tribe__Tickets_Plus__Editor__Provider extends \TEC\Common\Contracts\Service_Provider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9
	 *
	 */
	public function register() {
		// Should load blocks for Posts and Pages.
		$should_load_blocks = $this->should_load_blocks_for_posts_and_pages();

		if (
			! $should_load_blocks
			|| ! class_exists( 'Tribe__Tickets_Plus__Main' )
		) {
			return;
		}

		$this->container->singleton( 'tickets-plus.editor.assets', 'Tribe__Tickets_Plus__Editor__Assets', array( 'register' ) );
		$this->container->singleton( 'tickets-plus.editor.configuration', \Tribe\Tickets\Plus\Editor\Configuration::class, [ 'hook' ] );
		$this->hook();
	}

	/**
	 * Any hooking any class needs happen here.
	 *
	 * In place of delegating the hooking responsibility to the single classes they are all hooked here.
	 *
	 * @since 4.9
	 *
	 */
	protected function hook() {
		tribe( 'tickets-plus.editor.assets' );
		tribe( 'tickets-plus.editor.configuration' );
	}

	/**
	 * Binds and sets up implementations at boot time.
	 *
	 * @since 4.9
	 */
	public function boot() {
	}

	/**
	 * Enable Tickets blocks (Attendee Collection & Info) for Post and Pages.
	 *
	 * @since 5.6.9
	 *
	 * @return bool
	 */
	public function should_load_blocks_for_posts_and_pages(): bool {
		// Get should_load_blocks option.
		$should_load_blocks = tribe( 'editor' )->should_load_blocks();

		// If true, exit.
		if ( $should_load_blocks ) {
			return $should_load_blocks;
		}

		// Check to see if we are on a post type admin page.
		global $pagenow;
		if ( 'post-new.php' !== $pagenow && 'post.php' !== $pagenow ) {
			return $should_load_blocks;
		}

		// If $_GET['post'] is set, check to see if post_type exists on our list of post types.
		$post = tribe_get_request_var( 'post' );
		if ( ! empty( $post ) ) {
			$post_types = (array) \Tribe__Tickets__Main::instance()->post_types();
			$post_type  = get_post_type( $post );

			if ( ! in_array( $post_type, $post_types, true ) ) {
				return $should_load_blocks;
			}
		}

		// Load blocks with Attendee Collection & Info, if it is not an event admin page.
		if ( function_exists( 'tribe_is_event' ) && ! tribe_is_event( $post ) ) {
			return true;
		}

		return $should_load_blocks;
	}
}
