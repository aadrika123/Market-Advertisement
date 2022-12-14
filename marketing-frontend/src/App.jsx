import { useState } from 'react'
import Header from './Compnents/Header/Header'
import Sidebar from './Compnents/Sidebar/Sidebar'
import { BrowserRouter as Router, Routes, Route, Link, Outlet, } from "react-router-dom";
import Dashboard from './Pages/Dashboard';
import IndexLodgeHostel from './Pages/Citizen/MarketSection/LodgeHostel/IndexLodgeHostel';
import IndexBanquetMarriage from './Pages/Citizen/MarketSection/BanquetMarriage/IndexBanquetMarriage';
import IndexDharamshala from './Pages/Citizen/MarketSection/Dharamshala/IndexDharamshala';


function App() {

  return (
    <>
      <div>
        <Router>

          <Header />
          <div className='grid grid-cols-12'>
            <div className='col-span-2'>
              <Sidebar />
            </div>
            <div className='col-span-10 bg-gray-300 p-4'>
              <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/lodge-hostel" element={<IndexLodgeHostel />} />
                <Route path="/banquet-marriage" element={<IndexBanquetMarriage />} />
                <Route path="/dharamshala" element={<IndexDharamshala />} />
              </Routes>
            </div>
          </div>
        </Router>
      </div>
    </>
  )
}

export default App
