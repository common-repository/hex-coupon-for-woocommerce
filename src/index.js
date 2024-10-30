import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit';
import metadata from './block.json';
import {Icon, store} from '@wordpress/icons';

registerBlockType(metadata, {
	icon: {
		src: (
			<Icon icon={store}/>
		),
		foreground: '#874FB9',
	},
	edit: Edit
});
