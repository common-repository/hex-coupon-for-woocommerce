/* eslint-disable react/prop-types */

const PageBody = ({ children, padd, className }) => {
    const style = padd !== null ? { padding: `${padd}px` } : {};
    return (
        <div className={`pageBody ${className ?? ""}`} style={style}>
            {children}
        </div>
    )
};

export default PageBody;