import React, { useState } from 'react'
// import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
// import ApiHeader from '../../../../src/Compnents/ApiHeader'


function FindTradeLicense(props) {

    const [licenseList, setlicenseList] = useState('hidden')
    const [findByHolding, setfindByHolding] = useState('hidden')
    const [searchByHoldingList, setsearchByHoldingList] = useState('hidden')
    const [searchTradeLicense, setsearchTradeLicense] = useState()


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


    const handleList = () => {
        licenseList == 'hidden' ? setlicenseList('') : setlicenseList('hidden')
    }
    const searchHolding = () => {
        setsearchByHoldingList('');
    }

    const searchLicenseNo = () => {
        setfindByHolding('')
    }
    const getLicenseData = (licenseId) => {
        console.log("license Id", licenseId)
        setsearchTradeLicense(licenseId)
    }

    const confirmTradeLicense = () => {
        props.collectDataFun('licenseDataById', searchTradeLicense)
        props.closeFun(false)
    }
    console.log("trade license id in state...", searchTradeLicense)
    console.log("confirm trade license...", searchTradeLicense)

    return (
        <>

            <div className='pb-14 z-20 bg-gray-200'>
                {/* trade license list */}
                <div className={` mt-8  p-2 w-11/12 mx-auto pb-12`}>
                    <h1 className='ml-6 p-2 font-semibold text-gray-700'>Your Licenses</h1>
                    <h1 className='ml-8 -mt-2 font-semibold text-gray-500 text-xs'>Select your license from which you want to continue</h1>
                    <table class="table-auto text-slate-700 w-11/12 mx-auto border mt-4 ">
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
                            {dummyList?.map((items) => (
                                <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                    <td className={`${labelStyle}`}>{items?.tradeLicenseNo}</td>
                                    <td className={`${labelStyle}`} >{items?.holdingNo}</td>
                                    <td className={`${labelStyle}`}>{items?.ownerName}</td>
                                    <td className={`${labelStyle}`}>{items?.licenseIssueDate}</td>
                                    <td><button type='button' className='bg-indigo-500 px-2 rounded leading-5 text-white' onClick={() => getLicenseData(items?.tradeLicenseNo)} >select</button></td>
                                </tr>
                            ))}


                        </tbody>
                    </table>
                    <button className='float-right text-xs mt-2 mr-7 bg-indigo-500 hover:bg-indigo-600 px-2 py-1 text-white rounded leading-5' onClick={handleList} >Not In List</button>
                </div>

                {/* enter holding no. */}
                <div className={`${licenseList}  p-2 w-11/12 mx-auto`}>
                    <div className='flex flex-row  ml-3'>
                        <h1 className={`${labelStyle} lg:text-center md:text-center ml-6 `}>Enter Holding No.</h1>
                        <input name='holdingField' className={`ml-2 h-6 md:h-8 w-[10rem] md:w-[13rem] mt-4 bg-white rounded-l leading-5 shadow-md   text-xs px-2`} placeholder='Enter Holding No.' />
                        <button type='button' className=' text-xs mt-4 bg-indigo-500 px-2 h-8  rounded-r leading-5 text-white' onClick={searchHolding}>Find</button>
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
                                        <td><button type='button' className='bg-indigo-500 px-2 rounded leading-5 text-white' onClick={() => getLicenseData(items?.tradeLicenseNo)} >select</button></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <button className='float-right text-xs mt-2 mr-7 bg-indigo-500 hover:bg-indigo-600 px-4 py-1 text-white rounded leading-5' onClick={searchLicenseNo}>Search</button>
                    </div>

                </div>


                {searchTradeLicense == null || searchTradeLicense == undefined || searchTradeLicense == ''  ? '' :
                    <div class={` flex flex-wrap flex-row w-7/12 ml-14 `}>
                        <div class="flex-shrink max-w-full px-4 w-full lg:w-1/3">
                            <p className={`${labelStyle} lg:text-center md:text-center`}> Trade License No<span className='text-red-600'> *</span></p>
                        </div>
                        <div class="flex flex-row max-w-full px-4 w-2/12 lg:w-1/3 ml-8 ">
                            <input type="text" name='tradeLicenseNo' value={searchTradeLicense} className={`h-6 md:h-8 w-[10rem] md:w-[13rem] mt-4 bg-white rounded-l leading-5 shadow-md text-xs px-2 -ml-20 bg-gray-50`} placeholder='Enter Trade License No.' onChange={(e) => setsearchTradeLicense(e.target.value)}
                            />
                        </div>
                        <div className='mt-4'>
                            <button type='button' className='bg-indigo-500 px-2 h-8 rounded leading-5 text-white text-xs' onClick={confirmTradeLicense}>confirm</button>
                        </div>
                    </div>
                }
            </div>

        </>
    )
}

export default FindTradeLicense