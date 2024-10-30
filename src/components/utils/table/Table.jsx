/* eslint-disable react/prop-types */

const Table = ({ children = false, className = "default", padd }) => {
    const style = padd !== null ? { padding: `${padd}px` } : {};
    return (
        <div className={`tableWrap table-${className}`} style={style}>
            <table>
                {children}
            </table>
        </div>
    );
};

export default Table;