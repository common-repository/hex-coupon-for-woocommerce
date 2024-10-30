/* eslint-disable react/prop-types */

const Button = ({ children, className, buttonIcon, iconPosition, btnStyle, disabled, id, ...props }) => {
	const buttonArray = {
		primary: "btn_bg_1",
		primary_2: "btn_bg_2",
		primary_3: "btn_bg_3",
		secondary: "btn_bg_secondary",
		white: "btn_bg_white",
		danger: "btn_bg_danger",
		success: "btn_bg_success",
		secondary_outline: "btn_outline_secondary",
		outline: "btn_outline_border",
		outline_icon: "btn_outline_icon",
		primary_outline: "btn_outline_1",
		primary_outline_2: "btn_outline_2",
		primary_outline_3: "btn_outline_3",
		danger_outline: "btn_outline_danger",
		success_outline: "btn_outline_success",
	};

	return (

		<button className={`cmn_btn ${buttonArray[btnStyle] ?? ''} ${className ?? ""} ${iconPosition ?? ''}`} disabled={disabled ?? ''} id={id ?? ''} {...props}>
			{children}
			<span className={`icon ${iconPosition ?? ''}`} >{buttonIcon ?? ''}</span>
		</button>
	);
};

export default Button;
