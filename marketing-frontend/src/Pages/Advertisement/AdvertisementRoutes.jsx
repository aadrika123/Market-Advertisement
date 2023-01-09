import React from 'react'
import { Route, Routes } from 'react-router-dom'
import SelfAdvertisementIndexForm from './SelfAdvertisement/SelfAdvertisementIndexForm'
import MovableVehicleIndexForm from './MovableVehicle/MovableVehicleIndexForm'
import PrivateLandIndexForm from './PrivateLand/PrivateLandIndexForm'
import AgencyDetailIndexForm from './Agency/AgencyDetailIndexForm'
import AdvertisementDashboard from './AdvertisementDashboard'


function AdvertisementRoutes() {
    return (
        <>
            <div>
                <Routes>
                    {/*//////////// Advertisement routes/////////////// */}
                    <Route path="/advertDashboard" element={<AdvertisementDashboard />} />
                    <Route path="/selfAdvrt" element={<SelfAdvertisementIndexForm />} />
                    <Route path="/movableVehicle" element={<MovableVehicleIndexForm />} />
                    <Route path="/privateLand" element={<PrivateLandIndexForm />} />
                    <Route path="/agency" element={<AgencyDetailIndexForm />} />
                </Routes>
            </div>
        </>
    )
}

export default AdvertisementRoutes