import React from 'react'
import { Route, Routes } from 'react-router-dom'
import SelfAdvertisementIndexForm from './SelfAdvertisement/SelfAdvertisementIndexForm'
import MovableVehicleIndexForm from './MovableVehicle/MovableVehicleIndexForm'
import PrivateLandIndexForm from './PrivateLand/PrivateLandIndexForm'
import AgencyDetailIndexForm from './Agency/AgencyDetailIndexForm'
import AdvertisementDashboard from './AdvertisementDashboard'
import AgencyDashboard from './Agency/AgencyDashboard'


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
                    <Route path="/agencyDashboard" element={<AgencyDashboard />} />
                </Routes>
            </div>
        </>
    )
}

export default AdvertisementRoutes