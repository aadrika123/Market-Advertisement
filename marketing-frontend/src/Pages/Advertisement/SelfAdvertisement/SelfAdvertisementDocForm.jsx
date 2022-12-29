import { useFormik } from 'formik';
import React, { useState } from 'react'
// import * as yup from 'yup'


function SelfAdvertisementDocForm(props) {


    let labelStyle = "mt-4 pl-1 text-sm text-gray-600"
    let inputStyle = "border shadow-md px-1.5 py-1 rounded-lg w-48"

    const [adharDocFile, setadharDocFile] = useState()
    const [adharDocFilePreview, setadharDocFilePreview] = useState()
    const [tradeLicenseDocFile, settradeLicenseDocFile] = useState()
    const [tradeLicenseDocFilePreview, settradeLicenseDocFilePreview] = useState()
    const [photoWithGpsDocFile, setphotoWithGpsDocFile] = useState()
    const [photoWithGpsDocFilePreview, setphotoWithGpsDocFilePreview] = useState()
    const [holdingNoDocFile, setholdingNoDocFile] = useState()
    const [holdingNoDocFilePreview, setholdingNoDocFilePreview] = useState()
    const [gstDocPhotoDocFile, setgstDocPhotoDocFile] = useState()
    const [gstDocPhotoDocFilePreview, setgstDocPhotoDocFilePreview] = useState()
    const [proceedingPhoto1DocFile, setproceedingPhoto1DocFile] = useState()
    const [proceedingPhoto1DocFilePreview, setproceedingPhoto1DocFilePreview] = useState()
    const [proceedingPhoto2DocFile, setproceedingPhoto2DocFile] = useState()
    const [proceedingPhoto2FilePreview, setproceedingPhoto2FilePreview] = useState()
    const [proceedingPhoto3DocFile, setproceedingPhoto3DocFile] = useState()
    const [proceedingPhoto3FilePreview, setproceedingPhoto3FilePreview] = useState()
    const [uploadExtraDoc1File, setuploadExtraDoc1File] = useState()
    const [uploadExtraDoc1FilePreview, setuploadExtraDoc1FilePreview] = useState()
    const [uploadExtraDoc2File, setuploadExtraDoc2File] = useState()
    const [uploadExtraDoc2FilePreview, setuploadExtraDoc2FilePreview] = useState()


    // const validationSchema = yup.object({
    //     aadharDoc: yup.mixed(),
    //     tradeLicenseDoc: yup.mixed(),
    //     photoWithGps: yup.mixed(),
    //     holdingNoDoc: yup.mixed(),
    //     gstDocPhoto: yup.mixed(),
    //     proceedingPhoto1: yup.mixed(),
    //     proceedingPhoto2: yup.mixed(),
    //     proceedingPhoto3: yup.mixed(),
    //     uploadExtraDoc1: yup.mixed(),
    //     uploadExtraDoc2: yup.mixed(),
    // })

    const formik = useFormik({
        initialValues: {
            aadharDoc: '',
            tradeLicenseDoc: '',
            photoWithGps: '',
            holdingNoDoc: '',
            gstDocPhoto: '',
            proceedingPhoto1: '',
            proceedingPhoto2: '',
            proceedingPhoto3: '',
            uploadExtraDoc1: '',
            uploadExtraDoc2: ''
        },
        onSubmit: values => {
            alert(JSON.stringify(values, null, 2));
            console.log("self Advertisement", values)


        },
        // validationSchema
    });


    const handleChange = (e) => {
        let name = e.target.name
        if (name == 'aadharDoc') {
            let file = e.target.files[0]
            setadharDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setadharDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'tradeLicenseDoc') {
            let file = e.target.files[0]
            settradeLicenseDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                settradeLicenseDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'holdingNoDoc') {
            let file = e.target.files[0]
            setholdingNoDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setholdingNoDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'photoWithGps') {
            let file = e.target.files[0]
            setphotoWithGpsDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setphotoWithGpsDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'gstDocPhoto') {
            let file = e.target.files[0]
            setgstDocPhotoDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setgstDocPhotoDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'proceedingPhoto1') {
            let file = e.target.files[0]
            setproceedingPhoto1DocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setproceedingPhoto1DocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'proceedingPhoto2') {
            let file = e.target.files[0]
            setproceedingPhoto2DocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setproceedingPhoto2FilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'proceedingPhoto3') {
            let file = e.target.files[0]
            setproceedingPhoto3DocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setproceedingPhoto3FilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'uploadExtraDoc1') {
            let file = e.target.files[0]
            setuploadExtraDoc1File(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setuploadExtraDoc1FilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'uploadExtraDoc2') {
            let file = e.target.files[0]
            setuploadExtraDoc2File(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setuploadExtraDoc2FilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }



    }



    return (
        <>
            {/* <form onSubmit={formik.handleSubmit}>
                <div className='  grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 -mt-[54rem] '>
                    <h1 className='text-2xl font-bold text-gray-600 text-center'>UPLOAD DOCUMENT</h1>
                </div>
                <div className="container mx-auto bg-white rounded-lg  shadow-md p-8 mt-3 overflow-y-scroll mb-28 ">
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-1">
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Aadhar Document  <span className='text-red-600'> *</span></p>
                            <input type="file" name='aadharDoc' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.aadharDoc}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload  Trade License Document<span className='text-red-600'> *</span></p>
                            <input type="file" name='tradeLicenseDoc' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.tradeLicenseDoc}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Photograph with GPS<span className='text-red-600'> *</span></p>
                            <input type="file" name='photoWithGps' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.photoWithGps}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Holding No<span className='text-red-600'> *</span></p>
                            <input type="file" name='holdingNoDoc' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.holdingNoDoc}
                            />
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload GST Document Photo<span className='text-red-600'> *</span></p>
                            <input type="file" name='gstDocPhoto' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.gstDocPhoto}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Proceeding1 Photo  <span className='text-red-600'> *</span></p>
                            <input type="file" name='proceedingPhoto1' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.proceedingPhoto1}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Proceeding2 Photo<span className='text-red-600'> *</span></p>
                            <input type="file" name='proceedingPhoto2' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.proceedingPhoto2}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Proceeding3 Photo<span className='text-red-600'> *</span></p>
                            <input type="file" name='proceedingPhoto3' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.proceedingPhoto3}
                            />
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Extra Document1<span className='text-red-600'> *</span></p>
                            <input type="file" name='uploadExtraDoc1' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.uploadExtraDoc1}
                            />
                        </div>
                        <div className='px-1'>
                            <p className={`${labelStyle}`}>Upload Extra Document2<span className='text-red-600'> *</span></p>
                            <input type="file" name='uploadExtraDoc2' placeholder='' className={`${inputStyle}`}
                                onChange={formik.handleChange}
                                value={formik.values.uploadExtraDoc2}
                            />
                        </div>

                    </div>
                    <div className=' '>
                        <div className='t p-4'>
                            <div className='float-left p-4'>
                                <button type='button' onClick={() => props.backFun(1)} className='bg-blue-600 w-36 h-9 font-semibold shadow-md text-gray-100 hover:bg-blue-600' >BACK</button>
                            </div>
                            <div className='float-right p-4'>
                                <button type='submit' className='bg-green-600 w-36 h-9 font-semibold shadow-md text-gray-100 hover:bg-green-600' >SUBMIT</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form> */}

            {/* upload document */}
            <form onSubmit={formik.handleSubmit} onChange={handleChange} encType="multipart/form-data">
                <div className="container mx-auto bg-white overflow-x-auto -mt-[54rem]">
                    <div className=' grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 p-4 '>
                        <div className='flex flex-row'>
                            <img src='https://cdn-icons-png.flaticon.com/512/3039/3039527.png' className='h-6 mt-2 opacity-80' />
                            <h1 className='text-2xl ml-2 text-gray-600 font-sans '>Upload Document</h1>
                        </div>
                        {/* <h1 className='text-sm ml-9 text-gray-400 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1> */}
                    </div>
                    <div className="min-w-screen min-h-screen  flex md:pl-4 p-4 bg-white font-sans overflow-x-auto">
                        <div className="w-full lg:w-4/6 mx-auto">
                            <div className="bg-gray-50 shadow-md rounded my-2">
                                <table className="min-w-max w-full table-auto">
                                    <thead>
                                        <tr className="bg-blue-200 text-gray-600 uppercase text-sm leading-normal">
                                            <th className="py-3 px-6 text-left cursor-pointer" onClick={() => notify('just testing the context data', 'info')}>Image Type</th>
                                            <th className="py-3 px-6 text-left">Upload</th>
                                            <th className="py-3 px-6 text-center">Preview</th>
                                        </tr>
                                    </thead>
                                    <tbody className="text-gray-600 text-sm font-light bg-white">
                                        {/* adhar document */}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium">Aadhar Document</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('aadharDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.aadharDoc && formik.errors.aadharDoc ? formik.errors.aadharDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={adharDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/* Trade License */}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium">Trade License</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('tradeLicenseDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.tradeLicenseDoc && formik.errors.tradeLicenseDoc ? formik.errors.tradeLicenseDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={tradeLicenseDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/* Holding No*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium">Holding No</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('holdingNoDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.holdingNoDoc && formik.errors.holdingNoDoc ? formik.errors.holdingNoDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={holdingNoDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/* Photograph with GPS*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium">Photograph with GPS</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('photoWithGps')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.photoWithGps && formik.errors.photoWithGps ? formik.errors.photoWithGps : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={photoWithGpsDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/*  GST Document*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium"> GST Document</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('gstDocPhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.gstDocPhoto && formik.errors.gstDocPhoto ? formik.errors.gstDocPhoto : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={gstDocPhotoDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/*  Proceeding1 Photo*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium"> Proceeding1 Photo</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('proceedingPhoto1')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto1 && formik.errors.proceedingPhoto1 ? formik.errors.proceedingPhoto1 : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={proceedingPhoto1DocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/*  Proceeding2 Photo*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium"> Proceeding2 Photo</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('proceedingPhoto2')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto2 && formik.errors.proceedingPhoto2 ? formik.errors.proceedingPhoto2 : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={proceedingPhoto2FilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/*  Proceeding3 Photo*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium"> Proceeding3 Photo</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('proceedingPhoto3')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto3 && formik.errors.proceedingPhoto3 ? formik.errors.proceedingPhoto3 : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={proceedingPhoto3FilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/*  Upload Extra Document1*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium">Upload Extra Document1</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('uploadExtraDoc1')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.uploadExtraDoc1 && formik.errors.uploadExtraDoc1 ? formik.errors.uploadExtraDoc1 : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={uploadExtraDoc1FilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>
                                        {/*  Upload Extra Document2*/}
                                        <tr className="border-b border-gray-200 ">
                                            <td className="py-3 px-6 text-left whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                                                        <img alt="rain" className='w-3' />
                                                    </div>
                                                    <span className="font-medium">Upload Extra Document2</span>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('uploadExtraDoc2')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.uploadExtraDoc2 && formik.errors.uploadExtraDoc2 ? formik.errors.uploadExtraDoc2 : null}</span> */}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-3 px-6 text-center">
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <img src={uploadExtraDoc2FilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                                                </div>
                                            </td>
                                        </tr>


                                    </tbody>
                                </table>
                            </div>

                            <div className="grid grid-cols-12 w-full">
                                <div className='md:pl-0 col-span-4'>
                                    <button type="button" className=" px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out" onClick={() => props.backFun(2)}>back</button>
                                </div>
                                <div className='md:px-4 text-center col-span-4'>
                                    <button type='button' className=" px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Upload Document</button>
                                </div>
                                <div className='md:pl-10 text-right col-span-4'>
                                    <button type='submit' className=" px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Submit</button>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </form>

        </>
    )
}

export default SelfAdvertisementDocForm