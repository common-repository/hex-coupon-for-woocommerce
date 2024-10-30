/* eslint-disable react/prop-types */
import { useState } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css'

const DatePickerSelect = ({ isLabel, labelFor, labelClass, labelText }) => {
    const [date, setDate] = useState(new Date());

    return (
        <div className="form__wrap">
            <div className="form__wrap__item">
                {isLabel && <label htmlFor={labelFor ?? ''} className={`form__label ${labelClass ?? ''}`}>{labelText}</label>}
                <DatePicker selected={date} onChange={(date) => setDate(date)} />
            </div>
        </div>
    );
};

export default DatePickerSelect;
