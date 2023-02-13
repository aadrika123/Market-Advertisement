import React, { useEffect, useState } from 'react'
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import axios from 'axios'
import Loader from '../../Advertisement/Loader';
import BackButton from '../../Advertisement/BackButton';
import BanquetMarriageHallForm from './BanquetMarriageHallForm';
import BanquetMarriageHallDocForm from './BanquetMarriageHallDocForm';
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ReviewFormBanquetMarriageHall from './ReviewFormBanquetMarriageHall';
import ApiHeader from '../../../Compnents/ApiHeader';


function BanquetMarriageHallFormIndex() {
    const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
    const [allFormData, setAllFormData] = useState([])
    const [responseScreen, setresponseScreen] = useState()
    const [reviewData, setreviewData] = useState({})
    const [show, setshow] = useState(false)

    const showLoader = (val) => {
        setshow(val);
    }

    const { api_postBanquetApplication } = AdvertisementApiList()
    const notify = (toastData, type) => {
        toast.dismiss();
        if (type == 'success') {
            toast.success(toastData)
        }
        if (type == 'error') {
            toast.error(toastData)
        }
    };

    ///////////{*** COLLECTING ALL FORM DATA***}/////////
    const collectAllFormData = (key, formData, reviewIdName) => {
        console.log('prev of all Data', allFormData)
        console.log("review name by id in index...", reviewIdName)
        setAllFormData({ ...allFormData, [key]: formData })

        if (key == 'selfAdvertisement') {
            console.log("data collecting by key", key, 'formData', formData, 'reviewData', reviewIdName)
            setreviewData({ ...reviewData, [key]: reviewIdName })
        }
        else {
            console.log('data not in review ===', key, '===', formData, 'preview...', reviewIdName)
            setreviewData({ ...reviewData, [key]: formData })
        }
    }
    console.log("all form data in index", allFormData)
    console.log("all form data in index for doc", allFormData?.selfAdvertisementDoc?.[0])


    ///// SUBMIT FORM /////
    const submitButtonToggle = () => {
        console.log('final form ready to submit...', allFormData)
        submitBanquetApplicationForm()
    }

    const submitBanquetApplicationForm = () => {
        const requestBody = {
            // ulbId: allFormData?.selfAdvertisement?.ulb,
            ulbId: 2,
            licenseYear: allFormData?.banquetForm?.licenseYear,
            applicantName: allFormData?.banquetForm?.applicantName,
            fatherName: allFormData?.banquetForm?.fatherName,
            residentialAddress: allFormData?.banquetForm?.residenceAddress,
            residentialWardId: allFormData?.banquetForm?.residenceWardNo,
            permanentAddress: allFormData?.banquetForm?.permanentAddress,
            permanentWardId: allFormData?.banquetForm?.permanentWardNo,
            email: allFormData?.banquetForm?.email,
            mobile: allFormData?.banquetForm?.mobileNo,
            hallType: allFormData?.banquetForm?.hallType,
            entityName: allFormData?.banquetForm?.entityName,
            entityAddress: allFormData?.banquetForm?.entityAddress,
            entityWardId: allFormData?.banquetForm?.entityWardNo,
            holdingNo: allFormData?.banquetForm?.holdingNo,
            tradeLicenseNo: allFormData?.banquetForm?.tradeLicenseNo,
            longitude: allFormData?.banquetForm?.longitude,
            latitude: allFormData?.banquetForm?.latitude,
            organizationType: allFormData?.banquetForm?.organizationType,
            floorArea: allFormData?.banquetForm?.floorArea,
            landDeedType: allFormData?.banquetForm?.landDeedType,
            waterSupplyType: allFormData?.banquetForm?.waterSupplyType,
            electricityType: allFormData?.banquetForm?.electricityType,
            cctvCamera: allFormData?.banquetForm?.noOfCctv,
            fireExtinguisher: allFormData?.banquetForm?.noFireExtinguishers,
            entryGate: allFormData?.banquetForm?.noEntryGates,
            exitGate: allFormData?.banquetForm?.noExitGate,
            twoWheelersParking: allFormData?.banquetForm?.noTwoWheelerParking,
            fourWheelersParking: allFormData?.banquetForm?.noFourWheelerParking,
            aadharCard: allFormData?.banquetForm?.aadharNo,
            panCard: allFormData?.banquetForm?.panNo,
            securityType: allFormData?.banquetForm?.securityType,
            documents: allFormData?.banquetDoc?.[0]
        }

        console.log('request body...', requestBody)
        axios.post(`${api_postBanquetApplication}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('response after data submitted', response.data.data)
                setresponseScreen(response.data.data)
                notify("submitted successfully", "success")
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                notify("failed to submit", "error")

            })
    }

    // passing values in components
    const values = {
        setFormIndex: setFormIndex,
        showLoader: showLoader,
        collectFormDataFun: collectAllFormData,
        toastFun: notify,
    }

    console.log("response screen", responseScreen)
    if (responseScreen?.status == true) {
        return (
            <>
                <AdvrtSuccessScreen responseScreenData={responseScreen} />
            </>
        )
    }

    return (
        <>
            <div className=''>
                <Loader show={show} />
            </div>
            <ToastContainer position="top-right"
                autoClose={2000} />

            <div className='overflow-x-clip '>
                <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 bg-white p-2 rounded-md shadow-md shadow-violet-200  '>
                    <div className=''>
                        <div className='flex flex-row '>
                            <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold'>Banquet/Marriage Hall Application</h1>
                        </div>
                        <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>
                            You Can Get License To Advertise Your Business Name On Your Shop
                        </h1>
                    </div>
                    <div>
                        <div className='flex flex-row mt-2 float-right'>
                            <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle'>&emsp; <strong className='text-2xl text-violet-600 '>{3 - formIndex}
                            </strong> more screen</span>
                            <img src='https://cdn-icons-png.flaticon.com/512/2376/2376320.png' className='h-10 mr-4  opacity-80 float-right  ml-4' />
                            <div className='mt-2'>
                                <BackButton />
                            </div>
                        </div>
                    </div>
                </div>
                <div className={`${formIndex == 1 ? 'translate-x-0' : 'translate-x-full'} transition-all`}><BanquetMarriageHallForm values={values} /></div>
                <div className={`${formIndex == 2 ? 'translate-x-0' : 'translate-x-full'} transition-all`}><BanquetMarriageHallDocForm values={values} /></div>
                <div className={`${formIndex == 3 ? 'translate-x-0' : 'translate-x-full'} transition-all`}><ReviewFormBanquetMarriageHall values={values} reviewIdNameData={reviewData} allFormData={allFormData} submitFun={submitButtonToggle} /></div>
            </div>
        </>
    )
}

export default BanquetMarriageHallFormIndex