import { useFormik } from 'formik';
import React, { useState } from 'react'
import SelfAdvrtInformationScreen from '../SelfAdvertisement/SelfAdvrtInformationScreen';


function AgencyDirectorDetail(props) {
    // const [directorData, setdirectorData] = useState([])
    const [directorData, setdirectorData] = useState([])
    const [formToggleStatus, setformToggleStatus] = useState(true)
    const [AddMore, setAddMore] = useState(false);
    const [directorDataVisibility, setdirectorDataVisibility] = useState(false)



    // const [toggleCalcParams, settoggleCalcParams] = useState(true)


    let labelStyle = " text-sm text-gray-600 px-6"
    let inputStyle = "text-xs rounded-md shadow-md px-1.5 py-1 w-[10rem] md:w-[11rem] h-6 md:h-8  mt-2 mb-2 -ml-2"


    const formik = useFormik({
        initialValues: {
            name: '',
            mobile: '',
            email: '',

        },
        onSubmit: values => {
            // alert(JSON.stringify(values, null, 2));
            // console.log("agencyDirector", values)
            // props.collectFormDataFun('agencyDirector', values)
            // setAddMore(true);


            setformToggleStatus(false)
            setdirectorDataVisibility(true)
            setdirectorData([...directorData, values]);
            // console.log('myindexkey ', indexkey);
            setAddMore(true);
            // props?.nextFun(2)
            formik.resetForm();
        },
    });


    const handleMultipleSubmit = () => {
        console.log('Final submission of the form ', directorData)
        if (directorData.length == 0) {
            alert('Atleast One Owner is required !');
            showLoader(false);
        } else {
            props.collectFormDataFun('agencyDirector', directorData, 0) //sending BasicDetails data to parent to store all form data at one container
            props?.nextFun(2) //forwarding to next form level
            // setTimeout(() => {
            //     props?.nextFun(2) 
            //     currentStepFun(4)
            //     colorCodeFun(3)

            //     showLoader(false)
            // }, 500);


        }
    }

    console.log("director data", directorData)


    const handleRemove = (index) => {
        // alert('remove filter', key);
        console.log('key ', index);
        setdirectorData(current =>
            current.filter(ct => {
                if (current.indexOf(ct) == index) {
                    console.log('value matched at ', index)
                } else {
                    // alert('current index of ct ',current.indexOf(ct))
                    return ct
                }
            }),
        );
    }


    const handleOwnerShow = () => {
        // alert(formToggleStatus);
        { formToggleStatus == false ? setformToggleStatus(true) : setformToggleStatus(false) }
    }



    return (
        <>
            {/* DIRECTORS INFORMATION */}
            <div className=''>
                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto'>
                    <div className='col-span-8  p-2 border border-dashed border-violet-800 h-auto'>
                        <h1 className='text-md font-semibold text-gray-700 ml-2 bg-white p-2'>DIRECTOR INFORMATION</h1>
                        <form onSubmit={formik.handleSubmit} >
                            <div className={`  mt-2 ${formToggleStatus ? ' md:h-[96px]' : 'bg-white'}`}>

                                <>
                                    <div className={`${formToggleStatus ? '' : 'hidden'}`}>
                                        <div class="max-w-2xl mx-auto">
                                            <div class="flex flex-col">
                                                <div class="overflow-x-auto shadow-md ">
                                                    <div class="inline-block min-w-full align-middle">
                                                        <div class="overflow-hidden ">
                                                            <table class="min-w-full divide-y divide-gray-200 table-fixed ">
                                                                <thead class="bg-white">
                                                                    <tr>
                                                                        <th scope="col" class="py-3 px-6 text-xs font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                                                                            Director Name
                                                                        </th>
                                                                        <th scope="col" class="py-3 px-6 text-xs font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                                                                            Mobile No.
                                                                        </th>
                                                                        <th scope="col" class="py-3 px-6 text-xs font-medium tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                                                                            Email
                                                                        </th>

                                                                    </tr>
                                                                </thead>
                                                                <tbody class=" divide-y divide-gray-200 ">
                                                                    <tr class=" dark:hover:bg-gray-700">

                                                                        <td className={`${labelStyle}`}>
                                                                            <input type="text" name='name' placeholder='' className={`${inputStyle}`}
                                                                                onChange={formik.handleChange}
                                                                                value={formik.values.name}
                                                                            />
                                                                        </td>
                                                                        <td className={`${labelStyle}`}>
                                                                            <input type="text" name='mobile' placeholder='' className={`${inputStyle}`}
                                                                                onChange={formik.handleChange}
                                                                                value={formik.values.mobile}
                                                                            />
                                                                        </td>
                                                                        <td className={`${labelStyle}`}>
                                                                            <input type="text" name='email' placeholder='' className={`${inputStyle}`}
                                                                                onChange={formik.handleChange}
                                                                                value={formik.values.email}
                                                                            />
                                                                        </td>

                                                                    </tr>

                                                                </tbody>

                                                            </table>
                                                            <div className=''>
                                                                <button type="submit" onClick={() => setAddMore(true)} class="float-right mr-2 text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">Confirm 
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </>
                            </div>

                            <div className="grid grid-cols-3 w-full px-10 mt-16">
                                <div className=' text-left'>
                                    <button type="button" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(2)}>back</button>
                                </div>
                                <div className='text-center'>
                                    <button type="button" class={`${AddMore ? '' : 'hidden'} text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0`} onClick={handleOwnerShow}> Add Director </button>
                                </div>
                                <div className=' text-right'>
                                    <button type={directorData.length === 0 ? 'submit' : 'button'} onClick={handleMultipleSubmit} class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next </button>

                                </div>
                            </div>
                        </form>

                        {/* table */}
                        <div className={`${directorDataVisibility ? '' : 'hidden'}  h-56 mr-8 `}>
                            <table class="table-auto text-slate-700 w-full mx-auto ">
                                <thead>
                                    <tr className="bg-white text-gray-600 text-xs h-8 hover:bg-violet-200 uppercase">
                                        <th>Director Name</th>
                                        <th>Director Mobile No.</th>
                                        <th>Director Email</th>
                                        <th>Action.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {
                                        directorData?.map((items, index) => (
                                            <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                                <td>
                                                    <span>{items.name} </span>
                                                </td>
                                                <td>
                                                    <span>{items.mobile} </span>
                                                </td>
                                                <td>
                                                    <span>{items.email} </span>

                                                    {/* <input type="text" name="email[]" className=' text-center' value={items.email} readOnly /> */}
                                                </td>
                                                <td>
                                                    <button type='button' className='text-red-400' onClick={() => handleRemove(index)}>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-red-400">
                                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" />
                                                        </svg>

                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            </table>
                        </div >
                    </div>
                    <div className='col-span-4'>
                        <div className='-mt-20'>
                            <SelfAdvrtInformationScreen />
                        </div>
                    </div>
                </div>
            </div>

        </>

    )
}

export default AgencyDirectorDetail