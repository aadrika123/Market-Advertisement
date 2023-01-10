import React, { useEffect, useState } from 'react'
import { useFormik } from 'formik';
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import SelfAdvrtInformationScreen from '../SelfAdvertisement/SelfAdvrtInformationScreen';
import ApiHeader from '../../../../src/Compnents/ApiHeader'
import axios from 'axios';
function PrivateLandForm(props) {

    const { api_getAdvertMasterData, api_getUlbList } = AdvertisementApiList()
    const [masterData, setmasterData] = useState()
    const [ulbList, setulbList] = useState()

    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600"
    let inputStyle = "text-xs rounded-md shadow-md px-1.5 py-1 w-[10rem] md:w-[13rem] h-6 md:h-8  mt-5 -ml-2 "

    const formik = useFormik({
        initialValues: {
            ulb: '',
            entityType: '',
            entityName: '',
            address: '',
            mobileNo: '',
            officialTelephone: '',
            fax: '',
            email: '',
            panNo: '',
            gstNo: '',
            blacklisted: false,
            pendingCourtCase: false,
            pendingAmount: '',

        },
        onSubmit: values => {
            // alert(JSON.stringify(values, null, 2));
            console.log("agency", values)
            props.collectFormDataFun('agency', values)
            props?.nextFun(1)
        },
    });


    const handleChange = (e) => {
        let name = e.target.name
        let value = e.target.value

        { name == 'ulb' && getMasterDataFun(value) }
        console.log("ulb id...", value)
    }

    ///////////{*** GETTING ULB DATA***}/////////
    useEffect(() => {
        getUlbListData()
    }, [])
    const getUlbListData = () => {
        axios.get('http://192.168.0.16:8000/api/get-all-ulb', ApiHeader())
            .then(function (response) {
                console.log('ulb list in self advertisement', response.data.data)
                setulbList(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }
    console.log("ulb list", ulbList)

    ///////////{*** GETTING MASTER DATA***}/////////
    const getMasterDataFun = (ulbId) => {
        const requestBody = {
            // ulbId: ulbId,
            ulbId: 1,
            deviceId: "privateLand",
        }
        axios.post(`${api_getAdvertMasterData}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('master data for self advertisement', response.data.data)
                setmasterData(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }

    console.log(" master data  ...", masterData)

    return (
        <>
            <form onSubmit={formik.handleSubmit} onChange={handleChange}>
                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto '>
                    <div className='col-span-8'>
                        <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4  container  mx-auto pb-8 p-2 mt-3'>
                            {/* DETAILS */}
                            <div className='col-span-6 p-1 h-72 border border-dashed border-violet-800'>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}> Ulb <span className='text-red-600'>*</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('ulb')} >
                                            <option>select </option>
                                            {ulbList?.map((items) => (
                                                <option value={items?.id}>{items?.ulb_name}{items?.id}</option>
                                            ))}
                                        </select>
                                        {/* <p className='text-red-500 text-xs absolute'>{formik.touched.ulb && formik.errors.ulb ? formik.errors.ulb : null}</p> */}
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle} `}> Entity Type  <span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <select {...formik.getFieldProps('entityType')} className={`${inputStyle} bg-white`} >
                                            <option>select</option>
                                            {masterData?.paramCategories?.EntityType?.map((items) => (
                                                <option value={items?.id}>{items?.string_parameter}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Entity Name <span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.entityName}
                                        />

                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}> Address<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='address' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.address}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>PAN No.<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='panNo' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.panNo}
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className='col-span-6 p-1 h-72 border border-dashed border-violet-800'>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Email<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='email' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.email}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Mobile No<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='mobileNo' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.mobileNo}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Official Telephone<span className='text-red-600'>*</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='officialTelephone' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.officialTelephone}
                                        />
                                    </div>
                                </div>

                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>FAX<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='fax' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.fax}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>GST No. <span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='gstNo' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.gstNo}
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* OTHER INFORMATION */}

                        <h1 className='px-5 bg-white '>Other Information</h1>
                        <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12  container  mx-auto -mt-6 pb-8 p-2 border border-dashed border-violet-800 '>

                            <div className='col-span-4'>
                                <div className=' flex flex-row'>
                                    <div className=' '>
                                        <p className={`${labelStyle} ml-6`}>Blacklisted in RMC <span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className=' mt-4 ml-2 '>
                                        <input type="checkbox" name='blacklisted' placeholder='' className={` flex-1`}

                                            onChange={formik.handleChange}
                                            value={formik.values.blacklisted}
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className='col-span-4'>
                                <div className=' flex flex-row'>
                                    <div className=''>
                                        <p className={`${labelStyle}  ml-6`}>Pending Court Case<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className=' mt-4 ml-2 '>
                                        <input type="checkbox" name='pendingCourtCase' placeholder='' className={` flex-1`}
                                            onChange={formik.handleChange}
                                            value={formik.values.pendingCourtCase}
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className='col-span-4'>
                                <div className=' '>
                                    <div className=''>
                                        <p className={`${labelStyle} ml-6 `}>Pending Amount (If any)<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='-mt-3 ml-2'>
                                        <input type="text" name='pendingAmount' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.pendingAmount}
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className='float-right p-2'>
                            <button type="submit" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next</button>

                        </div>


                    </div>
                    <div className='col-span-4 hidden lg:block md:block'>
                        <div className='-mt-16'>
                            <SelfAdvrtInformationScreen />
                        </div>
                    </div>
                </div>

            </form>
        </>
    )
}

export default PrivateLandForm