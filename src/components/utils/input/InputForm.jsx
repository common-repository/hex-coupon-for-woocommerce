/* eslint-disable react/prop-types */

const Input = ({ labelText, labelClass, labelFor, inputId, inputType, inputClass, placeholderText, inputValue = '', onChange }) => {
    return (
        <div className="form__wrap">
            <div className="form__wrap__item">
                <label htmlFor={labelFor ?? ''} className={`form__label ${labelClass ?? ''}`}>{labelText}</label>
                <input type={inputType ?? 'text'} className={`form__input ${inputClass ?? ''}`} id={inputId ?? ''} placeholder={placeholderText} {...(inputValue ? { value: inputValue } : {})}
					   onChange={onChange} />
            </div>
        </div>
    );
};

export default Input;

