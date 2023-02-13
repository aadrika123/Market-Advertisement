import React, { useEffect, useState } from 'react'
import { useFormik } from 'formik';
import ApiHeader from '../../../Compnents/ApiHeader'
import axios from 'axios';
import * as yup from 'yup'
import Modal from 'react-modal';
import SelfAdvrtInformationScreen from '../../Advertisement/SelfAdvertisement/SelfAdvrtInformationScreen';
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';


const customStyles = {
    content: {
        top: "50%",
        left: "50%",
        right: "auto",
        bottom: "auto",
        marginRight: "-50%",
        transform: "translate(-50%, -50%)",
        background: "transparent",
        border: "none",
    },
};
Modal.setAppElement('#root');
function BanquetMarriageHallForm(props) {

    const { setFormIndex, showLoader, collectFormDataFun, toastFun } = props?.values

    const { api_getAdvertMasterData, api_getUlbList, api_getWardList, api_getTradeLicenseDetails } = AdvertisementApiList()
    const [masterData, setmasterData] = useState()
    const [ulbList, setulbList] = useState()
    const [wardList, setwardList] = useState()
    const [reviewIdName, setreviewIdName] = useState({})
    const [storeUlbValue, setstoreUlbValue] = useState()

    const [licenseId, setlicenseId] = useState()
    const [liceneData, setlicenseData] = useState()
    const [liceneDetails, setlicenseDetails] = useState()

    const [modalIsOpen, setIsOpen] = useState(true);
    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }

    let labelStyle = "mt-6 -ml-6 text-xs text-gray-600"
    let inputStyle = "text-xs rounded leading-5 shadow-md px-1.5 py-1 w-full h-6 md:h-8  mt-5 "

    // const validationSchema = yup.object({
    //     ulb: yup.string().required('select ulb'),
    //     licenseYear: yup.string().required('select license year'),
    //     applicantName: yup.string().required('Enter owner name').max(50, 'Enter maximum 50 characters'),
    //     fatherName: yup.string().required('Enter owner name').max(50, 'Enter maximum 50 characters'),
    //     email: yup.string(),
    //     residenceAddress: yup.string().required('This field is Required'),
    //     residenceWardNo: yup.string().required('This field is Required'),
    //     permanentAddress: yup.string().required('This field is Required'),
    //     permanentWardNo: yup.string().required('This field is Required'),
    //     mobileNo: yup.string().required('Enter mobile no.').min(10, 'Enter 10 digit number').max(10, 'Enter 10 digit number'),
    //     aadharNo: yup.string().required('Enter aadhar').min(12, 'Enter 12 digit number').max(12, 'Enter 12 digit number'),
    //     entityName: yup.string().required('This field is Required'),
    //     entityAddress: yup.string().required('This field is Required'),
    //     entityWardNo: yup.string().required('This field is Required'),
    //     installationLocation: yup.string().required('This field is Required'),
    //     brandDisplayName: yup.string().required('This field is Required'),
    //     // holdingNo: yup.string().required('This field is Required'),
    //     // tradeLicenseNo: yup.string().required('This field is Required'),
    //     gstNo: yup.string().required('This field is Required'),
    //     displayArea: yup.string().required('Enter Number'),
    //     displayType: yup.string().required('This field is Required'),
    //     longitude: yup.string().required('Enter Number'),
    //     latitude: yup.string().required('Enter Number'),
    // })

    const initialValues = {
        ulb: '',
        licenseYear: '',
        applicantName: '',
        fatherName: '',
        email: '',
        residenceAddress: '',
        residenceWardNo: '',
        permanentAddress: '',
        permanentWardNo: '',
        mobileNo: '',
        entityName: '',
        entityAddress: '',
        entityWardNo: '',
        holdingNo: '',
        tradeLicenseNo: '',
        longitude: '',
        latitude: '',
        organizationType: '',
        landDeedType: '',
        waterSupplyType: '',
        electricityType: '',
        securityType: '',
        noOfBeds: '',
        noOfRooms: '',
        noOfCctv: '',
        noFireExtinguishers: '',
        noEntryGates: '',
        noExitGate: '',
        noTwoWheelerParking: '',
        noFourWheelerParking: '',
        aadharNo: '',
        panNo: '',
    }

    const formik = useFormik({
        initialValues: initialValues,
        onSubmit: values => {
            console.log("self Advertisement", values)
            collectFormDataFun('selfAdvertisement', values, reviewIdName)
            setFormIndex(2)
        },
        // validationSchema
    });

    const handleOnChange = (e) => {
        console.log("target type", e.target.type)
        console.log("check box name", e.target.name)

        let name = e.target.name
        let value = e.target.value

        // { name == 'tradeLicenseNo' && formik.setFieldValue("tradeLicenseNo", formik.values.tradeLicenseNo) }

        { name == 'ulb' && getMasterDataFun(value) }
        { name == 'ulb' && getWardListFun(value) }

        { name == 'ulb' && setstoreUlbValue(value) }
        console.log("ulb id 1 ...", value)


        // {****** collection names By Id ******}//
        if (e.target.type == 'select-one') {
            setreviewIdName({ ...reviewIdName, [name]: e.target[e.target.selectedIndex].text })
        }
        else {
            setreviewIdName({ ...reviewIdName, [name]: value })
        }
    };

    console.log("review name by id in form", reviewIdName)
    console.log("loader", props.showLoader)

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

    console.log("ulb value...", storeUlbValue)

    ///////////{*** GETTING WARD LIST***}/////////
    const getWardListFun = (ulbId) => {
        showLoader(true);
        const requestBody = {
            // ulbId: ulbId,
            ulbId: 2,
        }
        axios.post('http://192.168.0.16:8000/api/workflow/getWardByUlb', requestBody, ApiHeader())
            .then(function (response) {
                console.log('ward list', response.data.data)
                setwardList(response.data.data)
                setTimeout(() => {
                    showLoader(false);
                }, 500);
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                setTimeout(() => {
                    showLoader(false);
                }, 500);
            })
    }
    console.log("ward master data...", wardList)

    ///////////{*** GETTING MASTER DATA***}/////////
    const getMasterDataFun = (ulbId) => {
        showLoader(true);
        const requestBody = {
            // ulbId: ulbId,
            ulbId: 1,

            deviceId: "selfAdvert",
        }
        axios.post(`${api_getAdvertMasterData}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('master data for self advertisement', response.data.data)
                setmasterData(response.data.data)
                setTimeout(() => {
                    showLoader(false);
                }, 500);
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                setTimeout(() => {
                    showLoader(false);
                }, 500);
            })
    }

    console.log(" master data...", masterData)
    console.log(" License full data", liceneData?.licenseDataById)

    return (
        <>
            <div className='absolute w-full top-0 bg-violet-50'>
                <form onSubmit={formik.handleSubmit} onChange={handleOnChange}>
                    <div>
                        <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 w-10/12  container mx-auto '>
                            <div className='col-span-12 '>
                                {storeUlbValue == undefined ?
                                    <>
                                        <h1 className='text-center text-xl font-semibold text-gray-500 p-14'>
                                            Registration of a <span className='text-gray-700 font-bold'>Dharamshala</span> is mendatory under the urban local body it falls in.
                                            If you are the owner of such a property, it need to be <span className='text-gray-700 font-bold'> registered and licensed properly.</span>
                                        </h1>


                                        <div className='w-96 h-64 mx-auto'>
                                            <div className='p-8 mt-6 bg-white w-80 h-64 mx-auto shadow-md shadow-violet-300 rounded leading-5'>
                                                <h1 className='text-left  text-gray-500 text-2xl uppercase font-bold mt-6'>select your <span className=''>ULB</span> </h1>
                                                <h1 className='text-left mt-2 text-gray-500 text-xs mb-2'>We will proceed this application based on your ulb</h1>
                                                <div className=' '>
                                                    <select className={`${inputStyle} border bg-white`} {...formik.getFieldProps('ulb')} >
                                                        <option>select </option>
                                                        {ulbList?.map((items) => (
                                                            <option value={items?.id}>{items?.ulb_name}</option>
                                                        ))}
                                                    </select>

                                                </div>
                                            </div>
                                            <div className='animate-wiggle'>
                                                <img src='https://cdn-icons-png.flaticon.com/512/7955/7955999.png' className='h-24 ' />
                                            </div>
                                        </div>
                                    </>
                                    :
                                    <div className='flex flex-row mt-4 space-x-2  '>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6 mt-1 text-gray-500">
                                            <path d="M3.288 4.819A1.5 1.5 0 001 6.095v7.81a1.5 1.5 0 002.288 1.277l6.323-3.905c.155-.096.285-.213.389-.344v2.973a1.5 1.5 0 002.288 1.276l6.323-3.905a1.5 1.5 0 000-2.553L12.288 4.82A1.5 1.5 0 0010 6.095v2.973a1.506 1.506 0 00-.389-.344L3.288 4.82z" />
                                        </svg>

                                        <h1 className='text-md text-gray-600 font-semibold '> you have selected ulb <span className='text-lg font-bold   uppercase underline px-2 text-md d md:text-xl text-violet-500'>{reviewIdName.ulb}</span> to proceed with the application.</h1>
                                    </div>
                                }
                            </div>
                        </div>
                        {storeUlbValue != undefined &&
                            <div className={``}>
                                <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto pb-8 p-2 mt-3'>
                                    <div className='col-span-4  border border-dashed border-violet-800'>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}> License Year <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('licenseYear')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.licenseYear?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.licenseYear && formik.errors.licenseYear ? formik.errors.licenseYear : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Applicant Name<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='applicantName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.applicantName}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.applicantName && formik.errors.applicantName ? formik.errors.applicantName : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Father Name<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='fatherName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.fatherName}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.fatherName && formik.errors.fatherName ? formik.errors.fatherName : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Residence Address <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='residenceAddress' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.residenceAddress}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.residenceAddress && formik.errors.residenceAddress ? formik.errors.residenceAddress : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Residence Ward No  <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('residenceWardNo')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.residenceWardNo?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.residenceWardNo && formik.errors.residenceWardNo ? formik.errors.residenceWardNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Permanent Address<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='permanentAddress' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.permanentAddress}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.permanentAddress && formik.errors.permanentAddress ? formik.errors.permanentAddress : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Permanent Ward No <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select {...formik.getFieldProps('permanentWardNo')} className={`${inputStyle} bg-white`} >
                                                    <option>select </option>
                                                    {wardList?.map((items) => (
                                                        <option value={items?.id}>{items?.ward_name}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.permanentWardNo && formik.errors.permanentWardNo ? formik.errors.permanentWardNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Mobile No.<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='mobileNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.mobileNo}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.mobileNo && formik.errors.mobileNo ? formik.errors.mobileNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>E-mail<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='email' placeholder='' className={`${inputStyle} `}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.email}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.email && formik.errors.email ? formik.errors.email : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Aadhar Card No <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='aadharNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.aadharNo}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.aadharNo && formik.errors.aadharNo ? formik.errors.aadharNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Pan Card No <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='panNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.panNo}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.panNo && formik.errors.panNo ? formik.errors.panNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8 '>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Entity Name<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.entityName}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.entityName && formik.errors.entityName ? formik.errors.entityName : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Entity Address<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='entityAddress' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.entityAddress}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.entityAddress && formik.errors.entityAddress ? formik.errors.entityAddress : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Entity Ward No <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select type="text" name='entityWardNo' placeholder='' className={`${inputStyle} bg-white`}{...formik.getFieldProps('entityWardNo')}  >
                                                    <option>select </option>
                                                    {wardList?.map((items) => (
                                                        <option value={items?.id}>{items?.ward_name}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.entityWardNo && formik.errors.entityWardNo ? formik.errors.entityWardNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8 '>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Holding No <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='holdingNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.holdingNo}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.holdingNo && formik.errors.holdingNo ? formik.errors.holdingNo : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8 mb-6'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Trade License No<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='tradeLicenseNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.tradeLicenseNo}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.tradeLicenseNo && formik.errors.tradeLicenseNo ? formik.errors.tradeLicenseNo : null}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 p-1 border border-dashed border-violet-800'>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Longitude  <span className='text-red-600'>*</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='longitude' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.longitude}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.longitude && formik.errors.longitude ? formik.errors.longitude : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Latitude<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="text" name='latitude' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.latitude}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.latitude && formik.errors.latitude ? formik.errors.latitude : null}</p>
                                            </div>
                                        </div>

                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Organization Type<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('organizationType')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.organizationType?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.organizationType && formik.errors.organizationType ? formik.errors.organizationType : null}</p>
                                            </div>
                                        </div>

                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Land Deed Type<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('landDeedType')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.landDeedType?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.landDeedType && formik.errors.landDeedType ? formik.errors.landDeedType : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Water Supply Type<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('waterSupplyType')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.waterSupplyType?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>

                                                <p className='text-red-500 text-xs absolute'>{formik.touched.waterSupplyType && formik.errors.waterSupplyType ? formik.errors.waterSupplyType : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Electricity Type<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('electricityType')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.electricityType?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.electricityType && formik.errors.electricityType ? formik.errors.electricityType : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>Security Type<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('securityType')} >
                                                    <option>select </option>
                                                    {masterData?.paramCategories?.securityType?.map((items) => (
                                                        <option value={items?.id}>{items?.string_parameter}</option>
                                                    ))}
                                                </select>
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.securityType && formik.errors.securityType ? formik.errors.securityType : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No of Beds<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noOfBeds' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noOfBeds}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noOfBeds && formik.errors.noOfBeds ? formik.errors.noOfBeds : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No of Rooms<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noOfRooms' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noOfRooms}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noOfRooms && formik.errors.noOfRooms ? formik.errors.noOfRooms : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No of CCTV Camera<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noOfCctv' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noOfCctv}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noOfCctv && formik.errors.noOfCctv ? formik.errors.noOfCctv : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No. of Fire Extinguishers<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noFireExtinguishers' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noFireExtinguishers}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noFireExtinguishers && formik.errors.noFireExtinguishers ? formik.errors.noFireExtinguishers : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No. of Entry Gates<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noEntryGates' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noEntryGates}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noEntryGates && formik.errors.noEntryGates ? formik.errors.noEntryGates : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No. of Exit Gates<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noExitGate' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noExitGate}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noExitGate && formik.errors.noExitGate ? formik.errors.noExitGate : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No. of TWo Wheelers Parking Space<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noTwoWheelerParking' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noTwoWheelerParking}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noTwoWheelerParking && formik.errors.noTwoWheelerParking ? formik.errors.noTwoWheelerParking : null}</p>
                                            </div>
                                        </div>
                                        <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                            <div className='col-span-1'>
                                                <p className={`${labelStyle}`}>No. of Four Wheelers Parking Space<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2 mr-2 '>
                                                <input type="number" name='noFourWheelerParking' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.noFourWheelerParking}
                                                />
                                                <p className='text-red-500 text-xs absolute'>{formik.touched.noFourWheelerParking && formik.errors.noFourWheelerParking ? formik.errors.noFourWheelerParking : null}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 hidden md:block lg:block'>
                                        <div className='-mt-20'>
                                            <SelfAdvrtInformationScreen />
                                        </div>
                                    </div>
                                </div>
                                <div className=' '>
                                    <div className='float-right p-2'>
                                        <button type="submit" className="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next</button>
                                    </div>
                                </div>
                            </div>
                        }
                    </div>

                </form>
            </div>
        </>
    )
}

export default BanquetMarriageHallForm