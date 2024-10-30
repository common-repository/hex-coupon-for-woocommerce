/* eslint-disable no-unused-vars */
/* eslint-disable react/prop-types */
import { useState } from 'react'
import TimezoneSelect from "react-timezone-select";
import '../../../assets/sass/component/time_zone.scss'


const TimeZone = ({ onChange, labelText, labelClass, labelFor, isLabel }) => {
    const [selectedTimezone, setSelectedTimezone] = useState(
        Intl.DateTimeFormat().resolvedOptions().timeZone
    );

    // const handleTimezoneChange = (timeZone) => {
    //     setSelectedTimezone(timeZone);

    //     if (onChange) {
    //         onChange(timeZone);
    //     } else {
    //         onChange();
    //     }
    // };

    return (
        <div className="form__wrap">
            <div className="form__wrap__item">
                {isLabel && <label htmlFor={labelFor ?? ''} className={`form__label ${labelClass ?? ''}`}>{labelText}</label>}
                <TimezoneSelect value={selectedTimezone} onChange={setSelectedTimezone} />
            </div>
        </div>
    );
};

export default TimeZone;