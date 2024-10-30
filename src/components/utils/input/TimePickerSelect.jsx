/* eslint-disable react/prop-types */

import { useState } from 'react';
import TimePicker from 'react-time-picker';
import 'react-time-picker/dist/TimePicker.css';
import 'react-clock/dist/Clock.css';
import '../../../assets/sass/component/time_picker.scss'

const TimePickerSelect = ({ isLabel, labelFor, labelClass, labelText }) => {
    const [time, setTime] = useState('12:00');

    const handleTimeChange = (newTime) => {
        setTime(newTime);
    };

    return (
        <div className="form__wrap">
            <div className="form__wrap__item">
                {isLabel && <label htmlFor={labelFor ?? ''} className={`form__label ${labelClass ?? ''}`}>{labelText}</label>}
                <TimePicker value={time} onChange={handleTimeChange} />
            </div>
        </div>
    );
};

export default TimePickerSelect;








