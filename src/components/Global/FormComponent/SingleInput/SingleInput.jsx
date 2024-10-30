import React from 'react';

const SingleInput = (props) => {
    const {inputLabel, labelId, inputId, inputType, inputClass, inputPlaceholder, inputSmall} = props;

    return (
        <>
            <div className="single__input">
                <label className='single__input__label' htmlFor={labelId}>{inputLabel}</label>
                <div className="single__input__item">
                    <input type={inputType} className={inputClass} placeholder={inputPlaceholder} id={inputId} />
                    <small className='single__input__small'>{inputSmall}</small>
                </div>
            </div>
        </>
    );
};

export default SingleInput;