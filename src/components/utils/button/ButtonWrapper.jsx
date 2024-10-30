/* eslint-disable react/prop-types */

const ButtonWrapper = ({ className, children, flexPosition, isFlex, ...props }) => {
	return (
		<div className={`btn_wrapper ${isFlex && 'd-flex'} ${flexPosition ?? ''} ${className ?? ''}`} {...props}>
			{children}
		</div>
	);
};

export default ButtonWrapper;
