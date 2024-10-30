

const SelectWrap = ({ className, children }) => {
    return (
        <div className={`selectWrap ${className ?? ''}`}>
            {children}
        </div>
    )
}

export default SelectWrap;