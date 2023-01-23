import axios from 'axios'
import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import AdvertisementApiList from '../../Compnents/AdvertisementApiList'
import ApiHeader from '../../Compnents/ApiHeader'
import Loader from './Loader'
// import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
// import ApiHeader from '../../../../src/Compnents/ApiHeader'


function FindTradeLicense(props) {

    const { api_getTradeLicenseByUserId, api_getTradeLicenseByHolding } = AdvertisementApiList()

    const [licenseList, setlicenseList] = useState('hidden')
    const [findByHolding, setfindByHolding] = useState('hidden')
    const [searchByHoldingList, setsearchByHoldingList] = useState('hidden')
    const [searchTradeLicense, setsearchTradeLicense] = useState()
    const [tradeLicenseList, settradeLicenseList] = useState()
    const [tradeLicenseListByHolding, settradeLicenseListByHolding] = useState()
    const [tradeInput, settradeInput] = useState(false)


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

    const navigate = useNavigate()
    const handleList = () => {
        licenseList == 'hidden' ? setlicenseList('') : setlicenseList('hidden')
        getTradeLicenseByHolding()
    }
    const searchHolding = () => {
        setsearchByHoldingList('');
    }

    const searchLicenseNo = () => {
        // setfindByHolding('')
        findByHolding == 'hidden' ? setfindByHolding('') : setfindByHolding('hidden')

    }
    const getLicenseData = (licenseId) => {
        console.log("license Id", licenseId)
        setsearchTradeLicense(licenseId)
    }

    const cancelModal = () => {
        props.closeFun(false)
        navigate('/advertDashboard')
    }

    const confirmTradeLicense = () => {
        props.collectDataFun('licenseDataById', searchTradeLicense)
        props.closeFun(false)
    }

    const showTradeInput = () => {
        tradeInput == false ? settradeInput(true) : settradeInput(false)
    }

    console.log("trade input state..", tradeInput)

    ///////////{*** user id will be from citizen login ***}/////////
    let userId = 214

    ///////////{*** trade license list bby user id***}/////////
    useEffect(() => {
        getTradeLicenseList()
    }, [userId])
    const getTradeLicenseList = () => {
        props.showLoader(true);
        const requestBody = {
            user_id: userId,
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getTradeLicenseByUserId}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('trade license list', response.data.data)
                settradeLicenseList(response.data.data)
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

    ///////////{*** trade license list by holding no***}/////////

    const getTradeLicenseByHolding = () => {
        props.showLoader(true);
        const requestBody = {
            holding_no: "hol12345",
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getTradeLicenseByHolding}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('trade license list by holding', response.data.data)
                settradeLicenseListByHolding(response.data.data)
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
    console.log("trade license id in state...", searchTradeLicense)
    console.log("confirm trade license...", searchTradeLicense)
    console.log("trade license list in state 2...", tradeLicenseList)
    console.log("trade license list by holding in state 2...", tradeLicenseListByHolding)
    console.log("trade license search state", findByHolding)

    return (
        <>

            <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12  gap-2 p-2 pb-2 bg-white h-[35rem] '>
                <div className='hidden md:block lg:block col-span-5 border border-violet-400 border-dashed '>
                    <h1 className='p-2 mt-4 font-bold text-center text-2xl text-gray-700'>Trade license is required in order to apply for advertisement</h1>
                    <h1 className='p-2 text-gray-700 text-center text-md'>Select one of the license from the <span className='font-bold  '>list </span> or you can search your trade license by <span className='font-bold'>Entering Holding no.</span>
                        If you want to proceed further with another trade license then <span className='font-bold'>Enter your trade license no.</span></h1>
                    <div>
                        <img src='https://img.freepik.com/free-vector/team-leader-teamwork-concept_74855-6671.jpg?w=740&t=st=1674021837~exp=1674022437~hmac=8d7b7d83c570fe3b2a3e943cc6b7e3e99d79e2712c19c4c2eb5c8da0cd8b3eb7' className='h-[20rem]  mx-auto' />
                    </div>
                </div>

                <div className='col-span-7 '>

                    <button className=' float-right mt-2 mr-2' onClick={cancelModal}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white bg-red-400 rounded-full shadow-lg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    {/* trade license list */}
                    {/* <Loader showLoader={props.showLoader} /> */}
                    <div className={` w-11/12 mx-auto pb-12 `}>
                        <div className='flex mt-2 border-b bg-violet-200  border-violet-400 px-3 py-2'>
                            <div className='flex-1 '>
                                <h1 className='font-semibold text-gray-700'>Your Licenses</h1>
                                <h1 className='font-semibold text-gray-500 text-xs'>Select  license from which you want to continue</h1>
                            </div>
                            <div className='flex-1'>
                                <button className='float-right text-xs mt-1 bg-indigo-500 hover:bg-indigo-600 px-2 py-1 text-white rounded leading-5 transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl shadow-md' onClick={handleList} >Search By Holding</button>
                                <button className='float-right mr-2 text-xs mt-1 bg-indigo-500 hover:bg-indigo-600 px-2 py-1 text-white rounded leading-5 transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl shadow-md' onClick={showTradeInput} >Trade License </button>
                            </div>
                        </div>

                        <table class="table-auto text-slate-700 w-full mx-auto border mt-4 ">
                            <thead>
                                <tr className="bg-violet-200 text-gray-600 text-xs h-8 hover:bg-violet-200 uppercase">
                                    <th>Trade License No.</th>
                                    <th>Holding No.</th>
                                    <th>Owner Name</th>
                                    <th>License Issue Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {tradeLicenseList?.map((items) => (
                                    <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                        <td className={`${labelStyle}`}>{items?.license_no || 'N/A'}</td>
                                        <td className={`${labelStyle}`} >{items?.holding_no || 'N/A'}</td>
                                        <td className={`${labelStyle}`}>{items?.applicant_name || 'N/A'}</td>
                                        <td className={`${labelStyle}`}>{items?.valid_from || 'N/A'}</td>
                                        <td><button type='button' className='bg-indigo-500 px-2 text-xs rounded leading-5 text-white' onClick={() => getLicenseData(items?.license_no)} >Select</button></td>
                                    </tr>
                                ))}


                            </tbody>
                        </table>

                    </div>

                    {/* enter holding no. */}
                    <div className={`${licenseList}  p-2 w-11/12 mx-auto`}>
                        <div className='flex flex-row  ml-3'>
                            <h1 className={`${labelStyle} lg:text-center md:text-center ml-6 `}>Enter Holding No.</h1>
                            <input name='holdingField' className={`ml-2 h-6 md:h-8 w-[10rem] md:w-[13rem] mt-4 bg-white rounded-l leading-5 shadow-md   text-xs px-2`} placeholder='Enter Holding No.' />
                            <button type='button' className=' text-xs mt-4 bg-indigo-500 px-2 h-8  rounded-r leading-5 text-white shadow-md ' onClick={searchHolding}>Find</button>
                        </div>
                        <div className={`${searchByHoldingList} mt-4`}>
                            <table class="table-auto text-slate-700 w-full mx-auto border ">
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
                                    {tradeLicenseListByHolding?.map((items) => (
                                        <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '> <td className={`${labelStyle}`}>{items?.license_no || 'N/A'}</td>
                                            <td className={`${labelStyle}`} >{items?.holding_no || 'N/A'}</td>
                                            <td className={`${labelStyle}`}>{items?.applicant_name || 'N/A'}</td>
                                            <td className={`${labelStyle}`}>{items?.valid_from || 'N/A'}</td>
                                            <td><button type='button' className='text-xs bg-indigo-500 px-2 rounded leading-5 text-white' onClick={() => getLicenseData(items?.license_no
                                            )} >Select</button></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {/* <button className='float-right text-xs mt-2 mr-7 bg-indigo-500 hover:bg-indigo-600 px-4 py-1 text-white rounded leading-5' onClick={searchLicenseNo}>Search</button> */}
                        </div>

                    </div>


                    {(searchTradeLicense == null || searchTradeLicense == undefined || searchTradeLicense == '') && tradeInput == false ? '' :
                        <div className=''>
                            <div class={` flex flex-wrap flex-row w-7/12 ml-14 bg-violet-200 p-6 border-4 border-gray-200`}>
                                <div class="flex-shrink max-w-full px-4 w-full lg:w-1/3">
                                    <p className={` -ml-8 text-md lg:text-center md:text-center`}> Trade License No.</p>
                                </div>
                                <div class="flex flex-row max-w-full px-4 w-2/12 lg:w-1/3 ml-10 ">
                                    <input type="text" name='tradeLicenseNo' value={searchTradeLicense} className={`h-6 md:h-8 w-[12rem] md:w-[16rem] lg:w-[16rem] bg-white rounded-l leading-5 shadow-md text-xs px-2 -ml-20 bg-gray-50`} placeholder='Enter Trade License No.' onChange={(e) => setsearchTradeLicense(e.target.value)}
                                    />
                                </div>
                                <div className=''>
                                    <button type='button' className='bg-indigo-500 px-4 h-8 rounded leading-5 text-white text-sm' onClick={confirmTradeLicense}>Confirm</button>
                                </div>
                            </div>
                        </div>

                    }

                </div>
            </div>
        </>
    )
}

export default FindTradeLicense