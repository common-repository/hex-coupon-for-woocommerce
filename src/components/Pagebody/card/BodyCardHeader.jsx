
/* eslint-disable react/prop-types */

const BodyCardHeader = ({ children, className, isFlex }) => {
    return (
        <div className={`bodyCard__header ${isFlex ? "d-flex" : ""} ${className ?? ""}`}>
            {children}
        </div>
    );
};

export default BodyCardHeader;