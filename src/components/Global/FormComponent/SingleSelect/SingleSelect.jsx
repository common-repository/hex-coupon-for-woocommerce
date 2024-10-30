import React from 'react';
import Select from 'react-select';

const SingleSelect = (props) => {
	const {options, selectLabel, handleChangeSelect} = props;

	return (
		<>
			<div className="single__select">
				<label className='single__input__label'>{selectLabel}</label>
				<Select defaultValue={{label:"This Week"}} options={options} onChange={(e) => handleChangeSelect(e.value)} />
			</div>
		</>
	);
};

export default SingleSelect;
