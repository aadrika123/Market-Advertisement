import React, { useEffect, useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import SelfAdvertisementDocForm from './SelfAdvertisementDocForm'
import SelfAdvertisementForm1 from './SelfAdvertisementForm1'
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import axios from 'axios'
import ReviewSelfAdvertForm from './ReviewSelfAdvertForm'
import AdvrtSuccessScreen from '../AdvrtSuccessScreen';
import ApiHeader from '../../../Compnents/ApiHeader'
import BackButton from '../BackButton';
import Loader from '../Loader';

function SelfAdvertisementIndexForm() {
    const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
    const [allFormData, setAllFormData] = useState([])
    const [responseScreen, setresponseScreen] = useState()
    const [reviewData, setreviewData] = useState({})
    const [show, setshow] = useState(false)

    const showLoader = (val) => {
        setshow(val);
    }

    const { api_postSelfAdvertApplication } = AdvertisementApiList()
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


    console.log("login data...", ApiHeader())

    ///// SUBMIT FORM /////
    const submitButtonToggle = () => {
        console.log('final form ready to submit...', allFormData)
        submitSelfAdvertForm()
    }

    const submitSelfAdvertForm = () => {
        showLoader(true)
        const requestBody = {
            // ulbId: allFormData?.selfAdvertisement?.ulb,
            ulbId: 2,
            deviceId: "selfAdvert",
            applicantName: allFormData?.selfAdvertisement?.applicantName,
            licenseYear: allFormData?.selfAdvertisement?.licenseYear,
            fatherName: allFormData?.selfAdvertisement?.fatherName,
            email: allFormData?.selfAdvertisement?.email,
            residenceAddress: allFormData?.selfAdvertisement?.residenceAddress,
            wardId: allFormData?.selfAdvertisement?.residenceWardNo,
            permanentAddress: allFormData?.selfAdvertisement?.permanentAddress,
            permanentWardId: allFormData?.selfAdvertisement?.permanentWardNo,
            entityName: allFormData?.selfAdvertisement?.entityName,
            entityAddress: allFormData?.selfAdvertisement?.entityAddress,
            entityWardId: allFormData?.selfAdvertisement?.entityWardNo,
            mobileNo: allFormData?.selfAdvertisement?.mobileNo,
            aadharNo: allFormData?.selfAdvertisement?.aadharNo,
            tradeLicenseNo: allFormData?.selfAdvertisement?.tradeLicenseNo,
            holdingNo: allFormData?.selfAdvertisement?.holdingNo,
            gstNo: allFormData?.selfAdvertisement?.gstNo,
            longitude: allFormData?.selfAdvertisement?.longitude,
            latitude: allFormData?.selfAdvertisement?.latitude,
            displayArea: allFormData?.selfAdvertisement?.displayArea,
            displayType: allFormData?.selfAdvertisement?.displayType,
            installationLocation: allFormData?.selfAdvertisement?.installationLocation,
            brandDisplayName: allFormData?.selfAdvertisement?.brandDisplayName,

            documents: allFormData?.selfAdvertisementDoc?.[0]

        }
        console.log('request body...', requestBody)
        axios.post(`${api_postSelfAdvertApplication}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('response after data submitted', response.data.data)
                setresponseScreen(response.data.data)
                setTimeout(() => {
                    showLoader(false)
                }, 500);
                notify("submitted successfully", "success")
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                setTimeout(() => {
                    showLoader(false)
                }, 500);
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
                            <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold'>Self Advertisement</h1>
                        </div>
                        <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>
                            You Can Get License To Advertise Your Business Name On Your Shop
                        </h1>
                    </div>
                    <div>
                        <div className='flex flex-row mt-2 float-right'>
                            <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle '>&emsp; <strong className='text-2xl text-violet-600 '>{3 - formIndex}
                            </strong> more screen</span>
                            <img src='https://cdn-icons-png.flaticon.com/512/2376/2376320.png' className='h-10 mr-4  opacity-80 float-right  ml-4' />
                            <div className='mt-2'>
                                <BackButton />
                            </div>
                        </div>
                    </div>
                </div>
                <div className={`${formIndex == 1 ? 'translate-x-0' : 'translate-x-full'} transition-all`}><SelfAdvertisementForm1 values={values} /></div>
                <div className={`${formIndex == 2 ? 'translate-x-0' : 'translate-x-full'} transition-all`}><SelfAdvertisementDocForm values={values} /></div>
                <div className={`${formIndex == 3 ? 'translate-x-0' : 'translate-x-full'} transition-all`}><ReviewSelfAdvertForm values={values} reviewIdNameData={reviewData} allFormData={allFormData} submitFun={submitButtonToggle} /></div>
            </div>
        </>
    )
}

export default SelfAdvertisementIndexForm