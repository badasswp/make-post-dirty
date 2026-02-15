import { ReactNode } from 'react';
import { render } from '@testing-library/react';

import { useDispatch } from '@wordpress/data';

import MakePostDirty from '../../../src';

const editPost = jest.fn();
const savePost = jest.fn();

jest.mock( '@wordpress/editor', () => ( {
	store: 'core/editor',
} ) );

jest.mock( '@wordpress/data', () => ( {
	useDispatch: jest.fn(),
} ) );

jest.mock( '@wordpress/components', () => ( {
	Fill: ( { name, children }: { name: string; children: ReactNode } ) => (
		<div
			className={
				'PinnedItems/core' === name
					? 'interface-pinned-items'
					: 'interface-pinned'
			}
		>
			{ children }
		</div>
	),
	Button: ( { children, ...props }: any ) => (
		<button { ...props }>{ children }</button>
	),
	Tooltip: ( { children }: any ) => <div>{ children }</div>,
} ) );

describe( 'MakePostDirty', () => {
	beforeEach( () => {
		jest.clearAllMocks();

		( useDispatch as jest.Mock ).mockReturnValue( {
			editPost,
			savePost,
		} );

		( window as any ).makePostDirty = {
			title: 'Default Title',
			content: 'Default Content',
			random: false,
			animationEnable: '1',
			animationSpeed: '10',
			wpVersion: '6.6',
		};
	} );

	afterEach( () => {
		jest.clearAllMocks();
	} );

	it( 'matches snapshot', () => {
		const { container } = render( <MakePostDirty /> );

		expect( container ).toMatchSnapshot();
	} );

	it( 'renders make post dirty button in pinned item area', () => {
		const { getByTestId } = render( <MakePostDirty /> );

		// Test if Make Post Dirty button is displayed.
		const button = getByTestId( 'make-post-dirty-btn' );
		expect( button ).toBeVisible();
	} );
} );
