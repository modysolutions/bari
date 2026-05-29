import './editor.scss';

/**
 * editor.js
 *
 * Scripts that run exclusively inside the Gutenberg block editor.
 * Use this file to:
 *  - Modify existing block behaviours (filters / transforms)
 *  - Register block variations
 *  - Add custom sidebar panels
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/filters/block-filters/
 */

// ─── Example: restrict a block to a specific alignment ───────────────────────
// import { addFilter } from '@wordpress/hooks';
// addFilter(
//     'blocks.registerBlockType',
//     'bari/restrict-cover-alignment',
//     ( settings, name ) => {
//         if ( name !== 'core/cover' ) return settings;
//         return { ...settings, supports: { ...settings.supports, align: [ 'full' ] } };
//     }
// );
