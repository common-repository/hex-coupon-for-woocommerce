/* eslint-disable react/prop-types */
import { Link } from "react-router-dom";

const DropDownButton = ({ text, dropdowns, icon }) => {
    const iconMarkup = icon ? <i className={icon}></i> : "";
    return (
        <div className="dropdownButtonWrapper">
            <button type="button" className="dropdownBtn">
                {iconMarkup}
                {text}
                <i className="las la-angle-down"></i>
            </button>
            <div className="dropdownWrapper">
                {
                    dropdowns.map(({ route, text }, index) => (
                        route ?
                            <Link key={index} className="dropdown-item" to={route}>
                                {text}
                            </Link>
                            :
                            <span key={index} className="dropdown-item"> {text}</span>
                    ))
                }
            </div>
        </div>
    )
};

export default DropDownButton;