/* eslint-disable react/prop-types */

const BodyCardHeaderRight = ({ children, className, isFlex }) => {
    return (
        <div className={`bodyCard__header__right ${isFlex ? "d-flex" : ""} ${className ?? ""}`}>
            {children}
        </div>
    );
};

export default BodyCardHeaderRight;