/* eslint-disable react/prop-types */

import '../../../assets/sass/component/card_list.scss'

const CardList = ({ lists, padd, className }) => {
    const style = padd != null ? { padding: `${padd}px` } : {};
    return (
        <div className={`cardList ${className ?? ''}`} style={style}>
            <ol>
                {lists.map((item, index) => (
                    <li className="line-container" key={index}>
                        <div className="title">{item.title}</div>
                        <div className="line"></div>
                        <div className="amount">{item.amount}</div>
                    </li>
                ))}
            </ol>
        </div>
    )
};

export default CardList;