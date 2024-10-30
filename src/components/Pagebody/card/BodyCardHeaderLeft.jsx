/* eslint-disable react/prop-types */

const BodyCardHeaderLeft = ({ children, className, isFlex }) => {
    return (
        <div className={`bodyCard__header__left ${isFlex ? "d-flex" : ""} ${className ?? ""}`}>
            {children}
        </div>
    );
};

export default BodyCardHeaderLeft;