import { useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { registerPlugin } from '@wordpress/plugins';
import { commentEditLink } from '@wordpress/icons';
import { __, sprintf } from '@wordpress/i18n';
import { createSlotFill, Button, Tooltip } from '@wordpress/components';

import { posts } from './utils/posts';

const MakePostDirty = () => {
	const { Fill } = createSlotFill( 'PinnedPlugins' );
	const { editPost, savePost } = useDispatch( editorStore );
	const { title, content, random, wpVersion } = window.makePostDirty;

	// Slot fill name changed in WP 6.6.
	const fillName =
		parseFloat( wpVersion ) >= 6.6
			? 'PinnedItems/core'
			: 'PinnedItems/core/edit-post';

	const populatePost = ( { attribute, value } ) => {
		let limit: number = 0;
		const dirty = [];

		return new Promise( ( resolve, reject ) => {
			const makeDirty = setInterval( () => {
				dirty[ attribute ] = value.substring( 0, limit );
				editPost( dirty );

				if ( limit === value.length ) {
					clearInterval( makeDirty );
					savePost();
					resolve( value );
				}

				if ( limit > value.length ) {
					reject( sprintf( 'Something went wrong: %s', value ) );
				}
				limit++;
			}, 10 );
		} );
	};

	const handleClick = async () => {
		const index = Math.floor( Math.random() * 5 );
		const { title: randomTitle, content: randomContent } = posts[ index ];

		await populatePost( {
			attribute: 'title',
			value: random ? randomTitle : title,
		} );
		await populatePost( {
			attribute: 'content',
			value: random ? randomContent : content,
		} );
	};

	return (
		<Fill name={ fillName }>
			<Tooltip text={ __( 'Make Post Dirty', 'make-post-dirty' ) }>
				<Button onClick={ handleClick } icon={ commentEditLink } />
			</Tooltip>
		</Fill>
	);
};

registerPlugin( 'make-post-dirty', {
	icon: null,
	render: MakePostDirty,
} );
