/* eslint-disable react/prop-types */
// import '../../../assets/sass/component/form_input.scss'

const Textarea = ({ infoText, labelText, labelClass, labelFor, inputId, inputType, inputClass, placeholderText, inputValue = '', ...restProps }) => {
    return (
        <div className="form__wrap">
            <div className="form__wrap__item">
                <label htmlFor={labelFor ?? ''} className={`form__label ${labelClass ?? ''}`}>{labelText}</label>
                <textarea type={inputType ?? 'text'} className={`form__input ${inputClass ?? ''}`} id={inputId ?? ''} placeholder={placeholderText ?? ''} {...(inputValue ? { value: inputValue } : {})} {...restProps}>
                </textarea>
                {infoText && <small className="infoText">{infoText}</small>}
            </div>
        </div>
    );
};

export default Textarea;