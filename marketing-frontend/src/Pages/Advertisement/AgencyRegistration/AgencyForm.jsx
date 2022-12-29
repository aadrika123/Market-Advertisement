import { useFormik } from 'formik';
import React from 'react'

function AgencyForm(props) {
    const formik = useFormik({
        initialValues: {
            entityType: '',
            entityName: '',
            address: '',
            mobileNo: '',
            officialTelephone: '',
            fax: '',
            email: '',
            panNo: '',
            gstNo: '',
            isBlackListed: '',
            isPendingCourtCase: '',
            isPendingAmount: '',
            directorName: '',
            directorMobile: '',
            directorEmail: '',

        },
        onSubmit: values => {
            alert(JSON.stringify(values, null, 2));
            console.log("Private Land", values)
            props?.nextFun(1)
        },
    });

    let labelStyle = "mt-6 pl-1 text-md text-gray-600"
    let inputStyle = "border shadow-md px-1.5 py-1 rounded-md w-[13rem]"
    return (
        <>
            <form onSubmit={formik.handleSubmit}>
                <div class="container mx-auto bg-white rounded-lg  shadow-md p-16 mt-3 overflow-y-scroll mb-28 ">
                    <div className=' grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 -mt-8 '>
                        <div className='flex flex-row'>
                            <img src='https://cdn-icons-png.flaticon.com/512/2518/2518048.png' className='h-6 mt-2 opacity-80' />
                            <h1 className='text-2xl ml-2 text-gray-600 font-sans '>Agency Register </h1>
                        </div>
                        <h1 className='text-sm ml-9 text-gray-400 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1>
                    </div>
                    <div className='bg-gray-50 p-6 rounded-lg mt-2'>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-1 ">
                            <div className='px-1'>
                                <p className={`${labelStyle}`}> Entity Type <span className='text-red-600'> *</span></p>
                                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('entityType')} >
                                    <option>select one</option>
                                    <option>1</option>
                                    <option>1</option>
                                    <option>1</option>
                                </select>
                            </div>

                            <div className='px-1'>
                                <p className={`${labelStyle}`}>Entity Name <span className='text-red-600'> *</span></p>
                                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                    onChange={formik.handleChange}
                                    value={formik.values.entityName}
                                />
                            </div>
                            <div className='px-1'>
                                <p className={`${labelStyle}`}>Address <span className='text-red-600'> *</span></p>
                                <input type="text" name='address' placeholder='' className={`${inputStyle}`}
                                    onChange={formik.handleChange}
                                    value={formik.values.address}
                                />
                            </div>
                            <div className='px-1'>
                                <p className={`${labelStyle}`}>PAN No.<span className='text-red-600'> *</span></p>
                                <input type="text" name='panNo' placeholder='' className={`${inputStyle} `}
                                    onChange={formik.handleChange}
                                    value={formik.values.panNo}
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 ">
                            <div className='px-1'>
                                <p className={`${labelStyle}`}>Mobile No<span className='text-red-600'> *</span></p>
                                <input type="text" name='mobileNo' placeholder='' className={`${inputStyle}`}
                                    onChange={formik.handleChange}
                                    value={formik.values.mobileNo}
                                />
                            </div>

                            <div className='px-1'>
                                <p className={`${labelStyle}`}>Official Telephone<span className='text-red-600'> *</span></p>
                                <input type="text" name='officialTelephone' placeholder='' className={`${inputStyle}`}
                                    onChange={formik.handleChange}
                                    value={formik.values.officialTelephone}
                                />
                            </div>
                            <div className='px-1'>
                                <p className={`${labelStyle}`}>FAX<span className='text-red-600'> *</span></p>
                                <input type="text" name='fax' placeholder='' className={`${inputStyle}`}
                                    onChange={formik.handleChange}
                                    value={formik.values.fax}
                                />
                            </div>
                            <div className='px-1'>
                                <p className={`${labelStyle}`}>GST No. <span className='text-red-600'> *</span></p>
                                <input type="text" name='gstNo' placeholder='' className={`${inputStyle}`}
                                    onChange={formik.handleChange}
                                    value={formik.values.gstNo}
                                />
                            </div>
                        </div>

                        {/* OTHER INFORMATION */}
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-3 ">
                            <div className='px-1 flex'>
                                <input type="checkbox" name='isBlackListed' placeholder='' className={`flex mt-7 ml-1`}
                                    onChange={formik.handleChange}
                                    value={formik.values.isBlackListed}
                                />
                                <p className={`flex ${labelStyle}`}>Blacklisted in RMC<span className='text-red-600'> *</span></p>

                            </div>
                            <div className='px-1 flex'>
                                <input type="checkbox" name='isPendingCourtCase' placeholder='' className={`flex mt-7 ml-1 `}
                                    onChange={formik.handleChange}
                                    value={formik.values.isPendingCourtCase}
                                />
                                <p className={`flex ${labelStyle}`}>Pending Court Case<span className='text-red-600'> *</span></p>

                            </div>
                            <div className='px-1 flex'>
                                <input type="checkbox" name='isPendingAmount' placeholder='' className={`flex mt-7 ml-1`}
                                    onChange={formik.handleChange}
                                    value={formik.values.isPendingAmount}
                                />
                                <p className={`flex ${labelStyle}`}>Pending Amount (If any)<span className='text-red-600'> *</span></p>

                            </div>
                        </div>

                        {/* DIRECTORS INFORMATION */}

                        <div class="overflow-x-auto relative mt-8 mx-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">
                                            Director Name
                                        </th>
                                        <th scope="col" class="py-3 px-6">
                                            Mobile
                                        </th>
                                        <th scope="col" class="py-3 px-6">
                                            Email
                                        </th>
                                        <th scope="col" class="py-3 px-6">
                                           <button className='bg-green-400 py-1 px-4 rounded-sm'>Add</button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <input type="text" name='directorName' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.directorName}
                                            />
                                        </th>
                                        <td class="py-4 px-6">
                                            <input type="text" name='directorMobile' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.directorMobile}
                                            />
                                        </td>
                                        <td class="py-4 px-6">
                                            <input type="text" name='directorEmail' placeholder='' className={`${inputStyle}`}
                                                onChange={formik.handleChange}
                                                value={formik.values.directorEmail}
                                            />
                                        </td>
                                        <td class="py-4 px-6">
                                           
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div className=' '>
                        <div className='float-right p-4'>
                            <button type='submit' className='bg-green-600 w-36 h-9 font-semibold shadow-md text-gray-100 hover:bg-green-600' >SAVE & NEXT</button>
                        </div>
                    </div>
                </div>
            </form>
        </>
    )
}

export default AgencyForm