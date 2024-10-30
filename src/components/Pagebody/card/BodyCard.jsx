/* eslint-disable react/prop-types */



const BodyCard = ({ children, padd, className }) => {
    const style = padd !== null ? { padding: `${padd}px` } : {};
    return (
        <div className={`bodyCard ${className ?? ""}`} style={style}>
            {children}
        </div>
    )
};

export default BodyCard;