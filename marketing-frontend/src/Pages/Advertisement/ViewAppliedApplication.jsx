import axios from 'axios'
import React, { useEffect, useState } from 'react'
import AdvertisementApiList from '../../Compnents/AdvertisementApiList'
import ApiHeader from '../../Compnents/ApiHeader'

function ViewAppliedApplication(props) {

    const { api_getAppliedApplicationDetail } = AdvertisementApiList()

    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600 font-semibold"
    let inputStyle = "mt-6 -ml-7 mb-2 text-sm text-gray-800 text-left font-bold"
    console.log("application data in list application", props.data)

    const [applicationDetail, setapplicationDetail] = useState()

    ///////////{*** GET APPLICATION LIST***}/////////
    useEffect(() => {
        getApplicationDetail()
    }, [])
    const getApplicationDetail = () => {
        const requestBody = {
            id: props?.data,
            deviceId: "selfAdvert",
        }
        axios.post(`${api_getAppliedApplicationDetail}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('ulb list in self advertisement', response.data.data)
                setapplicationDetail(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }
    console.log("details of application...1", applicationDetail)

    return (
        <>
            <div className='overflow-y-scroll h-screen'>
            <div className='col-span-8'>
                <h1 className='text-center p-3 mb-2 bg-violet-200 text-gray-700 font-sans font-semibold shadow-lg border-2 border-white'>APPLICATION FULL DETAILS</h1>
                <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-1 ml-12'>
                    <div className=''>
                        <p className={`${labelStyle}`}>Ulb :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.ulb_name}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Applicant :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.applicant}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Father Name :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.father}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>E-mail :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.email}</span>
                    </div>
                </div>
                <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-1 ml-12 -mt-2'>
                    <div className=''>
                        <p className={`${labelStyle}`}>Residence Address :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.residence_address}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Residence Ward No :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.ward_no}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Permanent Address :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.permanent_address}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Permanent Ward No :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.permanent_ward_no}</span>
                    </div>
                </div>
                <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-1 ml-12 -mt-2'>

                    <div className=''>
                        <p className={`${labelStyle}`}>Mobile No. :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.mobile_no}</span>
                    </div>

                    <div className=''>
                        <p className={`${labelStyle}`}>Aadhar No. :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.aadhar_no}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Entity Name :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.entity_name}</span>
                    </div>

                    <div className=''>
                        <p className={`${labelStyle}`}>Entity Address :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.entity_address}</span>
                    </div>
                </div>
                <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-1 ml-12 -mt-2'>

                    <div className=''>
                        <p className={`${labelStyle}`}>Entity Ward No. :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.entity_ward_no}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Installation Location :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.m_installation_location}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Brand Display Name :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.brand_display_name}</span>
                    </div><div className=''>
                        <p className={`${labelStyle}`}>Holding No. :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.holding_no}</span>
                    </div>
                </div>
                <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-1 ml-12 -mt-2'>

                    <div className=''>
                        <p className={`${labelStyle}`}>Trade License No. :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.trade_license_no}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>GST No. :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.gst_no}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Display Area :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.display_area}</span>
                    </div><div className=''>
                        <p className={`${labelStyle}`}>Display Type :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.m_display_type}</span>
                    </div>
                </div>
                <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-1 ml-12 -mt-2'>
                    <div className=''>
                        <p className={`${labelStyle}`}>License Year :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.m_license_year}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Longitude :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.longitude}</span>
                    </div>
                    <div className=''>
                        <p className={`${labelStyle}`}>Latitude :-</p>
                        <span className={`${inputStyle}`}>{applicationDetail?.latitude}</span>
                    </div>

                </div>
            </div>
            <div className='col-span-4 mb-16'>
                <h1 className='ml-6 font-semibold text-lg text-gray-600 mt-4'> Document</h1>
                <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 p-1 ml-12'>
                    {applicationDetail?.documents?.map((items) => (
                        <div className=''>
                            <p className={`${labelStyle}`}>{items?.document_name}</p>
                            <span className={`${inputStyle}`}><img className='w-20' src={`http://192.168.0.214:8001/${items?.document_path}`}/></span>
                        </div>
                    ))}
                </div>
            </div>
            </div>
          
        </>
    )
}

export default ViewAppliedApplication