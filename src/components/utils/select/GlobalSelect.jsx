/* eslint-disable react/prop-types */
// Import necessary packages and styles
import { useState } from 'react';
import Select from 'react-select';
// import { css } from '@emotion/react';

// Sample data for options
const options = [
    { value: 'saturday', label: 'Saturday' },
    { value: 'sunday', label: 'Sunday' },
    { value: 'monday', label: 'Monday' },
    { value: 'tuesday', label: 'Tuesday' },
    { value: 'wednesday', label: 'Wednesday' },
    { value: 'thursday', label: 'Thursday' },
    { value: 'friday', label: 'Friday' },
];

// React Select component
const GlobalSelect = ({ name, className, placeholder }) => {
    // State to manage the selected option
    const [selectedOption, setSelectedOption] = useState(null);

    // Handle change when an option is selected
    const handleChange = (selectedOption) => {
        setSelectedOption(selectedOption);
    };

    return (
        <div className={`selectWrap ${className ?? ''}`} >
            <Select name={name} className="selectField"
                value={selectedOption}
                onChange={handleChange}
                options={options}
                // isSearchable={true}
                placeholder={placeholder}

            />
        </div>
    );
};

export default GlobalSelect;
