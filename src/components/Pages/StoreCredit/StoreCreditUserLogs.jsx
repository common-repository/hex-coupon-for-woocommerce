import React, { useEffect, useState } from "react";
import { Link, useParams, useNavigate } from "react-router-dom";
import PageBody from "../../Pagebody/PageBody";
import BodyCard from "../../Pagebody/card/BodyCard";
import BodyCardHeader from "../../Pagebody/card/BodyCardHeader";
import BodyCardHeaderLeft from "../../Pagebody/card/BodyCardHeaderLeft";
import BodyCardHeaderLeftItem from "../../Pagebody/card/BodyCardHeaderLeftItem";
import BodyCardHeaderTItle from "../../Pagebody/card/BodyCardHeaderTItle";
import BodyCardHeaderRight from "../../Pagebody/card/BodyCardHeaderRight";
import ButtonWrapper from "../../utils/button/ButtonWrapper";
import Button from "../../utils/button/Button";
import { Skeleton } from "../../Skeleton";
import Table from "../../utils/table/Table";
import THead from "../../utils/table/THead";
import Th from "../../utils/table/Th";
import TBody from "../../utils/table/TBody";
import { TbChevronLeft } from "react-icons/tb";
import ReactPaginate from "react-paginate";
import { ToastContainer } from "react-toastify";
import axios from "axios";

const StoreCreditUserLogs = () => {
	const { nonce, ajaxUrl } = hexCuponData;
	const [isLoading, setIsLoading] = useState(true);
	const { userId } = useParams();
	const [userName, setUserName] = useState('');
	const [storeCreditLogs, setStoreCreditLogs] = useState([]);
	const [currentPage, setCurrentPage] = useState(0);
	const [filterOption, setFilterOption] = useState("all"); // State to manage the filter option
	const itemsPerPage = 10;

	const navigate = useNavigate();
	const [totalStoreCreditAmount, setTotalStoreCreditAmount] = useState([]);

	const handlePageChange = ({ selected }) => {
		setCurrentPage(selected);
	};

	useEffect(() => {
		axios
			.get(ajaxUrl, {
				params: {
					nonce: nonce,
					action: 'all_combined_data',
				},
				headers: {
					'Content-Type': 'application/json',
				},
			})
			.then(({ data }) => {
				setStoreCreditLogs(data.storeCreditLogs);
				setUserName(data.storeCreditLogs.find(log => log.user_id === userId)?.user_name || '');
				setTotalStoreCreditAmount(data.totalStoreCreditAmount);
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce, userId]);

	const filteredData = storeCreditLogs.filter(item => {
		if (filterOption === "all") {
			return true;
		} else if (filterOption === "received") {
			return item.status === "1"; // Assuming "1" represents received credits
		} else if (filterOption === "used") {
			return item.status === "0"; // Assuming "0" represents used credits
		}
	});

	const goBack = () => {
		navigate(-1);
	};

	const startIndex = currentPage * itemsPerPage;
	const endIndex = startIndex + itemsPerPage;

	const displayedLogs = filteredData.slice(startIndex, endIndex);

	const currentUserTotalAmount = Number(totalStoreCreditAmount[userId]) || 0;

	const handleFilterChange = (e) => {
		setFilterOption(e.target.value);
	};

	return (
		<>
			{isLoading ? (
				<Skeleton height={500} radius={10} />
			) : (
				<PageBody>
					<BodyCard className="p-0">
						<BodyCardHeader className="p-4" isFlex={true}>
							<BodyCardHeaderLeft isFlex={true}>
								<BodyCardHeaderLeftItem>
									<BodyCardHeaderTItle icon={<TbChevronLeft size={24} onClick={goBack}/>} children={userName + "'s Store Credit log"} />
								</BodyCardHeaderLeftItem>
							</BodyCardHeaderLeft>
							<BodyCardHeaderRight>
								<ButtonWrapper isFlex={true}>
									<select value={filterOption} onChange={handleFilterChange} className="customSelect py-2.5 pl-4 pr-4 h-[34px] !ring-1 !border-transparent !ring-[var(--hex-border-color)] text-md !text-[var(--hex-paragraph-color)] focus:!ring-[var(--hex-main-color-one)] focus:!border-transparent">
										<option value="all">All</option>
										<option value="received">Received</option>
										<option value="used">Used</option>
									</select>
									<span className="border-b-2 border-[var(--hex-main-color-one)] px-1.5 py-1.5 text-sm text-slate-600">Store Credit Balance: <b>{currentUserTotalAmount.toFixed(2)}</b></span>
									<Link to="/give-new-credit">
										<Button children="Give New Credit" btnStyle={"primary"} />
									</Link>
								</ButtonWrapper>
							</BodyCardHeaderRight>
						</BodyCardHeader>
						<Table className="border text-left">
							<THead>
								<Th children="Order Id" />
								<Th children="Credit Amount" />
								<Th children="Date" />
								<Th children="Credit Type" />
								<Th children="Status" />
							</THead>
							<TBody>
								{displayedLogs.map((log, index) => {
									let status;
									let creditType;

									switch (log.status) {
										case "1":
											status = "<i class=\"px-2.5 py-2 bg-green-100 text-green-800\">Received</i>";
											break;
										case "0":
											status = "<i class=\"px-2.5 py-2 bg-cyan-300 text-cyan-600\">Used</i>";
											break;
									}

									switch (log.label) {
										case "0":
											creditType = "<i class=\"px-2.5 py-2 bg-slate-200 text-slate-700\">Refund Credits</i>";
											break;
										case "1":
											creditType = "<i class=\"px-2.5 py-2 bg-purple-200 text-purple-700\">Gift Credits</i>";
											break;
										default :
											creditType = "<i class=\"px-2.5 py-2 bg-cyan-200 text-cyan-600\">None</i>";
											break;
									}

									return (
										<tr key={index}>
											<td>
												{
													log.order_edit_page_link.includes("post=0") ?
														(
															<a href="javascript:void(0)" className="text-sky-500 underline">{"#" + log.order_id}</a>
														) : (
															<a href={log.order_edit_page_link.replace("amp;","")} className="text-sky-500 underline">{"#" + log.order_id}</a>
														)
												}
											</td>
											<td>{log.amount + " Credits"}</td>
											<td>{log.created_at}</td>
											<td dangerouslySetInnerHTML={{ __html: creditType}}/>
											<td dangerouslySetInnerHTML={{ __html: status }} />
										</tr>
									);
								})}
							</TBody>
						</Table>
						<ReactPaginate
							pageCount={Math.ceil(filteredData.length / itemsPerPage)}
							pageRangeDisplayed={5}
							marginPagesDisplayed={2}
							onPageChange={handlePageChange}
							containerClassName={"pagination"}
							subContainerClassName={"pages pagination"}
							activeClassName={"active"}
						/>
					</BodyCard>
				</PageBody>
			)}
			<ToastContainer />
		</>
	);
};

export default StoreCreditUserLogs;
