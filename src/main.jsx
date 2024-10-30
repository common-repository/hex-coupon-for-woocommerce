import React from 'react'
import ReactDOM from 'react-dom/client'
// import { I18nProvider } from '@wordpress/react-i18n';
import App from './App'
import "./scss/main.scss";
import { createI18n } from '@wordpress/i18n';
import { I18nProvider } from '@wordpress/react-i18n';
const i18n = createI18n();

ReactDOM.createRoot(document.getElementById('vite-react-sample')).render(
	<I18nProvider  i18n={i18n}>
		<App />
	</I18nProvider>
)

