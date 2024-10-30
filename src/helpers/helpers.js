export function getDataForCharJS(labels, data){
	return {
		type: 'bar',
		labels: labels,
		backgroundColor: ['#A760FE', '#03AB67', '#4D77FF', '#98A2B3'],
		datasets: [
			{
				label: "Created",
				backgroundColor: '#A760FE',
				data: data.created,
				barThickness: 10,
				hoverBackgroundColor: 'transparent',
				hoverBorderColor: '#A760FE',
				borderColor: '#A760FE',
				borderWidth: 1,
			}, {
				label: "Redeemed",
				backgroundColor: '#03AB67',
				data: data.redeemed,
				barThickness: 10,
				hoverBackgroundColor: 'transparent',
				hoverBorderColor: '#03AB67',
				borderColor: '#03AB67',
				borderWidth: 1,
			}, {
				label: "Active",
				backgroundColor: '#4D77FF',
				data: data.active,
				barThickness: 10,
				hoverBackgroundColor: 'transparent',
				hoverBorderColor: '#4D77FF',
				borderColor: '#4D77FF',
				borderWidth: 1,
			}, {
				label: "Expired",
				backgroundColor: '#98A2B3',
				data: data.expired,
				barThickness: 10,
				hoverBackgroundColor: 'transparent',
				hoverBorderColor: '#98A2B3',
				borderColor: '#98A2B3',
				borderWidth: 1,
			},
		],
	};
}

export const getWeekList = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
export const getSingleDayList = ['1'];
