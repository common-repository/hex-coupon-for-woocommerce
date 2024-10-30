import {
	useBlockProps,
} from '@wordpress/block-editor';
import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

let Edit;

Edit = ({ attributes, setAttributes }) => {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<div className={ 'store-credit-fields' }>
				<CheckboxControl label={ __( "Use Store Credit","hex-coupon-for-woocommerce" ) } checked="checked"/>
			</div>
		</div>
	);
};

export { Edit };
