import React from 'react'
import { Route, Routes } from 'react-router-dom'
import SelfAdvertisementIndexForm from './SelfAdvertisement/SelfAdvertisementIndexForm'
import MovableVehicleIndexForm from './MovableVehicle/MovableVehicleIndexForm'
import PrivateLandIndexForm from './PrivateLand/PrivateLandIndexForm'
import AgencyDetailIndexForm from './Agency/AgencyDetailIndexForm'
import AdvertisementDashboard from './AdvertisementDashboard'
import HoardingIndex from './Agency/Hoarding/HoardingIndex'
import PaymentScreen from './PaymentScreen'
import SelfApprovalIndexForm from './SelfPrintable/SelfApprovalIndexForm'
import AgencyDashboard from './Agency/AgencyDashboard/AgencyDashboard'


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
                    <Route path="/hoarding" element={<HoardingIndex />} />
                    <Route path="/paymentScreen" element={<PaymentScreen />} />
                    <Route path="/approvalLetter" element={<SelfApprovalIndexForm />} />
                </Routes>
            </div>
        </>
    )
}

export default AdvertisementRoutes