/* eslint-disable react/prop-types */


const BodyCardHeaderTItle = ({ children, className, icon, isFlex }) => {
	return (
		<h4 className={`text-xl font-medium bodyCard__header__title ${className ?? ""} ${isFlex ?? 'flex'}`}>
			{icon && <span style={{ cursor: "pointer" }}>{icon}</span>}
			{children}
		</h4>
	);
};

export default BodyCardHeaderTItle;
