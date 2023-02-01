import axios from 'axios';
import { useFormik } from 'formik';
import { useEffect, useState } from 'react'
import AdvertisementApiList from '../../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../../Compnents/ApiHeader';
import SelfAdvrtInformationScreen from '../../SelfAdvertisement/SelfAdvrtInformationScreen';

function HoardingForm1(props) {

    const { api_getAdvertMasterData, api_getWardList } = AdvertisementApiList()


    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600"
    let inputStyle = "text-xs rounded-md shadow-md px-1.5 py-1 w-[10rem] md:w-[13rem] h-6 md:h-8  mt-5 -ml-2 "

    const [masterData, setmasterData] = useState()
    const [wardList, setwardList] = useState()

    const initialValues = {
        ulb: '',
        district: '',
        city: '',
        wardNo: '',
        zone: '',
        permitNumber: '',
        roadStreetAddress: '',
        dateGranted: '',
        applicationNo: '',
        permitIssueDate: '',
        permitExpireDate: '',
        accountNo: '',
        bankName: '',
        ifscCode: '',
        totalFeeCharged: '',

    }

    const formik = useFormik({
        initialValues: initialValues,
        // enableReinitialize: true,
        onSubmit: values => {
            alert(JSON.stringify(values, null, 2));
            console.log("hoarding1...1", values)
            // props.collectFormDataFun('hoarding1', values, reviewIdName)
            props.collectFormDataFun('hoarding1', values)
            props?.nextFun(1)
        },
    });

    const handleChange = (e) => {
        let name = e.target.name
        let value = e.target.value

        { name == 'ulb' && getMasterDataFun(value) }
        // { name == 'ulb' && getWardListFun(value) }
        { name == 'ulb' && setstoreUlbValue(value) }
    }

    ///////////{*** GETTING MASTER DATA***}/////////
    const getMasterDataFun = (ulbId) => {
        const requestBody = {
            // // ulbId: ulbId,
            ulbId: 1,
        }
        axios.post(`${api_getAdvertMasterData}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('master data for hoarding', response)
                setmasterData(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }

    ///////////{*** GETTING WARD LIST***}/////////
    useEffect(() => {
        getWardListFun()
    }, [])
    const getWardListFun = (ulbId) => {
        const requestBody = {
            // ulbId: ulbId,
            ulbId: 2,
        }
        axios.post('http://192.168.0.16:8000/api/workflow/getWardByUlb', requestBody, ApiHeader())
            .then(function (response) {
                console.log('ward list', response)
                setwardList(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }
    console.log("ward master data...1", wardList)


    return (
        <>
            <div>
                <form onSubmit={formik.handleSubmit} onChange={handleChange}>
                    <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto '>
                        <div className='col-span-8'>
                            <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4  container  mx-auto pb-8 p-2 mt-3'>
                                {/* DETAILS */}
                                <div className='col-span-6 p-1 h-72 border border-dashed border-violet-800'>
                                    {/* <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
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
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.ulb && formik.errors.ulb ? formik.errors.ulb : null}</p>
                                        </div>
                                    </div> */}
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle} `}> District <span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='district' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.district}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>City<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='city' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.city}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Ward No.<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <select {...formik.getFieldProps('wardNo')} className={`${inputStyle} bg-white`} >
                                                <option>select </option>
                                                {wardList?.map((items) => (
                                                    <option value={items?.id}>{items?.ward_name}</option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Zone<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='zone' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.zone}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Permit Number<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='permitNumber' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.permitNumber}
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div className='col-span-6 p-1 h-72 border border-dashed border-violet-800'>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Road Street/Address<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='roadStreetAddress' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.roadStreetAddress}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Date Granted<span className='text-red-600'>*</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="date" name='dateGranted' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.dateGranted}
                                            />
                                        </div>
                                    </div>

                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Application No.<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="text" name='applicationNo' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.applicationNo}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Permit Issue Date<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="date" name='permitIssueDate' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.permitIssueDate}
                                            />
                                        </div>
                                    </div>
                                    <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                        <div className='col-span-1'>
                                            <p className={`${labelStyle}`}>Permit Expired Date<span className='text-red-600'> *</span></p>
                                        </div>
                                        <div className='col-span-2'>
                                            <input type="date" name='permitExpireDate' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.permitExpireDate}
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* OTHER INFORMATION */}

                            <h1 className='px-5  bg-white '>Other Information</h1>

                            <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3  container  mx-auto -mt-6 pb-8 p-2 border border-dashed border-violet-800 '>

                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Account No.<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='accountNo' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.accountNo}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Bank Name<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='bankName' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.bankName}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>IFSC code<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='ifscCode' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.ifscCode}
                                        />
                                    </div>
                                </div>
                                <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                                    <div className='col-span-1'>
                                        <p className={`${labelStyle}`}>Total fees charged<span className='text-red-600'> *</span></p>
                                    </div>
                                    <div className='col-span-2'>
                                        <input type="text" name='totalFeeCharged' placeholder='' className={`${inputStyle}`}
                                            onChange={formik.handleChange}
                                            value={formik.values.totalFeeCharged}
                                        />
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
            </div>
        </>
    )
}

export default HoardingForm1
