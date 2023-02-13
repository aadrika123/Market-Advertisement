import React from 'react'
import { Route, Routes } from 'react-router-dom'
import SelfAdvertisementIndexForm from './SelfAdvertisement/SelfAdvertisementIndexForm'
import MovableVehicleIndexForm from './MovableVehicle/MovableVehicleIndexForm'
import PrivateLandIndexForm from './PrivateLand/PrivateLandIndexForm'
import AgencyDetailIndexForm from './Agency/AgencyDetailIndexForm'
import AdvertisementDashboard from './AdvertisementDashboard'
import HoardingIndex from './Agency/Hoarding/HoardingIndex'
import SelfApprovalIndexForm from './SelfPrintable/SelfApprovalIndexForm'
import AgencyDashboard from './Agency/AgencyDashboard/AgencyDashboard'
import PaymentScreen from '../../Pages/Advertisement/PaymentScreen'
import BanquetMarriageHallFormIndex from '../MarketingModules/BanquetMarriageHall/BanquetMarriageHallFormIndex'
import DharamshalaIndex from '../MarketingModules/Dharmsala/DharamshalaIndex'
import LodgeHostelIndex from '../MarketingModules/LodgeHostel/LodgeHostelIndex'
import ViewAllHoardingPendingList from './Agency/Hoarding/ViewAllHoardingPendingList'
import ViewAllHoardingApprovedList from './Agency/Hoarding/ViewAllHoardingApprovedList'
import ViewAllHoardingRejectedList from './Agency/Hoarding/ViewAllHoardingRejectedList'


function AdvertisementRoutes() {
    return (
        <>
            <div className=''>
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
                    {/* <Route path="/paymentSuccess" element={<PaymentSuccessScreen />} /> */}
                    <Route path="/hoarding-pending-list" element={<ViewAllHoardingPendingList />} />
                    <Route path="/hoarding-approved-list" element={<ViewAllHoardingApprovedList />} />
                    <Route path="/hoarding-Rejected-list" element={<ViewAllHoardingRejectedList />} />


                    {/*//////////// Marketing routes/////////////// */}
                    <Route path="/marriage-hall" element={<BanquetMarriageHallFormIndex />} />
                    <Route path="/dharamshala" element={<DharamshalaIndex />} />
                    <Route path="/lodge-hostel" element={<LodgeHostelIndex />} />


                </Routes>
            </div>
        </>
    )
}

export default AdvertisementRoutes