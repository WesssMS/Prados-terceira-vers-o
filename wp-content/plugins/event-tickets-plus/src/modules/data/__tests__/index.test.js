/**
 * Internal dependencies
 */
import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';

const { dispatch } = store;
const { TICKETS_PLUS } = plugins.constants;

jest.mock( '@moderntribe/common/data', () => {
	const original = jest.requireActual( '@moderntribe/common/data' );
	return {
		__esModule: true,
		...original,
		plugins: {
			...original.plugins,
			actions: {
				...original.plugins.actions,
				addPlugin: jest.fn( () => {} ),
			},
		},
	};
} );

jest.mock( '@moderntribe/common/store', () => {
	const original = jest.requireActual( '@moderntribe/common/store' );
	return {
		__esModule: true,
		...original,
		store: {
			...original.store,
			dispatch: jest.fn( () => {} ),
		},
	};
} );

describe( 'Data', () => {
	it( 'should add tickets plus plugin to store', () => {
		require( '../index.js' );
		expect( dispatch ).toHaveBeenCalledTimes( 1 );
		expect( plugins.actions.addPlugin ).toHaveBeenCalledTimes( 1 );
		expect( plugins.actions.addPlugin ).toHaveBeenCalledWith( TICKETS_PLUS );
	} );
} );
