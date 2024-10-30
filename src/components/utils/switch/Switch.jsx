const Switch = ({ className, isSwitchText, switchText, switchPosition = "left", isChecked, onSwitchChange, onClick, ...restProps }) => {
	const handleSwitchChange = (event) => {
		if (onSwitchChange) {
			onSwitchChange(!isChecked);
		}
	};

	return (
		<div className={`switchWrap ${className ?? ''}`}>
			<label className={`switchWrap__label ${switchPosition ?? ''}`}>
				<div className="switchWrap__main">
					<input
						type="checkbox"
						checked={isChecked}
						onChange={handleSwitchChange}
						onClick={onClick}
						{...restProps}
					/>
					<div className="slideSwitch rounded"></div>
				</div>
				{isSwitchText && <span className="text-sm font-medium text-gray-900 dark:text-gray-300">{switchText}</span>}
			</label>
		</div>
	);
};

export default Switch;
