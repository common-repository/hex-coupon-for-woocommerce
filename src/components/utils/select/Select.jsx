/* eslint-disable react/prop-types */
// import '../../../assets/sass/component/select.scss'

const Select = ({ name, options, className, mb = 0, itemClass, labelFor, labelClass, labelText, isLabel }) => {
    const style = mb !== null ? { marginBottom: `${mb}px` } : {};

    return (
        <div className={`selectWrap ${className ?? ''}`} >
            {isLabel && <label htmlFor={labelFor ?? ''} className={`form__label ${labelClass ?? ''}`}>{labelText}</label>}
            <div className={`selectWrap__item${itemClass ?? ''}`}>
                <select name={name} className="selectField" style={style}>
                    {options.map(({ val, text }, index) => (
                        <option key={index} value={val}>{text}</option>
                    ))}
                </select>
            </div>
        </div>
    )
};

export default Select;