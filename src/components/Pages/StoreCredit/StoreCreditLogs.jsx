import React, { useEffect, useState, useMemo } from "react";
import axios from "axios";
import { ToastContainer } from "react-toastify";
import Table from "../../utils/table/Table";
import THead from "../../utils/table/THead";
import Th from "../../utils/table/Th";
import TBody from "../../utils/table/TBody";
import BodyCard from "../../Pagebody/card/BodyCard";
import PageBody from "../../Pagebody/PageBody";
import BodyCardHeaderLeft from "../../Pagebody/card/BodyCardHeaderLeft";
import BodyCardHeaderLeftItem from "../../Pagebody/card/BodyCardHeaderLeftItem";
import BodyCardHeaderTItle from "../../Pagebody/card/BodyCardHeaderTItle";
import ButtonWrapper from "../../utils/button/ButtonWrapper";
import Button from "../../utils/button/Button";
import BodyCardHeaderRight from "../../Pagebody/card/BodyCardHeaderRight";
import BodyCardHeader from "../../Pagebody/card/BodyCardHeader";
import { Link } from "react-router-dom";
import { __ } from '@wordpress/i18n';

const StoreCreditLogs = () => {
	const { nonce, ajaxUrl } = hexCuponData;
	const [isLoading, setIsLoading] = useState(true);
	const [isHovering, setIsHovering] = useState(false);
	const [storeCreditFullLogs, setStoreCreditFullLogs] = useState([]);
	const [currentPage, setCurrentPage] = useState(0);
	const itemsPerPage = 10;
	const [searchTerm, setSearchTerm] = useState("");
	const [filterOption, setFilterOption] = useState("all");

	const handleFilterChange = (e) => {
		setFilterOption(e.target.value);
	};

	const handlePageChange = ({ selected }) => {
		setCurrentPage(selected);
	};

	// Dummy data for table rows
	const dummyData = Array.from({ length: 10 }, (_, index) => ({
		id: index + 1,
		customerName: `Customer ${index + 1}`,
		email: `customer${index + 1}@example.com`,
		orderId: `ORD-${index + 1}`,
		creditAmount: `${index + 1}0`,
		approvedBy: `Admin ${index + 1}`,
		status: index % 2 === 0 ? "Received" : "Used",
	}));

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
				setStoreCreditFullLogs(data.storeCreditLogs);
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce]);

	const filteredLogs = useMemo(() => {
		switch (filterOption) {
			case "received":
				return storeCreditFullLogs.filter(log => log.status === '1');
			case "used":
				return storeCreditFullLogs.filter(log => log.status === '0');
			default:
				return storeCreditFullLogs;
		}
	}, [filterOption, storeCreditFullLogs]);

	const filterLogsByOrderId = (logs, searchTerm) => {
		return logs.filter((log) => log.order_id.includes(searchTerm));
	};

	const displayedLogs = useMemo(() => {
		const filtered = filterLogsByOrderId(filteredLogs, searchTerm);
		const startIndex = currentPage * itemsPerPage;
		const endIndex = startIndex + itemsPerPage;
		return filtered.slice(startIndex, endIndex);
	}, [filteredLogs, currentPage, searchTerm, itemsPerPage]);

	const handleSearchTermChange = (e) => {
		setSearchTerm(e.target.value);
		setCurrentPage(0); // Reset page to 0 when search term changes
	};

	const handleMouseEnter = () => {
		setIsHovering(true);
	};

	const handleMouseLeave = () => {
		setIsHovering(false);
	};

	return (
		<>
			<PageBody>
				<BodyCard className="p-0">
					<BodyCardHeader className="p-4" isFlex={true}>
						<BodyCardHeaderLeft isFlex={true}>
							<BodyCardHeaderLeftItem>
								<BodyCardHeaderTItle children="Store Credit Logs" />
							</BodyCardHeaderLeftItem>
						</BodyCardHeaderLeft>
						<BodyCardHeaderRight>
							<ButtonWrapper isFlex={true}>
								<select value={filterOption} onChange={handleFilterChange} className="customSelect py-2.5 pl-4 pr-4 h-[34px] !ring-1 !border-transparent !ring-[var(--hex-border-color)] text-md !text-[var(--hex-paragraph-color)] focus:!ring-[var(--hex-main-color-one)] focus:!border-transparent">
									<option value="all">All</option>
									<option value="received">Received</option>
									<option value="used">Used</option>
								</select>
								<input
									type="text"
									placeholder="Search by Order ID"
									value={searchTerm}
									onChange={handleSearchTermChange}
									className="py-2.5 pl-4 pr-4 h-[34px] w-[170px] !ring-1 !border-transparent !ring-[var(--hex-border-color)] text-md !text-[var(--hex-paragraph-color)] focus:!ring-[var(--hex-main-color-one)] focus:!border-transparent"
								/>
								<Link to="/store-credit/give-new-credit">
									<Button children="Give New Credit" btnStyle={"primary"} />
								</Link>
							</ButtonWrapper>
						</BodyCardHeaderRight>
					</BodyCardHeader>
					<div
						className="relative"
						onMouseEnter={handleMouseEnter}
						onMouseLeave={handleMouseLeave}
						style={{ overflow: 'visible' }} // Allow the absolute positioned element to overflow
					>
						{isHovering && (
							<div className="absolute inset-0 flex items-center justify-center bg-blur">
								<a className="upgrade-text bg-purple-600 text-white p-4 rounded-md cursor-pointer" href="https://hexcoupon.com/pricing/">{__("Upgrade to Pro","hex-coupon-for-woocommerce")}</a>
							</div>
						)}
						<Table className="border text-left">
							<THead>
								<Th children="Customer Name" />
								<Th children="Email" />
								<Th children="Order ID" />
								<Th children="Credit Amount" />
								<Th children="Approved By" />
								<Th children="Status" />
							</THead>
							<TBody>
								{dummyData.map((row) => (
									<tr key={row.id}>
										<td>{row.customerName}</td>
										<td>{row.email}</td>
										<td>{row.orderId}</td>
										<td>{row.creditAmount}</td>
										<td>{row.approvedBy}</td>
										<td>{row.status}</td>
									</tr>
								))}
								{isHovering && (
									<tr>
										<td colSpan="6" style={{ textAlign: 'center', padding: '10px' }}>
											<a href="https://hexcoupon.com/pricing/" target="_blank">{__("Upgrade to Pro","hex-coupon-for-woocommerce")}</a>
										</td>
									</tr>
								)}
							</TBody>
						</Table>
					</div>
				</BodyCard>
				<style>
					{`
                    .bg-blur {
                        background-color: rgba(255, 255, 255, 0.8);
                        backdrop-filter: blur(5px);
                    }
                    .upgrade-text:hover {
                    	color: #ffffff;
                    }
                `}
				</style>
			</PageBody>
			<ToastContainer />
		</>
	);
};

export default StoreCreditLogs;
