<?php

namespace WCML\Synchronization\Component;

use WCML_Downloadable_Products;

class DownloadableFiles extends SynchronizerForMeta {

	/**
	 * @param \WP_Post          $product
	 * @param int[]             $translationsIds
	 * @param array<int,string> $translationsLanguages
	 */
	public function run( $product, $translationsIds, $translationsLanguages ) {
		$settingsFactory      = wpml_load_core_tm()->settings_factory();
		$postmetaFieldSetting = $settingsFactory->post_meta_setting( '_downloadable_files' );
		$postmetaFieldStatus  = $postmetaFieldSetting->status();
		if ( WPML_IGNORE_CUSTOM_FIELD === $postmetaFieldStatus ) {
			return;
		}

		$this->syncFiles( $product->ID, $translationsIds );
	}

	/**
	 * @param int   $productId
	 * @param int[] $translationsIds
	 */
	public function syncFiles( $productId, $translationsIds ) {
		$generalProductSync = $this->woocommerceWpml->settings[ WCML_Downloadable_Products::SYNC_MODE_SETTING_KEY ];
		$customProductSync  = get_post_meta( $productId, WCML_Downloadable_Products::SYNC_MODE_META, true );

		if (
			$customProductSync === WCML_Downloadable_Products::SYNC_MODE_META_SELF
			|| ( ! $customProductSync && ! $generalProductSync)
		) {
			$this->synchronizeMeta( $productId, $translationsIds, WCML_Downloadable_Products::DOWNLOADABLE_FILES_META );
		} elseif ( ( $customProductSync && $customProductSync === WCML_Downloadable_Products::SYNC_MODE_META_AUTO ) || $generalProductSync ) {
			$this->synchronizeMeta( $productId, $translationsIds, WCML_Downloadable_Products::DOWNLOADABLE_FILES_META );
		}
	}

}
