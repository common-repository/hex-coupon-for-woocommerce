/* eslint-disable react/prop-types */

import '../../../assets/sass/component/filter.scss'

const FilterWrap = ({ className, itemClass, children }) => {
    return (
        <div className={`filterWrap ${className || ""} `}>
            <ul className={`filterWrap__list ${itemClass || ""}`}>
                {children}
            </ul>
        </div>
    );
};

export default FilterWrap;
