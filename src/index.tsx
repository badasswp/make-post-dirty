import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { commentEditLink } from '@wordpress/icons';
import { registerPlugin } from '@wordpress/plugins';
import { store as editorStore } from '@wordpress/editor';
import { Fill, Button, Tooltip } from '@wordpress/components';

import { posts } from './utils/posts';
import { Post } from './utils/interfaces';

/**
 * Make Post Dirty.
 *
 * This component returns a button that is
 * placed in the PinnedItems area. It's sole purpose
 * is for populating the post title and content.
 *
 * @since 1.0.0
 *
 * @return {JSX.Element} MakePostDirty
 */
const MakePostDirty = (): JSX.Element => {
	const { editPost, savePost } = useDispatch( editorStore );
	const { title, content, random, animationEnable, wpVersion } =
		window.makePostDirty;

	// Slot fill name changed in WP 6.6.
	const fillName =
		parseFloat( wpVersion ) >= 6.6
			? 'PinnedItems/core'
			: 'PinnedItems/core/edit-post';

	/**
	 * Populate Post.
	 *
	 * Make post dirty by filling in the
	 * title and content.
	 *
	 * @since 1.0.0
	 *
	 * @param  prop           The object.
	 * @param  prop.attribute The post attribute for e.g. title or content.
	 * @param  prop.value     The value for the attribute.
	 *
	 * @return {Promise<string>} Returns a promise that resolves to string value.
	 */
	const populatePostUsingAnimation = ( {
		attribute,
		value,
	}: Post ): Promise< string > => {
		let limit: number = 0;
		const dirty: string[] = [];

		return new Promise( ( resolve, reject ) => {
			const makeDirty = setInterval( () => {
				dirty[ attribute ] = value.substring( 0, limit );
				editPost( dirty );

				if ( limit === value.length ) {
					clearInterval( makeDirty );
					resolve( value );
				}

				if ( limit > value.length ) {
					reject( sprintf( 'Something went wrong: %s', value ) );
				}
				limit++;
			}, 10 );
		} );
	};

	/**
	 * Click Handler.
	 *
	 * Populate title, then content, use random
	 * data, if set from option settings.
	 *
	 * @since 1.0.0
	 *
	 * @return {Promise<void>}
	 */
	const handleClick = async (): Promise< void > => {
		const index = Math.floor( Math.random() * ( posts?.length || 1 ) );
		const { title: randomTitle, content: randomContent } = posts[
			index
		] || { title, content };

		// If animation is enabled, then run.
		if ( '1' === animationEnable ) {
			await populatePostUsingAnimation( {
				attribute: 'title',
				value: random ? randomTitle : title,
			} );
			await populatePostUsingAnimation( {
				attribute: 'content',
				value: random ? randomContent : content,
			} );

			// Save Post.
			editPost( { status: 'publish' } );
			await savePost();
		}

		// Run this by default.
		editPost( {
			title: random ? randomTitle : title,
			content: random ? randomContent : content,
			status: 'publish',
		} );
		await savePost();
	};

	return (
		<Fill name={ fillName }>
			<Tooltip text={ __( 'Make Post Dirty', 'make-post-dirty' ) }>
				<Button
					onClick={ handleClick }
					icon={ commentEditLink }
					data-testid="make-post-dirty-btn"
				/>
			</Tooltip>
		</Fill>
	);
};

registerPlugin( 'make-post-dirty', {
	icon: null,
	render: MakePostDirty,
} );
