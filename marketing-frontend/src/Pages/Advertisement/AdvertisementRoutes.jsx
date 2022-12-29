import React from 'react'
import { Route, Routes } from 'react-router-dom'
import SelfAdvertisementIndexForm from './SelfAdvertisement/SelfAdvertisementIndexForm'
import MovableVehicleIndexForm from './MovableVehicle/MovableVehicleIndexForm'
import PrivateLandIndexForm from './PrivateLand/PrivateLandIndexForm'
import AgencyIndexForm from './AgencyRegistration/AgencyIndexForm'


function AdvertisementRoutes() {
    return (
        <>
            <div>
                <Routes>
                    {/*//////////// Advertisement routes/////////////// */}
                    <Route path="/selfAdvrt" element={<SelfAdvertisementIndexForm />} />
                    <Route path="/movableVehicle" element={<MovableVehicleIndexForm />} />
                    <Route path="/privateLand" element={<PrivateLandIndexForm />} />
                    <Route path="/agency" element={<AgencyIndexForm />} />
                </Routes>
            </div>
        </>
    )
}

export default AdvertisementRoutes