import axios from 'axios'
import React, { useEffect, useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import ApiHeader from '../../../Compnents/ApiHeader'


function ViewAgencyApplicationFullDetails(props) {

    const { api_getAgencyApplicationFullDetail, api_getAgencyAppliedDocumentList } = AdvertisementApiList()

    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600 font-semibold"
    let inputStyle = "mt-6 -ml-7 mb-2 text-sm text-gray-800 text-left font-bold"
    console.log("application data in list application", props.data)

    const [applicationDetail, setapplicationDetail] = useState()
    const [documentList, setdocumentList] = useState()

    let show = props.showLoader
    const applicationId = props.data
    const applicationType = props.applicationType


    console.log("onclick close modal", props.closeModal)
    console.log("application id..1", applicationId)
    console.log("application type", applicationType)


    const handleClose = () => {
        props.closeModal()
    }

    console.log("application id..1", applicationId)

    ///////////{*** GET APPLICATION LIST***}/////////
    useEffect(() => {
        getApplicationDetail()
    }, [])
    const getApplicationDetail = () => {
        props.showLoader(true);
        console.log("application no through props..", props?.data)
        const requestBody = {
            applicationId: applicationId,
            type: applicationType,
        }
        axios.post(`${api_getAgencyApplicationFullDetail}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('application agency view details 1', response)
                setapplicationDetail(response.data.data)
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
    }
    console.log("details of application agency...1", applicationDetail?.fullDetailsData
    )

    ///////////{*** GET UPLOADED DOCUMENT***}/////////
    useEffect(() => {
        getAppliedDocumentList()
    }, [])
    const getAppliedDocumentList = () => {
        props.showLoader(true)
        const requestBody = {
            applicationId: applicationId,
            type: applicationType,
        }
        axios.post(`${api_getAgencyAppliedDocumentList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('document list', response.data.data)
                setdocumentList(response.data.data)
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
    }

    console.log("document list...2", documentList)


    return (
        <>
            <div className=''>
                <div className=' shadow-md shadow-violet-200 p-2'>
                    <h1 className='text-2xl  font-semibold text-gray-700 '>Application Details</h1>
                    <h1 className='text-xs  text-gray-500'>Review your application</h1>
                    <button className='float-right -mt-9 mr-4' onClick={handleClose}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 bg-red-400 text-white  shadow-lg  rounded-full">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
                <div className='ml-96'>
                    {/* <Loader show={ props.showLoader(false)} /> */}
                </div>
                {props.showLoader}
                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 p-4 gap-4'>
                    <div className='col-span-7 '>
                        <div className='grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 p-4 bg-violet-100 rounded leading-5'>
                            <div className='flex '>
                                <h1 className='font-normal text-md '>Application No.</h1>
                                <h1 className='font-bold text-lg text-gray-900 ml-4 -mt-1'>{applicationDetail?.application_no}</h1>
                            </div>
                            <div className='flex'>
                                <h1 className='font-normal text-md '>Apply Date</h1>
                                <h1 className='font-bold text-lg text-gray-900 ml-4 -mt-1'>{applicationDetail?.apply_date}</h1>
                            </div>
                        </div>
                        {applicationDetail?.fullDetailsData?.dataArray?.map((data) => (
                            <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 container mx-auto bg-white rounded leading-5 p-2 mt-4'>
                                {data?.data?.map((data) => (
                                    <div className='bg-violet-100 border border-white p-2'>
                                        <h1 className='font-bold text-lg text-gray-900 '>{data?.value || 'N/A'} </h1>
                                        <h1 className=' font-normal text-sm text-gray-600 '>{data?.displayString}</h1>
                                    </div>
                                ))}
                            </div>
                        ))}
                        <h1 className='font-bold text-lg text-gray-600'>DIRECTORS DETAILS</h1>
                        <table class="table-auto text-slate-700 w-full mx-auto mt-2 mb-4 border">
                            <thead>
                                <tr className="bg-violet-100 text-gray-600 text-xs h-8  uppercase">
                                    <th>Director Name</th>
                                    <th>Director Mobile No.</th>
                                    <th>Director Email</th>

                                </tr>
                            </thead>
                            <tbody>

                                {applicationDetail?.directors?.map((data) => (
                                    <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                        <td>
                                            <span>{data?.director_name} </span>
                                        </td>
                                        <td>
                                            <span>{data?.director_email}</span>
                                        </td>
                                        <td>
                                            <span>{data?.director_mobile}</span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className='col-span-5 bg-white rounded leading-5'>
                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-violet-100 p-4 rounded leading-5 container'>
                            <div className='   '>
                                <h1 className='font-semibold text-gray-800 text-lg text-center '>Documents Uploaded</h1>
                            </div>
                        </div>
                        {documentList?.map((data) => (
                            <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 p-4 bg-white  rounded leading-5'>
                                <div className='flex  bg-violet-100'>
                                    <h1 className='flex-1 font-semibold text-sm text-gray-600 p-6 uppercase'>{data?.doc_type}</h1>
                                    <h1 className='flex-1 '>
                                        <embed className='w-16 h-16 float-right' src={`http://192.168.0.140:8000/${data?.doc_path}`} />
                                        {/* <img className='w-16 float-right' src={`http://192.168.0.140:8000/${data?.document_path}`} /> */}
                                    </h1>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

            </div>


        </>
    )
}

export default ViewAgencyApplicationFullDetails