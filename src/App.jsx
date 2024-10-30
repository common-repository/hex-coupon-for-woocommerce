import { HashRouter, Routes, Route } from 'react-router-dom';
import Sidebar from './components/HexMain/HexSidebar/sidebar';
import Dashboard from './components/Pages/Dashboard/index';
import MainContainer from './components/HexMain/HexMainContainer/MainContainer';
import { SidebarProvider } from "./components/context/SidebarContext";
import StoreCreditSettings from "./components/Pages/StoreCredit/StoreCreditSettings";
import StoreCreditLogs from "./components/Pages/StoreCredit/StoreCreditLogs";
import StoreCreditUserLogs from "./components/Pages/StoreCredit/StoreCreditUserLogs";
import GiveNewCredit from "./components/Pages/StoreCredit/GiveNewCredit";
import LoyaltyProgramSettings from "./components/pages/LoyaltyProgram/LoyaltyProgramSettings";
import LoyaltyProgramLogs from "./components/Pages/LoyaltyProgram/LoyaltyProgramLogs";
import PointBasedLoyaltySettings from "./components/Pages/LoyaltyProgram/PointBasedSettings";
import LoyaltyProgramUserLogs from "./components/Pages/LoyaltyProgram/LoyaltyProgramUserLogs";

import GiftCard from "./components/Pages/GiftCard/GiftCard";
import Automation from "./components/Pages/Automation/Automation";
import SpinWheel from "./components/Pages/SpinWheel/SpinWheel";

function App() {
	return (
		<>
			<HashRouter>
				<SidebarProvider>
					<div className="HxcAppWrapper">
						<Sidebar />
						<MainContainer>
							<Routes>
								<Route element={<Dashboard />} path="/" />
								<Route element={<StoreCreditSettings />} path="/store-credit/store-credit-settings" />
								<Route element={<StoreCreditLogs />} path="/store-credit/store-credit-logs" />
								<Route element={<StoreCreditUserLogs />} path="/store-credit-user-logs/:userId" />
								<Route element={<GiveNewCredit />} path="/store-credit/give-new-credit" />
								<Route element={<LoyaltyProgramSettings />} path="/loyalty-program/loyalty-program-settings" />
								<Route element={<LoyaltyProgramLogs />} path="/loyalty-program/loyalty-program-logs" />
								<Route element={<PointBasedLoyaltySettings />} path="/loyalty-program/point-based-loyalty-settings" />
								<Route element={<LoyaltyProgramUserLogs />} path="/loyalty-program-user-logs/:userId" />
								<Route element={<GiftCard />} path="/gift-card" />
								<Route element={<Automation />} path="/automation" />
								<Route element={<SpinWheel />} path="/spinwheel" />
							</Routes>
						</MainContainer>
					</div>
				</SidebarProvider>
			</HashRouter>
		</>
	)
}
export default App;
