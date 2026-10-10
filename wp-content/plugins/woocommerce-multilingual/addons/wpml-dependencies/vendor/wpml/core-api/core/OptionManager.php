<?php

namespace WPML\WP;

use WPML\Utilities\AdvisoryLockFactory;

use function WPML\Container\make;
use function WPML\FP\curryN;

class OptionManager {

	private $group_keys_key = 'WPML_Group_Keys';

	const MISSING = '__WPML_OPTION_MANAGER_MISSING__';

	const LOCK_TIMEOUT_SECONDS = 10;

	public function get( $group, $key, $default = false ) {
		$row = get_option( $this->get_item_key( $group, $key ), self::MISSING );
		if ( is_array( $row ) && array_key_exists( 'v', $row ) ) {
			return null === $row['v'] ? $default : $row['v'];
		}

		$data = get_option( $this->get_key( $group ), array() );

		return isset( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	public function set( $group, $key, $value, $autoload = true ) {
		$item_key = $this->get_item_key( $group, $key );

		update_option( $item_key, array( 'v' => $value ), $autoload );

		$this->store_group_key( $item_key );
		$this->store_group_key( $this->get_key( $group ) );

		$this->refresh_legacy_group_row( $group, $key, $value, $autoload );
	}

	private function refresh_legacy_group_row( $group, $key, $value, $autoload ) {
		$group_key = $this->get_key( $group );

		if ( function_exists( 'wp_cache_delete' ) ) {
			wp_cache_delete( $group_key, 'options' );
			wp_cache_delete( 'alloptions', 'options' );
			wp_cache_delete( 'notoptions', 'options' );
		}

		$data         = get_option( $group_key, array() );
		$data         = is_array( $data ) ? $data : array();
		$data[ $key ] = $value;
		update_option( $group_key, $data, $autoload );
	}

	private function get_key( $group ) {
		return 'WPML(' . $group . ')';
	}

	private function get_item_key( $group, $key ) {
		return 'WPML(' . $group . '/' . $key . ')';
	}

	public function getFresh( $group, $key, $default = false ) {
		if ( ! function_exists( 'wp_using_ext_object_cache' ) || ! wp_using_ext_object_cache() ) {
			$this->invalidate_cache( $this->get_item_key( $group, $key ) );
			$this->invalidate_cache( $this->get_key( $group ) );
		}

		return $this->get( $group, $key, $default );
	}

	public function invalidateGroup( $group ) {
		if ( function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache() ) {
			return;
		}

		$names  = [ $this->get_key( $group ) ];
		$prefix = 'WPML(' . $group . '/';

		foreach ( (array) get_option( $this->group_keys_key, [] ) as $name ) {
			if ( 0 === strpos( (string) $name, $prefix ) ) {
				$names[] = $name;
			}
		}
		$alloptions = function_exists( 'wp_cache_get' ) ? wp_cache_get( 'alloptions', 'options' ) : false;
		if ( is_array( $alloptions ) ) {
			foreach ( array_keys( $alloptions ) as $name ) {
				if ( 0 === strpos( (string) $name, $prefix ) ) {
					$names[] = $name;
				}
			}
		}

		foreach ( array_unique( $names ) as $name ) {
			$this->invalidate_cache( $name );
		}
	}

	public function mutate( $group, $key, callable $updater, $autoload = true ) {
		return $this->mutate_locked(
			$this->get_item_key( $group, $key ),
			function () use ( $group, $key, $updater, $autoload ) {
				$new = $updater( $this->getFresh( $group, $key ) );
				$this->set( $group, $key, $new, $autoload );

				return $new;
			}
		);
	}

	private function mutate_locked( $option_name, callable $operation ) {
		$lock   = make( AdvisoryLockFactory::class )->create( 'option_mutate_' . $option_name );
		$locked = $lock->acquire( self::LOCK_TIMEOUT_SECONDS );
		if ( ! $locked ) {
			do_action( 'wpml_option_mutate_lock_not_acquired', $option_name );
		}

		try {
			return $operation();
		} finally {
			if ( $locked ) {
				$lock->release();
			}
		}
	}

	private function invalidate_cache( $option_name ) {
		if ( ! function_exists( 'wp_cache_delete' ) ) {
			return;
		}

		wp_cache_delete( $option_name, 'options' );

		foreach ( [ 'alloptions', 'notoptions' ] as $bucket ) {
			$cached = wp_cache_get( $bucket, 'options' );
			if ( is_array( $cached ) && array_key_exists( $option_name, $cached ) ) {
				unset( $cached[ $option_name ] );
				wp_cache_set( $bucket, $cached, 'options' );
			}
		}
	}

	private function store_group_key( $group_key ) {
		$group_keys = get_option( $this->group_keys_key, array() );
		$group_keys = is_array( $group_keys ) ? $group_keys : array();
		if ( in_array( $group_key, $group_keys, true ) ) {
			return;
		}
		$group_keys[] = $group_key;
		update_option( $this->group_keys_key, array_unique( $group_keys ) );
	}

	public function reset_options( $options ) {
		$options[] = $this->group_keys_key;

		return array_merge( $options, get_option( $this->group_keys_key, array() ) );
	}

	public static function updateWithoutAutoLoad( $group = null, $key = null, $value = null ) {
		$update = function ( $group, $key, $value ) {
			( new OptionManager() )->set( $group, $key, $value, false );
		};

		return call_user_func_array( curryN( 3, $update ), func_get_args() );
	}

	public static function update( $group = null, $key = null, $value = null ) {
		return call_user_func_array( curryN( 3, [ new OptionManager(), 'set' ] ), func_get_args() );
	}

	public static function getOr( $default = null, $group = null, $key = null ) {
		$get = function ( $default, $group, $key ) {
			return ( new OptionManager() )->get( $group, $key, $default );
		};

		return call_user_func_array( curryN( 3, $get ), func_get_args() );
	}
}
