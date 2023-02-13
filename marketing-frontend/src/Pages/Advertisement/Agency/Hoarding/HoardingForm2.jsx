import axios from 'axios';
import { useFormik } from 'formik';
import { useEffect, useState } from 'react';
import AdvertisementApiList from '../../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../../Compnents/ApiHeader';
import SelfAdvrtInformationScreen from '../../SelfAdvertisement/SelfAdvrtInformationScreen';

function HoardingForm2(props) {

    const { setFormIndex, showLoader, collectFormDataFun, toastFun } = props?.values
    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600"
    let inputStyle = "text-xs rounded-md shadow-md px-1.5 py-1 w-[10rem] md:w-[13rem] h-6 md:h-8  mt-5 -ml-2 "

    const { api_getHoardingTypologyList } = AdvertisementApiList()


    const [typology, settypology] = useState()
    const initialValues = {
        checked: '',
    }

    const formik = useFormik({
        initialValues: initialValues,
        onSubmit: values => {
            alert(JSON.stringify(values, null, 2));
            console.log("hoarding2", values)
            // props.collectFormDataFun('hoarding2', values, reviewIdName)
            collectFormDataFun('hoarding2', values)
            setFormIndex(3)
        },
    });

    ///////////{***get typology list***}/////////
    useEffect(() => {
        getTypologyList()
    }, [])
    const getTypologyList = () => {
        showLoader(true);
        const requestBody = {
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getHoardingTypologyList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('typlology..1', response)
                settypology(response.data.data)
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
    console.log("Typology list ..2", typology?.typology
    )
    return (
        <>
            <div className='absolute w-full top-4 '>
                <div className='w-[64rem] mb-2'>
                    {/* <h1 className='px-5 bg-white text-xl p-2 '>Typology</h1> */}
                </div>
                <form onSubmit={formik.handleSubmit} >

                    <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto  '>
                        <div className='col-span-8 h-[48rem] overflow-y-auto scroll-m-1'>

                            {/*Type A */}
                            {typology?.typology?.map((data) => (
                                <>
                                    <h1 className='px-5 bg-white '>{data?.Type}</h1>
                                    <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3  container  mx-auto -mt-6 pb-8 p-2 border border-dashed border-violet-800 '>
                                        {data?.data?.map((data) => (
                                            <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3  container  mx-auto '>
                                                <div className='col-span-2'>
                                                    <p className={`mt-6 font-bold text-sm text-gray-600 ml-6`}>{data?.subtype}.<span className='font-normal'> {data?.descriptions}</span></p>
                                                </div>
                                                <div className='col-span-1'>
                                                    <input type="radio" name='checked' placeholder='' className={`h-5 w-5 text-gray-600 mt-6 ml-2 `}
                                                        onChange={formik.handleChange}
                                                        value={data?.id}
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </>
                            ))}
                            <div className='text-left'>
                                <button type="button" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => setFormIndex(1)}>back</button>
                            </div>
                            <div className='float-right p-2  -mt-14'>
                                <button type="submit" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0" >Save & Next</button>
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

export default HoardingForm2