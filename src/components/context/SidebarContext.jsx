/* eslint-disable react/prop-types */

import { createContext, useState, useContext } from 'react';

const SidebarContext = createContext();

export const useSidebar = () => {
    return useContext(SidebarContext);
}

export const SidebarProvider = ({ children }) => {
    const [isSidebarActive, setIsSidebarActive] = useState(false);
    const toggleSidebar = (prev) => {
        setIsSidebarActive(prev => !prev);
    };
    const closeSidebar = (prev) => {
        setIsSidebarActive(prev => !prev);
    };

    const state = { isSidebarActive, toggleSidebar, closeSidebar };

    return (
        <SidebarContext.Provider value={state}>
            {children}
        </SidebarContext.Provider>
    );
};
