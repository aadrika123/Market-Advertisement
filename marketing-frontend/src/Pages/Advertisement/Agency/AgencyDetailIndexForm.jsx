import React, { useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import AdvrtSuccessScreen from '../AdvrtSuccessScreen'
import AgencyDetailForm from './AgencyDetailForm'
import AgencyDetailDocForm from './AgencyDetailDocForm'
import ReviewAgencyApplication from './ReviewAgencyApplication'
import axios from 'axios'
import ApiHeader from '../../../Compnents/ApiHeader'
import AgencyDirectorDetail from './AgencyDirectorDetail'


function PrivateLandIndexForm() {

    const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
    const [animateform1, setAnimateform1] = useState('translate-x-0') //slide animation control state for self advertisement form
    const [animateform2, setAnimateform2] = useState('pl-20 translate-x-full')//slide animation control state for document form
    const [animateform4, setAnimateform4] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [animateform3, setAnimateform3] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [FirmStep, setFirmStep] = useState(1)
    const [regCurrentStep, setRegCurrentStep] = useState(1)
    const [colorCode, setcolorCode] = useState(false)
    const [allFormData, setAllFormData] = useState({})
    const [responseScreen, setresponseScreen] = useState()


    const { api_postAgencyApplication } = AdvertisementApiList()

    const backFun = (formIndex) => {
        let tempFormIndex = formIndex
        if (tempFormIndex == 1) { //backward by current form index 2
            setFormIndex(1) // go to form index 1 since back from index 2
            setAnimateform1('translate-x-0') // always setstate one index less than current index
            setAnimateform2('pl-20 translate-x-full') //always current index setstate
        }
        if (tempFormIndex == 2) { //backward by current form index 2
            setFormIndex(1) // go to form index 1 since back from index 2
            setAnimateform1('translate-x-0') // always setstate one index less than current index
            setAnimateform2('pl-20 translate-x-full') //always current index setstate
        }
        if (tempFormIndex == 3) {
            setFormIndex(2)
            setAnimateform2('translate-x-0')
            setAnimateform3('pl-20 translate-x-full')
        }
        if (tempFormIndex == 4) {
            setFormIndex(3)
            setAnimateform3('translate-x-0')
            setAnimateform4('pl-20 translate-x-full')
        }

    }
    const nextFun = (formIndex) => {
        let tempFormIndex = formIndex
        if (tempFormIndex == 1) { //forward by current form index 1
            setFormIndex(2) //go to form index 2 since forward from index 1
            setAnimateform1(' -translate-x-full right-80')  //always current index setstate
            setAnimateform2('pl-0 translate-x-0') // always setstate one index greater than current index
        }
        if (tempFormIndex == 2) {
            setFormIndex(3)
            setAnimateform2('-translate-x-full right-80')
            setAnimateform3('pl-0 translate-x-0')
        }
        if (tempFormIndex == 3) {
            setFormIndex(3)
            setAnimateform2('-translate-x-full right-80')
            setAnimateform3('pl-0 translate-x-0')
        }
        if (tempFormIndex == 4) {
            setFormIndex(4)
            setAnimateform3('-translate-x-full right-80')
            setAnimateform4('pl-0 translate-x-0')
        }
    }


    //activating notification if no owner or no floor added
    const notify = (toastData, actionFlag) => {
        toast.dismiss();
        { actionFlag == 'success' && toast.success(toastData) }
        { actionFlag == 'notice' && toast.warn(toastData) }
        { actionFlag == 'error' && toast.error(toastData) }
    };


    ///////////{*** COLLECTING ALL FORM DATA***}/////////
    const collectAllFormData = (key, formData) => {
        console.log('prev of all Data', allFormData)
        setAllFormData({ ...allFormData, [key]: formData })
    }
    console.log("all form data in index", allFormData)


    ///// SUBMIT FORM /////
    const submitButtonToggle = () => {

        console.log('final form ready to submit...', allFormData)
        submitAgencyForm()
    }

    const submitAgencyForm = () => {
        const requestBody = {
            ulbId: allFormData?.agency?.ulb,
            deviceId: "privateLand",
            entityType: allFormData?.agency?.entityType,
            entityName: allFormData?.agency?.entityName,
            address: allFormData?.agency?.address,
            mobileNo: allFormData?.agency?.mobileNo,
            officeTelephone: allFormData?.agency?.officialTelephone,
            fax: allFormData?.agency?.fax,
            email: allFormData?.agency?.email,
            panNo: allFormData?.agency?.panNo,
            gstNo: allFormData?.agency?.gstNo,
            blacklisted: 0,
            pendingCourtCase: 1,
            pendingAmount: allFormData?.agency?.pendingAmount,
            directors: allFormData?.agencyDirector,


            // gstDoc: allFormData?.agency?.ulb,
            // itReturnDoc1: allFormData?.agency?.ulb,
            // itReturnDoc2: allFormData?.agency?.ulb,
            // officeAddressDoc: allFormData?.agency?.ulb,
            // panDoc: allFormData?.agency?.ulb,
            // director1AadharDoc: allFormData?.agency?.ulb,
            // director2AadharDoc: allFormData?.agency?.ulb,
            // director3AadharDoc: allFormData?.agency?.ulb,
            // director4AadharDoc: allFormData?.agency?.ulb,
            // affidivitDoc: allFormData?.agency?.ulb,

        }

        console.log('request body...', requestBody)
        axios.post(`${api_postAgencyApplication}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('response after data submitted', response.data.data)
                setresponseScreen(response.data.data)
                notify('submitted successfully', 'success')
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                notify('failed to submit', 'error')

            })
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
            <div className='overflow-x-hidden'>
                <div className='bg-white p-1 rounded-md shadow-md shadow-violet-200 '>
                    <div className='flex flex-row '>
                        <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold '>Agency Registration</h1>
                    </div>
                    <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1>
                    <div className='flex flex-row float-right'>
                        {/* {FirmStep == 1 && */}
                        <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle -mt-10'>&emsp; <strong className='text-2xl text-violet-600 '>{4 - formIndex}
                        </strong> more screen</span>
                        <img src='https://cdn-icons-png.flaticon.com/512/1684/1684121.png' className='h-10 mr-4  opacity-80 float-right -mt-12 ml-4' />
                    </div>

                </div>
                <div className={`${animateform1} transition-all relative`}><AgencyDetailForm collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>
                <div className={`${animateform2} transition-all relative `}><AgencyDirectorDetail allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>

                <div className={`${animateform3} transition-all relative`}><AgencyDetailDocForm collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>

                <div className={`${animateform4} transition-all relative `}><ReviewAgencyApplication allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>

            </div>
        </>
    )
}

export default PrivateLandIndexForm