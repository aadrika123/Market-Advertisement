import React from 'react'
import ReactDOM from 'react-dom/client'
import { useQuery, QueryClientProvider, QueryClient } from "react-query";
import App from './App'
import './index.css'

const root = ReactDOM.createRoot(document.getElementById('root'));
const queryClient = new QueryClient()
root.render(
  // <React.StrictMode>
  //   <App />
  // </React.StrictMode>
  <QueryClientProvider client={queryClient}>
    {/* <Provider store={store}> */}
      {/* <BrowserRouter> */}
      <App />
      {/* </BrowserRouter>  */}
    {/* </Provider> */}
  </QueryClientProvider>
);
reportWebVitals();