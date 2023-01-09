import React, { useEffect, useState } from 'react'
import { useFormik } from 'formik';
import SelfAdvrtInformationScreen from './SelfAdvrtInformationScreen';
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../../src/Compnents/ApiHeader'
import axios from 'axios';
import * as yup from 'yup'

// import SaveIcon from '@mui/icons-material/Save';

function SelfAdvertisementForm1(props) {
    const { api_getAdvertMasterData, api_getUlbList } = AdvertisementApiList()
    const [masterData, setmasterData] = useState()
    const [ulbList, setulbList] = useState()
    const [reviewIdName, setreviewIdName] = useState({})
    const [licenseId, setlicenseId] = useState()
    const [licenseList, setlicenseList] = useState('hidden')
    const [searchByHolding, setsearchByHolding] = useState('hidden')
    // const [showApplication, setshowApplication] = useState('hidden')
    const [holdingNo, setholdingNo] = useState('')
    const [searchByHoldingList, setsearchByHoldingList] = useState('hidden')

    const [dummyList, setdummyList] = useState([
        {
            id: "1",
            tradeLicenseNo: "TRADE123456",
            holdingNo: "HOL / 9 / 2023",
            ownerName: "Anant",
            licenseIssueDate: "09 / 01 / 2023",
        },
        {
            id: "2",
            tradeLicenseNo: "TRADE123497",
            holdingNo: "HOL / 10 / 2023",
            ownerName: "Dev",
            licenseIssueDate: "10 / 01 / 2023",
        },
        {
            id: "3",
            tradeLicenseNo: "TRADE123487",
            holdingNo: "HOL / 11 / 2023",
            ownerName: "Applicant",
            licenseIssueDate: "11 / 01 / 2023",
        },

    ])

    let labelStyle = "mt-6 -ml-6 text-xs text-gray-600"
    let inputStyle = "text-xs rounded leading-5 shadow-md px-1.5 py-1 w-[10rem] md:w-[13rem] h-6 md:h-8  mt-5 -ml-2 "

    const validationSchema = yup.object({
        ulb: yup.string().required('select ulb'),
        licenseYear: yup.string().required('select license year'),
        applicantName: yup.string().required('Enter owner name').max(50, 'Enter maximum 50 characters'),
        fatherName: yup.string().required('Enter owner name').max(50, 'Enter maximum 50 characters'),
        email: yup.string(),
        residenceAddress: yup.string().required('This field is Required'),
        residenceWardNo: yup.string().required('This field is Required'),
        permanentAddress: yup.string().required('This field is Required'),
        permanentWardNo: yup.string().required('This field is Required'),
        mobileNo: yup.string().required('Enter mobile no.').min(10, 'Enter 10 digit number').max(10, 'Enter 10 digit number'),
        aadharNo: yup.string().required('This field is Required'),
        entityName: yup.string().required('This field is Required'),
        entityAddress: yup.string().required('This field is Required'),
        entityWardNo: yup.string().required('This field is Required'),
        installationLocation: yup.string().required('This field is Required'),
        brandDisplayName: yup.string().required('This field is Required'),
        holdingNo: yup.string().required('This field is Required'),
        tradeLicenseNo: yup.string().required('This field is Required'),
        gstNo: yup.string().required('This field is Required'),
        displayArea: yup.string().required('This field is Required'),
        displayType: yup.string().required('This field is Required'),
        longitude: yup.string().required('This field is Required'),
        latitude: yup.string().required('This field is Required'),
    })

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
        aadharNo: '',
        entityName: '',
        entityAddress: '',
        entityWardNo: '',
        installationLocation: '',
        brandDisplayName: '',
        holdingNo: '',
        tradeLicenseNo: licenseId,
        gstNo: '',
        displayArea: '',
        displayType: '',
        longitude: '',
        latitude: '',
    }

    const formik = useFormik({
        initialValues: initialValues,
        enableReinitialize: true,
        onSubmit: values => {
            // alert(JSON.stringify(values, null, 2));

            console.log("self Advertisement", values)
            props.collectFormDataFun('selfAdvertisement', values, reviewIdName)
            props?.nextFun(1)

        },
        validationSchema
    });

    const handleOnChange = (e) => {
        console.log("target type", e.target.type)
        console.log("check box name", e.target.name)
        // console.log('input type', e.target[e.target.selectedIndex].text)

        let name = e.target.name
        let value = e.target.value

        { name == 'tradeLicenseNo' && formik.setFieldValue("tradeLicenseNo", formik.values.tradeLicenseNo == null ? setshowApplication('hidden') : setshowApplication('')) }

        { name == 'ulb' && getMasterDataFun(value) }
        console.log("ulb id...", value)

        // {****** collection names By Id ******}//
        if (e.target.type == 'select-one') {
            setreviewIdName({ ...reviewIdName, [name]: e.target[e.target.selectedIndex].text })
        }
        else {
            setreviewIdName({ ...reviewIdName, [name]: value })
        }
    };

    console.log("review name by id in form", reviewIdName)

    const getLicenseData = (licenseId) => {
        console.log("license Id", licenseId)
        setlicenseId(licenseId)
        setlicenseList('hidden')
        // searchLicenseByHolding('hidden')
        setsearchByHolding('hidden')

    }
    console.log("license data...", licenseId)



    const showTradeLicenseList = () => {
        licenseList == 'hidden' ? setlicenseList('') : setlicenseList('hidden')
    }

    const findHolding = () => {
        showTradeLicenseList()
        searchByHolding == 'hidden' ? setsearchByHolding('') : setsearchByHolding('hidden')

    }

    const searchLicenseByHolding = () => {
        console.log("holding no.", holdingNo)
        setsearchByHoldingList('')

    }
    // console.log("holding no.", holdingNo)

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
            deviceId: "selfAdvert",
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

    console.log(" master data...", masterData)

    return (
        <>
            {/* trade license list */}
            <div className={`${licenseList} mt-5 bg-white p-2 w-11/12 mx-auto`}>
                <h1 className='ml-10 p-2 font-semibold text-gray-700'>Your Licenses</h1>
                <table class="table-auto text-slate-700 w-11/12 mx-auto border ">
                    <thead>
                        <tr className="bg-violet-200 text-gray-600 text-xs h-8 hover:bg-violet-200 uppercase">
                            <th>Trade License No.</th>
                            <th>Holding No.</th>
                            <th>Owner Name</th>
                            <th>License Issue Date</th>
                            <th>select</th>
                        </tr>
                    </thead>
                    <tbody>
                        {dummyList?.map((items) => (
                            <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                <td className={`${labelStyle}`}>{items?.tradeLicenseNo}</td>
                                <td className={`${labelStyle}`} >{items?.holdingNo}</td>
                                <td className={`${labelStyle}`}>{items?.ownerName}</td>
                                <td className={`${labelStyle}`}>{items?.licenseIssueDate}</td>
                                {/* <td><input type='checkbox' name='check' onChange={() => getLicenseData(items.id)} /></td> */}
                                <td><input type='checkbox' name='check' onChange={() => getLicenseData(items?.tradeLicenseNo)} /></td>
                            </tr>
                        ))}


                    </tbody>
                </table>
                <button className='text-xs mt-2 ml-12 bg-indigo-500 hover:bg-indigo-600 px-2 py-1 text-white rounded leading-5' onClick={findHolding}>Not In List</button>
            </div>

            {/* enter holding no. */}
            <div className={`${searchByHolding}   p-2 w-11/12 mx-auto`}>
                <div className='flex flex-row  ml-3'>
                    <h1 className={`${labelStyle} lg:text-center md:text-center `}>Enter Holding No.</h1>
                    <input name='holdingField' className={`ml-2 h-6 md:h-8 w-[10rem] md:w-[13rem] mt-4 bg-white rounded-l leading-5 shadow-md text-xs px-2`} placeholder='Enter Holding No.' onChange={(e) => setholdingNo(e.target.value)} />
                    <button type='button' className=' text-xs mt-4 bg-indigo-500 px-2 h-8  rounded-r leading-5 text-white' onClick={searchLicenseByHolding}>search</button>
                </div>
                <div className={`${searchByHoldingList} mt-4`}>
                    <table class="table-auto text-slate-700 w-11/12 mx-auto border ">
                        <thead>
                            <tr className="bg-violet-200 text-gray-600 text-xs h-8 hover:bg-violet-200 uppercase">
                                <th>Trade License No.</th>
                                <th>Holding No.</th>
                                <th>Owner Name</th>
                                <th>License Issue Date</th>
                                <th>select</th>
                            </tr>
                        </thead>
                        <tbody>
                            {dummyList?.map((items) => (
                                <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                    <td className={`${labelStyle}`}>{items?.tradeLicenseNo}</td>
                                    <td className={`${labelStyle}`} >{items?.holdingNo}</td>
                                    <td className={`${labelStyle}`}>{items?.ownerName}</td>
                                    <td className={`${labelStyle}`}>{items?.licenseIssueDate}</td>

                                    <td><input type='checkbox' name='check' onChange={() => getLicenseData(items?.tradeLicenseNo)} /></td>
                                </tr>
                            ))}


                        </tbody>
                    </table>
                </div>
                {/* <button className='text-xs mt-2 ml-12 bg-indigo-500 hover:bg-indigo-600 px-2 py-1 text-white rounded leading-5 '>Not In List</button> */}
            </div>

            <form onSubmit={formik.handleSubmit} onChange={handleOnChange}>
                <div>
                    <div class="flex flex-wrap flex-row w-7/12 ">
                        <div class="flex-shrink max-w-full px-4 w-full lg:w-1/3">
                            <p className={`${labelStyle} lg:text-center md:text-center`}>Trade License No<span className='text-red-600'> *</span></p>
                        </div>
                        <div class="flex flex-row max-w-full px-4 w-2/12 lg:w-1/3 ">
                            <input type="text" name='tradeLicenseNo' placeholder='' className={`h-6 md:h-8 w-[10rem] md:w-[13rem] mt-4 bg-white rounded-l leading-5 shadow-md text-xs px-2 -ml-20 bg-gray-50`} disabled
                                onChange={formik.handleChange}
                                value={formik.values.tradeLicenseNo}
                            />
                            <button className='text-xs mt-4 bg-indigo-500 px-2 h-8  rounded-r leading-5 text-white shadow-md' onClick={showTradeLicenseList}>{formik.values.tradeLicenseNo == null ? "Find" : "Apply"}</button>
                            {/* <p className='text-red-500 text-xs absolute'>{formik.touched.tradeLicenseNo && formik.errors.tradeLicenseNo ? formik.errors.tradeLicenseNo : null}</p> */}

                        </div>
                    </div>

                    {formik.values.tradeLicenseNo != null &&
                        < div className={``}>
                            <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto pb-8 p-2 mt-3'>
                                <div className='col-span-4  border border-dashed border-violet-800'>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}> Ulb <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('ulb')} >
                                                <option>select </option>
                                                {ulbList?.map((items) => (
                                                    <option value={items?.id}>{items?.ulb_name}</option>
                                                ))}
                                            </select>
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.ulb && formik.errors.ulb ? formik.errors.ulb : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}> License Year <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('licenseYear')} >
                                                <option>select </option>
                                                {masterData?.paramCategories?.LicenseYear?.map((items) => (
                                                    <option value={items?.id}>{items?.string_parameter}</option>
                                                ))}
                                            </select>
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.licenseYear && formik.errors.licenseYear ? formik.errors.licenseYear : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Applicant <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
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
                                        <div className='col-span-2'>
                                            <input type="text" name='fatherName' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.fatherName}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.fatherName && formik.errors.fatherName ? formik.errors.fatherName : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>E-mail <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='email' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.email}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.email && formik.errors.email ? formik.errors.email : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Residence Address <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='residenceAddress' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.residenceAddress}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.residenceAddress && formik.errors.residenceAddress ? formik.errors.residenceAddress : null}</p>
                                        </div>
                                    </div>

                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Ward No <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select {...formik.getFieldProps('residenceWardNo')} className={`${inputStyle} bg-white`} >
                                                <option>select </option>
                                                {masterData?.wards?.map((items) => (
                                                    <option value={items?.id}>{items?.ward_name}</option>
                                                ))}
                                            </select>
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.residenceWardNo && formik.errors.residenceWardNo ? formik.errors.residenceWardNo : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Permanent Address <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='permanentAddress' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.permanentAddress}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.permanentAddress && formik.errors.permanentAddress ? formik.errors.permanentAddress : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Ward No <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select  {...formik.getFieldProps('permanentWardNo')} className={`${inputStyle} bg-white`} >
                                                <option>select </option>
                                                {masterData?.wards?.map((items) => (
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
                                        <div className='col-span-2'>
                                            <input type="text" name='mobileNo' placeholder='' className={`${inputStyle} `}
                                                onChange={formik.handleChange}
                                                value={formik.values.mobileNo}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.mobileNo && formik.errors.mobileNo ? formik.errors.mobileNo : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Aadhar No <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='aadharNo' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.aadharNo}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.aadharNo && formik.errors.aadharNo ? formik.errors.aadharNo : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8 mb-6'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Entity Name<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.entityName}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.entityName && formik.errors.entityName ? formik.errors.entityName : null}</p>
                                        </div>
                                    </div>

                                </div>
                                <div className='col-span-4 p-1 border border-dashed border-violet-800'>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Entity Address<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
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
                                        <div className='col-span-2'>
                                            <select type="text" name='entityWardNo' placeholder='' className={`${inputStyle} bg-white`}{...formik.getFieldProps('entityWardNo')} >
                                                <option>select </option>
                                                {masterData?.wards?.map((items) => (
                                                    <option value={items?.id}>{items?.ward_name}</option>
                                                ))}
                                            </select>
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.entityWardNo && formik.errors.entityWardNo ? formik.errors.entityWardNo : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Installation Location <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select {...formik.getFieldProps('installationLocation')} className={`${inputStyle} bg-white`} >
                                                <option>select </option>
                                                {masterData?.paramCategories?.InstallationLocation?.map((items) => (
                                                    <option value={items?.id}>{items?.string_parameter}</option>
                                                ))}
                                            </select>
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.installationLocation && formik.errors.installationLocation ? formik.errors.installationLocation : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Brand Display Name<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='brandDisplayName' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.brandDisplayName}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.brandDisplayName && formik.errors.brandDisplayName ? formik.errors.brandDisplayName : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Holding No.<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='holdingNo' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.holdingNo}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.holdingNo && formik.errors.holdingNo ? formik.errors.holdingNo : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Trade License No<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='tradeLicenseNo' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.tradeLicenseNo}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.tradeLicenseNo && formik.errors.tradeLicenseNo ? formik.errors.tradeLicenseNo : null}</p>
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
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.gstNo && formik.errors.gstNo ? formik.errors.gstNo : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Display Area<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='displayArea' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.displayArea}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.displayArea && formik.errors.displayArea ? formik.errors.displayArea : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Display Type<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select {...formik.getFieldProps('displayType')} className={`${inputStyle} bg-white`} >
                                                <option>select </option>
                                                {masterData?.paramCategories?.DisplayType?.map((items) => (
                                                    <option value={items?.id}>{items?.string_parameter}</option>
                                                ))}
                                            </select>
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.displayType && formik.errors.displayType ? formik.errors.displayType : null}</p>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Longitude  <span className='text-red-600'>*</span></p>
                                        </div>
                                        <div className='col-span-2'>
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
                                        <div className='col-span-2'>
                                            <input type="text" name='latitude' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.latitude}
                                            />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.latitude && formik.errors.latitude ? formik.errors.latitude : null}</p>
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
        </>
    )
}

export default SelfAdvertisementForm1